import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'

// Import komponen utama dan halaman
import App from './App.vue'
import Index from './pages/guest/Index.vue' // Sesuaikan path jika berbeda
import Venue from './pages/guest/Venue.vue' // Sesuaikan path jika berbeda
import Branch from './pages/guest/CompetitionBranch.vue'

// 1. Konfigurasi Vue Router
const routes = [
    { path: '/', component: Index },
    { path: '/venue', component: Venue },
    { path: '/branch', component: Branch }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    // Mengembalikan scroll ke atas saat pindah halaman, atau ke anchor (contoh: /#beranda)
    scrollBehavior(to, from, savedPosition) {
        if (to.hash) {
            return { el: to.hash, behavior: 'smooth' }
        }
        return { top: 0 }
    }
})

// Data awal dari Blade
const events = window.__INITIAL_EVENTS__ || []
const loginUrl = window.__LOGIN_URL__ || '/login'

// 2. Mount App.vue (bukan Guest.vue lagi)
const app = createApp(App, {
  events,
  loginUrl,
})

const pinia = createPinia()

app.use(pinia)
app.use(router) // 3. Gunakan router di instance Vue
app.mount('#app')