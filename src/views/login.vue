<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const username = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const loading = ref(false)
const isRegister = ref(false)

function basculerMode() {
  isRegister.value = !isRegister.value
  error.value = ''
}

async function seConnecter() {
  error.value = ''
  loading.value = true
  try {
    const r = await fetch('http://localhost/backend/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ username: username.value, password: password.value })
    })
    const data = await r.json()
    if (data.success) {
      router.push('/acc')
    } else {
      error.value = data.error || 'Identifiants incorrects'
    }
  } catch {
    error.value = 'Erreur de connexion au serveur'
  } finally {
    loading.value = false
  }
}

async function creerCompte() {
  error.value = ''
  if (password.value !== confirmPassword.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }
  loading.value = true
  try {
    const r = await fetch('http://localhost/backend/auth/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: username.value, password: password.value })
    })
    const data = await r.json()
    if (data.success) {
      isRegister.value = false
      error.value = 'Compte créé ! Connectez-vous.'
    } else {
      error.value = data.error || 'Erreur lors de la création'
    }
  } catch {
    error.value = 'Erreur de connexion au serveur'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 bg-white rounded-xl shadow-lg">
      <div class="flex justify-center items-start">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
        width="80" height="80" stroke-width="1.5" stroke="currentColor" class="size-3">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg><br>
      </div>
      <h1 class="mb-6 text-2xl font-semibold text-center text-gray-800 dark:text-white">
        {{ isRegister ? 'Créer un compte' : 'Authentification' }}
      </h1>

      <div v-if="error" class="mb-4 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg text-sm text-center">
        {{ error }}
      </div>

      <form class="space-y-5" @submit.prevent="isRegister ? creerCompte() : seConnecter()">
        <div>
          <label for="username" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Nom d'utilisateur
          </label>
          <input
            type="text" id="username" v-model="username" required
            placeholder="Nom d'utilisateur"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition duration-200"
          />
        </div>

        <div>
          <label for="password" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Mot de passe
          </label>
          <input
            type="password" id="password" v-model="password" required
            placeholder="Mot de passe"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition duration-200"
          />
        </div>

        <div v-if="isRegister">
          <label for="confirmPassword" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Confirmer le mot de passe
          </label>
          <input
            type="password" id="confirmPassword" v-model="confirmPassword" required
            placeholder="Confirmer le mot de passe"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition duration-200"
          />
        </div>

        <button
          type="submit" :disabled="loading"
          class="w-full py-3 font-semibold text-white transition duration-300 bg-gradient-to-r from-orange-500 to-orange-700 rounded-xl hover:from-orange-600 hover:to-orange-800 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none disabled:hover:translate-y-0"
        >
          {{ loading ? 'Chargement...' : (isRegister ? 'Créer un compte' : 'Se connecter') }}
        </button>
      </form>

      <div class="flex justify-center mt-6">
        <button @click="basculerMode" class="text-sm font-medium text-orange-600 hover:text-orange-800 hover:underline transition duration-200">
          {{ isRegister ? 'Déjà un compte ? Connectez-vous' : 'Créer un compte' }}
        </button>
      </div>
    </div>
  </div>
</template>
