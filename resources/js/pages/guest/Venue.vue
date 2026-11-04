<template>
  <PublicLayout>
    <div class="pg">
      <!-- Judul halaman + pencarian (logo & menu sudah ada di PublicLayout) -->
      <header class="pg-top">
        <div>
          <p class="kicker">Informasi arena MTQ</p>
          <h1>Daftar Lokasi Lomba</h1>
          <p class="muted">
            {{ eventData.event_name || 'MTQ XLII Kabupaten Pesisir Selatan' }} TAHUN {{ eventData.event_year || '2026' }}
          </p>
        </div>

        <label class="search">
          <span class="sr-only">Cari lokasi lomba</span>
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" /></svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari bidang lomba, majelis, atau tempat…"
            autocomplete="off"
          />
          <button v-if="searchQuery" type="button" class="clear" aria-label="Hapus pencarian" @click="searchQuery = ''">×</button>
        </label>
      </header>

      <p v-if="!isLoading" class="count-line muted" aria-live="polite">
        {{ filteredVenues.length }} lokasi<template v-if="searchQuery.trim()"> cocok dengan “{{ searchQuery.trim() }}”</template>
      </p>

      <!-- Skeleton -->
      <div v-if="isLoading" class="cols" aria-busy="true" aria-label="Memuat">
        <div v-for="i in 4" :key="i" class="col skel-card"></div>
      </div>

      <!-- Daftar lokasi -->
      <div v-else-if="filteredVenues.length" class="cols">
        <article v-for="(venue, index) in filteredVenues" :key="venue.location_id" class="col venue">
          <div class="v-main">
            <div class="v-photo">
              <img v-if="venue.photo_url" :src="venue.photo_url" :alt="venue.tempat_lomba" loading="lazy" />
              <svg v-else viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z" /><circle cx="12" cy="10" r="2.5" /></svg>
              <span class="v-no">{{ index + 1 }}</span>
            </div>

            <div class="v-info">
              <h3 :title="venue.tempat_lomba">{{ venue.tempat_lomba }}</h3>
              <p v-if="venue.address" class="sub">{{ venue.address }}</p>
              <p class="tag"><b>{{ venue.cabang.length }} cabang lomba</b></p>

              <div v-if="venue.maps_url || venue.qr_code_url" class="v-actions">
                <a v-if="venue.maps_url" :href="venue.maps_url" target="_blank" rel="noopener" class="btn">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z" /><circle cx="12" cy="10" r="2.5" /></svg>
                  Google Maps
                </a>
                <button v-if="venue.qr_code_url" type="button" class="btn ghost" @click="openQrModal(venue)">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><path d="M14 14h3v3h-3zM20 14v.01M14 20v.01M20 20v.01" /></svg>
                  QR Peta
                </button>
              </div>
            </div>
          </div>

          <!-- Majelis & cabang lomba -->
          <p class="list-label">Majelis &amp; Cabang Lomba</p>
          <ul class="v-list">
            <li v-for="(cabang, idx) in visibleCabang(venue)" :key="idx">
              <span class="cname">{{ cabang.bidang_lomba }}</span>
              <span v-if="cabang.majelis" class="chip">{{ cabang.majelis }}</span>
            </li>
          </ul>
          <button
            v-if="canCollapse(venue)"
            type="button"
            class="more"
            :aria-expanded="isExpanded(venue)"
            @click="toggleExpand(venue)"
          >
            {{ isExpanded(venue) ? 'Ringkas daftar' : `Tampilkan ${venue.cabang.length - CABANG_LIMIT} lainnya` }}
          </button>
        </article>
      </div>

      <!-- Tidak ada hasil -->
      <p v-else class="state">Tidak menemukan lokasi atau cabang lomba yang cocok.</p>
    </div>

    <!-- Modal QR -->
    <Transition name="fade">
      <div
        v-if="isQrModalOpen"
        class="overlay"
        role="dialog"
        aria-modal="true"
        :aria-label="`QR Code ${selectedVenueQr.title}`"
        @click.self="closeQrModal"
      >
        <div class="modal">
          <button type="button" class="modal-x" aria-label="Tutup" @click="closeQrModal">×</button>
          <p class="kicker">QR Code peta</p>
          <h4>{{ selectedVenueQr.title }}</h4>
          <div class="qr-box">
            <!-- Render Base64 SVG dari API -->
            <img :src="selectedVenueQr.qrUrl" alt="QR Code" />
          </div>
          <p class="muted">Scan dengan kamera atau aplikasi pemindai untuk membuka navigasi peta.</p>
          <a v-if="selectedVenueQr.mapsUrl" :href="selectedVenueQr.mapsUrl" target="_blank" rel="noopener" class="btn block">
            Buka langsung di Maps
          </a>
        </div>
      </div>
    </Transition>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { storeToRefs } from 'pinia'
import PublicLayout from '../../layouts/PublicLayout.vue'
import { useEventStore } from '../../stores/EventStore.js'

const store = useEventStore()
const { eventData, venues } = storeToRefs(store)

const isLoading = ref(true)
const searchQuery = ref('')

// Jumlah cabang yang tampil sebelum diringkas (agar kartu tetap compact)
const CABANG_LIMIT = 4
const expanded = ref({})

// State Modal QR
const isQrModalOpen = ref(false)
const selectedVenueQr = ref({ qrUrl: '', title: '', mapsUrl: '' })

// Computed property untuk filter pencarian
const filteredVenues = computed(() => {
    const query = searchQuery.value.toLowerCase().trim()
    if (!query) return venues.value

    return venues.value.filter(venue => {
        const matchTempat = venue.tempat_lomba?.toLowerCase().includes(query)
        const matchCabang = venue.cabang.some(cabang =>
            cabang.bidang_lomba?.toLowerCase().includes(query) ||
            cabang.majelis?.toLowerCase().includes(query)
        )
        return matchTempat || matchCabang
    })
})

// Saat mencari, seluruh cabang ditampilkan agar kecocokan tidak tersembunyi
const isSearching = computed(() => !!searchQuery.value.trim())
const isExpanded = (venue) => isSearching.value || !!expanded.value[venue.location_id]
const canCollapse = (venue) => !isSearching.value && venue.cabang.length > CABANG_LIMIT
const visibleCabang = (venue) => (isExpanded(venue) ? venue.cabang : venue.cabang.slice(0, CABANG_LIMIT))
const toggleExpand = (venue) => {
    expanded.value = { ...expanded.value, [venue.location_id]: !expanded.value[venue.location_id] }
}

const openQrModal = (venue) => {
    selectedVenueQr.value = {
        qrUrl: venue.qr_code_url,
        title: venue.tempat_lomba,
        mapsUrl: venue.maps_url
    }
    isQrModalOpen.value = true
}

const closeQrModal = () => {
    isQrModalOpen.value = false
}

const onKeydown = (e) => {
    if (e.key === 'Escape' && isQrModalOpen.value) closeQrModal()
}

onMounted(async () => {
    window.addEventListener('keydown', onKeydown)
    try {
        await store.fetchVenues()
    } finally {
        isLoading.value = false
    }
})

onUnmounted(() => window.removeEventListener('keydown', onKeydown))
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
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.pg-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; padding: 8px 0 14px; flex-wrap: wrap; }
.pg-top h1 { margin: 0; font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: var(--navy); line-height: 1.15; }

/* pencarian */
.search { position: relative; display: flex; align-items: center; min-width: 320px; color: #6d86a3; }
.search svg { position: absolute; left: 13px; pointer-events: none; }
.search input { width: 100%; padding: 10px 36px 10px 38px; font: inherit; font-size: 14px; color: var(--ink); background: #f1f8fd; border: 1px solid #cfe0ee; border-radius: 12px; outline: none; }
.search input::placeholder { color: #8aa0b8; }
.search input:focus { background: #fff; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(15, 95, 140, .15); }
.clear { position: absolute; right: 8px; width: 24px; height: 24px; display: grid; place-items: center; font-size: 18px; line-height: 1; color: var(--muted); background: transparent; border: 0; border-radius: 50%; cursor: pointer; }
.clear:hover { background: #e3edf6; }
.count-line { margin: 0 0 10px; }

.cols { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.col { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; min-width: 0; }
.state { margin: 4px 0 0; padding: 18px 22px; background: #fff; border: 1px dashed #c9d7e6; border-radius: 14px; color: var(--muted); }

/* ====== Kartu lokasi ====== */
.venue { display: flex; flex-direction: column; }
.v-main { display: flex; gap: 12px; padding: 14px 16px; }
.v-photo { position: relative; width: 96px; height: 96px; flex: none; display: grid; place-items: center; color: #6d86a3; background: #eef3f9; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
.v-photo img { width: 100%; height: 100%; object-fit: cover; }
.v-no { position: absolute; top: 5px; left: 5px; width: 22px; height: 22px; display: grid; place-items: center; font-size: 11px; font-weight: 800; color: var(--navy); background: var(--gold); border-radius: 50%; }
.v-info { min-width: 0; flex: 1; }
.v-info h3 { margin: 0; font-size: 16px; font-weight: 800; line-height: 1.25; color: var(--navy); }
.sub { margin: 3px 0 4px; font-size: 13px; color: #3e5370; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.tag { margin: 0; font-size: 12px; color: var(--muted); }
.tag b { color: var(--blue); font-weight: 700; }

.v-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 6px 12px; font: inherit; font-size: 12.5px; font-weight: 700; color: #fff; text-decoration: none; background: var(--blue); border: 1px solid var(--blue); border-radius: 10px; cursor: pointer; }
.btn:hover { background: var(--navy); border-color: var(--navy); }
.btn.ghost { color: var(--navy); background: #fff; border-color: #cfe0ee; }
.btn.ghost:hover { background: #f1f8fd; }
.btn.block { width: 100%; margin-top: 14px; padding: 10px 14px; font-size: 14px; }
.btn:focus-visible, .more:focus-visible, .search input:focus-visible, .modal-x:focus-visible { outline: 3px solid #7cc3ea; outline-offset: 2px; }

.list-label { margin: 0; padding: 8px 16px; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #4b6481; background: #f6f9fc; border-top: 1px solid var(--line); }
.v-list { list-style: none; margin: 0; padding: 0; }
.v-list li { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 7px 16px; font-size: 13px; border-top: 1px solid var(--line); }
.cname { min-width: 0; color: var(--ink); line-height: 1.3; }
.chip { flex: none; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 999px; background: #eaf4fb; color: var(--blue); }
.more { margin-top: auto; padding: 9px 16px; font: inherit; font-size: 12.5px; font-weight: 700; text-align: center; color: var(--blue); background: #fff; border: 0; border-top: 1px solid var(--line); cursor: pointer; }
.more:hover { background: #f4f8fc; }

.skel-card { height: 220px; background: linear-gradient(90deg, #eef3f9 25%, #f8fafc 50%, #eef3f9 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite linear; }
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

/* ====== Modal QR ====== */
.overlay { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(11, 47, 85, .45); backdrop-filter: blur(3px); }
.modal { position: relative; width: 100%; max-width: 340px; padding: 24px 22px 22px; text-align: center; color: var(--ink); background: #fff; border-radius: 16px; box-shadow: 0 18px 50px rgba(11, 47, 85, .3); }
.modal h4 { margin: 0 0 14px; font-size: 17px; font-weight: 800; color: var(--navy); }
.modal .muted { margin-top: 12px; }
.modal-x { position: absolute; top: 10px; right: 10px; width: 30px; height: 30px; display: grid; place-items: center; font-size: 20px; line-height: 1; color: var(--muted); background: #eef3f9; border: 0; border-radius: 50%; cursor: pointer; }
.modal-x:hover { background: #e3edf6; }
.qr-box { display: inline-block; padding: 10px; background: #fff; border: 1px solid var(--line); border-radius: 14px; }
.qr-box img { display: block; width: 184px; height: 184px; }
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ====== Mobile ====== */
@media (max-width: 960px) {
  .cols { grid-template-columns: minmax(0, 1fr); }
  .search { min-width: 100%; }
}
@media (max-width: 420px) {
  .v-photo { width: 76px; height: 76px; }
}
</style>