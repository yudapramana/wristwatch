<template>
  <!-- <router-view> akan otomatis merender Guest.vue saat di URL '/' 
       dan merender Venue.vue saat di URL '/venue' -->
  <router-view></router-view>
</template>

<script setup>
// Karena ini hanya untuk halaman publik, kita tidak perlu authUserStore 
// dan pengecekan role admin di sini lagi.
</script>

<style>
/* Anda bisa menaruh styling global di sini jika diperlukan */
html, body {
  scroll-behavior: smooth;
}
</style>