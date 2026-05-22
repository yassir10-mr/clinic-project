<<<<<<< HEAD
import { createRouter, createWebHistory } from 'vue-router';

import LoginView from '@/views/LoginView.vue';
import AccueilView from '@/views/AccueilView.vue';

// Admin views
import DashboardView from '@/views/admin/DashboardView.vue';
import PatientsView from '@/views/admin/PatientsView.vue';
import MedecinsView from '@/views/admin/MedecinsView.vue';
import SecretairesView from '@/views/admin/SecretairesView.vue';
import InfirmiersView from '@/views/infirmier/InfirmiersView.vue';
import RendezVousView from '@/views/admin/RendezVousView.vue';
import ConsultationsView from '@/views/admin/ConsultationsView.vue';
import FacturesView from '@/views/admin/FacturesView.vue';
import MedicalRecordsView from '@/views/admin/MedicalRecordsView.vue';
import SettingsView from '@/views/admin/SettingsView.vue';

// Secretaire views
import SecretaireDashboard from '@/views/secretaire/DashboardView.vue';
import SecretairePatients from '@/views/secretaire/PatientsView.vue';
import SecretaireAppointments from '@/views/secretaire/AppointmentsView.vue';
import SecretaireInvoices from '@/views/secretaire/InvoicesView.vue';
import SecretaireDoctors from '@/views/secretaire/DoctorsView.vue';

const routes = [
  /*
  |--------------------------------------------------------------------------
  | Public Routes
  |--------------------------------------------------------------------------
  */

  {
    path: '/',
    name: 'Accueil',
    component: AccueilView,
    meta: { public: true }
  },

  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: { public: true }
  },

  /*
  |--------------------------------------------------------------------------
  | ADMIN ROUTES
  |--------------------------------------------------------------------------
  */

  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/patients',
    name: 'Patients',
    component: PatientsView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/medecins',
    name: 'Medecins',
    component: MedecinsView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/secretaires',
    name: 'Secretaires',
    component: SecretairesView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/infirmiers',
    name: 'Infirmiers',
    component: InfirmiersView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/rendez-vous',
    name: 'RendezVous',
    component: RendezVousView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/consultations',
    name: 'Consultations',
    component: ConsultationsView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/factures',
    name: 'Factures',
    component: FacturesView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/dossiers',
    name: 'MedicalRecords',
    component: MedicalRecordsView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/settings',
    name: 'Settings',
    component: SettingsView,
    meta: { requiresAuth: true, role: 'admin' }
  },

  /*
  |--------------------------------------------------------------------------
  | SECRETAIRE ROUTES
  |--------------------------------------------------------------------------
  */

  {
    path: '/secretaire/dashboard',
    name: 'SecretaireDashboard',
    component: SecretaireDashboard,
    meta: { requiresAuth: true, role: 'secretaire' }
  },

  {
    path: '/secretaire/patients',
    name: 'SecretairePatients',
    component: SecretairePatients,
    meta: { requiresAuth: true, role: 'secretaire' }
  },

  {
    path: '/secretaire/appointments',
    name: 'SecretaireAppointments',
    component: SecretaireAppointments,
    meta: { requiresAuth: true, role: 'secretaire' }
  },

  {
    path: '/secretaire/invoices',
    name: 'SecretaireInvoices',
    component: SecretaireInvoices,
    meta: { requiresAuth: true, role: 'secretaire' }
  },

  {
    path: '/secretaire/consultations',
    name: 'SecretaireConsultations',
    component: () => import('@/views/secretaire/ConsultationsView.vue'),
    meta: { requiresAuth: true, role: 'secretaire' }
  },

  {
    path: '/secretaire/doctors',
    name: 'SecretaireDoctors',
    component: SecretaireDoctors,
    meta: { requiresAuth: true, role: 'secretaire' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

/*
|--------------------------------------------------------------------------
| ROUTE GUARD
|--------------------------------------------------------------------------
*/

router.beforeEach((to, from, next) => {

  const isPublic = to.meta.public;

  // Public route
  if (isPublic) {
    next();
    return;
  }

  // Protected route
  if (to.meta.requiresAuth) {

    const requiredRole = to.meta.role;

    const token =
      localStorage.getItem(`${requiredRole}_token`) ||
      localStorage.getItem('token');

    if (!token) {
      next('/login');
      return;
    }

    const userRole = localStorage.getItem('user_role');

    // Wrong role
    if (requiredRole && userRole && userRole !== requiredRole) {

      if (userRole === 'admin') {
        next('/dashboard');
        return;
      }

      if (userRole === 'secretaire') {
        next('/secretaire/dashboard');
        return;
      }

      next('/login');
      return;
    }
  }

  // Redirect logged user away from login
  if (to.path === '/login') {

    const adminToken = localStorage.getItem('admin_token');
    const secretaireToken = localStorage.getItem('secretaire_token');
    const token = localStorage.getItem('token');

    const role = localStorage.getItem('user_role');

    if (adminToken || token) {

      if (role === 'admin') {
        next('/dashboard');
        return;
      }

      if (role === 'secretaire') {
        next('/secretaire/dashboard');
        return;
      }
    }

    if (secretaireToken) {
      next('/secretaire/dashboard');
      return;
    }
  }

  next();
});

=======
import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '@/views/LoginView.vue';
import AccueilView from '@/views/AccueilView.vue';
import DashboardView from '@/views/admin/DashboardView.vue';
import PatientsView from '@/views/admin/PatientsView.vue';
import MedecinsView from '@/views/admin/MedecinsView.vue';
import SecretairesView from '@/views/admin/SecretairesView.vue';
import InfirmiersView from '@/views/infirmier/InfirmiersView.vue';
import RendezVousView from '@/views/admin/RendezVousView.vue';
import ConsultationsView from '@/views/admin/ConsultationsView.vue';
import FacturesView from '@/views/admin/FacturesView.vue';
import MedicalRecordsView from '@/views/admin/MedicalRecordsView.vue';
import SettingsView from '@/views/admin/SettingsView.vue';

const routes = [
  // Page d'accueil publique (première page)
  { path: '/', name: 'Accueil', component: AccueilView, meta: { public: true } },
  
  // Login publique
  { path: '/login', name: 'Login', component: LoginView, meta: { public: true } },
  
  // Pages protégées (nécessitent login)
  { path: '/dashboard', name: 'Dashboard', component: DashboardView },
  { path: '/patients', name: 'Patients', component: PatientsView },
  { path: '/medecins', name: 'Medecins', component: MedecinsView },
  { path: '/secretaires', name: 'Secretaires', component: SecretairesView },
  { path: '/infirmiers', name: 'Infirmiers', component: InfirmiersView },
  { path: '/rendez-vous', name: 'RendezVous', component: RendezVousView },
  { path: '/consultations', name: 'Consultations', component: ConsultationsView },
  { path: '/factures', name: 'Factures', component: FacturesView },
  { path: '/dossiers', name: 'MedicalRecords', component: MedicalRecordsView },
  { path: '/settings', name: 'Settings', component: SettingsView },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Guard désactivé temporairement pour le développement
router.beforeEach((to, from, next) => {
  next(); 
});

>>>>>>> d4c4540 (feat: ajout de la page patient par Amine)
export default router;