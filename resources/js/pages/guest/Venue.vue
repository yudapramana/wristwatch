<template>
    <PublicLayout cta-text="Masuk Portal" :show-footer="false">
        
        <!-- MAIN KONTEN -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8 relative z-10">
            
            <!-- HEADER SECTION -->
            <section class="text-center space-y-4">
                <span class="inline-block border border-emerald-200 bg-emerald-50/50 text-emerald-800 text-[11px] font-bold tracking-widest uppercase px-3 py-1 rounded-full">
                    Informasi Arena MTQ
                </span>
                <h1 class="text-3xl sm:text-4xl font-serif text-slate-900 leading-[1.15]">
                    Daftar Lokasi Lomba
                </h1>
                <p class="text-slate-500 text-sm font-light">
                    {{ eventData.event_name || 'MTQ XLII Kabupaten Pesisir Selatan' }} TAHUN {{ eventData.event_year || '2026' }}
                </p>
            </section>

            <!-- SEARCH BAR (Sticky Glassmorphism) -->
            <section class="sticky top-[88px] z-20">
                <div class="bg-white/80 backdrop-blur-md p-2 rounded-full border border-slate-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center">
                    <span class="pl-4 pr-2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Cari bidang lomba, majelis, atau nama tempat..." 
                        class="w-full bg-transparent border-none focus:ring-0 text-sm text-slate-700 py-3 px-2 outline-none placeholder-slate-400"
                    />
                    <button v-if="searchQuery" @click="searchQuery = ''" class="pr-4 text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
                <p v-if="searchQuery" class="text-xs text-slate-500 mt-2 px-4">
                    Menemukan {{ filteredVenues.length }} lokasi.
                </p>
            </section>

            <!-- LOADING SKELETON -->
            <div v-if="isLoading" class="space-y-6">
                <div v-for="i in 3" :key="i" class="bg-white/80 rounded-3xl p-6 border border-slate-100 shadow-sm animate-pulse h-48"></div>
            </div>

            <!-- VENUE LIST -->
            <section v-else class="space-y-6">
                <div 
                    v-for="(venue, index) in filteredVenues" 
                    :key="venue.location_id"
                    class="bg-white/90 backdrop-blur-md rounded-3xl border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.02)] overflow-hidden hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition-all duration-300"
                >
                    <div class="sm:flex">
                        <!-- Foto Venue & Tombol QR -->
                        <div class="relative sm:w-64 shrink-0 bg-slate-100/50">
                            <img v-if="venue.photo_url" :src="venue.photo_url" :alt="venue.tempat_lomba" class="w-full h-48 sm:h-full object-cover">
                            <div v-else class="w-full h-48 sm:h-full bg-gradient-to-br from-emerald-50 to-teal-50 flex items-center justify-center text-4xl opacity-70">
                                🕌
                            </div>

                            <!-- Nomor Urut -->
                            <span class="absolute top-4 left-4 bg-white/90 backdrop-blur text-emerald-800 text-xs font-bold w-7 h-7 rounded-full flex items-center justify-center shadow-sm">
                                {{ index + 1 }}
                            </span>

                            <!-- Tombol QR Code -->
                            <button 
                                v-if="venue.qr_code_url" 
                                @click="openQrModal(venue)"
                                class="absolute top-4 right-4 bg-white/90 backdrop-blur hover:bg-white w-9 h-9 rounded-full shadow-sm flex items-center justify-center text-lg transition-colors group"
                                title="Lihat QR Code Map"
                            >
                                <span class="grayscale group-hover:grayscale-0 transition duration-300">🔳</span>
                            </button>
                        </div>

                        <!-- Info Venue & List Cabang -->
                        <div class="p-5 sm:p-6 sm:flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start gap-2">
                                    <span class="shrink-0 mt-1 text-emerald-600">📍</span>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ venue.tempat_lomba }}</h3>
                                        <p v-if="venue.address" class="text-slate-500 text-[13px] mt-1">{{ venue.address }}</p>
                                    </div>
                                </div>
                                <span class="inline-block bg-emerald-50 text-emerald-700 text-[11px] font-bold px-2.5 py-1 rounded-full mt-3">
                                    {{ venue.cabang.length }} Cabang Lomba
                                </span>
                            </div>

                            <div class="mt-5">
                                <a v-if="venue.maps_url" :href="venue.maps_url" target="_blank" class="w-full flex items-center justify-center gap-2 bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-200 text-slate-700 hover:text-emerald-800 text-sm font-medium py-3 rounded-xl transition-all duration-300">
                                    🗺️ Buka di Google Maps
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Cabang Lomba -->
                    <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                            Majelis & Cabang Lomba
                        </p>
                        <div class="space-y-2">
                            <div v-for="(cabang, idx) in venue.cabang" :key="idx" class="flex items-center justify-between gap-3 bg-white px-4 py-2.5 rounded-xl border border-slate-100 shadow-sm">
                                <span class="text-slate-700 font-medium text-[13px] leading-snug">
                                    {{ cabang.bidang_lomba }}
                                </span>
                                <span v-if="cabang.majelis" class="shrink-0 bg-emerald-50 text-emerald-700 text-[11px] font-bold px-2.5 py-1 rounded-lg">
                                    {{ cabang.majelis }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jika tidak ada hasil pencarian -->
                <div v-if="filteredVenues.length === 0" class="text-center py-16 bg-white/50 backdrop-blur rounded-3xl border border-slate-100">
                    <span class="text-4xl opacity-50 mb-4 block">🔍</span>
                    <p class="text-slate-500 text-sm">Tidak menemukan lokasi atau cabang lomba yang cocok.</p>
                </div>
            </section>
        </main>

        <!-- MODAL QR CODE (Tetap di dalam Layout agar styling konsisten) -->
        <Transition name="fade">
            <div v-if="isQrModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="closeQrModal">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all">
                    <button @click="closeQrModal" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                    
                    <div class="text-center">
                        <h4 class="font-bold text-slate-800 mb-5">{{ selectedVenueQr.title }}</h4>
                        <div class="p-3 bg-white border-2 border-slate-100 rounded-2xl shadow-inner inline-block">
                            <!-- Render Base64 SVG dari API -->
                            <img :src="selectedVenueQr.qrUrl" alt="QR Code" class="w-48 h-48">
                        </div>
                        <p class="text-xs text-slate-500 mt-4 font-light px-2">
                            Scan QR Code ini menggunakan kamera atau aplikasi scanner untuk navigasi peta.
                        </p>
                        <a v-if="selectedVenueQr.mapsUrl" :href="selectedVenueQr.mapsUrl" target="_blank" class="mt-6 w-full inline-block bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium py-3 rounded-xl transition-colors shadow-lg shadow-emerald-900/20">
                            🗺️ Buka Langsung di Maps
                        </a>
                    </div>
                </div>
            </div>
        </Transition>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import PublicLayout from '../../layouts/PublicLayout.vue'
import { useEventStore } from '../../stores/EventStore.js'

const store = useEventStore()
const { eventData, venues } = storeToRefs(store)

const isLoading = ref(true)
const searchQuery = ref('')

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

onMounted(async () => {
    await store.fetchVenues()
    isLoading.value = false
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>