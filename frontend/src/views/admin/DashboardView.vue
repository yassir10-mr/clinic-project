<template>
  <div class="space-y-6 p-6 bg-slate-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Tableau de Bord</h1>
        <p class="text-slate-500 mt-1">Ravi de vous revoir, {{ currentUserName }}</p>
      </div>
      <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-xs font-bold uppercase">
        Rôle : {{ currentUserRole }}
      </span>
    </div>

    <!-- Stats Cards (Masqué pour les patients) -->
    <div v-if="currentUserRole !== 'patient'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Patients Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase">Total Patients</p>
          <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.totalPatients }}</p>
          <span class="text-xs font-semibold text-emerald-600">↑ 12% depuis le mois dernier</span>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
      </div>

      <!-- Appointments Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase">Rendez-vous Aujourd'hui</p>
          <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.todayAppointments }}</p>
          <span class="text-xs font-semibold text-emerald-600">↑ 8% aujourd'hui</span>
        </div>
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </div>
      </div>

      <!-- Revenue Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase">Revenu Mensuel</p>
          <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.monthlyRevenue }} DH</p>
          <span class="text-xs font-semibold text-emerald-600">↑ 15% ce mois</span>
        </div>
        <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
      </div>

      <!-- Doctors Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase">Médecins Actifs</p>
          <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.activeDoctors }}</p>
          <span class="text-xs text-slate-400">Personnel de garde</span>
        </div>
        <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
        </div>
      </div>
    </div>

    <!-- Charts (Affiche uniquement pour l'admin ou le médecin) -->
    <div v-if="currentUserRole === 'admin' || currentUserRole === 'doctor'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Graphique RDV (Stylisé en pur CSS / Tailwind) -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Rendez-vous de la semaine</h3>
        <div class="flex items-end justify-between h-48 pt-4 border-b border-slate-100">
          <div v-for="bar in mockAppointmentsPerDay" :key="bar.date" class="flex flex-col items-center flex-1">
            <!-- La barre du graphe -->
            <div 
              class="w-8 bg-blue-500 rounded-t-lg transition-all duration-500 hover:bg-blue-600 cursor-pointer"
              :style="{ height: (bar.appointments * 15) + 'px' }"
            ></div>
            <span class="text-xs text-slate-400 mt-2">{{ bar.date }}</span>
          </div>
        </div>
      </div>

      <!-- Graphique Revenus (Stylisé en pur CSS) -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Évolution des Revenus (DH)</h3>
        <div class="flex items-end justify-between h-48 pt-4 border-b border-slate-100">
          <div v-for="line in mockRevenueData" :key="line.month" class="flex flex-col items-center flex-1">
            <!-- Une barre esthétique pour simuler la ligne -->
            <div 
              class="w-6 bg-emerald-400 rounded-t-md transition-all duration-500 hover:bg-emerald-500"
              :style="{ height: (line.revenue / 700) + 'px' }"
            ></div>
            <span class="text-xs text-slate-400 mt-2">{{ line.month }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Appointments -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-slate-900">Aujourd'hui : Consultations & Visites</h3>
        <button class="text-xs font-bold text-blue-600 hover:underline">Voir tout</button>
      </div>

      <div class="space-y-4">
        <div v-if="todayAppointments.length === 0" class="text-center py-12 text-slate-400">
          <p>Aucune consultation prévue pour aujourd'hui</p>
        </div>
        <div 
          v-else 
          v-for="app in todayAppointments" 
          :key="app.id"
          class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl transition duration-150"
        >
          <div class="flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
              <h4 class="font-semibold text-slate-900">{{ app.patientName }}</h4>
              <p class="text-xs text-slate-500">{{ app.time }} • Dr. {{ app.doctorName }}</p>
              <p class="text-xs font-bold text-blue-600 mt-1">{{ app.type }}</p>
            </div>
          </div>
          <span 
            class="px-2.5 py-1 rounded-full text-xs font-bold border"
            :class="getStatusColor(app.status)"
          >
            {{ app.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Récupérer le nom et le rôle de l'utilisateur connecté
const currentUserName = ref(localStorage.getItem('admin_user') ? JSON.parse(localStorage.getItem('admin_user')).nom : 'Marie Dupont');
const currentUserRole = ref(localStorage.getItem('user_role') || 'admin');

// Données statiques de démo (Comme prévu sur ton Figma !)
const stats = ref({
  totalPatients: 1247,
  todayAppointments: 18,
  monthlyRevenue: 85420,
  activeDoctors: 12
});

const mockAppointmentsPerDay = [
  { date: 'Lun', appointments: 5 },
  { date: 'Mar', appointments: 8 },
  { date: 'Mer', appointments: 12 },
  { date: 'Jeu', appointments: 10 },
  { date: 'Ven', appointments: 9 },
  { date: 'Sam', appointments: 4 }
];

const mockRevenueData = [
  { month: 'Jan', revenue: 65000 },
  { month: 'Feb', revenue: 72000 },
  { month: 'Mar', revenue: 85420 }
];

const todayAppointments = ref([
  { id: 1, patientName: 'John Smith', time: '09:00', doctorName: 'Alaoui', type: 'Consultation Générale', status: 'confirmed' },
  { id: 2, patientName: 'Emily Davis', time: '10:30', doctorName: 'Mansouri', type: 'Pédiatrie', status: 'confirmed' },
  { id: 3, patientName: 'Michael Brown', time: '14:00', doctorName: 'Alaoui', type: 'Contrôle', status: 'pending' }
]);

// Gérer la couleur du badge de statut
const getStatusColor = (status) => {
  switch (status) {
    case 'confirmed':
      return 'bg-emerald-50 text-emerald-700 border-emerald-100';
    case 'pending':
      return 'bg-amber-50 text-amber-700 border-amber-100';
    case 'cancelled':
      return 'bg-rose-50 text-rose-700 border-rose-100';
    default:
      return 'bg-slate-50 text-slate-700 border-slate-100';
  }
};
</script>