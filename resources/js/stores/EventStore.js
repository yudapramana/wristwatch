import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useStorage } from '@vueuse/core';

export const useEventStore = defineStore('EventStore', () => {
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

    // ==================================================
    // ACTIONS
    // ==================================================
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
        fetchEventData,
        fetchVenues,
        fetchBranches
    };
});