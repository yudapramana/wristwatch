<template>
  <PublicLayout>
    <div class="pg">
      <!-- Judul halaman + pencarian (logo & menu sudah ada di PublicLayout) -->
      <header class="pg-top">
        <div>
          <p class="kicker">Hierarki musabaqah</p>
          <h1>Cabang, Golongan &amp; Kategori</h1>
          <p class="muted">
            {{ eventData.event_name || 'MTQ XLII Kabupaten Pesisir Selatan' }} TAHUN {{ eventData.event_year || '2026' }}
          </p>
        </div>

        <label class="search">
          <span class="sr-only">Cari cabang, golongan, atau kategori</span>
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" /></svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari cabang, golongan, atau kategori lomba…"
            autocomplete="off"
          />
          <button v-if="searchQuery" type="button" class="clear" aria-label="Hapus pencarian" @click="searchQuery = ''">×</button>
        </label>
      </header>

      <p v-if="!isLoading" class="count-line muted" aria-live="polite">
        {{ filteredBranches.length }} cabang<template v-if="searchQuery.trim()"> cocok dengan “{{ searchQuery.trim() }}”</template>
      </p>

      <!-- Skeleton -->
      <div v-if="isLoading" class="list" aria-busy="true" aria-label="Memuat">
        <div v-for="i in 3" :key="i" class="card skel-card"></div>
      </div>

      <!-- Daftar hierarki -->
      <div v-else-if="filteredBranches.length" class="list">
        <section v-for="(branch, index) in filteredBranches" :key="branch.id" class="card">
          <!-- Cabang -->
          <header class="card-head">
            <span class="no">{{ index + 1 }}</span>
            <h3>{{ branch.branch_name }}</h3>
            <span class="count">{{ branch.groups.length }} Golongan</span>
          </header>

          <!-- Golongan & kategori -->
          <div v-if="branch.groups.length > 0" class="groups">
            <div v-for="group in branch.groups" :key="group.id" class="group">
              <p class="g-kick">Golongan</p>
              <h4>{{ group.group_name }}</h4>

              <ul v-if="group.categories.length > 0" class="cats">
                <li v-for="category in group.categories" :key="category.id">{{ category.category_name }}</li>
              </ul>
              <p v-else class="muted empty">Tidak ada kategori</p>
            </div>
          </div>
          <p v-else class="muted pad">Tidak ada golongan</p>
        </section>
      </div>

      <!-- Tidak ada hasil -->
      <p v-else class="state">Tidak menemukan cabang, golongan, atau kategori yang cocok.</p>
    </div>
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
    try {
        await store.fetchBranches()
    } finally {
        isLoading.value = false
    }
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
.muted { color: var(--muted); font-size: 13px; margin: 0; } .pad { padding: 12px 18px; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.pg-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; padding: 8px 0 14px; flex-wrap: wrap; }
.pg-top h1 { margin: 0; font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: var(--navy); line-height: 1.15; }

/* pencarian */
.search { position: relative; display: flex; align-items: center; min-width: 320px; color: #6d86a3; }
.search svg { position: absolute; left: 13px; pointer-events: none; }
.search input { width: 100%; padding: 10px 36px 10px 38px; font: inherit; font-size: 14px; color: var(--ink); background: #f1f8fd; border: 1px solid #cfe0ee; border-radius: 12px; outline: none; }
.search input::placeholder { color: #8aa0b8; }
.search input:focus { background: #fff; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(15, 95, 140, .15); }
.search input:focus-visible { outline: 3px solid #7cc3ea; outline-offset: 2px; }
.clear { position: absolute; right: 8px; width: 24px; height: 24px; display: grid; place-items: center; font-size: 18px; line-height: 1; color: var(--muted); background: transparent; border: 0; border-radius: 50%; cursor: pointer; }
.clear:hover { background: #e3edf6; }
.count-line { margin: 0 0 10px; }

/* ====== Kartu cabang ====== */
.list { display: flex; flex-direction: column; gap: 12px; }
.card { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; min-width: 0; }
.card-head { display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-bottom: 1px solid var(--line); }
.no { width: 30px; height: 30px; flex: none; display: grid; place-items: center; font-size: 12px; font-weight: 800; color: var(--navy); background: #eaf4fb; border-radius: 10px; }
.card-head h3 { flex: 1; min-width: 0; margin: 0; font-size: 16px; font-weight: 700; color: var(--navy); }
.count { background: var(--blue); color: #fff; font-size: 12px; font-weight: 600; padding: 3px 11px; border-radius: 999px; white-space: nowrap; }

/* golongan & kategori */
.groups { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; padding: 12px 14px 14px; }
.group { padding: 10px 14px 12px; background: #f6f9fc; border: 1px solid var(--line); border-radius: 12px; min-width: 0; }
.g-kick { margin: 0; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #4b6481; }
.group h4 { margin: 2px 0 8px; font-size: 14.5px; font-weight: 800; color: var(--navy); line-height: 1.25; }
.cats { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
.cats li { padding: 3px 10px; font-size: 12.5px; color: #3e5370; background: #fff; border: 1px solid var(--line); border-radius: 999px; }
.empty { font-style: italic; }
.state { margin: 4px 0 0; padding: 18px 22px; background: #fff; border: 1px dashed #c9d7e6; border-radius: 14px; color: var(--muted); }

.skel-card { height: 130px; background: linear-gradient(90deg, #eef3f9 25%, #f8fafc 50%, #eef3f9 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite linear; }
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

/* ====== Mobile ====== */
@media (max-width: 960px) {
  .search { min-width: 100%; }
}
@media (max-width: 520px) {
  .groups { grid-template-columns: minmax(0, 1fr); }
  .card-head { flex-wrap: wrap; }
}
</style>