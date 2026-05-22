<template>
  <div class="p-8 bg-slate-50 min-h-screen">
    <!-- En-tête -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900">👋 Bienvenue dans votre Espace Patient</h1>
      <p class="text-slate-500">Consultez vos visites, vos factures et prenez vos rendez-vous en ligne.</p>
    </div>

    <!-- Grille principale à deux colonnes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- COLONNE 1 & 2 : HISTORIQUE MEDICAL & FACTURES -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Section Consultations -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
          <h2 class="text-xl font-bold text-slate-900 mb-4">🩺 Mon Historique Médical</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Médecin</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Diagnostic / Traitement</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="consultations.length === 0">
                  <td colspan="3" class="px-4 py-6 text-center text-slate-400 text-sm">Aucune consultation enregistrée.</td>
                </tr>
                <tr v-for="c in consultations" :key="c.id_consultation" class="hover:bg-slate-50">
                  <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ c.date }}</td>
                  <td class="px-4 py-4 text-sm text-slate-600">Dr. {{ c.medecin_nom }}</td>
                  <td class="px-4 py-4 text-sm text-slate-600">
                    <div class="font-bold text-slate-800">{{ c.diagnostic }}</div>
                    <div class="text-xs text-slate-400">Traitement : {{ c.traitement }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Section Factures -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
          <h2 class="text-xl font-bold text-slate-900 mb-4">💳 Mes Factures</h2>
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Montant</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Statut</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="factures.length === 0">
                <td colspan="3" class="px-4 py-6 text-center text-slate-400 text-sm">Aucune facture disponible.</td>
              </tr>
              <tr v-for="f in factures" :key="f.id_facture" class="hover:bg-slate-50">
                <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ f.date }}</td>
                <td class="px-4 py-4 text-sm font-bold text-slate-900">{{ f.montant_total }} DH</td>
                <td class="px-4 py-4 text-sm">
                  <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700">
                    {{ f.statut_paiement }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- COLONNE 3 : PRENDRE RENDEZ-VOUS -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm h-fit">
        <h2 class="text-xl font-bold text-slate-900 mb-4">📅 Prendre RDV en ligne</h2>
        <form @submit.prevent="prendreRendezVous" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Date souhaitée</label>
            <input type="date" v-model="formRDV.date_rdv" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500" required>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Heure</label>
            <input type="time" v-model="formRDV.heure" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500" required>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Motif de la visite</label>
            <textarea v-model="formRDV.motif" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500" rows="3" placeholder="Ex: Consultation de contrôle, fièvre..." required></textarea>
          </div>
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-lg transition duration-150 cursor-pointer">
            Confirmer le Rendez-vous
          </button>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const consultations = ref([]);
const factures = ref([]);
const loading = ref(true);

// On utilise l'ID du premier patient de test (Jean Dupont)
const idPatient = 1; 

const formRDV = ref({
  id_patient: idPatient,
  id_medecin: 1, // On l'envoie chez le médecin ID 1 (Dr. House)
  date_rdv: '',
  heure: '',
  motif: ''
});

// Charger les données de l'API
const loadData = async () => {
  try {
    const resCons = await fetch(`http://127.0.0.1:8000/api/patient/${idPatient}/consultations`);
    const dataCons = await resCons.json();
    if (dataCons.success) consultations.value = dataCons.consultations;

    const resFact = await fetch(`http://127.0.0.1:8000/api/patient/${idPatient}/factures`);
    const dataFact = await resFact.json();
    if (dataFact.success) factures.value = dataFact.factures;

  } catch (error) {
    console.error("Erreur lors de la récupération des données", error);
  } finally {
    loading.value = false;
  }
};

// Prendre rendez-vous
const prendreRendezVous = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/patient/prendre-rdv', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formRDV.value)
    });
    const data = await response.json();
    if (data.success) {
      alert("Votre rendez-vous a bien été enregistré !");
      formRDV.value.date_rdv = '';
      formRDV.value.heure = '';
      formRDV.value.motif = '';
    }
  } catch (error) {
    alert("Erreur lors de la prise de rendez-vous.");
  }
};

onMounted(() => {
  loadData();
});
</script>