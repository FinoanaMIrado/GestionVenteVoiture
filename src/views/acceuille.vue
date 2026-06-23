<script setup>
import { ref, onMounted } from 'vue'
import { ArrowRightIcon } from '@heroicons/vue/24/solid'

const bilans = ref({
  monthly: [],
  total_revenu: 0,
  total_ventes: 0,
  stock_disponible: 0,
  moyenne_mensuelle: 0,
  achats_recents: [],
  stock_faible: [],
  max_revenu: 1
})
const chargement = ref(true)
const erreur = ref('')

async function chargerDonnees() {
  chargement.value = true
  erreur.value = ''

  try {
    const [stats, voitures, achats] = await Promise.all([
      fetch('/backend/achat/stats.php').then(r => r.json()),
      fetch('/backend/voiture/lire.php').then(r => r.json()),
      fetch('/backend/achat/lire.php').then(r => r.json())
    ])

    const monthly = stats.monthly || []
    const totalRevenue = stats.total_revenu || 0
    const totalSales = stats.total_ventes || 0

    const stockDisponible = (voitures || []).filter(v => parseInt(v.nombre) > 0).length
    const stockFaible = (voitures || []).filter(v => parseInt(v.nombre) > 0 && parseInt(v.nombre) < 3).slice(0, 5)

    const achatsRecents = (achats || []).slice(0, 5)

    const nbMois = monthly.length
    const moyenne = nbMois > 0 ? Math.round(totalRevenue / nbMois) : 0
    const maxRev = monthly.length ? Math.max(...monthly.map(m => parseFloat(m.revenu)), 1) : 1

    bilans.value = {
      monthly,
      total_revenu: totalRevenue,
      total_ventes: totalSales,
      stock_disponible: stockDisponible,
      moyenne_mensuelle: moyenne,
      achats_recents: achatsRecents,
      stock_faible: stockFaible,
      max_revenu: maxRev
    }

  } catch (e) {
    erreur.value = 'Erreur: ' + e.message
  } finally {
    chargement.value = false
  }
}

onMounted(() => {
  chargerDonnees()
})
</script>

<template>
  <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-950 p-6">
    <div class="max-w-7xl mx-auto space-y-6">

      <div v-if="chargement" class="flex justify-center items-center py-20">
        <div class="flex flex-col items-center gap-3">
          <div class="w-10 h-10 border-4 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-gray-500 dark:text-gray-400">Chargement des données...</p>
        </div>
      </div>

      <div v-else-if="erreur" class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 p-4 rounded-xl text-center">
        {{ erreur }}
      </div>

      <template v-else>

      <!-- En-tête -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Rapport sur les 6 derniers mois</h1>
      </div>

      <!-- Bannière -->
      <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl shadow-xl p-8 text-center">
        <p class="text-white text-2xl font-bold">Gérez votre inventaire et effectuez des ventes</p>
      </div>

      <!-- KPIs -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
          <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Ventes effectuées</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ bilans.total_ventes }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
          <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Revenu total</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ Number(bilans.total_revenu).toLocaleString('fr-FR') }} Ar</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
          <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Stock disponible</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ bilans.stock_disponible }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
          <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Moyenne/mois</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ Number(bilans.moyenne_mensuelle).toLocaleString('fr-FR') }} Ar</p>
        </div>
      </div>

      </template>

      <!-- Revenu mensuel -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Revenu mensuel</h3>
        <div v-if="bilans.monthly.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
          Aucune donnée pour les 6 derniers mois
        </div>
        <div v-else class="space-y-3">
          <div v-for="m in bilans.monthly" :key="m.mois" class="flex items-center gap-4">
            <div class="w-24 text-sm font-semibold text-gray-700 dark:text-gray-300">
              {{ ('Janv Fév Mar Avr Mai Juin Juil Août Sept Oct Nov Déc').split(' ')[parseInt(m.mois.split('-')[1]) - 1] }} {{ m.mois.split('-')[0] }}
            </div>
            <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-7 overflow-hidden">
              <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full transition-all duration-500 flex items-center justify-end pr-3"
                   :style="{ width: (parseFloat(m.revenu) / bilans.max_revenu * 100) + '%' }">
                <span v-if="parseFloat(m.revenu) / bilans.max_revenu > 0.15" class="text-white text-xs font-bold">{{ Number(m.revenu).toLocaleString('fr-FR') }} Ar</span>
              </div>
            </div>
            <div class="w-16 text-sm text-gray-500 dark:text-gray-400 text-right">{{ m.nombre_ventes }} vente(s)</div>
          </div>
        </div>
      </div>

      <template v-if="!chargement && !erreur">

      <!-- Achats récents et Stock faible -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
            Achats récents
          </h3>
          <div v-if="bilans.achats_recents.length" class="space-y-3">
            <div v-for="a in bilans.achats_recents" :key="a.numAchat"
              class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ a.voiture_design }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ a.client_nom }} &middot; {{ a.date }}</p>
              </div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ Number(a.total).toLocaleString('fr-FR') }} Ar</p>
            </div>
          </div>
          <p v-else class="text-gray-500 dark:text-gray-400">Aucun achat récent.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
            Stock faible
          </h3>
          <div v-if="bilans.stock_faible.length" class="space-y-3">
            <div v-for="v in bilans.stock_faible" :key="v.idvoit"
              class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ v.Design }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ v.idvoit }}</p>
              </div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ v.nombre }} restant(s)</p>
            </div>
          </div>
          <p v-else class="text-gray-500 dark:text-gray-400">Stock suffisant.</p>
        </div>
      </div>

      <div class="flex justify-end">
        <router-link to="/voiture"
          class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5">
          <span>Aller au Catalogue des Voitures</span>
          <ArrowRightIcon class="w-5 h-5" />
        </router-link>
      </div>

      </template>

    </div>
  </div>
</template>
