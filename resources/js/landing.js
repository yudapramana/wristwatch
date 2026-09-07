import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Guest from './pages/Guest.vue'

// Data awal dari Blade (diisi dari Blade lewat window.__INITIAL_EVENTS__)
const events = window.__INITIAL_EVENTS__ || []
const loginUrl = window.__LOGIN_URL__ || '/login'

const app = createApp(Guest, {
  events,
  loginUrl,
})

const pinia = createPinia()

app.use(pinia)
app.mount('#app')
