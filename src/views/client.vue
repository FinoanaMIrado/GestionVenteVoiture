<script setup>
import { ref, onMounted } from 'vue'
import bg from '../assets/bakg.png'

const clients = ref([])
const clientSelectionne = ref(null)
const showForm = ref(false)

const form = ref({ CodeClient: '', nom: '', contact: '', statut: 'En attente' })

onMounted(async () => {
    await chargerClients()
})

async function chargerClients() {
    const response = await fetch('http://localhost/backend/client/lire.php')
    clients.value = await response.json()
}

function selectionner(client) {
    if (clientSelectionne.value?.CodeClient === client.CodeClient) {
        clientSelectionne.value = null
    } else {
        clientSelectionne.value = client
    }
}

function ouvrirModifier() {
    if (!clientSelectionne.value) return
    form.value = { ...clientSelectionne.value }
    if (form.value.contact && !form.value.contact.startsWith('+261')) {
        form.value.contact = '+261 ' + form.value.contact
    }
    showForm.value = true
}

function annuler() {
    showForm.value = false
    clientSelectionne.value = null
}

async function supprimerClient() {
    if (!clientSelectionne.value) return
    if (!confirm('Supprimer ce client ?')) return

    await fetch('http://localhost/backend/client/supprimer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idcli: clientSelectionne.value.CodeClient })
    })
    clientSelectionne.value = null
    await chargerClients()
}

async function modifierClient() {
    if (!clientSelectionne.value) return

    await fetch('http://localhost/backend/client/modifier.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            idcli: form.value.CodeClient,
            nom: form.value.nom,
            contact: form.value.contact,
            statut: form.value.statut
        })
    })
    annuler()
    await chargerClients()
}

</script>

<template>
  <div
    class="w-full min-h-screen bg-cover bg-center flex items-center justify-center p-4"
    :style="{ backgroundImage: `url(${bg})` }"
  >
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 w-full max-w-5xl">

      <!-- En-tête -->
      <div class="mb-8 border-b border-slate-200 dark:border-gray-600 pb-6">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Historique des Clients</h1>
        <p class="text-slate-500 dark:text-gray-400 mt-2">Consultez et gérez l'ensemble de vos clients</p>
      </div>

      <!-- Boutons d'action -->
      <div class="flex gap-3 mb-8">
        <button
          v-if="clientSelectionne"
          @click="ouvrirModifier"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
        >
          Éditer
        </button>
        <button
          v-if="clientSelectionne"
          @click="supprimerClient"
          class="bg-slate-700 hover:bg-slate-800 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
        >
          Supprimer
        </button>
      </div>

      <!-- Formulaire -->
      <div v-if="showForm" class="border-2 border-indigo-200 dark:border-indigo-800 p-6 rounded-xl mb-8 bg-indigo-50 dark:bg-indigo-950">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">
          Modifier le client
        </h2>
        <div class="flex gap-3 flex-wrap mb-4">
          <input
            v-model="form.CodeClient"
            placeholder="Code Client"
            disabled
            class="border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg flex-1 min-w-[150px] disabled:bg-slate-100 dark:disabled:bg-gray-600"
          />
          <input
            v-model="form.nom"
            placeholder="Nom du client"
            class="border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg flex-1 min-w-[150px]"
          />
          <input
            v-model="form.contact"
            placeholder="Contact"
            class="border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg flex-1 min-w-[150px]"
          />
        </div>
        <div class="flex justify-end gap-3">
          <button
            @click="annuler"
            class="bg-slate-500 hover:bg-slate-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
          >
            Fermer
          </button>
          <button
            @click="modifierClient()"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
          >
            Valider
          </button>
        </div>
      </div>

      <!-- Tableau -->
      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full">
          <thead>
            <tr class="bg-slate-900 dark:bg-slate-700 text-white">
              <th class="p-4 text-left font-semibold">ID Client</th>
              <th class="p-4 text-left font-semibold">Nom</th>
              <th class="p-4 text-left font-semibold">Contact</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="client in clients"
              :key="client.CodeClient"
              @click="selectionner(client)"
              class="cursor-pointer border-b border-slate-200 dark:border-gray-600 transition-colors duration-150"
              :class="clientSelectionne?.CodeClient === client.CodeClient 
                ? 'bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-150' 
                : 'bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700'"
            >
              <td class="p-4 text-slate-900 dark:text-white font-medium">{{ client.CodeClient }}</td>
              <td class="p-4 text-slate-700 dark:text-gray-200">{{ client.nom }}</td>
              <td class="p-4 text-slate-700 dark:text-gray-200">{{ client.contact }}</td>
            </tr>
            <tr v-if="clients.length === 0">
              <td colspan="3" class="p-8 text-center text-slate-500">
                Aucun client trouvé.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>
