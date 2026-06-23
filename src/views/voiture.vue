<script setup>
import { ref, onMounted } from 'vue'
import bg from '../assets/bakg.png'

const voitures = ref([])
const voitureSelectionnee = ref(null)
const showForm = ref(false)
const isEditing = ref(false)

const showInvoice = ref(false)
const invoiceData = ref(null)
const modeForm = ref('')
const recherche = ref('')
const imagePreview = ref(null)
const imageFile = ref(null)

const form = ref({
  idvoit: '',
  Design: '',
  prix: '',
  nombre: '',
  image: ''
})

const achatForm = ref({ nom: '', prenom: '', contact: '', qte: 1 })

const moisNoms = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']

onMounted(async () => {
  await chargerVoitures()
})

async function chargerVoitures() {
  try {
    const response = await fetch('/backend/voiture/lire.php')
    if (!response.ok) throw new Error('HTTP ' + response.status)
    const data = await response.json()
    voitures.value = data.filter(v => Number(v.nombre) > 0)
  } catch (e) {
    console.error('Erreur chargement voitures:', e)
    voitures.value = []
  }
}

function ouvrirDetail(voiture) {
  voitureSelectionnee.value = voiture
  achatForm.value = { nom: '', prenom: '', contact: '', qte: 1 }
}

function fermerDetail() {
  voitureSelectionnee.value = null
}

function ouvrirAjout() {
  modeForm.value = 'ajouter'
  form.value = { idvoit: '', Design: '', prix: '', nombre: '', image: '' }
  imagePreview.value = null
  imageFile.value = null
  showForm.value = true
}

function ouvrirModifier() {
  if (!voitureSelectionnee.value) return
  form.value = { ...voitureSelectionnee.value }
  imagePreview.value = form.value.image ? `http://localhost/backend/uploads/${form.value.image}` : null
  imageFile.value = null
  isEditing.value = true
}

function annuler() {
  showForm.value = false
  voitureSelectionnee.value = null
  imagePreview.value = null
  imageFile.value = null
  isEditing.value = false
}

function annulerEdition() {
  isEditing.value = false
  imagePreview.value = null
  imageFile.value = null
}

function handleImageUpload(event) {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

async function uploadImage() {
  if (!imageFile.value) {
    return form.value.image ?? ''
  }

  const formData = new FormData()
  formData.append('image', imageFile.value)

  try {
    const response = await fetch('http://localhost/backend/voiture/uploader.php', {
      method: 'POST',
      body: formData
    })
    const data = await response.json()
    if (data.success) {
      return data.filename
    }
    alert('Erreur upload image: ' + (data.error || 'Échec inconnu') + '. La voiture sera enregistrée sans image.')
    return form.value.image ?? ''
  } catch (error) {
    alert('Erreur réseau lors de l\'upload: ' + error.message + '. La voiture sera enregistrée sans image.')
    return form.value.image ?? ''
  }
}

async function ajouterVoiture() {
  if (!form.value.idvoit || !form.value.Design) {
    alert('Veuillez remplir tous les champs obligatoires')
    return
  }

  try {
    const imageFilename = await uploadImage()

    const dataToSend = {
      idvoit: form.value.idvoit.trim(),
      Design: form.value.Design.trim(),
      prix: parseInt(form.value.prix) || 0,
      nombre: parseInt(form.value.nombre) || 0,
      image: imageFilename || ''
    }

    const response = await fetch('http://localhost/backend/voiture/creer.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dataToSend)
    })

    const text = await response.text()

    if (!text) {
      alert('Erreur: Réponse vide du serveur')
      return
    }

    try {
      const result = JSON.parse(text)

      if (result.success) {
        alert('✓ Voiture ajoutée avec succès!')
        annuler()
        await chargerVoitures()
      } else {
        alert('✗ Erreur: ' + (result.error || 'Erreur inconnue'))
      }
    } catch (parseError) {
      alert('Erreur serveur: ' + text.substring(0, 200))
    }
  } catch (error) {
    alert('Erreur réseau: ' + error.message)
  }
}

async function enregistrerModification() {
  if (!voitureSelectionnee.value) return
  if (!form.value.Design) { alert('Veuillez remplir la désignation'); return }

  try {
    const formData = new FormData()
    formData.append('idvoit', form.value.idvoit.trim())
    formData.append('Design', form.value.Design.trim())
    formData.append('prix', parseInt(form.value.prix) || 0)
    formData.append('nombre', parseInt(form.value.nombre) || 0)
    if (imageFile.value) formData.append('image', imageFile.value)

    const response = await fetch('http://localhost/backend/api/voitures.php', {
      method: 'POST',
      body: formData
    })

    const text = await response.text()
    if (!text) { alert('Erreur: Réponse vide du serveur'); return }

    const result = JSON.parse(text)
    if (result.success) {
      const updated = {
        ...voitureSelectionnee.value,
        idvoit: form.value.idvoit.trim(),
        Design: form.value.Design.trim(),
        prix: parseInt(form.value.prix) || 0,
        nombre: parseInt(form.value.nombre) || 0,
        image: result.image || voitureSelectionnee.value.image || ''
      }
      voitureSelectionnee.value = updated
      isEditing.value = false
      await chargerVoitures()
    } else {
      alert('✗ Erreur: ' + (result.error || 'Erreur inconnue'))
    }
  } catch (error) {
    alert('Erreur réseau: ' + error.message)
  }
}

async function supprimerVoiture() {
  if (!voitureSelectionnee.value) return
  if (!confirm('Supprimer cette voiture ?')) return

  await fetch('http://localhost/backend/voiture/supprimer.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ idvoit: voitureSelectionnee.value.idvoit })
  })
  voitureSelectionnee.value = null
  await chargerVoitures()
}

async function effectuerAchat() {
  const nomComplet = (achatForm.value.nom + ' ' + achatForm.value.prenom).trim()
  if (!nomComplet || !achatForm.value.contact || achatForm.value.qte < 1) {
    alert('Veuillez remplir tous les champs')
    return
  }
  if (achatForm.value.qte > parseInt(voitureSelectionnee.value.nombre)) {
    alert('Stock insuffisant. Stock actuel: ' + voitureSelectionnee.value.nombre)
    return
  }
  try {
    const r = await fetch('http://localhost/backend/api/achats.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        numAchat: "ACH-" + Date.now(),
        idvoit: voitureSelectionnee.value.idvoit,
        nom: nomComplet,
        contact: achatForm.value.contact.trim(),
        qte: parseInt(achatForm.value.qte)
      })
    })
    const result = await r.json()
    if (result.success) {
      invoiceData.value = result.invoiceDetails
      voitureSelectionnee.value = null
      await chargerVoitures()
      showInvoice.value = true
    } else {
      alert('✗ ' + (result.error || 'Erreur'))
    }
  } catch (error) {
    alert('Erreur réseau: ' + error.message)
  }
}

function fermerFacture() {
  showInvoice.value = false
  invoiceData.value = null
}

function imprimerFacture() {
  window.print()
}

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
  if (d < 17) s += lesUnites[d]
  else if (d < 20) s += 'dix-' + lesUnites[d - 10]
  else {
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
  if (reste > 0) result.push(centaineEnLettres(reste))
  return result.join(' ')
}

async function rechercherVoiture() {
  if (recherche.value === '') {
    await chargerVoitures()
    return
  }
  const response = await fetch('http://localhost/backend/voiture/rechercher.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ mot: recherche.value })
  })
  const data = await response.json()
  voitures.value = data.filter(v => Number(v.nombre) > 0)
}
</script>

<template>
  <div class="w-full min-h-screen bg-cover bg-center p-4" :style="{ backgroundImage: `url(${bg})` }">
    <div class="max-w-7xl mx-auto">

      <!-- En-tête -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 mb-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-4xl font-bold text-slate-900 dark:text-white">Catalogue des Voitures</h1>
          </div>
          <button @click="ouvrirAjout"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl text-lg">
            + Ajouter une voiture
          </button>
        </div>

        <!-- Barre de recherche -->
        <div class="mt-6">
          <input v-model="recherche" @input="rechercherVoiture" placeholder="Rechercher par ID ou désignation..."
            class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg" />
        </div>
      </div>

      <!-- Modal Formulaire -->
      <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-2xl w-full">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
            {{ modeForm === 'ajouter' ? 'Ajouter une voiture' : 'Modifier la voiture' }}
          </h2>

          <div class="space-y-4">
            <!-- Upload image -->
            <div class="flex gap-4">
              <div class="flex-1">
                <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Image</label>
                <input type="file" accept="image/*" @change="handleImageUpload"
                  class="border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 p-2 rounded-lg w-full" />
              </div>
              <div v-if="imagePreview" class="w-24 h-24">
                <img :src="imagePreview" class="w-full h-full object-cover rounded-lg" />
              </div>
            </div>

            <!-- ID Voiture -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">ID Voiture</label>
              <input v-model="form.idvoit" placeholder="ID voiture" :disabled="modeForm === 'modifier'"
                class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg disabled:bg-slate-100 dark:disabled:bg-gray-600" />
            </div>

            <!-- Désignation -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Désignation</label>
              <input v-model="form.Design" placeholder="Modèle de la voiture"
                class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg" />
            </div>

            <!-- Prix et Nombre -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Prix (Ar)</label>
                <input v-model="form.prix" placeholder="Prix" type="number"
                  class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Nombre</label>
                <input v-model="form.nombre" placeholder="Stock" type="number"
                  class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-3 rounded-lg" />
              </div>
            </div>

            <!-- Boutons -->
            <div class="flex gap-3 mt-6 pt-4 border-t">
              <button @click="ajouterVoiture()"
                class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200">
                Valider
              </button>
              <button @click="annuler"
                class="flex-1 bg-slate-500 hover:bg-slate-600 text-white font-semibold py-3 rounded-lg transition-colors duration-200">
                Fermer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Détail popup -->
      <div v-if="voitureSelectionnee"
        class="fixed inset-0 bg-slate-900/30 backdrop-blur-md flex items-center justify-center p-4 z-50">
        <div class="bg-white/95 dark:bg-gray-800/95 shadow-2xl rounded-3xl max-w-5xl w-full p-8 border border-slate-200/50 dark:border-gray-600/50 overflow-hidden relative">
          <button @click="fermerDetail"
            class="absolute top-5 right-5 w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors z-10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
            <!-- Left: Image -->
            <div>
              <img
                :src="voitureSelectionnee.image ? `http://localhost/backend/uploads/${voitureSelectionnee.image}` : 'https://unsplash.com'"
                @error="e => e.target.src = 'https://unsplash.com'"
                class="w-full h-[400px] object-cover rounded-2xl shadow-inner"
                :alt="voitureSelectionnee.Design" />
            </div>
            <!-- Right: Specs + Actions + Form -->
            <div class="space-y-5">
              <div>
                <p class="text-sm text-slate-500 font-semibold">ID: {{ voitureSelectionnee.idvoit }}</p>
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ voitureSelectionnee.Design }}</h2>
                <div class="mt-4 space-y-3">
                  <div class="flex justify-between text-lg border-b border-slate-200 dark:border-gray-600 pb-2">
                    <span class="text-slate-500">Prix unitaire</span>
                    <span class="font-bold text-emerald-600">{{ Number(voitureSelectionnee.prix).toLocaleString() }} Ar</span>
                  </div>
                  <div class="flex justify-between text-lg border-b border-slate-200 dark:border-gray-600 pb-2">
                    <span class="text-slate-500">Stock disponible</span>
                    <span class="font-bold" :class="voitureSelectionnee.nombre > 0 ? 'text-emerald-600' : 'text-red-600'">
                      {{ voitureSelectionnee.nombre }} unité(s)
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex gap-2">
                <button @click="ouvrirModifier"
                  class="flex-1 bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-sm">
                  Modifier
                </button>
                <button @click="supprimerVoiture"
                  class="flex-1 bg-white text-slate-600 hover:bg-slate-50 border border-slate-300 text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-sm">
                  Supprimer
                </button>
              </div>

              <!-- Purchase form card (default) -->
              <div v-if="!isEditing" class="bg-slate-50 dark:bg-gray-800/50 p-5 rounded-2xl border border-slate-200 dark:border-gray-600 mt-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Effectuer l'achat</h3>
                <div class="space-y-3">
                  <div class="grid grid-cols-2 gap-2">
                    <input v-model="achatForm.nom" placeholder="Nom"
                      class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-2.5 rounded-lg text-sm" />
                    <input v-model="achatForm.prenom" placeholder="Prénom"
                      class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-2.5 rounded-lg text-sm" />
                  </div>
                  <input v-model="achatForm.contact" placeholder="Contact"
                    class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-2.5 rounded-lg text-sm" />
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Quantité</label>
                    <input type="number" min="1" v-model="achatForm.qte" :max="voitureSelectionnee.nombre"
                      class="w-full border-2 border-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:outline-none p-2.5 rounded-lg text-sm" />
                  </div>
                  <div class="bg-indigo-50 dark:bg-indigo-950 p-3 rounded-xl text-center">
                    <span class="text-slate-500 text-sm">Total: </span>
                    <span class="text-xl font-bold text-indigo-600">
                      {{ (Number(voitureSelectionnee.prix) * (parseInt(achatForm.qte) || 0)).toLocaleString() }} Ar
                    </span>
                  </div>
                  <button @click="effectuerAchat" :disabled="!achatForm.nom || !achatForm.prenom || !achatForm.contact"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-md transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                    Valider l'achat
                  </button>
                </div>
              </div>

              <!-- Edit form card (when isEditing) -->
              <div v-if="isEditing" class="bg-slate-50 dark:bg-gray-800/50 p-5 rounded-2xl border border-slate-200 dark:border-gray-600 mt-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Modifier la voiture</h3>
                <div class="space-y-3">
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">ID Voiture</label>
                    <input v-model="form.idvoit" disabled
                      class="w-full bg-slate-50 border border-slate-200 dark:bg-gray-700 dark:border-gray-600 text-slate-950 dark:text-white p-2.5 rounded-lg text-sm" />
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Désignation</label>
                    <input v-model="form.Design" placeholder="Modèle"
                      class="w-full bg-slate-50 border border-slate-200 dark:bg-gray-700 dark:border-gray-600 text-slate-950 dark:text-white focus:border-indigo-500 focus:outline-none p-2.5 rounded-lg text-sm" />
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Prix (Ar)</label>
                      <input v-model="form.prix" type="number"
                        class="w-full bg-slate-50 border border-slate-200 dark:bg-gray-700 dark:border-gray-600 text-slate-950 dark:text-white focus:border-indigo-500 focus:outline-none p-2.5 rounded-lg text-sm" />
                    </div>
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Stock</label>
                      <input v-model="form.nombre" type="number"
                        class="w-full bg-slate-50 border border-slate-200 dark:bg-gray-700 dark:border-gray-600 text-slate-950 dark:text-white focus:border-indigo-500 focus:outline-none p-2.5 rounded-lg text-sm" />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Image</label>
                    <input type="file" accept="image/*" @change="handleImageUpload"
                      class="w-full bg-slate-50 border border-slate-200 dark:bg-gray-700 dark:border-gray-600 text-sm p-2 rounded-lg" />
                    <div v-if="imagePreview" class="mt-2 w-20 h-20">
                      <img :src="imagePreview" class="w-full h-full object-cover rounded-lg" />
                    </div>
                  </div>
                  <div class="flex gap-2 pt-2">
                    <button @click="enregistrerModification"
                      class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-sm text-sm">
                      Valider
                    </button>
                    <button @click="annulerEdition"
                      class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-2.5 px-4 rounded-xl transition-all text-sm">
                      Annuler
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoice modal -->
      <div v-if="showInvoice && invoiceData"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-start justify-center p-4 z-50 overflow-y-auto">
        <div
          class="invoice-sheet bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-3xl w-full my-8 border border-slate-200 dark:border-gray-600">
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
          <div class="mb-8 p-4 bg-slate-50 rounded-xl">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Client</h3>
            <p class="text-lg font-bold text-slate-900">{{ invoiceData.clientNom }}</p>
            <p class="text-slate-600">Code: {{ invoiceData.idcli }}</p>
            <p class="text-slate-600">Contact: {{ invoiceData.clientContact }}</p>
          </div>
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
                <td class="p-4 text-slate-900 font-medium">{{ invoiceData.designation }}</td>
                <td class="p-4 text-right text-slate-700">{{ Number(invoiceData.prixUnitaire).toLocaleString() }} Ar
                </td>
                <td class="p-4 text-center text-slate-700">{{ invoiceData.qte }}</td>
                <td class="p-4 text-right text-slate-900 font-semibold">{{
                  Number(invoiceData.totalGeneral).toLocaleString() }} Ar</td>
              </tr>
            </tbody>
          </table>
          <div class="flex justify-end">
            <div class="w-72">
              <div class="flex justify-between py-2 text-lg">
                <span class="text-slate-600">Sous-total:</span>
                <span class="font-semibold">{{ Number(invoiceData.totalGeneral).toLocaleString() }} Ar</span>
              </div>
              <div class="flex justify-between py-2 text-lg border-t border-slate-300">
                <span class="text-slate-600">Remise:</span>
                <span class="font-semibold">0 Ar</span>
              </div>
              <div class="flex justify-between py-3 text-2xl font-black border-t-2 border-slate-900 mt-2 pt-3">
                <span class="text-slate-900">Net à payer:</span>
                <span class="text-indigo-600">{{ Number(invoiceData.totalGeneral).toLocaleString() }} Ar</span>
              </div>
            </div>
          </div>
          <div class="mt-6 p-4 bg-indigo-50 rounded-xl text-center">
            <p class="text-sm text-slate-500">Arrêté par la présente facture à la somme de</p>
            <p class="text-lg font-bold text-indigo-700 capitalize">
              {{ nombreEnLettres(Number(invoiceData.totalGeneral)) }} ariary
            </p>
          </div>
          <div class="mt-6 pt-6 border-t border-slate-200 text-center text-sm text-slate-400">
            <p>Merci de votre confiance !</p>
            <p class="mt-1">Cette facture est générée automatiquement par le système GVV.</p>
          </div>
        </div>
      </div>

      <!-- Galerie de cartes -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="voiture in voitures" :key="voiture.idvoit"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-all duration-300">
          <!-- Image cliquable -->
          <div @click="ouvrirDetail(voiture)" class="cursor-pointer">
            <div class="relative h-48 bg-slate-200 overflow-hidden">
              <img :src="voiture.image ? `http://localhost/backend/uploads/${voiture.image}` : 'https://unsplash.com'"
                @error="e => e.target.src = 'https://unsplash.com'"
                class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                :alt="voiture.Design" />
            </div>
            <div class="p-4">
              <p class="text-sm text-slate-500 dark:text-gray-400 font-semibold">ID: {{ voiture.idvoit }}</p>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">{{ voiture.Design }}</h3>
              <div class="mt-3 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-slate-600 dark:text-gray-300">Prix:</span>
                  <span class="font-semibold text-indigo-600">{{ Number(voiture.prix).toLocaleString() }} Ar</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-slate-600">Stock:</span>
                  <span class="font-semibold" :class="voiture.nombre > 0 ? 'text-emerald-600' : 'text-red-600'">
                    {{ voiture.nombre }}
                  </span>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>

      <!-- Message vide -->
      <div v-if="voitures.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 text-center">
        <p class="text-xl text-slate-500 dark:text-gray-400">Aucune voiture trouvée. Ajoutez-en une pour commencer !</p>
      </div>

    </div>
  </div>
</template>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }

  body {
    background: white !important;
    margin: 0;
    padding: 0;
  }

  .fixed {
    position: static !important;
    background: none !important;
  }

  .invoice-sheet {
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 20px !important;
    margin: 0 !important;
    max-width: 100% !important;
  }

  .bg-white {
    background: white !important;
  }

  .shadow-2xl {
    box-shadow: none !important;
  }
}
</style>