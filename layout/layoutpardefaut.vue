<template>
  <div class="flex h-screen">
    <Navbar />

    <main class="flex-1 overflow-y-auto">
      <div class="sticky top-0 z-50 flex justify-end items-center gap-4 p-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700">
        <button @click="toggle"
          class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200">
          <SunIcon v-if="!isDark" class="w-5 h-5 text-yellow-500" />
          <MoonIcon v-else class="w-5 h-5 text-blue-400" />
          <span class="text-sm font-medium">{{ isDark ? 'Clair' : 'Sombre' }}</span>
        </button>
        <router-link to="/"
          class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200">
          <ArrowRightOnRectangleIcon class="w-5 h-5" />
          <span class="text-sm font-medium">Déconnexion</span>
        </router-link>
      </div>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import Navbar from '../src/components/navbar.vue'
import { SunIcon, MoonIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import { useDarkMode } from '../src/composables/useDarkMode'

const { isDark, toggle } = useDarkMode()
</script>
