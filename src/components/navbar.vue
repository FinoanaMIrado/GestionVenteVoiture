<script setup>
import {
  HomeIcon,
  UserIcon,
  ShoppingCartIcon,
  ChartBarIcon,
  TruckIcon
} from '@heroicons/vue/24/outline'
</script>

<template>
  <div class="h-screen w-64 bg-black text-white flex flex-col">

  
    <div class="p-4 border-b border-gray-700 flex justify-center">
      <img src="../assets/iz.png" class="h-25 w-auto object-contain" />
    </div>

   
    <nav class="flex-1 p-4 space-y-2">

      <a href="#" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700">
        <HomeIcon class="w-5 h-5" />
        <span>Accueil</span>
      </a>

      <a href="#" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700">
        <TruckIcon class="w-5 h-5" />
        <span>Voiture</span>
      </a>

      <a href="#" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700">
        <UserIcon class="w-5 h-5" />
        <span>Client</span>
      </a>

      <a href="#" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700">
        <ShoppingCartIcon class="w-5 h-5" />
        <span>Achat</span>
      </a>

      <a href="#" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700">
        <ChartBarIcon class="w-5 h-5" />
        <span>Bilan</span>
      </a>

    </nav>
  </div>
</template>