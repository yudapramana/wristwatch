<template>
    <PublicLayout cta-text="Masuk Portal" :show-footer="false">
        
        <!-- MAIN KONTEN -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8 relative z-10">
            
            <!-- HEADER SECTION -->
            <section class="text-center space-y-4">
                <span class="inline-block border border-emerald-200 bg-emerald-50/50 text-emerald-800 text-[11px] font-bold tracking-widest uppercase px-3 py-1 rounded-full">
                    Hierarki Musabaqah
                </span>
                <h1 class="text-3xl sm:text-4xl font-serif text-slate-900 leading-[1.15]">
                    Cabang, Golongan & Kategori
                </h1>
                <p class="text-slate-500 text-sm font-light">
                    {{ eventData.event_name || 'MTQ XLII Kabupaten Pesisir Selatan' }} TAHUN {{ eventData.event_year || '2026' }}
                </p>
            </section>

            <!-- SEARCH BAR -->
            <section class="sticky top-[88px] z-20">
                <div class="bg-white/80 backdrop-blur-md p-2 rounded-full border border-slate-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center">
                    <span class="pl-4 pr-2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Cari cabang, golongan, atau kategori lomba..." 
                        class="w-full bg-transparent border-none focus:ring-0 text-sm text-slate-700 py-3 px-2 outline-none placeholder-slate-400"
                    />
                    <button v-if="searchQuery" @click="searchQuery = ''" class="pr-4 text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </section>

            <!-- LOADING SKELETON -->
            <div v-if="isLoading" class="space-y-6">
                <div v-for="i in 3" :key="i" class="bg-white/80 rounded-3xl p-6 border border-slate-100 shadow-sm animate-pulse h-40"></div>
            </div>

            <!-- HIERARCHY LIST -->
            <section v-else class="space-y-6">
                <div 
                    v-for="(branch, index) in filteredBranches" 
                    :key="branch.id"
                    class="bg-white/90 backdrop-blur-md rounded-3xl border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.02)] p-6 transition-all duration-300 space-y-4"
                >
                    <!-- Cabang Header -->
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div class="flex items-start gap-3">
                            <span class="bg-emerald-50 text-emerald-800 text-xs font-bold w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                                {{ index + 1 }}
                            </span>
                            <div>
                                <h3 class="font-bold text-slate-800 text-base leading-snug">
                                    {{ branch.branch_name }}
                                </h3>
                            </div>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-[11px] font-bold px-3 py-1 rounded-full shrink-0">
                            {{ branch.groups.length }} Golongan
                        </span>
                    </div>

                    <!-- Golongan & Kategori List -->
                    <div v-if="branch.groups.length > 0" class="space-y-3 pl-2 sm:pl-4">
                        <div 
                            v-for="group in branch.groups" 
                            :key="group.id"
                            class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 space-y-3"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <h4 class="font-bold text-slate-800 text-sm">
                                    🏷️ Golongan: {{ group.group_name }}
                                </h4>
                            </div>

                            <!-- Kategori List -->
                            <div v-if="group.categories.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-slate-200/50">
                                <div 
                                    v-for="category in group.categories" 
                                    :key="category.id"
                                    class="bg-white px-3.5 py-2 rounded-xl border border-slate-100 flex items-center justify-between shadow-xs"
                                >
                                    <span class="text-slate-700 text-xs font-medium">
                                        • {{ category.category_name }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-xs text-slate-400 italic pt-1">
                                Tidak ada kategori
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-slate-400 text-xs italic">
                        Tidak ada golongan
                    </div>
                </div>

                <!-- Jika tidak ada hasil pencarian -->
                <div v-if="filteredBranches.length === 0" class="text-center py-16 bg-white/50 backdrop-blur rounded-3xl border border-slate-100">
                    <span class="text-4xl opacity-50 mb-4 block">🔍</span>
                    <p class="text-slate-500 text-sm">Tidak menemukan cabang, golongan, atau kategori yang cocok.</p>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import PublicLayout from '../../layouts/PublicLayout.vue'
import { useEventStore } from '../../stores/EventStore.js'

const store = useEventStore()
const { eventData, branches } = storeToRefs(store)

const isLoading = ref(true)
const searchQuery = ref('')

// Computed property untuk filter pencarian real-time
const filteredBranches = computed(() => {
    const query = searchQuery.value.toLowerCase().trim()
    if (!query) return branches.value

    return branches.value.map(branch => {
        const matchBranch = branch.branch_name?.toLowerCase().includes(query)
        
        const filteredGroups = branch.groups.map(group => {
            const matchGroup = group.group_name?.toLowerCase().includes(query)
            const filteredCategories = group.categories.filter(cat => 
                cat.category_name?.toLowerCase().includes(query)
            )

            if (matchGroup || filteredCategories.length > 0) {
                return {
                    ...group,
                    categories: matchGroup ? group.categories : filteredCategories
                }
            }
            return null
        }).filter(Boolean)

        if (matchBranch || filteredGroups.length > 0) {
            return {
                ...branch,
                groups: matchBranch ? branch.groups : filteredGroups
            }
        }
        return null
    }).filter(Boolean)
})

onMounted(async () => {
    await store.fetchBranches()
    isLoading.value = false
})
</script>