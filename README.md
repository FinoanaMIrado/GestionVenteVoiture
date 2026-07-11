# Vue 3 + Vite

This template should help get you started developing with Vue 3 in Vite. The template uses Vue 3 `<script setup>` SFCs, check out the [script setup docs](https://v3.vuejs.org/api/sfc-script-setup.html#sfc-script-setup) to learn more.

Learn more about IDE Support for Vue in the [Vue Docs Scaling up Guide](https://vuejs.org/guide/scaling-up/tooling.html#ide-support).
# 🚗 Varotra Fiara

## Présentation du projet

**Varotra Fiara** est une application web de gestion de vente de voitures développée avec **Vue.js**, **PHP** et **MySQL**. Elle permet de gérer les clients, les véhicules disponibles, les achats, ainsi que la génération de factures et des statistiques de ventes.

L'application facilite la gestion commerciale d'un concessionnaire automobile en centralisant les informations relatives aux véhicules, aux clients et aux transactions.

---
## Objectifs du projet

- Gérer les informations des clients
- Gérer le catalogue des voitures disponibles
- Enregistrer les achats de véhicules
- Mettre à jour automatiquement le stock après chaque vente
- Générer des factures en format PDF
- Consulter l'historique des achats
- Produire des statistiques sur les recettes mensuelles

---
## Technologies utilisées

### Frontend

- **Vue.js** : Développement de l'interface utilisateur
- **Tailwind CSS** : Création d'une interface moderne et responsive
- **Heroicons** : Bibliothèque d'icônes SVG

### Backend

- **PHP** : Développement de l'API et de la logique métier

### Base de données

- **MySQL** : Gestion et stockage des données

### Outils

- **Visual Studio Code**
- **XAMPP / WAMP**
- **Git & GitHub**

---
## Base de données

L'application manipule les tables suivantes :

### CLIENT

| Champ | Type |
|-------|------|
| idcli | String |
| nom | String |
| contact | String |

### VOITURE

| Champ | Type |
|-------|------|
| idvoit | String |
| design | String |
| prix | Integer |
| nombre | Integer |

### ACHAT

| Champ | Type |
|-------|------|
| numAchat | String |
| idcli | String |
| idvoit | String |
| date | Date |
| qte | Integer |

> **Remarque :**
>
> À chaque enregistrement d'un achat, la quantité disponible (`nombre`) de la voiture est automatiquement décrémentée en fonction de la quantité achetée.

---

## Fonctionnalités principales

### 👤 Gestion des clients

- Ajouter un client
- Modifier un client
- Supprimer un client
- Consulter la liste des clients

### 🚗 Gestion des voitures

- Ajouter une voiture
- Modifier les informations d'une voiture
- Supprimer une voiture
- Consulter les véhicules disponibles
- Gestion automatique du stock

### 🛒 Gestion des achats

- Enregistrer un achat
- Mise à jour automatique du stock
- Historique des achats
- Consultation de toutes les ventes
### 🔍 Recherche

- Recherche d'une voiture par :
  - Identifiant
  - Désignation (avec l'opérateur **LIKE**)

### 🧾 Facturation

- Génération automatique de la facture après un achat
- Affichage du détail :
  - Informations du client
  - Véhicule acheté
  - Quantité
  - Prix unitaire
  - Montant total

### 📄 Génération PDF

- Export de la facture au format PDF
- Impression de la facture

### 📅 Recherche par période

- Recherche des achats effectués entre deux dates

### 📊 Tableau de bord

Affichage des recettes totales des **6 derniers mois** :

- Total des ventes par mois
- Suivi des performances commerciales

---
# Base de données

La base de données **GESTIONVENTEVOITURE** est composée de cinq tables principales permettant la gestion des clients, des voitures, des achats, des factures et des utilisateurs.

## Tables

### Client
Contient les informations des clients.

| Champ | Type | Description |
|-------|------|-------------|
| idcli | VARCHAR(255) (PK) | Identifiant du client |
| nom | VARCHAR(255) | Nom du client |
| contact | VARCHAR(255) | Contact du client |

---

### Voiture
Contient les informations des voitures disponibles.

| Champ | Type | Description |
|-------|------|-------------|
| idvoit | VARCHAR(255) (PK) | Identifiant de la voiture |
| Design | VARCHAR(255) | Désignation de la voiture |
| prix | INT | Prix unitaire |
| nombre | INT | Quantité disponible |

---

### Achat
Enregistre les achats de voitures effectués par les clients.

| Champ | Type | Description |
|-------|------|-------------|
| numAchat | VARCHAR(20) (PK) | Identifiant de l'achat |
| idcli | VARCHAR(20) (FK) | Client concerné |
| idvoit | VARCHAR(20) (FK) | Voiture achetée |
| date | DATE | Date de l'achat |
| qte | INT | Quantité achetée |

---

### Facture
Stocke les factures générées après un achat.

| Champ | Type | Description |
|-------|------|-------------|
| NumFact | VARCHAR(20) (PK) | Numéro de facture |
| DateFact | DATE | Date de la facture |
| NumAchat | VARCHAR(20) (FK) | Achat associé |

---

### Utilisateur
Permet l'authentification dans l'application.

| Champ | Type | Description |
|-------|------|-------------|
| NumeroUtilisateur | INT (PK) | Identifiant de l'utilisateur |
| nomUtilisateur | VARCHAR(250) | Nom d'utilisateur |
| motDePasse | VARCHAR(500) | Mot de passe |

## Relations entre les tables

```text
Client
   │
   └── Achat ─── Voiture
          │
          └── Facture
```

## Clés primaires

- Client : `idcli`
- Voiture : `idvoit`
- Achat : `numAchat`
- Facture : `NumFact`
- Utilisateur : `NumeroUtilisateur`

## Clés étrangères

- `Achat.idcli` → `Client.idcli`
- `Achat.idvoit` → `Voiture.idvoit`
- `Facture.NumAchat` → `Achat.numAchat`
## FINOANA MIRADO X ANT-SA ANDRIAMISAINA
