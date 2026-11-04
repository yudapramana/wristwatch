<template>
  <PublicLayout cta-text="Masuk Portal" :show-footer="false">
    <main class="std-main max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8 relative z-10">

      <!-- ================= HEADER SECTION ================= -->
      <section class="text-center space-y-4 mb-8">
        <span class="inline-block border border-emerald-200 bg-emerald-50/50 text-emerald-800 text-[11px] font-bold tracking-widest uppercase px-3 py-1 rounded-full">
          Hasil Resmi
        </span>
        <h1 class="text-3xl sm:text-4xl font-serif text-slate-900 leading-[1.15]">
          Klasemen Juara Umum
        </h1>
        <p class="text-slate-500 text-sm font-light">
          {{ eventData.event_name || 'MTQ XLII Kabupaten Pesisir Selatan' }} TAHUN {{ eventData.event_year || '2026' }}
        </p>
        <button
          v-if="access === 'ok' && standingsRequiresPassword"
          type="button"
          class="std-back"
          @click="store.lockStandings()"
        >
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>
          Kunci kembali
        </button>
        <!-- <router-link :to="BRANCH_RESULT_ROUTE" class="std-back">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
          Hasil Cabang
        </router-link> -->
      </section>
      <!-- ================= MENGECEK AKSES (hindari kilasan tabel) ================= -->
      <section v-if="access === 'unknown'" class="std-gate std-gate-loading" aria-busy="true" aria-label="Memuat">
        <div class="skel skel-circle"></div>
        <div class="skel skel-line"></div>
        <div class="skel skel-line short"></div>
      </section>

      <!-- ================= GERBANG PASSWORD ================= -->
      <section v-else-if="access === 'locked'" class="std-gate">
        <div class="std-gate-icon">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>
        </div>
        <h2>Klasemen Dilindungi</h2>
        <p>Masukkan password untuk melihat klasemen juara umum.</p>

        <form @submit.prevent="submitPassword">
          <div class="std-gate-field">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Password"
              autocomplete="off"
              :disabled="isUnlocking"
              @input="localError = ''"
            />
            <button type="button" class="std-gate-eye" @click="showPassword = !showPassword">
              {{ showPassword ? 'Sembunyi' : 'Lihat' }}
            </button>
          </div>
          <p v-if="gateError" class="std-gate-error">{{ gateError }}</p>
          <button type="submit" class="std-gate-btn" :disabled="isUnlocking">
            {{ isUnlocking ? 'Memeriksa…' : 'Buka Klasemen' }}
          </button>
        </form>

        <a v-if="waLink" :href="waLink" target="_blank" rel="noopener" class="std-gate-wa">
          Minta password ke admin
        </a>
      </section>

      <!-- ================= BELUM DIPUBLIKASIKAN ================= -->
      <section v-else-if="access === 'unpublished'" class="std-gate">
        <div class="std-gate-icon">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
        </div>
        <h2>Klasemen Belum Dipublikasikan</h2>
        <p>Hasil akan ditampilkan setelah diumumkan oleh panitia.</p>
        <button type="button" class="std-gate-btn" @click="loadStandings(true)">Periksa lagi</button>
      </section>

      <!-- ================= KONTEN KLASEMEN ================= -->
      <template v-else-if="access === 'ok'">
        <!-- ================= INFO BAR ================= -->
        <div class="std-info">
          <div class="std-info-left">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#b45309" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z" /><path d="M7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3" />
            </svg>
            <span class="std-info-label">Poin Juara I–VI:</span>
            <strong class="std-info-points">{{ POINT_SCHEME.join(' / ') }}</strong>
          </div>
          <div v-if="updatedLabel" class="std-info-right">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="17" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" />
            </svg>
            Diperbarui {{ updatedLabel }}
          </div>
        </div>

        <!-- ================= PODIUM ================= -->
        <Transition name="swap">
          <section v-if="!isLoading && podium.length" class="std-podium">
            <article
              v-for="(p, i) in podium"
              :key="p.item.region_name"
              class="pd-card"
              :class="`pd-${i + 1}`"
            >
              <div class="pd-label">
                <!-- ikon -->
                <svg v-if="i === 0" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18h18M4 18 3 7l5 4 4-7 4 7 5-4-1 11" /></svg>
                <svg v-else-if="i === 1" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="15" r="5" /><path d="M8 3h3l1 5M16 3h-3l-1 5" /></svg>
                <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="5" /><path d="m9 13-1.5 8L12 18l4.5 3L15 13" /></svg>
                <span>{{ i === 0 ? 'Juara Umum MTQ' : `Peringkat ${p.rank}` }}</span>
              </div>
              <div v-if="i === 0" class="pd-sub">Peringkat {{ p.rank }}</div>
              <h2 class="pd-name" :title="p.item.region_name">{{ p.item.region_name }}</h2>
              <div class="pd-point"><strong>{{ toNum(p.item.total_point) }}</strong> poin</div>
            </article>
          </section>
        </Transition>

        <!-- ================= TABEL ================= -->
        <section class="std-card">
          <div class="std-card-head">
            <h2>Klasemen Lengkap</h2>
            <p>Peringkat resmi berdasarkan Total Poin. Kontingen dengan Total Poin sama berbagi peringkat.</p>
          </div>

          <div class="std-scroll">
            <table class="std-table">
              <thead>
                <tr class="h-top">
                  <th rowspan="2" class="c-rank">Peringkat</th>
                  <th rowspan="2" class="c-name">Kontingen</th>
                  <th colspan="3" class="grp">Juara</th>
                  <th colspan="3" class="grp grp-l">Harapan</th>
                  <th rowspan="2" class="c-total grp-l">Total Poin</th>
                </tr>
                <tr class="h-sub">
                  <th v-for="col in medalColumns" :key="col.key" class="sub" :class="[col.head, { 'grp-l': col.key === 'harapan_1' }]">
                    {{ col.label }}
                  </th>
                </tr>
              </thead>

              <tbody v-if="isLoading">
                <tr v-for="n in 8" :key="n">
                  <td :colspan="colCount" class="cell-pad"><div class="skel"></div></td>
                </tr>
              </tbody>

              <tbody v-else-if="errorMessage">
                <tr>
                  <td :colspan="colCount" class="empty-cell">
                    <div>{{ errorMessage }}</div>
                    <button type="button" class="retry" @click="loadStandings(true)">Coba lagi</button>
                  </td>
                </tr>
              </tbody>

              <tbody v-else-if="!items.length">
                <tr>
                  <td :colspan="colCount" class="empty-cell">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z" /><path d="M7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3" /></svg>
                    <div>Klasemen belum tersedia.</div>
                  </td>
                </tr>
              </tbody>

              <tbody v-else>
                <tr v-for="row in rankedItems" :key="row.item.region_name" :class="{ 'is-top': row.rank === 1 }">
                  <td class="c-rank">
                    <span class="rank-chip" :class="rankClass(row.rank)">{{ row.rank }}</span>
                  </td>
                  <td class="c-name">
                    <span class="region" :title="row.item.region_name">{{ row.item.region_name }}</span>
                  </td>
                  <td
                    v-for="col in medalColumns"
                    :key="col.key"
                    class="num"
                    :class="[col.cls, { zero: !toNum(row.item[col.key]), 'grp-l': col.key === 'harapan_1' }]"
                  >
                    {{ toNum(row.item[col.key]) }}
                  </td>
                  <td class="num total grp-l">{{ toNum(row.item.total_point) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </main>
  </PublicLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import Swal from 'sweetalert2'
import { storeToRefs } from 'pinia'
import PublicLayout from '../../layouts/PublicLayout.vue'
import { useEventStore } from '../../stores/EventStore.js'

/* ======================
 |  KONFIGURASI
 ====================== */
// Ganti bila halaman Hasil Cabang sudah punya route sendiri
const BRANCH_RESULT_ROUTE = '/'
// Skema poin Juara I–VI (hanya tampilan informasi)
const POINT_SCHEME = [9, 7, 5, 3, 2, 1]
// Nomor WhatsApp admin untuk meminta password (format 62812...). Kosongkan untuk menyembunyikan tombol.
const ADMIN_WHATSAPP = ''

/* ======================
 |  STORE (info event: nama, tahun)
 ====================== */
const store = useEventStore()
const {
  eventData,
  standings: items,
  standingsPublishedAt: publishedAt,
  isStandingsLoading,
  standingsError,
  standingsAccess: access,
  standingsRequiresPassword,
  standingsUnlockError,
  isUnlocking,
} = storeToRefs(store)

/* ======================
 |  GERBANG PASSWORD
 ====================== */
const password = ref('')
const showPassword = ref(false)
const localError = ref('')
const gateError = computed(() => localError.value || standingsUnlockError.value)

const waLink = computed(() => {
  if (!ADMIN_WHATSAPP) return ''
  const text = encodeURIComponent('Halo admin, saya ingin meminta password untuk membuka Klasemen Juara Umum MTQ.')
  return `https://wa.me/${ADMIN_WHATSAPP}?text=${text}`
})

const submitPassword = async () => {
  localError.value = ''
  if (!password.value.trim()) {
    localError.value = 'Password wajib diisi.'
    return
  }
  const ok = await store.unlockStandings(password.value)
  if (ok) password.value = ''
}

/* ======================
 |  STATE
 ====================== */
// Skeleton hanya tampil bila belum ada data di store; kalau sudah ada, data lama langsung tampil
const isLoading = computed(() => isStandingsLoading.value && !items.value.length)
// Error hanya ditampilkan di tabel bila tidak ada data yang bisa ditampilkan
const errorMessage = computed(() => (items.value.length ? '' : standingsError.value))

/* ======================
 |  KOLOM MEDALI
 ====================== */
const medalColumns = [
  { key: 'juara_1', label: 'I', cls: 'm-j1', head: 'sub-gold' },
  { key: 'juara_2', label: 'II', cls: 'm-j2', head: '' },
  { key: 'juara_3', label: 'III', cls: 'm-j3', head: 'sub-bronze' },
  { key: 'harapan_1', label: 'I', cls: 'm-h', head: '' },
  { key: 'harapan_2', label: 'II', cls: 'm-h', head: '' },
  { key: 'harapan_3', label: 'III', cls: 'm-h', head: '' },
]
const colCount = medalColumns.length + 3

/* ======================
 |  HELPER
 ====================== */
const toNum = (v) => Number(v) || 0

const rankClass = (rank) => (rank >= 1 && rank <= 3 ? `rank-${rank}` : '')

// Urutan dari server dipertahankan; kontingen dengan total poin sama berbagi peringkat
const rankedItems = computed(() => {
  let prevPoint = null
  let rank = 0
  return items.value.map((item, i) => {
    const point = toNum(item.total_point)
    if (point !== prevPoint) {
      rank = i + 1
      prevPoint = point
    }
    return { item, rank }
  })
})

const podium = computed(() => rankedItems.value.slice(0, 3))

const updatedLabel = computed(() => {
  if (!publishedAt.value) return ''
  const d = new Date(String(publishedAt.value).replace(' ', 'T'))
  if (isNaN(d.getTime())) return ''
  const tz = 'Asia/Jakarta'
  const date = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: tz }).format(d)
  const time = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: tz }).format(d)
  return `${date}, ${time} WIB`
})

/* ======================
 |  LOAD KLASEMEN (data disimpan di EventStore)
 ====================== */
// Tanpa force: dibaca dari store selama cache masih berlaku. force = true untuk "Coba lagi".
const loadStandings = async (force = false) => {
  await store.fetchStandings({ force })
  if (standingsError.value && !items.value.length) {
    Swal.fire('Gagal', standingsError.value, 'error')
  }
}

/* ======================
 |  LIFECYCLE
 ====================== */
onMounted(() => {
  loadStandings()
  // info event tidak boleh menghambat klasemen (sudah di-guard flag di store)
  store.fetchEventData()
})
</script>

<style scoped>
.std-main {
  --navy: #0b3b5c;
  --ink: #0f2a43;
  --muted: #64748b;
  --line: #e8edf3;
  color: var(--ink);
}

/* ---------- Tombol kembali ---------- */
.std-back {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 9px 16px; font-size: 13px; font-weight: 600; color: var(--ink); text-decoration: none;
  background: #fff; border: 1px solid #dbe3ec; border-radius: 12px; box-shadow: 0 1px 3px rgba(15, 42, 67, 0.06);
  transition: background 0.15s ease, border-color 0.15s ease;
}
.std-back:hover { background: #f1f6fb; border-color: #c4d2e1; }

/* ---------- Info bar ---------- */
.std-info {
  display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 16px;
  padding: 11px 18px; font-size: 13px; background: #fff; border: 1px solid var(--line);
  border-radius: 14px; box-shadow: 0 1px 4px rgba(15, 42, 67, 0.05);
}
.std-info-left { display: flex; align-items: center; gap: 8px; }
.std-info-label { font-weight: 600; }
.std-info-points { color: #0e5a7a; letter-spacing: 0.04em; }
.std-info-right { display: flex; align-items: center; gap: 7px; color: var(--muted); }

/* ---------- Podium ---------- */
.std-podium {
  display: grid; grid-template-columns: 1fr 1.3fr 1fr; align-items: end;
  max-width: 1010px; margin: 40px auto 16px;
}
.pd-card { padding: 22px 16px; text-align: center; border: 1px solid; min-width: 0; }
.pd-label {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  font-size: 12.5px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;
}
.pd-sub { margin-top: 10px; font-size: 12.5px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #0e5a7a; }
.pd-name { margin: 10px 0 0; font-weight: 800; line-height: 1.1; color: var(--navy); overflow-wrap: anywhere; }
.pd-point { margin-top: 14px; font-size: 15px; color: #0e5a7a; }
.pd-point strong { font-size: 22px; font-weight: 800; }

.pd-1 {
  order: 2; padding: 30px 22px 34px; border-radius: 14px 14px 0 0; border-color: #f3c64b;
  background: linear-gradient(180deg, #fff6c9 0%, #fff9e0 100%);
  box-shadow: 0 -4px 30px rgba(245, 180, 30, 0.2);
}
.pd-1 .pd-label { color: #9a3412; }
.pd-1 .pd-name { font-size: 40px; }
.pd-1 .pd-point strong { font-size: 28px; }

.pd-2 { order: 1; padding: 34px 16px 30px; border-radius: 12px 0 0 0; border-color: #8fd0ea; background: linear-gradient(180deg, #f1faff, #eaf6fd); }
.pd-2 .pd-label { color: #0e5a7a; }
.pd-2 .pd-name { font-size: 28px; }

.pd-3 { order: 3; padding: 34px 16px 30px; border-radius: 0 12px 0 0; border-color: #f6a35c; background: linear-gradient(180deg, #fff1e3, #fee8d2); }
.pd-3 .pd-label { color: #9a3412; }
.pd-3 .pd-name { font-size: 26px; }

/* ---------- Kartu tabel ---------- */
.std-card { margin-top: 16px; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 2px 14px rgba(15, 42, 67, 0.06); overflow: hidden; }
.std-card-head { padding: 22px 20px 16px; }
.std-card-head h2 { margin: 0; font-size: 22px; font-weight: 800; color: var(--navy); }
.std-card-head p { margin: 6px 0 0; font-size: 13px; color: var(--muted); }

.std-scroll { max-height: 520px; overflow: auto; border-top: 1px solid var(--line); }
.std-table { width: 100%; min-width: 720px; border-collapse: separate; border-spacing: 0; font-size: 13.5px; }
.std-table th, .std-table td { padding: 11px 12px; border-bottom: 1px solid #eef2f6; }
.std-table thead th {
  position: sticky; z-index: 2; background: #fafcfe; font-size: 12.5px; font-weight: 700; color: var(--ink); text-align: center;
}
.h-top th { top: 0; }
.h-sub th { top: 38px; padding: 5px 12px; font-size: 11px; background: #f1f5f9; }
.std-table .grp { border-bottom: 1px solid #eef2f6; }
.grp-l { border-left: 1px solid #e2e8f0; }
.sub-gold { background: #fef3c7 !important; color: #b45309; }
.sub-bronze { background: #ffedd5 !important; color: #c2410c; }
.c-rank { width: 90px; text-align: center; }
.c-name { text-align: left !important; min-width: 220px; }
.c-total { width: 120px; }

.std-table tbody tr:hover { background: #f6faff; }
.std-table tbody tr.is-top { background: #fffdf3; }
.region { font-weight: 700; color: var(--navy); }
.num { text-align: center; font-weight: 700; font-variant-numeric: tabular-nums; }
.num.zero { font-weight: 500; color: #cbd5e1; }
.m-j1 { color: #c2410c; }
.m-j2 { color: #334155; }
.m-j3 { color: #b91c1c; }
.m-h { color: #0b3b5c; }
.num.zero.m-j1, .num.zero.m-j2, .num.zero.m-j3, .num.zero.m-h { color: #cbd5e1; }
.total { font-size: 18px; font-weight: 800; color: var(--navy); }

.rank-chip {
  display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 30px; padding: 0 6px;
  font-size: 12px; font-weight: 800; color: #475569; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px;
}
.rank-chip.rank-1 { color: #92400e; background: #fef3c7; border-color: #f3c64b; }
.rank-chip.rank-2 { color: #475569; background: #e8edf3; border-color: #cbd5e1; }
.rank-chip.rank-3 { color: #9a3412; background: #ffedd5; border-color: #f6a35c; }

/* ---------- Kosong & skeleton ---------- */
.empty-cell { padding: 48px 16px !important; text-align: center; color: var(--muted); font-size: 14px; }
.empty-cell svg { display: block; margin: 0 auto 10px; }
.retry { margin-top: 12px; padding: 7px 16px; font-size: 13px; font-weight: 600; color: var(--navy); background: #fff; border: 1px solid #dbe3ec; border-radius: 10px; cursor: pointer; }
.retry:hover { background: #f1f6fb; }
.cell-pad { padding: 6px 12px !important; }
.skel {
  height: 30px; border-radius: 8px; background: linear-gradient(90deg, #eef2f6 25%, #f8fafc 50%, #eef2f6 75%);
  background-size: 200% 100%; animation: shimmer 1.2s infinite linear;
}
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

/* ---------- Gerbang password ---------- */
.std-gate {
  max-width: 420px; margin: 8px auto 0; padding: 32px 24px; text-align: center;
  background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 2px 14px rgba(15, 42, 67, 0.06);
}
.std-gate-icon {
  width: 52px; height: 52px; margin: 0 auto 14px; display: grid; place-items: center;
  color: var(--navy); background: #eef5fb; border-radius: 50%;
}
.std-gate h2 { margin: 0; font-size: 20px; font-weight: 800; color: var(--navy); }
.std-gate p { margin: 8px 0 0; font-size: 13.5px; color: var(--muted); }
.std-gate form { margin-top: 20px; }
.std-gate-field { position: relative; }
.std-gate-field input {
  width: 100%; padding: 11px 74px 11px 14px; font-size: 14px; color: var(--ink);
  border: 1px solid #dbe3ec; border-radius: 12px; outline: none; background: #fff;
}
.std-gate-field input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(11, 59, 92, 0.12); }
.std-gate-eye {
  position: absolute; top: 50%; right: 8px; transform: translateY(-50%);
  padding: 4px 8px; font-size: 12px; font-weight: 600; color: var(--muted);
  background: transparent; border: 0; cursor: pointer;
}
.std-gate-error { margin: 8px 0 0 !important; font-size: 13px !important; color: #b91c1c !important; text-align: left; }
.std-gate-btn {
  width: 100%; margin-top: 14px; padding: 11px 16px; font-size: 14px; font-weight: 700; color: #fff;
  background: var(--navy); border: 0; border-radius: 12px; cursor: pointer; transition: opacity 0.15s ease;
}
.std-gate-btn:hover { opacity: 0.92; }
.std-gate-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.std-gate form + .std-gate-wa, .std-gate-wa { display: inline-block; margin-top: 14px; font-size: 13px; font-weight: 600; color: #047857; text-decoration: none; }
.std-gate-wa:hover { text-decoration: underline; }
.std-gate-loading { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.std-gate-loading .skel-circle { width: 52px; height: 52px; border-radius: 50%; }
.std-gate-loading .skel-line { width: 70%; height: 18px; }
.std-gate-loading .skel-line.short { width: 45%; }

.swap-enter-active, .swap-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.swap-enter-from { opacity: 0; transform: translateY(8px); }
.swap-leave-to { opacity: 0; }

/* ---------- Responsif ---------- */
@media (max-width: 767.98px) {
  .std-back { padding: 8px 12px; }
  .std-podium { grid-template-columns: 1fr; gap: 10px; margin-top: 20px; }
  .pd-1, .pd-2, .pd-3 { order: 0; border-radius: 14px; padding: 18px 14px; }
  .pd-1 .pd-name { font-size: 28px; }
  .pd-2 .pd-name, .pd-3 .pd-name { font-size: 22px; }
}
</style>