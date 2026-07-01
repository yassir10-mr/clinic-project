<template>
  <div class="dashboard-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ userFirstName }}</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total Patients</p>
          <p class="stat-value">{{ stats.total_patients }}</p>
          <p class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 12% from last month
          </p>
        </div>
        <div class="stat-icon patients">
          <i class="fas fa-users"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Today's Appointments</p>
          <p class="stat-value">{{ stats.today_appointments }}</p>
          <p class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 8% from last month
          </p>
        </div>
        <div class="stat-icon appointments">
          <i class="fas fa-calendar-check"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Monthly Revenue</p>
          <p class="stat-value">{{ formatNumber(stats.monthly_revenue) }} DH</p>
          <p class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 15% from last month
          </p>
        </div>
        <div class="stat-icon revenue">
          <i class="fas fa-dollar-sign"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Active Doctors</p>
          <p class="stat-value">{{ stats.active_doctors }}</p>
          <p class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 2 new this month
          </p>
        </div>
        <div class="stat-icon doctors">
          <i class="fas fa-user-md"></i>
        </div>
      </div>
    </div>

    <!-- Today's Appointments Section -->
    <div class="appointments-section">
      <div class="section-header">
        <h2 class="section-title">Today's Appointments</h2>
        <router-link to="/infirmier/appointments" class="view-all-btn">
          View All
        </router-link>
      </div>

      <div class="appointments-list">
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i> Loading appointments...
        </div>

        <div v-else-if="todayAppointments.length === 0" class="empty-state">
          <i class="fas fa-calendar-day"></i>
          <p>No appointments scheduled for today.</p>
        </div>

        <div v-else v-for="rdv in todayAppointments.slice(0, 4)" :key="rdv.id_rdv" class="appointment-card">
          <div class="appointment-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="appointment-info">
            <p class="appointment-patient">{{ rdv.patient_prenom }} {{ rdv.patient_nom }}</p>
            <p class="appointment-details">
              {{ rdv.heure }} &bull; Dr. {{ rdv.medecin_prenom }} {{ rdv.medecin_nom }}
            </p>
            <p class="appointment-type">{{ rdv.motif }}</p>
          </div>
          <div class="appointment-status">
            <span :class="['status-badge', rdv.statut]">
              {{ formatStatus(rdv.statut) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getInfirmierDashboard, getInfirmierConsultations } from '@/services/api.js';

const stats = ref({
  total_patients: 0,
  today_appointments: 0,
  monthly_revenue: 0,
  active_doctors: 0
});

const todayAppointments = ref([]);
const loading = ref(true);

const userData = computed(() => {
  const user = localStorage.getItem('infirmier_user');
  if (user) {
    try {
      return JSON.parse(user);
    } catch (e) {
      return { prenom: 'Raed', nom: 'El Bouazzati' };
    }
  }
  return { prenom: 'Raed', nom: 'El Bouazzati' };
});

const userFirstName = computed(() => userData.value.prenom || 'Nurse');

const formatNumber = (num) => {
  if (!num) return '0';
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
};

const formatStatus = (status) => {
  const map = {
    'en attente': 'pending',
    'confirme': 'confirmed',
    'annule': 'cancelled',
    'termine': 'completed'
  };
  return status || 'pending';
};

const fetchData = async () => {
  loading.value = true;
  try {
    const [statsRes, consultationsRes] = await Promise.all([
      getInfirmierDashboard(),
      getInfirmierConsultations()
    ]);

    if (statsRes.data.success) {
      stats.value = statsRes.data.stats;
    }

    if (consultationsRes.data.success) {
      todayAppointments.value = consultationsRes.data.consultations;
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.dashboard-page {
  width: 100%;
  max-width: none;
  box-sizing: border-box;
}

/* Header */
.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #475569;
  margin: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 13px;
  color: #475569;
  margin: 0 0 8px 0;
  font-weight: 500;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 8px 0;
}

.stat-change {
  font-size: 12px;
  font-weight: 500;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 4px;
}

.stat-change.positive {
  color: #059669;
}

.stat-change i {
  font-size: 10px;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.stat-icon.patients {
  background: #effafd;
  color: #3b8d99;
}

.stat-icon.appointments {
  background: #f0fdf4;
  color: #059669;
}

.stat-icon.revenue {
  background: #ecfdf5;
  color: #10b981;
}

.stat-icon.doctors {
  background: #f5f3ff;
  color: #8b5cf6;
}

/* Appointments Section */
.appointments-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

.view-all-btn {
  padding: 8px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
  text-decoration: none;
  background: white;
  transition: all 0.2s;
}

.view-all-btn:hover {
  border-color: #3b8d99;
  color: #3b8d99;
}

/* Appointment Cards */
.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.appointment-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 10px;
  transition: background 0.2s;
}

.appointment-card:hover {
  background: #f1f5f9;
}

.appointment-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #effafd;
  color: #3b8d99;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}

.appointment-info {
  flex: 1;
  min-width: 0;
}

.appointment-patient {
  font-size: 14px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 4px 0;
}

.appointment-details {
  font-size: 12px;
  color: #475569;
  margin: 0 0 4px 0;
}

.appointment-type {
  font-size: 12px;
  color: #475569;
  margin: 0;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: lowercase;
}

.status-badge.confirme,
.status-badge.confirmed {
  background: #d1fae5;
  color: #059669;
}

.status-badge.en\ attente,
.status-badge.pending {
  background: #fef3c7;
  color: #d97706;
}

.status-badge.annule,
.status-badge.cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.status-badge.termine,
.status-badge.completed {
  background: #e0e7ff;
  color: #4f46e5;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  color: #475569;
  gap: 12px;
}

.loading-state i {
  font-size: 24px;
  color: #3b8d99;
}

.empty-state i {
  font-size: 32px;
  color: #cbd5e1;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
