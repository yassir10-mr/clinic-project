import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '@/views/LoginView.vue';
import AccueilView from '@/views/AccueilView.vue';

// Admin views
import DashboardView from '@/views/admin/DashboardView.vue';
import PatientsView from '@/views/admin/PatientsView.vue';
import MedecinsView from '@/views/admin/MedecinsView.vue';
import SecretairesView from '@/views/admin/SecretairesView.vue';
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

// Infirmier layout + views
import InfirmierLayout from '@/layouts/InfirmierLayout.vue';
import InfirmierDashboard from '@/views/infirmier/DashboardView.vue';
import InfirmierPatients from '@/views/infirmier/PatientsView.vue';
import InfirmierAppointments from '@/views/infirmier/AppointmentsView.vue';
import InfirmierMedicalRecords from '@/views/infirmier/MedicalRecordsView.vue';

// --- TON IMPORT PATIENT (AMINE) ---
import DashboardPatient from '@/views/patient/Patient.vue';

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
  /*
  |--------------------------------------------------------------------------
  | INFIRMIER ROUTES (LAYOUT DÉDIÉ)
  |--------------------------------------------------------------------------
  */
  {
    path: '/infirmier',
    component: InfirmierLayout,
    meta: { requiresAuth: true, role: 'infirmier' },
    children: [
      { path: 'dashboard', name: 'InfirmierDashboard', component: InfirmierDashboard },
      { path: 'patients', name: 'InfirmierPatients', component: InfirmierPatients },
      { path: 'appointments', name: 'InfirmierAppointments', component: InfirmierAppointments },
      { path: 'medical-records', name: 'InfirmierMedicalRecords', component: InfirmierMedicalRecords },
    ]
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
    component: () => import('@/views/secretaire/ConsultationsView.vue'),
    meta: { requiresAuth: true, role: 'secretaire' }
  },
  {
    path: '/secretaire/doctors',
    name: 'SecretaireDoctors',
    component: SecretaireDoctors,
    meta: { requiresAuth: true, role: 'secretaire' }
  },

  /*
  |--------------------------------------------------------------------------
  | TON ESPACE PATIENT (AMINE)
  |--------------------------------------------------------------------------
  */
  {
    path: '/mon-espace-patient',
    name: 'EspacePatient',
    component: DashboardPatient,
    meta: { public: true } // DÉVERROUILLÉ POUR TOI AMINE !
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

  if (isPublic) {
    next();
    return;
  }

  if (to.meta.requiresAuth) {
    const requiredRole = to.meta.role;
    const token =
      localStorage.getItem(`${requiredRole}_token`) ||
      localStorage.getItem('infirmier_token') ||
      localStorage.getItem('token');

    if (!token) {
      next('/login');
      return;
    }

    const userRole = localStorage.getItem('user_role');

    if (requiredRole && userRole && userRole !== requiredRole) {
      if (userRole === 'admin') {
        next('/dashboard');
        return;
      }
      if (userRole === 'secretaire') {
        next('/secretaire/dashboard');
        return;
      }
      if (userRole === 'infirmier') {
        next('/infirmier/dashboard');
        return;
      }
      next('/login');
      return;
    }
  }

  if (to.path === '/login') {
    const adminToken = localStorage.getItem('admin_token');
    const secretaireToken = localStorage.getItem('secretaire_token');
    const infirmierToken = localStorage.getItem('infirmier_token');
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
      if (role === 'infirmier') {
        next('/infirmier/dashboard');
        return;
      }
    }

    if (secretaireToken) {
      next('/secretaire/dashboard');
      return;
    }

    if (infirmierToken) {
      next('/infirmier/dashboard');
      return;
    }
  }

  next();
});

export default router;