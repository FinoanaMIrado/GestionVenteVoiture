<script setup>
import {
  HomeIcon,
  UserIcon,
  WalletIcon,
  TruckIcon
} from '@heroicons/vue/24/outline'
</script>

<template>
  <div class="h-screen w-72 flex flex-col" style="background-color: #1e293b;">

    <div class="p-6 border-b border-slate-600 flex justify-center">
      <img src="../assets/iz.png" class="h-20 w-auto object-contain" />
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2">
      <router-link to="/acc"
        class="flex items-center gap-4 px-5 py-4 rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50 transition-all duration-200 text-base"
        active-class="!text-white !bg-slate-700/70 font-semibold">
        <HomeIcon class="w-6 h-6" /><span>Bilan</span>
      </router-link>
      <router-link to="/voiture"
        class="flex items-center gap-4 px-5 py-4 rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50 transition-all duration-200 text-base"
        active-class="!text-white !bg-slate-700/70 font-semibold">
        <TruckIcon class="w-6 h-6" /><span>Voiture</span>
      </router-link>
      <router-link to="/client"
        class="flex items-center gap-4 px-5 py-4 rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50 transition-all duration-200 text-base"
        active-class="!text-white !bg-slate-700/70 font-semibold">
        <UserIcon class="w-6 h-6" /><span>Client</span>
      </router-link>
      <router-link to="/achat"
        class="flex items-center gap-4 px-5 py-4 rounded-xl text-slate-300 hover:text-white hover:bg-slate-700/50 transition-all duration-200 text-base"
        active-class="!text-white !bg-slate-700/70 font-semibold">
        <WalletIcon class="w-6 h-6" /><span>Achat</span>
      </router-link>
    </nav>

    <div class="p-6 border-t border-slate-600"></div>
  </div>
</template>
