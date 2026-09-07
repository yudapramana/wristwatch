<script setup>
import { useRouter, useRoute } from 'vue-router';
import { ref, onMounted, computed, watch } from 'vue';
import HeaderApp from './components/guest/Header.vue';
import FooterApp from './components/guest/Footer.vue';
import { useAuthUserStore } from './stores/AuthUserStore.js';

const authUserStore = useAuthUserStore();
const route = useRoute();
const router = useRouter();

watch(() => [authUserStore.docsUpdateState, route.name], function () {
    console.log('what is docsUpdate State: ' + authUserStore.docsUpdateState);
    if (authUserStore.docsUpdateState) {
        authUserStore.getMyDocuments();
    }
});

</script>



<template>
    <body class="layout-top-nav">
        <HeaderApp />

        <div class="content-wrapper" style="min-height: 283px;">
            <div class="content">
                <div class="container">
                    <router-view></router-view>
                </div>
            </div>
        </div>

        <FooterApp />
    </body>
</template>