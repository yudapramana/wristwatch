import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useStorage } from '@vueuse/core';

// Gerbang password BERSAMA untuk Klasemen & Hasil Cabang (satu password, satu token).
// Di mana akses (token) disimpan di perangkat pengunjung:
//  'session' = hilang saat tab/browser ditutup (default; reload di tab yang sama tetap terbuka)
//  'memory'  = hilang juga saat halaman di-reload (selalu minta password lagi)
//  'local'   = bertahan sampai password diganti / tombol "Kunci kembali" ditekan
export const ACCESS_STORAGE_MODE = 'session';

const memoryMap = new Map();
const memoryStorage = {
    getItem: (k) => (memoryMap.has(k) ? memoryMap.get(k) : null),
    setItem: (k, v) => { memoryMap.set(k, v); },
    removeItem: (k) => { memoryMap.delete(k); },
};
export const accessStorage =
    ACCESS_STORAGE_MODE === 'local' ? localStorage
    : ACCESS_STORAGE_MODE === 'memory' ? memoryStorage
    : sessionStorage;

// Bersihkan sisa akses klasemen versi lama yang tersimpan permanen di localStorage
if (ACCESS_STORAGE_MODE !== 'local') {
    try {
        ['standings', 'standingsPublishedAt', 'standingsFetchedAt', 'standingsToken', 'standingsRequiresPassword']
            .forEach((k) => localStorage.removeItem(`EventStore:${k}`));
    } catch (e) { /* abaikan */ }
}

export const useAccessStore = defineStore('AccessStore', () => {
    // Key lama dipertahankan agar sesi yang sudah terbuka tidak minta password lagi
    const token = useStorage('EventStore:standingsToken', '', accessStorage);
    const requiresPassword = useStorage('EventStore:standingsRequiresPassword', false, accessStorage);

    // 'unknown' (belum dicek server) | 'ok' | 'locked' (butuh password)
    // Selalu mulai 'unknown' setiap halaman dimuat: server dicek dulu, supaya password yang diganti
    // langsung berlaku dan data lama tidak sempat tampil.
    // Catatan: status "belum dipublikasikan" khusus Klasemen, disimpan di EventStore.
    const status = ref('unknown');
    const isUnlocking = ref(false);
    const unlockError = ref('');

    const headers = () => (token.value ? { 'X-Standings-Token': token.value } : {});

    const markOk = (needsPassword) => {
        if (typeof needsPassword === 'boolean') requiresPassword.value = needsPassword;
        status.value = 'ok';
    };

    // Dipanggil saat server membalas 401 locked (password diganti / belum dimasukkan) atau "Kunci kembali".
    // Store data (EventStore / ResultStore) memantau status ini lalu mengosongkan datanya.
    const markLocked = () => {
        token.value = '';
        requiresPassword.value = true;
        status.value = 'locked';
    };

    // Error lain (mis. jaringan): keluar dari 'unknown' agar halaman menampilkan pesan & tombol coba lagi
    const leaveUnknown = () => {
        if (status.value === 'unknown') status.value = 'ok';
    };

    // true bila password benar (token tersimpan). Pemanggil lalu memuat ulang datanya.
    const unlock = async (password) => {
        isUnlocking.value = true;
        unlockError.value = '';
        try {
            const { data } = await axios.post('/api/v1/guest/access/unlock', { password });
            token.value = data.token || '';
            return true;
        } catch (error) {
            const errors = error?.response?.data?.errors;
            unlockError.value = errors
                ? Object.values(errors).flat().join('\n')
                : error?.response?.data?.message || 'Gagal memverifikasi password.';
            return false;
        } finally {
            isUnlocking.value = false;
        }
    };

    // True bila response error adalah "terkunci"
    const isLockedError = (error) =>
        error?.response?.status === 401 && error?.response?.data?.code === 'locked';

    return {
        token, requiresPassword, status, isUnlocking, unlockError,
        headers, markOk, markLocked, leaveUnknown, unlock, isLockedError,
        lock: markLocked,
    };
});