import axios from 'axios';
import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { useStorage } from '@vueuse/core';
import { useAccessStore, accessStorage } from './AccessStore.js';

export const useEventStore = defineStore('EventStore', () => {
    const access = useAccessStore();

    // ==================================================
    // STATE (Menggunakan useStorage agar persisten & di-cache)
    // ==================================================
    const eventData = useStorage('EventStore:eventData', {
        app_name: 'Tingkat Kabupaten Pesisir Selatan',
        event_name: 'MTQ XLII KABUPATEN PESISIR SELATAN',
        event_year: '2026',
        event_location: 'KECAMATAN IV JURAI',
        event_tagline: 'Menuju MTQ yang Bermartabat',
        start_date: '1 - 6 November 2026',
        end_date: '2026-11-06',
        logo_event: null,
        logo_sponsor_1: null,
        logo_sponsor_2: null,
        logo_sponsor_3: null,
    });

    const venues = useStorage('EventStore:venues', []);
    const branches = useStorage('EventStore:branches', []);

    // Penanda agar tidak fetch berulang kali
    const isEventDataFetched = useStorage('EventStore:isEventDataFetched', false);
    const isVenuesFetched = useStorage('EventStore:isVenuesFetched', false);
    const isBranchesFetched = useStorage('EventStore:isBranchesFetched', false);

    // Klasemen Juara Umum (data berubah selama lomba, jadi cache punya masa berlaku / TTL)
    // Selama masih dalam rentang ini, klasemen dibaca dari store tanpa request ke server
    const STANDINGS_TTL_MS = 5 * 60 * 1000; // 5 menit
    const standings = useStorage('EventStore:standings', [], accessStorage);
    const standingsPublishedAt = useStorage('EventStore:standingsPublishedAt', null, accessStorage);
    const standingsFetchedAt = useStorage('EventStore:standingsFetchedAt', 0, accessStorage); // timestamp (ms) fetch terakhir yang sukses
    const isStandingsLoading = ref(false);
    const standingsError = ref('');

    // Khusus Klasemen (gerbang password-nya ada di AccessStore, dibagi dengan Hasil Cabang):
    // - standingsChecked: server sudah menjawab di halaman ini (hindari kilasan data lama)
    // - standingsUnpublished: admin belum / tidak lagi mempublikasikan klasemen
    const standingsChecked = ref(false);
    const standingsUnpublished = ref(false);

    let standingsRequest = null; // mencegah request ganda saat diklik/dimuat berulang
    // Cache (TTL) hanya dipakai setelah server memverifikasi akses pada pemuatan halaman ini
    let standingsVerified = false;

    // ==================================================
    // ACTIONS
    // ==================================================
    const resetStandingsData = () => {
        standings.value = [];
        standingsPublishedAt.value = null;
        standingsFetchedAt.value = 0;
    };

    // Terkunci dari mana pun (klasemen, hasil cabang, atau "Kunci kembali") -> hapus data klasemen
    watch(() => access.status, (s) => {
        if (s === 'locked') {
            standingsVerified = false;
            resetStandingsData();
        }
    });

    const fetchEventData = async () => {
        // Jika data sudah di-fetch sebelumnya dan tersimpan di storage, hentikan eksekusi
        if (isEventDataFetched.value) return;

        try {
            const response = await axios.get('/api/guest/event-info');
            eventData.value = { ...eventData.value, ...response.data };
            isEventDataFetched.value = true;
        } catch (error) {
            console.error('Gagal mengambil data snapshot guest:', error);
        }
    };

    const fetchVenues = async () => {
        if (isVenuesFetched.value) return;

        try {
            const response = await axios.get('/api/venues?event_id=1');

            // Update event data jika endpoint ini juga mengembalikannya
            if (response.data.event) {
                eventData.value = { ...eventData.value, ...response.data.event };
            }

            venues.value = response.data.venues || [];
            isVenuesFetched.value = true;
        } catch (error) {
            console.error('Gagal memuat data lokasi:', error);
        }
    };

    const fetchBranches = async () => {
        if (isBranchesFetched.value) return;

        try {
            const response = await axios.get('/api/hierarki-mtq');

            if (response.data.event) {
                eventData.value = { ...eventData.value, ...response.data.event };
            }

            branches.value = response.data.branches || [];
            isBranchesFetched.value = true;
        } catch (error) {
            console.error('Gagal memuat data hierarki:', error);
        }
    };

    const fetchStandings = async ({ force = false } = {}) => {
        // Request yang sama sedang berjalan -> pakai hasilnya, jangan kirim lagi
        if (standingsRequest) return standingsRequest;

        const isFresh = standingsFetchedAt.value > 0
            && Date.now() - standingsFetchedAt.value < STANDINGS_TTL_MS;
        if (standingsVerified && isFresh && !force) {
            standingsUnpublished.value = false;
            standingsChecked.value = true;
            access.markOk();
            return; // cukup dari store
        }

        standingsRequest = (async () => {
            isStandingsLoading.value = true;
            standingsError.value = '';
            try {
                const response = await axios.get('/api/v1/guest/standings', {
                    headers: access.headers(),
                });
                standings.value = response.data.data || [];
                standingsPublishedAt.value = response.data.published_at || null;
                standingsFetchedAt.value = Date.now();
                standingsUnpublished.value = false;
                access.markOk(!!response.data.requires_password);
                standingsVerified = true;
            } catch (error) {
                const status = error?.response?.status;
                const code = error?.response?.data?.code;

                // Password diganti / belum dimasukkan -> kunci & hapus data dari store
                if (access.isLockedError(error)) {
                    standingsVerified = false;
                    standingsUnpublished.value = false;
                    resetStandingsData();
                    access.markLocked();
                    return;
                }
                // Admin belum / tidak lagi mempublikasikan klasemen
                if (status === 403 && code === 'not_published') {
                    standingsVerified = false;
                    standingsUnpublished.value = true;
                    resetStandingsData();
                    return;
                }

                // Error lain (mis. jaringan): keluar dari 'unknown' agar halaman menampilkan pesan & tombol coba lagi
                access.leaveUnknown();

                // Data lama di store (jika ada) tetap dipertahankan
                const errors = error?.response?.data?.errors;
                standingsError.value = errors
                    ? Object.values(errors).flat().join('\n')
                    : error?.response?.data?.message || 'Gagal memuat klasemen.';
                console.error('Gagal memuat klasemen:', error);
            } finally {
                isStandingsLoading.value = false;
                standingsChecked.value = true;
                standingsRequest = null;
            }
        })();

        return standingsRequest;
    };

    // ==================================================
    // EXPOSE STATE & ACTIONS
    // ==================================================
    return {
        eventData,
        venues,
        branches,
        isEventDataFetched,
        isVenuesFetched,
        isBranchesFetched,
        standings,
        standingsPublishedAt,
        standingsFetchedAt,
        isStandingsLoading,
        standingsError,
        standingsChecked,
        standingsUnpublished,
        fetchEventData,
        fetchVenues,
        fetchBranches,
        fetchStandings,
    };
});