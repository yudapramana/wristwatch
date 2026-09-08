<template>
    <div :class="['relative min-h-screen bg-slate-50 text-slate-800 font-sans selection:bg-emerald-200 selection:text-emerald-900 overflow-hidden z-0', { 'pb-16': !showFooter }]">
        
        <!-- Subtle Green Gradient & Pattern Background -->
        <div class="absolute inset-0 z-[-1] pointer-events-none fixed">
            <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] opacity-[0.04] [background-size:24px_24px]"></div>
            <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-emerald-200/30 to-teal-100/10 blur-[120px]"></div>
            <div class="absolute top-[40%] -right-[10%] w-[60%] h-[60%] rounded-full bg-gradient-to-tl from-emerald-200/20 to-green-100/10 blur-[100px]"></div>
        </div>

        <!-- NAVBAR -->
        <header class="w-full bg-transparent pt-6 pb-4 relative z-20 sticky top-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white/90 backdrop-blur-md rounded-full px-6 py-3 flex justify-between items-center border border-white/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full overflow-hidden shadow-inner flex items-center justify-center border border-emerald-100 bg-white">
                            <img src="https://res.cloudinary.com/dezj1x6xp/image/upload/v1763442267/PandanViewMandeh/uaccfb20uvhpvjyafdoj.png" alt="Logo MTQ" class="w-full h-full object-cover" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-sm tracking-wide text-slate-900 leading-tight">MTQ XLII PESSEL</span>
                            <span class="text-[10px] text-emerald-700 font-medium">IV Jurai 2026</span>
                        </div>
                    </div>

                    <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-500">
                        <router-link to="/" class="hover:text-emerald-700 transition-colors" active-class="text-emerald-700 font-semibold">Beranda</router-link>
                        <router-link to="/#tentang" class="hover:text-emerald-700 transition-colors">Tentang</router-link>
                        <router-link to="/#tahapan" class="hover:text-emerald-700 transition-colors">Tahapan</router-link>
                        <router-link to="/#informasi" class="hover:text-emerald-700 transition-colors">Informasi</router-link>
                        <router-link to="/venue" class="hover:text-emerald-700 transition-colors" active-class="text-emerald-700 font-semibold border-b-2 border-emerald-600 pb-0.5">Venue</router-link>
                        <router-link to="/branch" class="hover:text-emerald-700 transition-colors" active-class="text-emerald-700 font-semibold border-b-2 border-emerald-600 pb-0.5">Cabang</router-link>
                    </nav>

                    <div>
                        <a :href="ctaLink" class="bg-emerald-800 hover:bg-emerald-700 text-white font-medium px-6 py-2.5 rounded-full text-sm transition-all duration-300 shadow-sm hover:shadow-emerald-900/20">
                            {{ ctaText }}
                        </a>
                    </div>
                </div>
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
                    <p class="mt-2 md:mt-0">Sistem Pendaftaran Digital e-MTQ</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
defineProps({
    ctaText: { type: String, default: 'Masuk Portal' },
    ctaLink: { type: String, default: '#' },
    showFooter: { type: Boolean, default: true }
})
</script>