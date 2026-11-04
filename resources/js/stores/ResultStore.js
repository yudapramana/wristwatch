import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useStorage } from '@vueuse/core';

// Hasil berubah selama lomba, jadi hanya di-cache di memori (hilang saat reload)
// dengan masa berlaku singkat. Tidak memakai localStorage agar data lama tidak tampil.
const TTL_MS = 2 * 60 * 1000; // 2 menit
const EVENT_ID = 1;

export const useResultStore = defineStore('ResultStore', () => {
    const groups = ref([]);
    const meta = ref({ total: 0, released: 0, pending: 0 });
    const groupsFetchedAt = ref(0);
    const isGroupsLoading = ref(false);
    const groupsError = ref('');

    // cache hasil per cabang: { [groupId]: { data, fetchedAt } }
    const resultCache = ref({});
    const isResultLoading = ref(false);
    const resultError = ref('');

    // Gerbang akses (sama seperti Klasemen di EventStore).
    // Token memakai key & storage yang SAMA dengan EventStore, jadi satu password membuka dua halaman.
    // (Bila STANDINGS_ACCESS_STORAGE di EventStore diubah dari 'session', samakan juga di sini.)
    const token = useStorage('EventStore:standingsToken', '', sessionStorage);
    const requiresPassword = useStorage('EventStore:standingsRequiresPassword', false, sessionStorage);
    // 'unknown' (belum dicek) | 'ok' | 'locked' (butuh password) | 'unpublished' (belum dipublikasikan)
    // Selalu mulai 'unknown' setiap halaman dimuat: server dicek dulu.
    const access = ref('unknown');
    const isUnlocking = ref(false);
    const unlockError = ref('');

    let groupsRequest = null;
    const resultRequests = {};

    const authHeaders = () => (token.value ? { 'X-Standings-Token': token.value } : {});

    const resetData = () => {
        groups.value = [];
        meta.value = { total: 0, released: 0, pending: 0 };
        groupsFetchedAt.value = 0;
        resultCache.value = {};
    };

    // Password diganti / belum dimasukkan -> kunci & hapus data. Belum dipublikasikan -> hapus data.
    // Mengembalikan true bila error ini sudah ditangani sebagai status akses.
    const handleGateError = (error) => {
        const status = error?.response?.status;
        const code = error?.response?.data?.code;

        if (status === 401 && code === 'locked') {
            resetData();
            token.value = '';
            requiresPassword.value = true;
            access.value = 'locked';
            return true;
        }
        if (status === 403 && code === 'not_published') {
            resetData();
            access.value = 'unpublished';
            return true;
        }
        return false;
    };

    const fetchGroups = async ({ force = false } = {}) => {
        if (groupsRequest) return groupsRequest;
        const fresh = groupsFetchedAt.value > 0 && Date.now() - groupsFetchedAt.value < TTL_MS;
        if (fresh && !force) {
            access.value = 'ok';
            return;
        }

        groupsRequest = (async () => {
            isGroupsLoading.value = true;
            groupsError.value = '';
            try {
                const { data } = await axios.get('/api/v1/guest/results', {
                    params: { event_id: EVENT_ID },
                    headers: authHeaders(),
                });
                groups.value = data.data || [];
                meta.value = data.meta || { total: 0, released: 0, pending: 0 };
                requiresPassword.value = !!data.requires_password;
                groupsFetchedAt.value = Date.now();
                access.value = 'ok';
            } catch (error) {
                if (handleGateError(error)) return;

                // Error lain (mis. jaringan): keluar dari 'unknown' agar halaman menampilkan pesan & tombol coba lagi
                if (access.value === 'unknown') access.value = 'ok';
                groupsError.value = error?.response?.data?.message || 'Gagal memuat daftar cabang.';
                console.error('Gagal memuat daftar cabang:', error);
            } finally {
                isGroupsLoading.value = false;
                groupsRequest = null;
            }
        })();
        return groupsRequest;
    };

    const getResult = (groupId) => resultCache.value[groupId]?.data || null;

    const fetchResult = async (groupId, { force = false } = {}) => {
        if (!groupId) return;
        if (resultRequests[groupId]) return resultRequests[groupId];

        const cached = resultCache.value[groupId];
        const fresh = cached && Date.now() - cached.fetchedAt < TTL_MS;
        if (fresh && !force) return;

        resultRequests[groupId] = (async () => {
            isResultLoading.value = true;
            resultError.value = '';
            try {
                const { data } = await axios.get(`/api/v1/guest/results/${groupId}`, {
                    params: { event_id: EVENT_ID },
                    headers: authHeaders(),
                });
                resultCache.value = { ...resultCache.value, [groupId]: { data, fetchedAt: Date.now() } };
            } catch (error) {
                if (handleGateError(error)) return;

                resultError.value = error?.response?.data?.message || 'Gagal memuat hasil cabang.';
                console.error('Gagal memuat hasil cabang:', error);
            } finally {
                isResultLoading.value = false;
                delete resultRequests[groupId];
            }
        })();
        return resultRequests[groupId];
    };

    // Verifikasi password ke server; bila benar simpan token lalu muat daftar cabang
    const unlockResults = async (password) => {
        isUnlocking.value = true;
        unlockError.value = '';
        try {
            const { data } = await axios.post('/api/v1/guest/results/unlock', { password });
            token.value = data.token || '';
            await fetchGroups({ force: true });
            return access.value === 'ok';
        } catch (error) {
            if (error?.response?.status === 403 && error?.response?.data?.code === 'not_published') {
                access.value = 'unpublished';
            } else {
                const errors = error?.response?.data?.errors;
                unlockError.value = errors
                    ? Object.values(errors).flat().join('\n')
                    : error?.response?.data?.message || 'Gagal memverifikasi password.';
            }
            return false;
        } finally {
            isUnlocking.value = false;
        }
    };

    // Kunci kembali: hapus token & data dari perangkat ini
    const lockResults = () => {
        token.value = '';
        resetData();
        access.value = 'locked';
    };

    return {
        groups, meta, isGroupsLoading, groupsError,
        isResultLoading, resultError,
        access, requiresPassword, isUnlocking, unlockError,
        fetchGroups, fetchResult, getResult, unlockResults, lockResults,
    };
});