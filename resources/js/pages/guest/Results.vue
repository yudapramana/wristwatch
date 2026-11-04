<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useEventStore } from '../../stores/EventStore'
import { useResultStore } from '../../stores/ResultStore'
import PublicLayout from '../../layouts/PublicLayout.vue' // sesuaikan path jika berbeda

const route = useRoute()
const router = useRouter()
const eventStore = useEventStore()
const store = useResultStore()
// Status gerbang akses (sama seperti Klasemen): 'unknown' | 'ok' | 'locked' | 'unpublished'
const { access, requiresPassword, isUnlocking, unlockError } = storeToRefs(store)

const selectedId = ref(null)

const groups = computed(() => store.groups)
const selectedGroup = computed(() => groups.value.find((g) => g.id === selectedId.value) || null)
const result = computed(() => (selectedId.value ? store.getResult(selectedId.value) : null))
const isReleased = computed(() => selectedGroup.value?.status === 'released')

// Error hanya ditampilkan bila tidak ada data yang bisa ditampilkan
const errorMessage = computed(() => {
    if (!groups.value.length) return store.groupsError
    return result.value ? '' : store.resultError
})

/* ======================
 |  GERBANG PASSWORD (sama dengan Standings)
 ====================== */
// Nomor WhatsApp admin untuk meminta password (format 62812...). Kosongkan untuk menyembunyikan tombol.
const ADMIN_WHATSAPP = ''
const password = ref('')
const showPassword = ref(false)
const localError = ref('')
const gateError = computed(() => localError.value || unlockError.value)

const waLink = computed(() => {
    if (!ADMIN_WHATSAPP) return ''
    const text = encodeURIComponent('Halo admin, saya ingin meminta password untuk membuka Hasil Cabang Lomba MTQ.')
    return `https://wa.me/${ADMIN_WHATSAPP}?text=${text}`
})

const initial = (name) =>
    (name || '?').replace(/^(M\.|H\.|Hj\.|Dr\.)\s*/i, '').trim().charAt(0).toUpperCase()

const formatDate = (iso) => {
    if (!iso) return ''
    return new Date(iso).toLocaleString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).replace(/\./g, '.').replace(',', '')
}

const rankClass = (rank) => (rank <= 3 ? `r${rank}` : 'rx')
const isHarapan = (rank) => rank > 3

// Hanya satu babak yang tersimpan di snapshot (Final + Harapan), jadi dropdown berisi satu pilihan
const roundLabel = computed(() => result.value?.round || 'Final')

const select = (id) => {
    selectedId.value = id
    router.replace({ query: { ...route.query, cabang: id } })
}

watch(selectedId, (id) => { if (id) store.fetchResult(id) })

// Pilih cabang aktif setelah daftar cabang termuat (juga dipakai setelah password benar)
const pickGroup = () => {
    if (!groups.value.length) return // terkunci / gagal / kosong
    const fromQuery = Number(route.query.cabang)
    const target =
        groups.value.find((g) => g.id === selectedId.value) ||
        groups.value.find((g) => g.id === fromQuery) ||
        groups.value.find((g) => g.status === 'released') ||
        groups.value[0]
    if (target) {
        selectedId.value = target.id
        store.fetchResult(target.id) // cache dikosongkan saat terkunci; watch tidak terpicu bila id sama
    }
}

const submitPassword = async () => {
    localError.value = ''
    if (!password.value.trim()) {
        localError.value = 'Password wajib diisi.'
        return
    }
    const ok = await store.unlockResults(password.value)
    if (ok) {
        password.value = ''
        pickGroup()
    }
}

onMounted(async () => {
    eventStore.fetchEventData()
    await store.fetchGroups()
    pickGroup()
})

// Coba lagi (tombol di pesan error)
const reload = async () => {
    await store.fetchGroups({ force: true })
    if (selectedId.value) store.fetchResult(selectedId.value, { force: true })
    else pickGroup()
}

// Periksa lagi (status belum dipublikasikan)
const recheck = async () => {
    await store.fetchGroups({ force: true })
    pickGroup()
}
</script>

<template>
  <PublicLayout>
  <div class="res-page">
    <!-- Judul halaman + pilihan babak (logo & menu sudah ada di PublicLayout) -->
    <header class="res-top">
      <div>
        <p class="kicker">Hasil resmi</p>
        <h1>Hasil Cabang Lomba</h1>
        <p class="muted">{{ eventStore.eventData.event_name }}</p>
      </div>

      <div v-if="access === 'ok'" class="top-right">
        <label class="round-pick">
          <span>Pilih Babak</span>
          <select :disabled="!result" aria-label="Pilih babak">
            <option>Babak terbaru · {{ roundLabel }}</option>
          </select>
        </label>
        <button v-if="requiresPassword" type="button" class="btn-ghost" @click="store.lockResults()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>
          Kunci kembali
        </button>
      </div>
    </header>

    <!-- Mengecek akses (hindari kilasan data) -->
    <section v-if="access === 'unknown'" class="gate gate-loading" aria-busy="true" aria-label="Memuat">
      <div class="skel skel-circle"></div>
      <div class="skel skel-line"></div>
      <div class="skel skel-line short"></div>
    </section>

    <!-- Gerbang password -->
    <section v-else-if="access === 'locked'" class="gate">
      <div class="gate-icon">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>
      </div>
      <h2>Hasil Dilindungi</h2>
      <p>Masukkan password untuk melihat hasil cabang lomba.</p>

      <form @submit.prevent="submitPassword">
        <div class="gate-field">
          <input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Password"
            aria-label="Password"
            autocomplete="off"
            :disabled="isUnlocking"
            @input="localError = ''"
          />
          <button type="button" class="gate-eye" @click="showPassword = !showPassword">
            {{ showPassword ? 'Sembunyi' : 'Lihat' }}
          </button>
        </div>
        <p v-if="gateError" class="gate-error" role="alert">{{ gateError }}</p>
        <button type="submit" class="gate-btn" :disabled="isUnlocking">
          {{ isUnlocking ? 'Memeriksa…' : 'Buka Hasil' }}
        </button>
      </form>

      <a v-if="waLink" :href="waLink" target="_blank" rel="noopener" class="gate-wa">Minta password ke admin</a>
    </section>

    <!-- Belum dipublikasikan -->
    <section v-else-if="access === 'unpublished'" class="gate">
      <div class="gate-icon">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
      </div>
      <h2>Hasil Belum Dipublikasikan</h2>
      <p>Hasil akan ditampilkan setelah diumumkan oleh panitia.</p>
      <button type="button" class="gate-btn" @click="recheck">Periksa lagi</button>
    </section>

    <div v-else-if="access === 'ok'" class="res-body">
      <!-- Sidebar cabang -->
      <aside class="side" aria-label="Daftar cabang">
        <div class="side-head">
          <strong>Semua {{ store.meta.total }} Cabang</strong>
          <small>{{ store.meta.released }} dirilis · {{ store.meta.pending }} menunggu</small>
        </div>

        <p v-if="store.isGroupsLoading && !groups.length" class="muted pad">Memuat cabang…</p>

        <ul class="side-list">
          <li v-for="g in groups" :key="g.id">
            <button
              class="side-item"
              :class="{ active: g.id === selectedId }"
              :aria-current="g.id === selectedId ? 'true' : undefined"
              @click="select(g.id)"
            >
              <span class="code">{{ g.code }}</span>
              <span class="gname" :title="g.name">{{ g.name }}</span>
              <span class="chip" :class="g.status">{{ g.status === 'released' ? 'Dirilis' : 'Menunggu' }}</span>
            </button>
          </li>
        </ul>
      </aside>

      <!-- Konten -->
      <main class="main">
        <RouterLink to="/klasemen" class="banner">
          <div class="crown" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8z"/></svg>
          </div>
          <div class="banner-text">
            <p class="kicker light">Klasemen kontingen</p>
            <h2>Klasemen Juara UMUM</h2>
            <p>Lihat perolehan poin seluruh Kontingen dan status rilis {{ result ? 'Babak ' + roundLabel : 'babak terbaru' }}.</p>
          </div>
          <span class="banner-btn">Buka klasemen →</span>
        </RouterLink>

        <section v-if="selectedGroup" class="now">
          <div>
            <p class="kicker dark">Sedang melihat cabang</p>
            <h2>{{ selectedGroup.name }}</h2>
          </div>
          <div v-if="isReleased && result" class="now-meta">
            <span class="pill">Babak {{ roundLabel }}</span>
            <small>
              {{ result.categories.reduce((n, c) => n + c.total, 0) }} {{ selectedGroup.is_team ? 'tim' : 'peserta' }}
              <template v-if="result.published_at"> · Dirilis {{ formatDate(result.published_at) }}</template>
            </small>
          </div>
        </section>

        <p v-if="errorMessage" class="state">
          {{ errorMessage }} <button class="link" @click="reload">Coba lagi</button>
        </p>
        <p v-else-if="selectedGroup && !isReleased" class="state">
          Hasil cabang ini belum dirilis. Halaman akan menampilkan hasil setelah panitia mempublikasikannya.
        </p>
        <p v-else-if="isReleased && !result && store.isResultLoading" class="state">Memuat hasil…</p>

        <div v-if="isReleased && result" class="cols" :class="{ single: result.categories.length === 1 }">
          <section v-for="cat in result.categories" :key="cat.id ?? 'all'" class="col">
            <header class="col-head">
              <h3>{{ cat.name }}</h3>
              <span class="count">{{ cat.total }} {{ cat.is_team ? 'tim' : 'peserta' }}</span>
            </header>

            <p v-if="!cat.ranking.length" class="muted pad">Belum ada peringkat.</p>

            <ol class="rows">
              <li v-for="r in cat.ranking" :key="r.rank" class="row">
                <span class="medal" :class="rankClass(r.rank)">{{ r.rank }}</span>

                <!-- Individu: satu foto. Tim: tumpukan foto anggota -->
                <div v-if="!cat.is_team" class="photo">
                  <img v-if="r.photo_url" :src="r.photo_url" :alt="r.name" loading="lazy" />
                  <span v-else>{{ initial(r.name) }}</span>
                </div>
                <div v-else class="stack" aria-hidden="true">
                  <div v-for="(m, i) in r.members.slice(0, 3)" :key="i" class="photo mini">
                    <img v-if="m.photo_url" :src="m.photo_url" :alt="m.name" loading="lazy" />
                    <span v-else>{{ initial(m.name) }}</span>
                  </div>
                </div>

                <div class="info">
                  <h4>{{ r.name }}</h4>
                  <p v-if="!cat.is_team" class="sub">{{ r.contingent }}</p>
                  <p v-else class="sub">{{ r.members.map((m) => m.name).join(', ') }}</p>
                  <p class="tag">
                    <b :class="{ harapan: isHarapan(r.rank) }">{{ r.medal }}</b>
                    <template v-if="r.participant_number"> <i>·</i> <span>#{{ r.participant_number }}</span></template>
                  </p>
                </div>
              </li>
            </ol>
          </section>
        </div>
      </main>
    </div>
  </div>
  </PublicLayout>
</template>

<style scoped>
.res-page {
  --navy: #0b2f55; --blue: #0f5f8c; --ink: #0d2b4a; --muted: #6b7c93;
  --line: #e6ecf3; --bg: #eef3f9; --gold: #f6c744;
  max-width: 80rem; margin: 0 auto; padding: 8px 16px 24px; color: var(--ink);
}
@media (min-width: 640px) { .res-page { padding-inline: 24px; } }
@media (min-width: 1024px) { .res-page { padding-inline: 32px; } }
.kicker { font-size: 11px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--navy); margin: 0 0 2px; }
.kicker.light { color: #bfe0f5; } .kicker.dark { color: #4b6481; }
.muted { color: var(--muted); font-size: 13px; } .pad { padding: 12px 18px; }
.link { background: none; border: 0; color: var(--blue); font-weight: 600; cursor: pointer; text-decoration: underline; }

/* header */
.res-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; padding: 12px 0 18px; flex-wrap: wrap; }
.res-top h1 { margin: 0; font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: var(--navy); line-height: 1.15; }
.top-right { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
.btn-ghost { display: inline-flex; align-items: center; gap: 6px; padding: 10px 14px; font: inherit; font-size: 13px; font-weight: 600; color: var(--navy); background: #fff; border: 1px solid #cfe0ee; border-radius: 12px; cursor: pointer; }
.btn-ghost:hover { background: #f1f8fd; } .btn-ghost:focus-visible, .gate-btn:focus-visible, .gate-eye:focus-visible { outline: 3px solid #7cc3ea; outline-offset: 2px; }
.round-pick { display: flex; flex-direction: column; gap: 4px; font-size: 13px; font-weight: 600; min-width: 260px; }
.round-pick select { appearance: none; padding: 10px 14px; border-radius: 12px; border: 1px solid #cfe0ee; background: #f1f8fd; color: var(--navy); font-weight: 700; font-size: 15px; }

/* layout */
.res-body { display: grid; grid-template-columns: 290px minmax(0, 1fr); gap: 22px; padding: 0; align-items: start; }
.side { background: #fff; border: 1px solid var(--line); border-radius: 16px; position: sticky; top: 104px; max-height: calc(100vh - 120px); display: flex; flex-direction: column; overflow: hidden; }
.side-head { padding: 16px 18px 12px; border-bottom: 1px solid var(--line); margin: 0 4px; }
.side-head strong { display: block; font-size: 14px; } .side-head small { color: var(--muted); font-size: 12px; }
.side-list { list-style: none; margin: 0; padding: 6px 8px 8px; overflow-y: auto; }
.side-item { width: 100%; display: grid; grid-template-columns: 34px minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 11px 10px; border: 0; background: transparent; border-radius: 10px; cursor: pointer; text-align: left; font: inherit; color: inherit; }
.side-item:hover { background: #f4f8fc; }
.side-item:focus-visible, .banner:focus-visible { outline: 3px solid #7cc3ea; outline-offset: 2px; }
.side-item.active { background: #eaf4fb; }
.side-item.active .gname { font-weight: 700; color: var(--navy); }
.code { font-size: 11px; font-weight: 700; color: #6d86a3; letter-spacing: .06em; }
.gname { font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chip { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.chip.released { background: #e3f6ec; color: #0e8a52; } .chip.pending { background: #fff1db; color: #b4690e; }

/* banner */
.banner { display: flex; align-items: center; gap: 18px; padding: 20px 24px; border-radius: 16px; color: #fff; text-decoration: none; background: linear-gradient(100deg, #0a3b63, #0f6a99); box-shadow: 0 6px 18px rgba(11,47,85,.18); }
.crown { width: 56px; height: 56px; border-radius: 14px; background: var(--gold); color: var(--navy); display: grid; place-items: center; flex: none; }
.banner-text { flex: 1; min-width: 0; } .banner h2 { margin: 0; font-size: 24px; font-weight: 800; } .banner p:last-child { margin: 4px 0 0; font-size: 14px; color: #d6ecf9; }
.banner-btn { background: rgba(255,255,255,.2); padding: 10px 18px; border-radius: 12px; font-weight: 700; font-size: 14px; white-space: nowrap; }
.banner:hover .banner-btn { background: rgba(255,255,255,.3); }

.now { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-top: 14px; padding: 18px 24px; background: #fff; border: 1px solid var(--line); border-radius: 16px; }
.now h2 { margin: 0; font-size: 26px; font-weight: 700; color: var(--navy); text-transform: uppercase; }
.now-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; } .now-meta small { color: var(--muted); font-size: 12px; }
.pill { background: var(--navy); color: #fff; font-weight: 700; font-size: 13px; padding: 6px 16px; border-radius: 999px; }
.state { margin: 14px 0 0; padding: 18px 22px; background: #fff; border: 1px dashed #c9d7e6; border-radius: 14px; color: var(--muted); }

/* kolom hasil */
.cols { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px; }
.cols.single { grid-template-columns: minmax(0, 1fr); }
.col { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; min-width: 0; }
.col-head { display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; }
.col-head h3 { margin: 0; font-size: 16px; font-weight: 700; color: var(--navy); }
.count { background: #0f6a99; color: #fff; font-size: 12px; font-weight: 600; padding: 3px 11px; border-radius: 999px; }
.rows { list-style: none; margin: 0; padding: 0; }
.row { display: flex; align-items: center; gap: 12px; padding: 8px 16px; border-top: 1px solid var(--line); min-height: 96px; }
.medal { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; flex: none; border: 2px solid; }
.medal.r1 { background: #fff6d6; border-color: #f2d77a; color: #9a6b00; }
.medal.r2 { background: #e8edf3; border-color: #cdd6e1; color: #3b4d63; }
.medal.r3 { background: #ffe9d2; border-color: #f2c79a; color: #a24a0d; }
.medal.rx { background: #eef3f9; border-color: #e1e8f0; color: #3b4d63; }
.photo { width: 62px; height: 82px; border-radius: 10px; overflow: hidden; background: #eef3f9; border: 1px solid var(--line); display: grid; place-items: center; font-weight: 800; color: #6d86a3; flex: none; }
.photo img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
.stack { display: flex; flex: none; } .photo.mini { width: 44px; height: 58px; margin-left: -12px; border: 2px solid #fff; } .photo.mini:first-child { margin-left: 0; }
.info { min-width: 0; } .info h4 { margin: 0; font-size: 18px; font-weight: 800; color: var(--navy); line-height: 1.2; }
.sub { margin: 3px 0 4px; font-size: 14px; color: #3e5370; } .tag { margin: 0; font-size: 12px; color: var(--muted); }
.tag b { color: #0f6a99; font-weight: 700; } .tag b.harapan { color: #b35a00; } .tag i { font-style: normal; margin: 0 3px; }


/* gerbang password (sama dengan Standings) */
.gate { max-width: 420px; margin: 8px auto 0; padding: 28px 24px; text-align: center; background: #fff; border: 1px solid var(--line); border-radius: 16px; }
.gate-icon { width: 48px; height: 48px; margin: 0 auto 12px; display: grid; place-items: center; color: var(--navy); background: #eaf4fb; border-radius: 50%; }
.gate h2 { margin: 0; font-size: 19px; font-weight: 800; color: var(--navy); }
.gate p { margin: 6px 0 0; font-size: 13px; color: var(--muted); }
.gate form { margin-top: 18px; }
.gate-field { position: relative; }
.gate-field input { width: 100%; padding: 10px 74px 10px 14px; font: inherit; font-size: 14px; color: var(--ink); background: #fff; border: 1px solid #cfe0ee; border-radius: 12px; outline: none; }
.gate-field input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(15, 95, 140, .15); }
.gate-eye { position: absolute; top: 50%; right: 8px; transform: translateY(-50%); padding: 4px 8px; font: inherit; font-size: 12px; font-weight: 600; color: var(--muted); background: transparent; border: 0; cursor: pointer; }
.gate p.gate-error { margin: 8px 0 0; font-size: 13px; color: #b91c1c; text-align: left; }
.gate-btn { width: 100%; margin-top: 12px; padding: 10px 16px; font: inherit; font-size: 14px; font-weight: 700; color: #fff; background: var(--navy); border: 0; border-radius: 12px; cursor: pointer; transition: opacity .15s ease; }
.gate-btn:hover { opacity: .92; } .gate-btn:disabled { opacity: .6; cursor: not-allowed; }
.gate-wa { display: inline-block; margin-top: 12px; font-size: 13px; font-weight: 600; color: var(--blue); text-decoration: none; }
.gate-wa:hover { text-decoration: underline; }
.gate-loading { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.gate-loading .skel-circle { width: 48px; height: 48px; border-radius: 50%; }
.gate-loading .skel-line { width: 70%; height: 16px; } .gate-loading .skel-line.short { width: 45%; }
.skel { background: linear-gradient(90deg, #eef3f9 25%, #f8fafc 50%, #eef3f9 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite linear; border-radius: 8px; }
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

/* mobile */
@media (max-width: 960px) {
  .res-body { grid-template-columns: minmax(0, 1fr); }
  .side { position: static; max-height: none; }
  .side-list { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 10px; }
  .side-item { grid-template-columns: auto auto; white-space: nowrap; width: auto; border: 1px solid var(--line); }
  .side-item .chip { display: none; }
  .cols { grid-template-columns: minmax(0, 1fr); }
  .banner { flex-wrap: wrap; } .banner-btn { width: 100%; text-align: center; }
  .now h2 { font-size: 20px; } .now-meta { align-items: flex-start; }
  .round-pick { min-width: 100%; } .top-right { width: 100%; }
}
@media (prefers-reduced-motion: no-preference) { .side-item { transition: background .15s; } }
</style>