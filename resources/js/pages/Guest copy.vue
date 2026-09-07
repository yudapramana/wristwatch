<template>
    <!-- Background utama: Slate sangat terang dengan pola dot samar dan gradasi hijau lembut -->
    <div class="relative min-h-screen bg-slate-50 text-slate-800 font-sans selection:bg-emerald-200 selection:text-emerald-900 overflow-hidden z-0">
        
        <!-- Subtle Green Gradient & Pattern Background -->
        <div class="absolute inset-0 z-[-1] pointer-events-none">
            <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] opacity-[0.04] [background-size:24px_24px]"></div>
            <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-emerald-200/30 to-teal-100/10 blur-[120px]"></div>
            <div class="absolute top-[40%] -right-[10%] w-[60%] h-[60%] rounded-full bg-gradient-to-tl from-emerald-200/20 to-green-100/10 blur-[100px]"></div>
        </div>

        <!-- NAVBAR -->
        <header class="w-full bg-transparent pt-6 pb-4 relative z-20 sticky top-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white/90 backdrop-blur-md rounded-full px-6 py-3 flex justify-between items-center border border-white/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-gradient-to-br from-emerald-700 to-emerald-900 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-inner">
                            42
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-sm tracking-wide text-slate-900 leading-tight">MTQ XLII PESSEL</span>
                            <span class="text-[10px] text-emerald-700 font-medium">IV Jurai 2026</span>
                        </div>
                    </div>

                    <!-- Menu Navigasi dengan Anchor Links -->
                    <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-500">
                        <a href="#beranda" class="hover:text-emerald-700 transition-colors">Beranda</a>
                        <a href="#tentang" class="hover:text-emerald-700 transition-colors">Tentang</a>
                        <a href="#tahapan" class="hover:text-emerald-700 transition-colors">Tahapan</a>
                        <a href="#informasi" class="hover:text-emerald-700 transition-colors">Informasi</a>
                    </nav>

                    <div>
                        <a href="#" class="bg-emerald-800 hover:bg-emerald-700 text-white font-medium px-6 py-2.5 rounded-full text-sm transition-all duration-300 shadow-sm hover:shadow-emerald-900/20">
                            Masuk Portal
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN KONTEN -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12 lg:space-y-16 relative z-10">
            
            <!-- HERO SECTION (Beranda) -->
            <section id="beranda" class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center pt-4 scroll-mt-24">
                <!-- Kiri: Teks & Info Utama -->
                <div class="col-span-1 lg:col-span-8 space-y-8">
                    <div class="space-y-4">
                        <span class="inline-block border border-emerald-200 bg-emerald-50/50 text-emerald-800 text-[11px] font-bold tracking-widest uppercase px-3 py-1 rounded-full">
                            {{ eventData.app_name || 'Tingkat Kabupaten Pesisir Selatan' }}
                        </span>
                        <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-serif text-slate-900 leading-[1.15]">
                            {{ eventData.event_name || 'Musabaqah Tilawatil Qur’an' }} <br /> 
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-800 to-teal-700 font-semibold">Nasional XLII</span>
                        </h1>
                        <p class="text-slate-600 max-w-2xl text-lg leading-relaxed font-light">
                            Selamat datang di pusat informasi resmi pelaksanaan MTQ Nasional ke-42 Tingkat Kabupaten Pesisir Selatan. Tahun ini, <strong>{{ eventData.event_location || 'Kecamatan IV Jurai' }}</strong> bangga menjadi tuan rumah perhelatan akbar dengan semangat <em>"{{ eventData.event_tagline }}"</em>.
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button class="bg-emerald-800 text-white px-7 py-3.5 rounded-xl font-medium text-sm hover:bg-emerald-700 transition-all duration-300 shadow-lg shadow-emerald-900/15 hover:shadow-emerald-900/25">
                            Buku Panduan
                        </button>
                        <a href="#tahapan" class="bg-white border border-emerald-100 text-slate-700 px-7 py-3.5 rounded-xl font-medium text-sm hover:bg-emerald-50 hover:border-emerald-200 transition-all duration-300 shadow-sm inline-block">
                            Lihat Tahapan &rarr;
                        </a>
                    </div>

                    <!-- Grid 3 Info Box -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-4">
                        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl border border-white shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                            <span class="text-[10px] uppercase font-bold text-emerald-600/70 block mb-1">📅 PELAKSANAAN</span>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">{{ eventData.start_date || 'Juli 2026' }}</h4>
                            <p class="text-[13px] text-slate-500 leading-snug">Rangkaian acara hingga penutupan.</p>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl border border-white shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                            <span class="text-[10px] uppercase font-bold text-emerald-600/70 block mb-1">👥 KAFILAH</span>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">15 Kecamatan</h4>
                            <p class="text-[13px] text-slate-500 leading-snug">Utusan kafilah terbaik se-Pesisir Selatan.</p>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl border border-white shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                            <span class="text-[10px] uppercase font-bold text-emerald-600/70 block mb-1">📍 LOKASI</span>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">{{ eventData.event_location || 'IV Jurai' }}</h4>
                            <p class="text-[13px] text-slate-500 leading-snug">Berpusat di arena utama.</p>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Kartu Logo Dinamis & Status -->
                <div class="col-span-1 lg:col-span-4 h-full">
                    <div class="bg-white rounded-[2rem] p-2 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col items-center h-full justify-between">
                        <div class="flex-1 flex items-center justify-center py-14 w-full bg-gradient-to-b from-slate-50 to-white rounded-[1.75rem] mb-2 border border-slate-50">
                            
                            <!-- Loading / Placeholder jika logo belum siap -->
                            <div v-if="isLoading" class="w-36 h-44 bg-emerald-50/50 border-2 border-dashed border-emerald-200 rounded-2xl flex flex-col items-center justify-center text-emerald-600 space-y-2 animate-pulse">
                                <span class="text-xs font-medium opacity-60">Memuat Logo...</span>
                            </div>

                            <!-- Render Logo Event Dinamis dari Database -->
                            <img v-else-if="eventData.logo_event" 
                                 :src="eventData.logo_event" 
                                 :alt="eventData.event_name" 
                                 class="w-auto h-36 object-contain drop-shadow-md transition-all duration-500 hover:scale-105"
                            />

                            <div v-else class="w-36 h-44 bg-emerald-50/50 border-2 border-dashed border-emerald-200 rounded-2xl flex flex-col items-center justify-center text-emerald-600 space-y-2">
                                <span class="text-xs font-medium opacity-60">[ LOGO ]</span>
                            </div>

                        </div>
                        <div class="bg-gradient-to-br from-emerald-800 to-teal-900 w-full p-5 rounded-3xl text-white shadow-lg shadow-emerald-900/20 relative overflow-hidden">
                            <span class="text-[10px] uppercase tracking-widest font-semibold text-emerald-200/80">Status Saat Ini</span>
                            <div class="flex justify-between items-center mt-1.5 relative z-10">
                                <h4 class="font-bold text-[15px]">Menuju Tahap Persiapan</h4>
                                <span class="bg-white/10 backdrop-blur-sm px-2.5 py-1.5 rounded-lg text-xs font-medium">&rarr;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION SPONSOR / LOGO PENDUKUNG (Opsional, memanfaatkan data sponsor dari DB) -->
            <section v-if="eventData.logo_sponsor_1 || eventData.logo_sponsor_2 || eventData.logo_sponsor_3" class="bg-white/60 backdrop-blur-sm py-8 px-6 rounded-2xl border border-slate-100 flex flex-wrap justify-center items-center gap-8 lg:gap-16">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest w-full text-center mb-[-8px]">Didukung Oleh / Instansi Terkait</span>
                <img v-if="eventData.logo_sponsor_1" :src="eventData.logo_sponsor_1" alt="Sponsor 1" class="h-10 object-contain grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition duration-300" />
                <img v-if="eventData.logo_sponsor_2" :src="eventData.logo_sponsor_2" alt="Sponsor 2" class="h-10 object-contain grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition duration-300" />
                <img v-if="eventData.logo_sponsor_3" :src="eventData.logo_sponsor_3" alt="Sponsor 3" class="h-10 object-contain grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition duration-300" />
            </section>

            <!-- HASIL MUSABAQAH SECTION -->
            <section class="bg-white/90 backdrop-blur-md rounded-3xl p-8 lg:p-10 border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.02)] flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
                <div class="max-w-md">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-2">HASIL MUSABAQAH</span>
                    <h3 class="text-[26px] font-serif text-slate-800 mb-2 leading-tight">Ikuti Perolehan Nilai & Klasemen Resmi.</h3>
                    <p class="text-sm text-slate-500 font-light">Pilih menu di samping untuk melihat rincian kejuaraan dari seluruh majelis hakim.</p>
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
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-4">SYIAR ISLAM DI BUMI SEJUTA PESONA</span>
                    <h3 class="text-3xl lg:text-4xl font-serif text-slate-800 mb-6 leading-snug">
                        Meneguhkan Nilai Qur'ani <br/> di Kecamatan IV Jurai.
                    </h3>
                    <p class="text-[15px] text-slate-600 leading-relaxed font-light mb-4">
                        Musabaqah Tilawatil Qur'an (MTQ) Nasional Tingkat Kabupaten Pesisir Selatan ke-42 merupakan perhelatan keagamaan akbar yang mempertemukan duta-duta Al-Qur'an terbaik dari 15 kecamatan. Ajang ini tidak hanya menjadi wadah kompetisi seni baca dan hafalan, tetapi juga momentum strategis untuk membumikan nilai-nilai ajaran Islam di tengah masyarakat.
                    </p>
                    <p class="text-[15px] text-slate-600 leading-relaxed font-light">
                        Sebagai tuan rumah, Kecamatan IV Jurai berkomitmen menyelenggarakan kegiatan ini dengan sistem digital yang transparan dan profesional, guna melahirkan generasi Qur'ani yang unggul, berprestasi, dan berakhlakul karimah.
                    </p>
                </div>
                <div class="lg:w-1/2 flex flex-col justify-center space-y-2">
                    <div class="border-b border-slate-100 py-5 flex justify-between items-center group cursor-pointer">
                        <span class="font-medium text-slate-700 text-sm group-hover:text-emerald-700">Pedoman & Juknis Lomba</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    </div>
                    <div class="border-b border-slate-100 py-5 flex justify-between items-center group cursor-pointer">
                        <span class="font-medium text-slate-700 text-sm group-hover:text-emerald-700">Pendaftaran Kafilah Kecamatan</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    </div>
                    <div class="py-5 flex justify-between items-center group cursor-pointer">
                        <span class="font-medium text-slate-700 text-sm group-hover:text-emerald-700">Daftar Cabang & Golongan Musabaqah</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    </div>
                </div>
            </section>

            <!-- TAHAPAN PENDAFTARAN -->
            <section id="tahapan" class="py-12 relative z-10 scroll-mt-24">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-2">ALUR PENDAFTARAN</span>
                    <h3 class="text-3xl font-serif text-slate-800 mb-4">Fase persiapan administratif yang terukur.</h3>
                    <p class="text-sm text-slate-500 font-light">Setiap tahapan dirancang untuk memudahkan kesiapan kafilah kecamatan mulai dari registrasi akun hingga validasi faktual.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
                        <div class="flex justify-between items-start mb-6">
                            <span class="text-xs font-medium text-slate-400">Mei 2026</span>
                            <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">Fase 1</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 mb-3">Registrasi Admin</h4>
                        <p class="text-[13px] text-slate-500 font-light">Pendaftaran akun resmi LPTQ/KUA Kecamatan melalui formulir sistem terpusat.</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
                        <div class="flex justify-between items-start mb-6">
                            <span class="text-xs font-medium text-slate-400">Juni 2026</span>
                            <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">Fase 2</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 mb-3">Input Data Kafilah</h4>
                        <p class="text-[13px] text-slate-500 font-light">Pemilihan cabang lomba dan pengisian biodata lengkap peserta.</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
                        <div class="flex justify-between items-start mb-6">
                            <span class="text-xs font-medium text-slate-400">Juli 2026</span>
                            <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">Fase 3</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 mb-3">Verifikasi Faktual</h4>
                        <p class="text-[13px] text-slate-500 font-light">Pemeriksaan dan pencocokan berkas asli oleh tim panitia kabupaten.</p>
                    </div>
                </div>
            </section>

            <!-- INFORMASI PARTISIPASI -->
            <section id="informasi" class="pt-4 pb-8 scroll-mt-24">
                <div class="max-w-2xl mb-10">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-2">PANDUAN SINGKAT</span>
                    <h3 class="text-[26px] font-serif text-slate-800 mb-4 leading-snug">Ringkasan ketentuan umum bagi delegasi kecamatan.</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-transparent border-t border-slate-200/60 pt-6">
                        <h4 class="font-bold text-slate-800 text-sm mb-2">Syarat Dokumen</h4>
                        <p class="text-[13px] text-slate-500 font-light">KTP Pesisir Selatan / KK Domisili, akta kelahiran, serta surat mandat resmi dari Camat.</p>
                    </div>
                    <div class="bg-transparent border-t border-slate-200/60 pt-6">
                        <h4 class="font-bold text-slate-800 text-sm mb-2">Kunci Pendaftaran</h4>
                        <p class="text-[13px] text-slate-500 font-light">Akses input data otomatis terkunci tepat pada batas waktu yang telah dijadwalkan.</p>
                    </div>
                    <div class="bg-transparent border-t border-slate-200/60 pt-6">
                        <h4 class="font-bold text-slate-800 text-sm mb-2">Delegasi Resmi</h4>
                        <p class="text-[13px] text-slate-500 font-light">Peserta diakui keikutsertaannya hanya jika didaftarkan melalui LPTQ kecamatan masing-masing.</p>
                    </div>
                </div>
            </section>

            <!-- CALL TO ACTION BANNER -->
            <section class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 rounded-[2.5rem] p-10 md:p-14 flex flex-col md:flex-row justify-between items-center gap-8 shadow-xl relative overflow-hidden">
                <div class="text-left text-white max-w-xl relative z-10">
                    <span class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-3">PORTAL ADMIN KECAMATAN</span>
                    <h3 class="text-3xl font-serif mb-3 leading-tight">Kelola kafilah dan dokumen secara efisien.</h3>
                    <p class="text-emerald-100 text-[15px] font-light">Akses sistem pendaftaran terpadu untuk mengelola kontingen kecamatan Anda menuju persiapan MTQ Nasional XLII.</p>
                </div>
                <button class="bg-white text-emerald-900 px-9 py-4 rounded-2xl font-bold text-sm w-full md:w-auto hover:bg-slate-50 transition shrink-0 relative z-10">
                    Buka Dashboard
                </button>
            </section>
        </main>

        <!-- FOOTER -->
        <footer class="bg-white border-t border-slate-100 pt-16 pb-8 mt-12 relative z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                    <div class="md:col-span-1">
                        <div class="flex items-center space-x-3 mb-5">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-700 to-emerald-900 rounded-full flex items-center justify-center text-white font-bold text-[10px]">
                                42
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-sm text-slate-900 leading-tight">MTQ XLII PESSEL</span>
                                <span class="text-[10px] text-emerald-700">IV Jurai 2026</span>
                            </div>
                        </div>
                        <p class="text-[13px] text-slate-500 font-light leading-relaxed pr-6">
                            Pusat informasi teknis dan pendaftaran resmi Musabaqah Tilawatil Qur'an Nasional ke-42 Tingkat Kabupaten Pesisir Selatan di Kecamatan IV Jurai.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-xs tracking-widest uppercase mb-5">Navigasi Utama</h4>
                        <ul class="space-y-3 text-[13px] text-slate-500 font-light">
                            <li><a href="#beranda" class="hover:text-emerald-700">Beranda Utama</a></li>
                            <li><a href="#tahapan" class="hover:text-emerald-700">Tahapan & Linimasa</a></li>
                            <li><a href="#tentang" class="hover:text-emerald-700">Tentang Kegiatan</a></li>
                        </ul>
                    </div>
                    <div>
                        <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-100">
                            <h4 class="font-bold text-slate-800 text-[11px] tracking-widest uppercase mb-2">Penyelenggara</h4>
                            <p class="text-[12px] text-slate-500 font-light mb-4">
                                LPTQ Kabupaten Pesisir Selatan <br/> & Panitia Pelaksana Kecamatan IV Jurai
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-slate-100 pt-8 text-center md:flex md:justify-between text-[11px] text-slate-400 font-light tracking-wide">
                    <p>&copy; 2026 Pemerintah Kabupaten Pesisir Selatan. Hak Cipta Dilindungi.</p>
                    <p class="mt-2 md:mt-0">Sistem Pendaftaran Digital MTQ</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// State reaktif menampung payload dari snapshot 'guest'
const eventData = ref({
    app_name: 'Tingkat Kabupaten Pesisir Selatan',
    event_name: 'MTQ XLII KABUPATEN PESISIR SELATAN',
    event_year: '2026',
    event_location: 'IV JURAI PESISIR SELATAN',
    event_tagline: 'Menuju MTQ yang Bermartabat',
    start_date: '2026-09-07',
    end_date: '2026-11-09',
    logo_event: null,
    logo_sponsor_1: null,
    logo_sponsor_2: null,
    logo_sponsor_3: null,
})

const isLoading = ref(true)

// Fungsi untuk mengambil data snapshot dari endpoint API Laravel
const fetchGuestSnapshot = async () => {
    try {
        const response = await fetch('/api/guest/event-info') // Sesuaikan rute API Anda jika ada prefix tambahan
        if (response.ok) {
            const data = await response.json()
            eventData.value = { ...eventData.value, ...data }
        }
    } catch (error) {
        console.error('Gagal mengambil data snapshot guest:', error)
    } finally {
        isLoading.value = false
    }
}

onMounted(() => {
    fetchGuestSnapshot()
})
</script>

<style scoped>
html, body {
    scroll-behavior: smooth;
}
</style>