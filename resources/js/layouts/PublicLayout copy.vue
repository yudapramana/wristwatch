<template>
    <div :class="['relative min-h-screen bg-slate-50 text-slate-800 font-sans selection:bg-emerald-200 selection:text-emerald-900 overflow-x-clip z-0', { 'pb-16': !showFooter }]">
        
        <!-- Subtle Green Gradient & Pattern Background -->
        <div class="absolute inset-0 z-[-1] pointer-events-none fixed">
            <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] opacity-[0.04] [background-size:24px_24px]"></div>
            <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-emerald-200/30 to-teal-100/10 blur-[120px]"></div>
            <div class="absolute top-[40%] -right-[10%] w-[60%] h-[60%] rounded-full bg-gradient-to-tl from-emerald-200/20 to-green-100/10 blur-[100px]"></div>
        </div>

        <!-- NAVBAR -->
        <header class="w-full bg-transparent pt-4 md:pt-6 pb-4 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="bg-white/90 backdrop-blur-md rounded-full pl-4 pr-3 md:px-6 py-2.5 md:py-3 flex justify-between items-center gap-4 border border-white/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">

                    <router-link to="/" class="flex items-center space-x-3 min-w-0" aria-label="MTQ XLII PESSEL - Beranda">
                        <div class="w-9 h-9 rounded-full overflow-hidden shadow-inner flex items-center justify-center border border-emerald-100 bg-white flex-none">
                            <img src="https://res.cloudinary.com/dezj1x6xp/image/upload/v1763621790/PandanViewMandeh/qpiwf4a8dubcgok9nnzr.png" alt="Logo MTQ" class="w-full h-full object-cover" />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-bold text-sm tracking-wide text-slate-900 leading-tight truncate">MTQN XLII KABUPATEN PESISIR SELATAN</span>
                            <span class="text-[10px] text-emerald-700 font-medium">KECAMATAN IV JURAI 2026</span>
                        </div>
                    </router-link>

                    <!-- Menu desktop -->
                    <nav class="hidden md:flex items-center gap-x-6 lg:gap-x-8 text-sm font-medium text-slate-500" aria-label="Navigasi utama">
                        <router-link
                            v-for="l in links" :key="l.to" :to="l.to"
                            class="hover:text-emerald-700 transition-colors border-b-2 border-transparent pb-0.5"
                            :active-class="l.page ? 'text-emerald-700 font-semibold !border-emerald-600' : ''"
                            :exact-active-class="l.exact ? 'text-emerald-700 font-semibold' : ''"
                        >{{ l.label }}</router-link>
                    </nav>

                    <!-- Tombol menu mobile -->
                    <button
                        type="button"
                        class="md:hidden w-10 h-10 rounded-full flex items-center justify-center text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                        :aria-expanded="menuOpen ? 'true' : 'false'"
                        aria-controls="menu-mobile"
                        aria-label="Buka menu"
                        @click="menuOpen = !menuOpen"
                    >
                        <svg v-if="!menuOpen" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                        <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <!-- Panel menu mobile -->
                <nav
                    v-if="menuOpen" id="menu-mobile"
                    class="md:hidden absolute left-4 right-4 sm:left-6 sm:right-6 mt-2 bg-white rounded-3xl border border-slate-100 shadow-[0_16px_40px_rgb(0,0,0,0.10)] p-2"
                    aria-label="Navigasi utama"
                >
                    <router-link
                        v-for="l in links" :key="l.to" :to="l.to"
                        class="block px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-emerald-50 hover:text-emerald-700"
                        :active-class="l.page ? '!bg-emerald-50 !text-emerald-700 font-semibold' : ''"
                        :exact-active-class="l.exact ? '!bg-emerald-50 !text-emerald-700 font-semibold' : ''"
                    >{{ l.label }}</router-link>
                </nav>
            </div>
        </header>

        <!-- KONTEN HALAMAN (GUEST / VENUE) AKAN MUNCUL DI SINI -->
        <slot></slot>

        <!-- FOOTER -->
        <footer v-if="showFooter" class="bg-white border-t border-slate-100 pt-16 pb-8 mt-12 relative z-20">
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
                            <li><router-link to="/" class="hover:text-emerald-700">Beranda Utama</router-link></li>
                            <li><router-link to="/#tahapan" class="hover:text-emerald-700">Tahapan & Linimasa</router-link></li>
                            <li><router-link to="/#tentang" class="hover:text-emerald-700">Tentang Kegiatan</router-link></li>
                            <li><router-link to="/hasil" class="hover:text-emerald-700">Hasil Cabang</router-link></li>
                            <li><router-link to="/klasemen" class="hover:text-emerald-700">Klasemen Juara Umum</router-link></li>
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
                
                <div class="border-t border-slate-100 pt-8 text-center md:flex md:justify-between md:items-start md:gap-8 md:text-left text-[11px] text-slate-400 font-light tracking-wide">
                    <p>
                        &copy; 2026 Pemerintah Kabupaten Pesisir Selatan &amp; Kementerian Agama Kabupaten Pesisir Selatan.
                        Hak Cipta Dilindungi.
                    </p>
                    <p class="mt-2 md:mt-0 md:text-right">
                        Sistem Pendaftaran Digital e-MTQ <br class="hidden md:block" />
                        Dikembangkan bersama Pranata Komputer - Pramana Yuda Sayeti
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

defineProps({
    showFooter: { type: Boolean, default: true }
})

// Satu daftar menu dipakai untuk desktop dan mobile.
// page: true -> diberi penanda aktif; anchor (#...) tidak.
const links = [
    { to: '/',          label: 'Beranda',  exact: true },
    { to: '/#tentang',  label: 'Tentang' },
    { to: '/#tahapan',  label: 'Tahapan' },
    { to: '/#informasi', label: 'Informasi' },
    { to: '/venue',     label: 'Venue',    page: true },
    { to: '/branch',    label: 'Cabang',   page: true },
    { to: '/hasil',     label: 'Hasil',    page: true },
    { to: '/klasemen',  label: 'Klasemen', page: true },
]

const menuOpen = ref(false)
const route = useRoute()
watch(() => route.fullPath, () => { menuOpen.value = false })
</script>