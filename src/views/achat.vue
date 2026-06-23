<script setup>
import { ref, onMounted, computed } from 'vue'
import bg from '../assets/bakg.png'

const achats = ref([])
const voitures = ref([])
const showForm = ref(false)
const dateDebut = ref('')
const dateFin = ref('')

const form = ref({ idvoit: '', nom: '', contact: '', qte: 1 })

const voitureSelectionnee = computed(() =>
    voitures.value.find(v => v.idvoit === form.value.idvoit)
)

const prixUnitaire = computed(() => voitureSelectionnee.value ? Number(voitureSelectionnee.value.prix) : 0)
const total = computed(() => prixUnitaire.value * (parseInt(form.value.qte) || 0))


const showInvoice = ref(false)
const invoiceData = ref(null)

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', {
        year: 'numeric', month: 'long', day: 'numeric'
    })
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

onMounted(async () => {
    await Promise.all([
        chargerAchats(),
        chargerVoitures()
    ])
})

async function chargerAchats() {
    const r = await fetch('http://localhost/backend/achat/lire.php')
    achats.value = await r.json()
}

async function chargerVoitures() {
    const r = await fetch('http://localhost/backend/voiture/lire.php')
    voitures.value = await r.json()
}

function fermerFormulaire() { showForm.value = false }

async function effectuerAchat() {
    if (!form.value.idvoit || !form.value.nom || !form.value.contact || form.value.qte < 1) {
        alert('Veuillez remplir tous les champs')
        return
    }
    try {
        const r = await fetch('http://localhost/backend/api/achats.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                numAchat: "ACH-" + Date.now(),
                idvoit: form.value.idvoit,
                nom: form.value.nom.trim(),
                contact: form.value.contact.trim(),
                qte: parseInt(form.value.qte)
            })
        })
        const result = await r.json()
        if (result.success) {
            invoiceData.value = result.invoiceDetails
            showForm.value = false
            await Promise.all([chargerAchats(), chargerVoitures()])
            showInvoice.value = true
        } else {
            alert('✗ ' + (result.error || 'Erreur'))
        }
    } catch (error) {
        alert('Erreur réseau: ' + error.message)
    }
}

async function rechercherParDate() {
    if (!dateDebut.value || !dateFin.value) {
        await chargerAchats()
        return
    }
    const r = await fetch('http://localhost/backend/achat/rechercher.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ date_debut: dateDebut.value, date_fin: dateFin.value })
    })
    achats.value = await r.json()
}

async function annulerAchat(a) {
    if (!confirm('Annuler cet achat et restaurer le stock?')) return
    try {
        const r = await fetch('http://localhost/backend/achat/supprimer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ numAchat: a.numAchat })
        })
        const result = await r.json()
        if (result.success) {
            await Promise.all([chargerAchats(), chargerVoitures()])
        } else {
            alert('✗ ' + (result.error || 'Erreur'))
        }
    } catch { alert('Erreur réseau') }
}

async function ouvrirFacture(numAchat) {
    try {
        const r = await fetch(`http://localhost/backend/achat/lire_un.php?id=${numAchat}`)
        invoiceData.value = await r.json()
        showInvoice.value = true
    } catch {
        alert('Impossible de charger la facture')
    }
}

function fermerFacture() {
    showInvoice.value = false
    invoiceData.value = null
}

function imprimerFacture() {
    window.print()
}
</script>

<template>
  <div class="w-full min-h-screen bg-cover bg-center p-4"
       :style="{ backgroundImage: `url(${bg})` }">
    <div class="max-w-7xl mx-auto">

      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 mb-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-4xl font-bold text-slate-900 dark:text-white">Gestion des Achats</h1>
            <p class="text-slate-500 dark:text-gray-400 mt-2">Gérez les ventes de voitures</p>
          </div>
        </div>


      </div>

      <!-- TAB: Liste des achats -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 mb-6">
          <div class="flex gap-4 items-end">
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Date début</label>
              <input type="date" v-model="dateDebut" @change="rechercherParDate"
                class="border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-2 rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Date fin</label>
              <input type="date" v-model="dateFin" @change="rechercherParDate"
                class="border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-2 rounded-lg" />
            </div>
            <button @click="dateDebut = ''; dateFin = ''; rechercherParDate()"
              class="bg-slate-500 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition-colors">
              Réinitialiser
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-slate-900 dark:bg-slate-700 text-white">
                  <th class="p-4 text-left font-semibold">N°</th>
                  <th class="p-4 text-left font-semibold">Voiture</th>
                  <th class="p-4 text-left font-semibold">Client</th>
                  <th class="p-4 text-left font-semibold">Qté</th>
                  <th class="p-4 text-left font-semibold">Prix unit.</th>
                  <th class="p-4 text-left font-semibold">Total</th>
                  <th class="p-4 text-left font-semibold">Date</th>
                  <th class="p-4 text-left font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="a in achats" :key="a.numAchat"
                  class="border-b border-slate-200 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                  <td class="p-4 font-medium text-slate-900 dark:text-white">{{ a.numAchat }}</td>
                  <td class="p-4 text-slate-700 dark:text-gray-200">{{ a.voiture_design }}</td>
                  <td class="p-4 text-slate-700 dark:text-gray-200">{{ a.client_nom }}</td>
                  <td class="p-4 text-slate-700 dark:text-gray-200">{{ a.qte }}</td>
                  <td class="p-4 text-slate-700 dark:text-gray-200">{{ Number(a.prix_unitaire).toLocaleString() }} Ar</td>
                  <td class="p-4 font-semibold text-indigo-600">{{ Number(a.total).toLocaleString() }} Ar</td>
                  <td class="p-4 text-slate-500 dark:text-gray-400 text-sm">{{ new Date(a.date).toLocaleDateString('fr-FR') }}</td>
                  <td class="p-4">
                    <div class="flex gap-2">
                      <button @click="ouvrirFacture(a.numAchat)"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-sm transition-colors">
                        Facture
                      </button>
                      <button @click="annulerAchat(a)"
                        class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1.5 rounded text-sm transition-colors">
                        Annuler
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="achats.length === 0">
                  <td colspan="8" class="p-8 text-center text-slate-500 dark:text-gray-400">Aucun achat trouvé</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      <!-- Formulaire modal -->
      <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-lg w-full">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Nouvel achat</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Voiture</label>
              <select v-model="form.idvoit"
                class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg">
                <option value="">Sélectionner une voiture</option>
                <option v-for="v in voitures" :key="v.idvoit" :value="v.idvoit"
                  :disabled="v.nombre < 1">
                  {{ v.idvoit }} — {{ v.Design }} (stock: {{ v.nombre }}) — {{ Number(v.prix).toLocaleString() }} Ar
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Nom du client</label>
              <input v-model="form.nom" placeholder="Nom complet"
                class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Contact</label>
              <input v-model="form.contact" placeholder="Téléphone / email"
                class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Quantité</label>
              <input type="number" min="1" v-model="form.qte"
                :max="voitureSelectionnee?.nombre || 1"
                class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg" />
            </div>
            <div class="bg-slate-50 dark:bg-gray-700 p-4 rounded-xl space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-600 dark:text-gray-300">Prix unitaire:</span>
                <span class="font-semibold">{{ prixUnitaire.toLocaleString() }} Ar</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-600">Quantité:</span>
                <span class="font-semibold">{{ form.qte || 0 }}</span>
              </div>
              <div class="border-t border-slate-300 pt-2 flex justify-between">
                <span class="text-slate-900 font-bold">Total:</span>
                <span class="text-indigo-600 font-bold text-lg">{{ total.toLocaleString() }} Ar</span>
              </div>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t mt-4">
            <button @click="fermerFormulaire"
              class="bg-slate-500 hover:bg-slate-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
              Annuler
            </button>
            <button @click="effectuerAchat"
              class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
              Valider l'achat
            </button>
          </div>
        </div>
      </div>

      <!-- Invoice modal -->
      <div v-if="showInvoice && invoiceData" class="fixed inset-0 bg-black bg-opacity-50 flex items-start justify-center p-4 z-50 overflow-y-auto">
        <div class="invoice-sheet bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-3xl w-full my-8 border border-slate-200 dark:border-gray-600">
          <div class="no-print flex justify-end mb-4 gap-3">
            <button @click="imprimerFacture"
              class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors shadow-md">
              Imprimer / PDF
            </button>
            <button @click="fermerFacture"
              class="bg-slate-500 hover:bg-slate-600 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors shadow-md">
              Fermer
            </button>
          </div>

          <!-- En-tête -->
          <div class="flex justify-between items-start border-b-2 border-slate-300 pb-6 mb-6">
            <div>
              <h1 class="text-3xl font-bold text-slate-900">GESTION VENTE VOITURE</h1>
              <p class="text-slate-600 mt-1">Antananarivo, Madagascar</p>
              <p class="text-slate-600">+261 34 00 000 00</p>
              <p class="text-slate-600">contact@gvv.mg</p>
            </div>
            <div class="text-right">
              <h2 class="text-4xl font-black text-indigo-600">FACTURE</h2>
              <p class="text-slate-500 mt-2">N° {{ String(invoiceData.numAchat).padStart(4, '0') }}</p>
              <p class="text-slate-500">Date: {{ formatDate(invoiceData.date) }}</p>
            </div>
          </div>

          <!-- Infos client -->
          <div class="mb-8 p-4 bg-slate-50 rounded-xl">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Client</h3>
            <p class="text-lg font-bold text-slate-900">{{ invoiceData.clientNom || invoiceData.client_nom }}</p>
            <p class="text-slate-600">Code: {{ invoiceData.idcli }}</p>
            <p class="text-slate-600">Contact: {{ invoiceData.clientContact || invoiceData.client_contact }}</p>
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
                <td class="p-4 text-slate-900 font-medium">{{ invoiceData.designation || invoiceData.voiture_design }}</td>
                <td class="p-4 text-right text-slate-700">{{ Number(invoiceData.prixUnitaire || invoiceData.prix_unitaire).toLocaleString() }} Ar</td>
                <td class="p-4 text-center text-slate-700">{{ invoiceData.qte }}</td>
                <td class="p-4 text-right text-slate-900 font-semibold">{{ Number(invoiceData.totalGeneral || invoiceData.total).toLocaleString() }} Ar</td>
              </tr>
            </tbody>
          </table>

          <!-- Total -->
          <div class="flex justify-end">
            <div class="w-72">
              <div class="flex justify-between py-2 text-lg">
                <span class="text-slate-600">Sous-total:</span>
                <span class="font-semibold">{{ Number(invoiceData.totalGeneral || invoiceData.total).toLocaleString() }} Ar</span>
              </div>
              <div class="flex justify-between py-2 text-lg border-t border-slate-300">
                <span class="text-slate-600">Remise:</span>
                <span class="font-semibold">0 Ar</span>
              </div>
              <div class="flex justify-between py-3 text-2xl font-black border-t-2 border-slate-900 mt-2 pt-3">
                <span class="text-slate-900">Net à payer:</span>
                <span class="text-indigo-600">{{ Number(invoiceData.totalGeneral || invoiceData.total).toLocaleString() }} Ar</span>
              </div>
            </div>
          </div>

          <!-- Montant en lettres -->
          <div class="mt-6 p-4 bg-indigo-50 rounded-xl text-center">
            <p class="text-sm text-slate-500">Arrêté par la présente facture à la somme de</p>
            <p class="text-lg font-bold text-indigo-700 capitalize">
              {{ nombreEnLettres(Number(invoiceData.totalGeneral || invoiceData.total)) }} ariary
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
  </div>
</template>

<style scoped>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; margin: 0; padding: 0; }
    .fixed { position: static !important; background: none !important; }
    .invoice-sheet { box-shadow: none !important; border: none !important; border-radius: 0 !important; padding: 20px !important; margin: 0 !important; max-width: 100% !important; }
    .bg-white { background: white !important; }
    .shadow-2xl { box-shadow: none !important; }
}
</style>
