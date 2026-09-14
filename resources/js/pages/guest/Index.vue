<template>
    <PublicLayout cta-text="Kec. IV Jurai" :show-footer="true">
        
        <!-- MAIN KONTEN -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12 lg:space-y-16 relative z-10">
            
            <!-- HERO SECTION (Beranda) -->
            <section id="beranda" class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center pt-4 scroll-mt-24">
                <div class="col-span-1 lg:col-span-8 space-y-8">
                    <div class="space-y-4">
                        <span class="inline-block border border-emerald-200 bg-emerald-50/50 text-emerald-800 text-[11px] font-bold tracking-widest uppercase px-3 py-1 rounded-full">
                            {{ eventData.app_name || 'Tingkat Kabupaten Pesisir Selatan' }}
                        </span>
                        <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-serif text-slate-900 leading-[1.15]">
                            {{ eventData.event_name || 'MTQ XLII KABUPATEN PESISIR SELATAN' }} <br /> 
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-800 to-teal-700 font-semibold">Tahun 2026</span>
                        </h1>
                        <p class="text-slate-600 max-w-2xl text-lg leading-relaxed font-light">
                            Selamat datang di portal resmi Musabaqah Tilawatil Qur’an Nasional ke-42. Perhelatan akbar tingkat kabupaten ini diselenggarakan di <strong>{{ eventData.event_location || 'Kecamatan IV Jurai' }}</strong> dengan semangat <em>"{{ eventData.event_tagline }}"</em>.
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button class="bg-emerald-800 text-white px-7 py-3.5 rounded-xl font-medium text-sm hover:bg-emerald-700 transition-all duration-300 shadow-lg shadow-emerald-900/15 hover:shadow-emerald-900/25">
                            Buku Panduan Juknis
                        </button>
                        <a href="#tahapan" class="bg-white border border-emerald-100 text-slate-700 px-7 py-3.5 rounded-xl font-medium text-sm hover:bg-emerald-50 hover:border-emerald-200 transition-all duration-300 shadow-sm inline-block">
                            Lihat Linimasa &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-4">
                        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl border border-white shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                            <span class="text-[10px] uppercase font-bold text-emerald-600/70 block mb-1">📅 PELAKSANAAN</span>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">1 - 6 November 2026</h4>
                            <p class="text-[13px] text-slate-500 leading-snug">Jadwal inti perlombaan MTQ.</p>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl border border-white shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                            <span class="text-[10px] uppercase font-bold text-emerald-600/70 block mb-1">👥 KAFILAH</span>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">15 Kecamatan</h4>
                            <p class="text-[13px] text-slate-500 leading-snug">Utusan kafilah terbaik se-Kabupaten Pesisir Selatan.</p>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl border border-white shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                            <span class="text-[10px] uppercase font-bold text-emerald-600/70 block mb-1">📍 TUVAN RUMAH</span>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">Kec. IV Jurai</h4>
                            <p class="text-[13px] text-slate-500 leading-snug">Pusat pemerintahan dan arena utama.</p>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-4 h-full">
                    <div class="bg-white rounded-[2rem] p-2 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col items-center h-full justify-between">
                        <div class="flex-1 flex items-center justify-center py-12 w-full bg-gradient-to-b from-slate-50 to-white rounded-[1.75rem] mb-2 border border-slate-50">
                            
                            <div v-if="isLoading" class="w-36 h-40 bg-emerald-50/50 border-2 border-dashed border-emerald-200 rounded-2xl flex flex-col items-center justify-center text-emerald-600 space-y-2 animate-pulse">
                                <span class="text-xs font-medium opacity-60">Memuat Logo...</span>
                            </div>
                                 
                            <img v-else-if="eventData.logo_event" 
                                 src="https://res.cloudinary.com/dezj1x6xp/image/upload/v1763442267/PandanViewMandeh/uaccfb20uvhpvjyafdoj.png"
                                 :alt="eventData.event_name" 
                                 class="w-auto h-96 object-contain drop-shadow-md transition-all duration-500 hover:scale-105"
                            />

                            <div v-else class="w-36 h-40 bg-emerald-50/50 border-2 border-dashed border-emerald-200 rounded-2xl flex flex-col items-center justify-center text-emerald-600 space-y-2">
                                <span class="text-xs font-medium opacity-60">[ LOGO MTQ ]</span>
                            </div>

                        </div>
                        <div class="bg-gradient-to-br from-emerald-800 to-teal-900 w-full p-5 rounded-3xl text-white shadow-lg shadow-emerald-900/20 relative overflow-hidden">
                            <span class="text-[10px] uppercase tracking-widest font-semibold text-emerald-200/80">Status Pendaftaran</span>
                            <div class="flex justify-between items-center mt-1.5 relative z-10">
                                <h4 class="font-bold text-[15px]">Pendaftaran e-MTQ Dibuka</h4>
                                <span class="bg-white/10 backdrop-blur-sm px-2.5 py-1.5 rounded-lg text-xs font-medium">&rarr;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION SPONSOR -->
            <section v-if="eventData.logo_sponsor_1 || eventData.logo_sponsor_2 || eventData.logo_sponsor_3" class="bg-white/70 backdrop-blur-md py-10 px-8 rounded-3xl border border-slate-100 shadow-sm">
                <div class="text-center mb-6">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Didukung Oleh & Instansi Terkait</span>
                </div>
                <div class="flex flex-wrap justify-center items-center gap-10 lg:gap-20">
                    <div v-if="eventData.logo_sponsor_1" class="h-12 flex items-center justify-center">
                        <img :src="eventData.logo_sponsor_1" alt="Sponsor 1" class="max-h-12 w-auto object-contain grayscale opacity-75 hover:grayscale-0 hover:opacity-100 transition duration-300" />
                    </div>
                    <div v-if="eventData.logo_sponsor_2" class="h-12 flex items-center justify-center">
                        <img :src="eventData.logo_sponsor_2" alt="Sponsor 2" class="max-h-12 w-auto object-contain grayscale opacity-75 hover:grayscale-0 hover:opacity-100 transition duration-300" />
                    </div>
                    <div v-if="eventData.logo_sponsor_3" class="h-12 flex items-center justify-center">
                        <img :src="eventData.logo_sponsor_3" alt="Sponsor 3" class="max-h-12 w-auto object-contain grayscale opacity-75 hover:grayscale-0 hover:opacity-100 transition duration-300" />
                    </div>
                </div>
            </section>

            <!-- HASIL MUSABAQAH SECTION -->
            <section class="bg-white/90 backdrop-blur-md rounded-3xl p-8 lg:p-10 border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
                <div class="max-w-md">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-2">HASIL MUSABAQAH</span>
                    <h3 class="text-[26px] font-serif text-slate-800 mb-2 leading-tight">Ikuti Perolehan Nilai & Klasemen Resmi.</h3>
                    <p class="text-sm text-slate-500 font-light">Pilih menu untuk memantau hasil nilai dewan hakim dari seluruh cabang lomba.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 w-full sm:w-64 flex justify-between items-center group cursor-pointer hover:border-emerald-300 hover:bg-emerald-50/30 transition-all duration-300">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-800">Penyisihan & Final</h4>
                            <p class="text-xs text-slate-500 mt-1">Nilai per cabang lomba</p>
                        </div>
                        <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 w-full sm:w-64 flex justify-between items-center group cursor-pointer hover:border-emerald-300 hover:bg-emerald-50/30 transition-all duration-300">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-800">Juara Umum</h4>
                            <p class="text-xs text-slate-500 mt-1">Klasemen kafilah kecamatan</p>
                        </div>
                        <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
                    </div>
                </div>
            </section>

            <!-- TENTANG KEGIATAN SECTION -->
            <section id="tentang" class="bg-white/90 backdrop-blur-md rounded-3xl p-8 lg:p-12 border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col lg:flex-row gap-12 lg:gap-20 scroll-mt-24">
                <div class="lg:w-1/2">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-4">SYIAR ISLAM DI KECAMATAN IV JURAI</span>
                    <h3 class="text-3xl lg:text-4xl font-serif text-slate-800 mb-6 leading-snug">
                        Meneguhkan Nilai Qur'ani <br/> di Bumi Sejuta Pesona.
                    </h3>
                    <p class="text-[15px] text-slate-600 leading-relaxed font-light mb-4">
                        Kecamatan IV Jurai merupakan pusat pemerintahan Kabupaten Pesisir Selatan di mana kota Painan berada. Dengan wilayah seluas 368,19 km² yang mencakup 20 nagari, IV Jurai dengan bangga menjadi tuan rumah penyelenggaraan MTQ Nasional Tingkat Kabupaten Pesisir Selatan ke-42 Tahun 2026.
                    </p>
                    <p class="text-[15px] text-slate-600 leading-relaxed font-light">
                        Ajang ini tidak hanya sekadar wadah kompetisi seni baca, hafalan, tafsir, dan pemahaman Al-Qur'an, melainkan momentum strategis untuk membina kader-kader Qur'ani yang unggul, religius, serta berakhlakul karimah di seluruh pelosok nagari.
                    </p>
                </div>
                <div class="lg:w-1/2 flex flex-col justify-center space-y-2">
                    <div class="border-b border-slate-100 py-5 flex justify-between items-center group cursor-pointer">
                        <span class="font-medium text-slate-700 text-sm group-hover:text-emerald-700">Pedoman & Juknis Perlombaan</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    </div>
                    <div class="border-b border-slate-100 py-5 flex justify-between items-center group cursor-pointer">
                        <span class="font-medium text-slate-700 text-sm group-hover:text-emerald-700">Pendaftaran Kafilah 15 Kecamatan</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    </div>
                    <div class="py-5 flex justify-between items-center group cursor-pointer">
                        <span class="font-medium text-slate-700 text-sm group-hover:text-emerald-700">Cabang & Sistem Penomoran Peserta</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    </div>
                </div>
            </section>

            <!-- TAHAPAN PENDAFTARAN -->
            <section id="tahapan" class="py-12 relative z-10 scroll-mt-24">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-2">LINIMASA KEGIATAN</span>
                    <h3 class="text-3xl font-serif text-slate-800 mb-4">Tahapan Pendaftaran & Verifikasi e-MTQ.</h3>
                    <p class="text-sm text-slate-500 font-light">Sistem pendaftaran dan verifikasi berkas kafilah dilaksanakan secara transparan dan bertahap.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
                        <div class="flex justify-between items-start mb-6">
                            <span class="text-xs font-medium text-slate-400">7 - 21 Sep 2026</span>
                            <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">Pendaftaran</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 mb-3">Pendaftaran Online</h4>
                        <p class="text-[13px] text-slate-500 font-light">Input data melalui aplikasi e-MTQ melampirkan scan Surat Mandat Camat, KTP/KK, Akta Kelahiran, dan Pasfoto.</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
                        <div class="flex justify-between items-start mb-6">
                            <span class="text-xs font-medium text-slate-400">5 - 13 Okt 2026</span>
                            <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">Verifikasi</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 mb-3">Verifikasi & Sanggah</h4>
                        <p class="text-[13px] text-slate-500 font-light">Verifikasi tahap I & II oleh Panitia Kabupaten serta masa sanggah dan perbaikan berkas kontingen.</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
                        <div class="flex justify-between items-start mb-6">
                            <span class="text-xs font-medium text-slate-400">26 - 27 Okt 2026</span>
                            <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">Technical Meeting</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 mb-3">Pengesahan & TM</h4>
                        <p class="text-[13px] text-slate-500 font-light">Pengesahan peserta tetap, rapat official, dan pelaksanaan technical meeting sebelum lomba.</p>
                    </div>
                </div>
            </section>

            <!-- INFORMASI PARTISIPASI -->
            <section id="informasi" class="pt-4 pb-8 scroll-mt-24">
                <div class="max-w-2xl mb-10">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-2">KETENTUAN UMUM</span>
                    <h3 class="text-[26px] font-serif text-slate-800 mb-4 leading-snug">Persyaratan dan Ketentuan Kafilah.</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-transparent border-t border-slate-200/60 pt-6">
                        <h4 class="font-bold text-slate-800 text-sm mb-2">Domisili & Mandat</h4>
                        <p class="text-[13px] text-slate-500 font-light">Peserta merupakan penduduk Kabupaten Pesisir Selatan berdomisili di kecamatan masing-masing, dibuktikan dengan Surat Mandat Camat/LPTQ.</p>
                    </div>
                    <div class="bg-transparent border-t border-slate-200/60 pt-6">
                        <h4 class="font-bold text-slate-800 text-sm mb-2">Batas Usia</h4>
                        <p class="text-[13px] text-slate-500 font-light">Perhitungan batas usia maksimal bagi seluruh peserta dihitung mulai tanggal 1 Juli 2026.</p>
                    </div>
                    <div class="bg-transparent border-t border-slate-200/60 pt-6">
                        <h4 class="font-bold text-slate-800 text-sm mb-2">Pendaftaran Ulang</h4>
                        <p class="text-[13px] text-slate-500 font-light">Pendaftaran ulang dilaksanakan H-1 di Sekretariat Panitia dengan menunjukkan KTP/KK/Akte Asli.</p>
                    </div>
                </div>
            </section>

            <!-- CALL TO ACTION BANNER -->
            <section class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 rounded-[2.5rem] p-10 md:p-14 flex flex-col md:flex-row justify-between items-center gap-8 shadow-xl relative overflow-hidden">
                <div class="text-left text-white max-w-xl relative z-10">
                    <span class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-3">PORTAL ADMIN KECAMATAN</span>
                    <h3 class="text-3xl font-serif mb-3 leading-tight">Kelola kafilah dan dokumen secara efisien.</h3>
                    <p class="text-emerald-100 text-[15px] font-light">Akses sistem pendaftaran terpadu e-MTQ untuk mendaftarkan kontingen terbaik kecamatan Anda pada MTQ ke-42 di IV Jurai.</p>
                </div>
                <a href="https://admin.mtqnpesisirselatan.com" target="_blank" rel="noopener noreferrer" class="inline-block text-center bg-white text-emerald-900 px-9 py-4 rounded-2xl font-bold text-sm w-full md:w-auto hover:bg-slate-50 transition shrink-0 relative z-10 shadow-lg">
                    Buka Dashboard Admin
                </a>
            </section>
        </main>
    </PublicLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import PublicLayout from '../../layouts/PublicLayout.vue'
import { useEventStore } from '../../stores/EventStore.js'

const store = useEventStore()
// Extract state agar reaktif
const { eventData } = storeToRefs(store) 

const isLoading = ref(true)

onMounted(async () => {
    // Panggil action dari store
    await store.fetchEventData() 
    isLoading.value = false
})
</script>

<style scoped>
html, body {
    scroll-behavior: smooth;
}
</style>