<script setup>
// Gerbang password bersama untuk Klasemen & Hasil Cabang.
// Memakai AccessStore (token & status). Setelah password benar, memanggil `reload`
// agar halaman memuat ulang datanya.
import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useAccessStore } from '../stores/AccessStore.js'

const props = defineProps({
  // 'unknown' | 'locked' | 'unpublished'
  status: { type: String, required: true },
  // Teks: "Klasemen Dilindungi", "Masukkan password untuk melihat klasemen juara umum."
  label: { type: String, default: 'Halaman' },
  subject: { type: String, default: 'halaman ini' },
  // Fungsi async untuk memuat ulang data halaman (setelah unlock / "Periksa lagi")
  reload: { type: Function, default: null },
})

// Nomor WhatsApp admin untuk meminta password (format 62812...). Kosongkan untuk menyembunyikan tombol.
const ADMIN_WHATSAPP = ''

const access = useAccessStore()
const { isUnlocking, unlockError } = storeToRefs(access)

const password = ref('')
const showPassword = ref(false)
const localError = ref('')
const busy = ref(false)

const gateError = computed(() => localError.value || unlockError.value)
const isBusy = computed(() => busy.value || isUnlocking.value)

const waLink = computed(() => {
  if (!ADMIN_WHATSAPP) return ''
  const text = encodeURIComponent(`Halo admin, saya ingin meminta password untuk membuka ${props.subject} MTQ.`)
  return `https://wa.me/${ADMIN_WHATSAPP}?text=${text}`
})

const submit = async () => {
  localError.value = ''
  if (!password.value.trim()) {
    localError.value = 'Password wajib diisi.'
    return
  }
  busy.value = true
  try {
    const ok = await access.unlock(password.value)
    if (ok) {
      password.value = ''
      await props.reload?.()
    }
  } finally {
    busy.value = false
  }
}

const retry = async () => {
  busy.value = true
  try {
    await props.reload?.()
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <!-- Mengecek akses (hindari kilasan data) -->
  <section v-if="status === 'unknown'" class="gate gate-loading" aria-busy="true" aria-label="Memuat">
    <div class="skel skel-circle"></div>
    <div class="skel skel-line"></div>
    <div class="skel skel-line short"></div>
  </section>

  <!-- Gerbang password -->
  <section v-else-if="status === 'locked'" class="gate">
    <div class="gate-icon">
      <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>
    </div>
    <h2>{{ label }} Dilindungi</h2>
    <p>Masukkan password untuk melihat {{ subject }}.</p>

    <form @submit.prevent="submit">
      <div class="gate-field">
        <input
          v-model="password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Password"
          aria-label="Password"
          autocomplete="off"
          :disabled="isBusy"
          @input="localError = ''"
        />
        <button type="button" class="gate-eye" @click="showPassword = !showPassword">
          {{ showPassword ? 'Sembunyi' : 'Lihat' }}
        </button>
      </div>
      <p v-if="gateError" class="gate-error" role="alert">{{ gateError }}</p>
      <button type="submit" class="gate-btn" :disabled="isBusy">
        {{ isBusy ? 'Memeriksa…' : `Buka ${label}` }}
      </button>
    </form>

    <a v-if="waLink" :href="waLink" target="_blank" rel="noopener" class="gate-wa">Minta password ke admin</a>
  </section>

  <!-- Belum dipublikasikan (khusus Klasemen) -->
  <section v-else-if="status === 'unpublished'" class="gate">
    <div class="gate-icon">
      <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
    </div>
    <h2>{{ label }} Belum Dipublikasikan</h2>
    <p>Hasil akan ditampilkan setelah diumumkan oleh panitia.</p>
    <button type="button" class="gate-btn" :disabled="isBusy" @click="retry">
      {{ isBusy ? 'Memeriksa…' : 'Periksa lagi' }}
    </button>
  </section>
</template>

<style scoped>
.gate {
  --navy: #0b2f55; --blue: #0f5f8c; --ink: #0d2b4a; --muted: #6b7c93; --line: #e6ecf3;
  max-width: 420px; margin: 8px auto 0; padding: 28px 24px; text-align: center; color: var(--ink);
  background: #fff; border: 1px solid var(--line); border-radius: 16px;
}
.gate-icon { width: 48px; height: 48px; margin: 0 auto 12px; display: grid; place-items: center; color: var(--navy); background: #eaf4fb; border-radius: 50%; }
.gate h2 { margin: 0; font-size: 19px; font-weight: 800; color: var(--navy); }
.gate p { margin: 6px 0 0; font-size: 13px; color: var(--muted); }
.gate form { margin-top: 18px; }
.gate-field { position: relative; }
.gate-field input { width: 100%; padding: 10px 74px 10px 14px; font: inherit; font-size: 14px; color: var(--ink); background: #fff; border: 1px solid #cfe0ee; border-radius: 12px; outline: none; }
.gate-field input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(15, 95, 140, .15); }
.gate-eye { position: absolute; top: 50%; right: 8px; transform: translateY(-50%); padding: 4px 8px; font: inherit; font-size: 12px; font-weight: 600; color: var(--muted); background: transparent; border: 0; cursor: pointer; }
.gate p.gate-error { margin: 8px 0 0; font-size: 13px; color: #b91c1c; text-align: left; }
.gate-btn { width: 100%; margin-top: 12px; padding: 10px 16px; font: inherit; font-size: 14px; font-weight: 700; color: #fff; background: var(--navy); border: 0; border-radius: 12px; cursor: pointer; transition: opacity .15s ease; }
.gate-btn:hover { opacity: .92; }
.gate-btn:disabled { opacity: .6; cursor: not-allowed; }
.gate-btn:focus-visible, .gate-eye:focus-visible { outline: 3px solid #7cc3ea; outline-offset: 2px; }
.gate-wa { display: inline-block; margin-top: 12px; font-size: 13px; font-weight: 600; color: var(--blue); text-decoration: none; }
.gate-wa:hover { text-decoration: underline; }

.gate-loading { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.gate-loading .skel-circle { width: 48px; height: 48px; border-radius: 50%; }
.gate-loading .skel-line { width: 70%; height: 16px; }
.gate-loading .skel-line.short { width: 45%; }
.skel { background: linear-gradient(90deg, #eef3f9 25%, #f8fafc 50%, #eef3f9 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite linear; border-radius: 8px; }
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
</style>