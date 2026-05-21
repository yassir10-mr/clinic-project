<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <!-- En-tête (Header) style Vercel/Linear -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Consultations du jour</h1>
        <p class="text-sm text-slate-500">Gérer et saisir les constantes des patients d'aujourd'hui</p>
      </div>
      <!-- Petit badge d'identité infirmier -->
      <div class="bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-xs font-semibold">
        🩺 Service Infirmier Actif
      </div>
    </div>

    <!-- Barre de recherche et filtres de ta page -->
    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm mb-6 flex gap-4">
      <div class="relative flex-1">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">🔍</span>
        <input type="text" class="pl-10 pr-4 py-2 w-full border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500" placeholder="Rechercher un patient...">
      </div>
      <select class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-600 focus:outline-none">
        <option>Tous les statuts</option>
        <option>En attente</option>
        <option>Confirmé</option>
      </select>
    </div>

    <!-- Le Tableau style Haute Technologie -->
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Heure</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Patient Name</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Motif de visite</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
            <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          <!-- Si la liste est vide -->
          <tr v-if="consultations.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
              Aucune consultation prévue aujourd'hui.
            </td>
          </tr>
          <!-- Lignes de tes patients -->
          <tr v-for="rdv in consultations" :key="rdv.id_rdv" class="hover:bg-slate-50/80 transition duration-150">
            <td class="px-6 py-4 text-sm font-bold text-slate-800">{{ rdv.heure }}</td>
            <td class="px-6 py-4">
              <div class="text-sm font-semibold text-slate-900">{{ rdv.nom }} {{ rdv.prenom }}</div>
              <div class="text-xs text-slate-400">ID: {{ rdv.id_patient }}</div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600">{{ rdv.motif }}</td>
            <td class="px-6 py-4">
              <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                {{ rdv.statut }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <button 
                @click="ouvrirModal(rdv)"
                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm cursor-pointer transition duration-150">
                Saisir Constantes
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- LA FENÊTRE MODALE STYLE "CLINIQUE LUXE" -->
    <div v-if="afficherModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex justify-center items-center z-50">
      <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg border border-slate-100 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center gap-3 mb-4">
          <span class="p-2 bg-blue-50 text-blue-600 rounded-lg text-lg">🩺</span>
          <h2 class="text-xl font-bold text-slate-900">Observations Médicales</h2>
        </div>
        
        <p class="mb-5 text-sm text-slate-500 border-b border-slate-100 pb-3">
          Saisie pour : <strong class="text-slate-800">{{ patientSelectionne?.nom }} {{ patientSelectionne?.prenom }}</strong>
        </p>

        <!-- Formulaire épuré -->
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tension Artérielle</label>
            <input type="text" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500" placeholder="ex: 12/8">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Symptômes & Constantes</label>
            <textarea class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-500" rows="4" placeholder="Fièvre, rythme cardiaque, observations..."></textarea>
          </div>
        </div>

        <!-- Boutons -->
        <div class="mt-6 flex justify-end gap-3">
          <button @click="fermerModal" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-50 cursor-pointer">
            Annuler
          </button>
          <button @click="sauvegarder" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg cursor-pointer transition">
            Enregistrer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const consultations = ref([]);
const loading = ref(true);
const afficherModal = ref(false);
const patientSelectionne = ref(null);

const fetchConsultations = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/infirmier/consultations');
    const data = await response.json();
    if (data.success) {
      consultations.value = data.consultations;
    }
  } catch (error) {
    console.error("Erreur :", error);
  } finally {
    loading.value = false;
  }
};

const ouvrirModal = (patient) => {
  patientSelectionne.value = patient;
  afficherModal.value = true;
};

const fermerModal = () => {
  afficherModal.value = false;
  patientSelectionne.value = null;
};

const sauvegarder = () => {
  alert("Observations enregistrées pour " + patientSelectionne.value.nom);
  fermerModal();
};

onMounted(() => {
  fetchConsultations();
});
</script>