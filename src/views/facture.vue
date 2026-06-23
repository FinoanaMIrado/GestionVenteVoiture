<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const facture = ref(null)
const loading = ref(true)
const error = ref('')

onMounted(async () => {
    const id = route.params.id
    try {
        const r = await fetch(`http://localhost/backend/achat/lire_un.php?id=${id}`)
        if (!r.ok) throw new Error('Facture introuvable')
        facture.value = await r.json()
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
})

function imprimer() {
    window.print()
}

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', {
        year: 'numeric', month: 'long', day: 'numeric'
    })
}

const company = {
    nom: "Varotra Fiara",
    adresse: "Antananarivo, Madagascar",
    contact: "+261 34 87 823 45",
    email: "fiaravarotra@gvv.mg"
}

const lesUnites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
    'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize']
const lesDizaines = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante']

function centaineEnLettres(n) {
    if (n === 0) return ''
    const c = Math.floor(n / 100)
    const d = n % 100
    let s = ''
    if (c === 1) s = 'cent'
    else if (c > 1) s = lesUnites[c] + ' cent'
    if (d === 0) {
        if (c > 1) s += 's'
        return s
    }
    if (c > 0) s += ' '
    if (d < 17) {
        s += lesUnites[d]
    } else if (d < 20) {
        s += 'dix-' + lesUnites[d - 10]
    } else {
        const d10 = Math.floor(d / 10)
        const u = d % 10
        if (d10 === 7 || d10 === 9) {
            const base = d10 === 7 ? 60 : 80
            const diff = d - base
            const prefix = d10 === 7 ? 'soixante' : 'quatre-vingt'
            if (diff === 11) s += prefix + '-onze'
            else if (diff === 1) s += prefix + '-un'
            else s += prefix + '-' + lesUnites[diff]
        } else {
            if (d10 === 8) {
                if (u === 0) s += 'quatre-vingts'
                else s += 'quatre-vingt-' + lesUnites[u]
            } else {
                if (u === 1) s += lesDizaines[d10] + ' et un'
                else if (u > 0) s += lesDizaines[d10] + '-' + lesUnites[u]
                else s += lesDizaines[d10]
            }
        }
    }
    return s
}

function nombreEnLettres(n) {
    if (n === 0) return 'zéro'
    const billions = Math.floor(n / 1000000000)
    const millions = Math.floor((n % 1000000000) / 1000000)
    const milliers = Math.floor((n % 1000000) / 1000)
    const reste = n % 1000
    let result = []
    if (billions > 0) {
        const p = centaineEnLettres(billions)
        result.push(billions === 1 ? 'un milliard' : p + ' milliards')
    }
    if (millions > 0) {
        const p = centaineEnLettres(millions)
        result.push(millions === 1 ? 'un million' : p + ' millions')
    }
    if (milliers > 0) {
        const p = centaineEnLettres(milliers)
        if (milliers === 1) result.push('mille')
        else result.push(p + ' mille')
    }
    if (reste > 0) {
        result.push(centaineEnLettres(reste))
    }
    return result.join(' ')
}
</script>

<template>
  <div class="facture-wrapper">
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <p class="text-xl text-slate-500">Chargement...</p>
    </div>

    <div v-else-if="error" class="flex items-center justify-center min-h-screen">
      <p class="text-xl text-red-500">{{ error }}</p>
    </div>

    <div v-else class="facture-container">
      <div class="no-print text-center mb-6">
        <button @click="imprimer"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-lg shadow-lg transition-colors text-lg">
          Imprimer la facture
        </button>
      </div>

      <div class="facture bg-white rounded-2xl shadow-2xl p-10 max-w-4xl mx-auto border border-slate-200">
        <!-- En-tête -->
        <div class="flex justify-between items-start border-b-2 border-slate-300 pb-6 mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ company.nom }}</h1>
            <p class="text-slate-600 mt-1">{{ company.adresse }}</p>
            <p class="text-slate-600">{{ company.contact }}</p>
            <p class="text-slate-600">{{ company.email }}</p>
          </div>
          <div class="text-right">
            <h2 class="text-4xl font-black text-indigo-600">FACTURE</h2>
            <p class="text-slate-500 mt-2">N° {{ String(facture.numAchat).padStart(4, '0') }}</p>
            <p class="text-slate-500">Date: {{ formatDate(facture.date) }}</p>
          </div>
        </div>

        <!-- Infos client -->
        <div class="mb-8 p-4 bg-slate-50 rounded-xl">
          <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Client</h3>
          <p class="text-lg font-bold text-slate-900">{{ facture.client_nom }}</p>
          <p class="text-slate-600">Code: {{ facture.idcli }}</p>
          <p class="text-slate-600">Contact: {{ facture.client_contact }}</p>
        </div>

        <!-- Détails -->
        <table class="w-full mb-8">
          <thead>
            <tr class="bg-slate-900 text-white">
              <th class="p-4 text-left font-semibold">Désignation</th>
              <th class="p-4 text-right font-semibold">Prix unitaire</th>
              <th class="p-4 text-center font-semibold">Quantité</th>
              <th class="p-4 text-right font-semibold">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b border-slate-200">
              <td class="p-4 text-slate-900 font-medium">{{ facture.voiture_design }}</td>
              <td class="p-4 text-right text-slate-700">{{ Number(facture.prix_unitaire).toLocaleString() }} Ar</td>
              <td class="p-4 text-center text-slate-700">{{ facture.qte }}</td>
              <td class="p-4 text-right text-slate-900 font-semibold">{{ Number(facture.prix_unitaire * facture.qte).toLocaleString() }} Ar</td>
            </tr>
          </tbody>
        </table>

        <!-- Total -->
        <div class="flex justify-end">
          <div class="w-72">
            <div class="flex justify-between py-2 text-lg">
              <span class="text-slate-600">Sous-total:</span>
              <span class="font-semibold">{{ Number(facture.total).toLocaleString() }} Ar</span>
            </div>
            <div class="flex justify-between py-2 text-lg border-t border-slate-300">
              <span class="text-slate-600">Remise:</span>
              <span class="font-semibold">0 Ar</span>
            </div>
            <div class="flex justify-between py-3 text-2xl font-black border-t-2 border-slate-900 mt-2 pt-3">
              <span class="text-slate-900">Net à payer:</span>
              <span class="text-indigo-600">{{ Number(facture.total).toLocaleString() }} Ar</span>
            </div>
          </div>
        </div>

        <!-- Montant en lettres -->
        <div class="mt-6 p-4 bg-indigo-50 rounded-xl text-center">
          <p class="text-sm text-slate-500">Arrêté par la présente facture à la somme de</p>
          <p class="text-lg font-bold text-indigo-700 capitalize">
            {{ nombreEnLettres(Number(facture.total)) }} ariary
          </p>
        </div>

        <!-- Bas de page -->
        <div class="mt-6 pt-6 border-t border-slate-200 text-center text-sm text-slate-400">
          <p>Merci de votre confiance !</p>
          <p class="mt-1">Cette facture est générée automatiquement par le système GVV.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; margin: 0; padding: 0; }
    .facture-wrapper { padding: 0; }
    .facture-container { padding: 0; background: white; }
    .facture { box-shadow: none !important; border: none !important; border-radius: 0 !important; padding: 20px !important; }
    .bg-white { background: white !important; }
    .shadow-2xl { box-shadow: none !important; }
}
</style>
