<template>
  <div class="container">
    <Navbar />

    <div class="content">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import Navbar from '../src/components/navbar.vue'
</script>