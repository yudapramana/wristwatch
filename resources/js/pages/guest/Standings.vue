<template>
  <PublicLayout>
    <div class="pg">
      <!-- Judul halaman (logo & menu sudah ada di PublicLayout) -->
      <header class="pg-top">
        <div>
          <p class="kicker">Hasil resmi</p>
          <h1>Klasemen Juara Umum</h1>
          <p class="muted">
            {{ eventData.event_name || 'MTQ XLII Kabupaten Pesisir Selatan' }} TAHUN {{ eventData.event_year || '2026' }}
          </p>
        </div>

        <div v-if="access === 'ok'" class="top-meta">
          <span v-if="updatedLabel" class="stamp">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" /></svg>
            Diperbarui {{ updatedLabel }}
          </span>
          <button v-if="standingsRequiresPassword" type="button" class="btn-ghost" @click="accessStore.lock()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>
            Kunci kembali
          </button>
        </div>
      </header>

      <!-- Gerbang bersama: memuat / password / belum dipublikasikan -->
      <AccessGate
        v-if="access !== 'ok'"
        :status="access"
        label="Klasemen"
        subject="klasemen juara umum"
        :reload="() => loadStandings(true)"
      />

      <!-- Konten klasemen -->
      <template v-else>
        <!-- Skema poin -->
        <section class="scheme" aria-label="Skema poin juara">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#9a6b00" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z" /><path d="M7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3" />
          </svg>
          <span class="scheme-label">Poin Juara I–VI</span>
          <ul class="pts">
            <li v-for="(pt, i) in POINT_SCHEME" :key="i"><b>{{ ROMAN[i] }}</b> {{ pt }}</li>
          </ul>
        </section>

        <!-- Podium -->
        <Transition name="swap">
          <section v-if="!isLoading && podium.length" class="podium" aria-label="Tiga besar">
            <article
              v-for="p in podium"
              :key="p.item.region_name"
              class="pd"
              :class="{ first: p.rank === 1 }"
            >
              <span class="medal" :class="rankClass(p.rank)">{{ p.rank }}</span>
              <div class="pd-body">
                <p class="pd-kick">{{ p.rank === 1 ? 'Juara Umum MTQ' : `Peringkat ${p.rank}` }}</p>
                <h2 :title="p.item.region_name">{{ p.item.region_name }}</h2>
              </div>
              <div class="pd-point"><strong>{{ toNum(p.item.total_point) }}</strong><small>poin</small></div>
            </article>
          </section>
        </Transition>

        <!-- Tabel -->
        <section class="col tbl">
          <header class="col-head">
            <div>
              <h3>Klasemen Lengkap</h3>
              <p class="muted">Peringkat resmi berdasarkan Total Poin. Kontingen dengan Total Poin sama berbagi peringkat.</p>
            </div>
            <span v-if="items.length" class="count">{{ items.length }} kontingen</span>
          </header>

          <div class="scroll">
            <table class="std-table">
              <thead>
                <tr class="h-top">
                  <th rowspan="2" class="c-rank">Peringkat</th>
                  <th rowspan="2" class="c-name">Kontingen</th>
                  <th colspan="3" class="grp">Juara</th>
                  <th colspan="3" class="grp grp-l grp-h">Harapan</th>
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
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z" /><path d="M7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3" /></svg>
                    <div>Klasemen belum tersedia.</div>
                  </td>
                </tr>
              </tbody>

              <tbody v-else>
                <tr v-for="row in rankedItems" :key="row.item.region_name" :class="{ 'is-top': row.rank === 1 }">
                  <td class="c-rank">
                    <span class="medal sm" :class="rankClass(row.rank)">{{ row.rank }}</span>
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
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import Swal from 'sweetalert2'
import { storeToRefs } from 'pinia'
import PublicLayout from '../../layouts/PublicLayout.vue'
import AccessGate from '../../components/AccessGate.vue'
import { useEventStore } from '../../stores/EventStore.js'
import { useAccessStore } from '../../stores/AccessStore.js'

/* ======================
 |  KONFIGURASI
 ====================== */
// Ganti bila halaman Hasil Cabang sudah punya route sendiri
const BRANCH_RESULT_ROUTE = '/'
// Skema poin Juara I–VI (hanya tampilan informasi)
const POINT_SCHEME = [9, 7, 5, 3, 2, 1]
const ROMAN = ['I', 'II', 'III', 'IV', 'V', 'VI']

/* ======================
 |  STORE (data klasemen) + AccessStore (gerbang password bersama dengan Hasil Cabang)
 ====================== */
const store = useEventStore()
const accessStore = useAccessStore()
const {
  eventData,
  standings: items,
  standingsPublishedAt: publishedAt,
  isStandingsLoading,
  standingsError,
  standingsChecked,
  standingsUnpublished,
} = storeToRefs(store)
const { status: accessStatus, requiresPassword: standingsRequiresPassword } = storeToRefs(accessStore)

// Status tampilan: 'unknown' sampai server menjawab di halaman ini (hindari kilasan data lama)
const access = computed(() => {
  if (!standingsChecked.value) return 'unknown'
  if (standingsUnpublished.value) return 'unpublished'
  return accessStatus.value
})

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

// Sama dengan Results.vue: r1/r2/r3 untuk tiga besar, rx untuk selebihnya
const rankClass = (rank) => (rank >= 1 && rank <= 3 ? `r${rank}` : 'rx')

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
/* ====== Dasar layout (sama dengan Results.vue) ====== */
.pg {
  --navy: #0b2f55; --blue: #0f5f8c; --ink: #0d2b4a; --muted: #6b7c93;
  --line: #e6ecf3; --bg: #eef3f9; --gold: #f6c744;
  max-width: 80rem; margin: 0 auto; padding: 8px 16px 24px; color: var(--ink);
}
@media (min-width: 640px) { .pg { padding-inline: 24px; } }
@media (min-width: 1024px) { .pg { padding-inline: 32px; } }
.kicker { font-size: 11px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--navy); margin: 0 0 2px; }
.muted { color: var(--muted); font-size: 13px; margin: 0; }
.pg-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; padding: 8px 0 14px; flex-wrap: wrap; }
.pg-top h1 { margin: 0; font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: var(--navy); line-height: 1.15; }
.top-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.stamp { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; font-size: 12px; color: var(--muted); background: #fff; border: 1px solid var(--line); border-radius: 999px; }
.btn-ghost { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font: inherit; font-size: 13px; font-weight: 600; color: var(--navy); background: #fff; border: 1px solid #cfe0ee; border-radius: 999px; cursor: pointer; }
.btn-ghost:hover { background: #f1f8fd; }
.btn-ghost:focus-visible, .retry:focus-visible { outline: 3px solid #7cc3ea; outline-offset: 2px; }

.col { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; min-width: 0; }
.col-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 18px; }
.col-head h3 { margin: 0; font-size: 16px; font-weight: 700; color: var(--navy); }
.col-head .muted { margin-top: 2px; }
.count { background: var(--blue); color: #fff; font-size: 12px; font-weight: 600; padding: 3px 11px; border-radius: 999px; white-space: nowrap; }

.medal { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; flex: none; border: 2px solid; }
.medal.sm { width: 30px; height: 30px; font-size: 13px; margin: 0 auto; }
.medal.r1 { background: #fff6d6; border-color: #f2d77a; color: #9a6b00; }
.medal.r2 { background: #e8edf3; border-color: #cdd6e1; color: #3b4d63; }
.medal.r3 { background: #ffe9d2; border-color: #f2c79a; color: #a24a0d; }
.medal.rx { background: #eef3f9; border-color: #e1e8f0; color: #3b4d63; }

/* ====== Skema poin ====== */
.scheme { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 10px; padding: 8px 16px; font-size: 13px; background: #fff; border: 1px solid var(--line); border-radius: 16px; }
.scheme-label { font-weight: 700; color: var(--navy); }
.pts { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
.pts li { padding: 2px 10px; font-size: 12px; font-weight: 700; color: var(--blue); background: #eaf4fb; border-radius: 999px; }
.pts li b { margin-right: 3px; color: var(--muted); font-weight: 700; }

/* ====== Podium ====== */
.podium { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 12px; }
.pd { display: flex; align-items: center; gap: 12px; padding: 12px 16px; min-width: 0; background: #fff; border: 1px solid var(--line); border-radius: 16px; }
.pd-body { flex: 1; min-width: 0; }
.pd-kick { margin: 0 0 2px; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #4b6481; }
.pd h2 { margin: 0; font-size: 17px; font-weight: 800; line-height: 1.2; color: var(--navy); overflow-wrap: anywhere; }
.pd-point { display: flex; flex-direction: column; align-items: flex-end; line-height: 1.1; flex: none; }
.pd-point strong { font-size: 22px; font-weight: 800; color: var(--blue); font-variant-numeric: tabular-nums; }
.pd-point small { font-size: 11px; color: var(--muted); }
.pd.first { color: #fff; background: linear-gradient(100deg, #0a3b63, #0f6a99); border-color: transparent; box-shadow: 0 6px 18px rgba(11, 47, 85, .18); }
.pd.first .medal { background: var(--gold); border-color: var(--gold); color: var(--navy); }
.pd.first .pd-kick { color: #bfe0f5; }
.pd.first h2 { color: #fff; font-size: 19px; }
.pd.first .pd-point strong { color: #fff; }
.pd.first .pd-point small { color: #d6ecf9; }

/* ====== Tabel ====== */
.tbl { margin-top: 12px; }
.scroll { max-height: max(360px, calc(100vh - 300px)); overflow: auto; border-top: 1px solid var(--line); }
.std-table { width: 100%; min-width: 680px; border-collapse: separate; border-spacing: 0; font-size: 13px; }
.std-table th, .std-table td { padding: 7px 10px; border-bottom: 1px solid var(--line); }
.std-table thead th { position: sticky; z-index: 2; text-align: center; font-size: 12px; font-weight: 700; }
.h-top th { top: 0; height: 34px; padding-block: 0; color: #fff; background: var(--navy); border-bottom-color: rgba(255, 255, 255, .15); }
.h-top .grp-h { background: var(--blue); }
.h-sub th { top: 34px; height: 28px; padding-block: 0; font-size: 11px; color: var(--navy); background: #eaf4fb; }
.grp-l { border-left: 1px solid var(--line); }
.h-top .grp-l { border-left-color: rgba(255, 255, 255, .2); }
.sub-gold { background: #fff1c2 !important; color: #9a6b00 !important; }
.sub-bronze { background: #ffe3c9 !important; color: #a24a0d !important; }
.c-rank { width: 84px; text-align: center; }
.c-name { text-align: left !important; min-width: 200px; }
.c-total { width: 100px; }

.std-table tbody tr:hover { background: #f4f8fc; }
.std-table tbody tr.is-top { background: #fffbea; }
.std-table tbody tr:last-child td { border-bottom: 0; }
.region { font-weight: 700; color: var(--navy); }
.num { text-align: center; font-weight: 700; font-variant-numeric: tabular-nums; }
.m-j1 { color: #a24a0d; }
.m-j2 { color: #3b4d63; }
.m-j3 { color: #b91c1c; }
.m-h { color: var(--navy); }
.num.zero, .num.zero.m-j1, .num.zero.m-j2, .num.zero.m-j3, .num.zero.m-h { font-weight: 500; color: #c3cfdd; }
.total { font-size: 16px; font-weight: 800; color: var(--navy); }

/* ====== Kosong & skeleton ====== */
.empty-cell { padding: 36px 16px !important; text-align: center; color: var(--muted); font-size: 14px; }
.empty-cell svg { display: block; margin: 0 auto 8px; }
.retry { margin-top: 10px; padding: 7px 16px; font: inherit; font-size: 13px; font-weight: 600; color: var(--navy); background: #fff; border: 1px solid #cfe0ee; border-radius: 10px; cursor: pointer; }
.retry:hover { background: #f1f8fd; }
.cell-pad { padding: 5px 10px !important; }
.skel { height: 28px; border-radius: 8px; background: linear-gradient(90deg, #eef3f9 25%, #f8fafc 50%, #eef3f9 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite linear; }
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

.swap-enter-active, .swap-leave-active { transition: opacity .2s ease, transform .2s ease; }
.swap-enter-from { opacity: 0; transform: translateY(6px); }
.swap-leave-to { opacity: 0; }

/* ====== Mobile ====== */
@media (max-width: 960px) {
  .podium { grid-template-columns: minmax(0, 1fr); gap: 8px; }
  .pd.first { order: -1; }
}
@media (max-width: 640px) {
  .col-head { align-items: flex-start; flex-direction: column; gap: 6px; }
  .top-meta { width: 100%; }
}
</style>