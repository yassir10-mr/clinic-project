import { createRouter, createWebHistory } from 'vue-router';
import AccueilView from '@/views/AccueilView.vue';
import DashboardView from '@/views/admin/DashboardView.vue';
import PatientsView from '@/views/admin/PatientsView.vue';
import MedecinsView from '@/views/admin/MedecinsView.vue';
import SecretairesView from '@/views/admin/SecretairesView.vue';
import InfirmiersView from '@/views/admin/InfirmiersView.vue';
import RendezVousView from '@/views/admin/RendezVousView.vue';
import ConsultationsView from '@/views/admin/ConsultationsView.vue';
import FacturesView from '@/views/admin/FacturesView.vue';

// Ton import Patient !
import DashboardPatient from '@/views/patient/DashboardPatient.vue';

const routes = [
  { path: '/', name: 'Accueil', component: AccueilView },
  { path: '/dashboard', name: 'Dashboard', component: DashboardView },
  { path: '/patients', name: 'Patients', component: PatientsView },
  { path: '/medecins', name: 'Medecins', component: MedecinsView },
  { path: '/secretaires', name: 'Secretaires', component: SecretairesView },
  { path: '/infirmiers', name: 'Infirmiers', component: InfirmiersView },
  { path: '/rendez-vous', name: 'RendezVous', component: RendezVousView },
  { path: '/consultations', name: 'Consultations', component: ConsultationsView },
  { path: '/factures', name: 'Factures', component: FacturesView },

  // Ta route Patient !
  { path: '/mon-espace-patient', name: 'EspacePatient', component: DashboardPatient },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;