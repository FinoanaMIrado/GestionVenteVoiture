<script setup>
import bg from '../assets/bakg.png'
import Popupmodif from '../components/popupmodif.vue';
</script>
<template>
    <div
  class="w-full min-h-screen bg-cover bg-center flex items-center justify-center "
  :style="{ backgroundImage: `url(${bg})` }"
>
  <div class="w-[90%] h-[85vh] bg-white/20 backdrop-blur-md border border-white/30 rounded-2xl shadow-lg overflow-hidden">
    <Popupmodif/>
  </div>
</div>
</template>