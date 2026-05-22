<template>
  <div class="appointments-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Appointments</h1>
        <p class="page-subtitle">Schedule and manage appointments</p>
      </div>
      <div class="header-actions">
        <button class="toggle-btn" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
          <i class="fas fa-list"></i>
        </button>
        <button class="toggle-btn" :class="{ active: viewMode === 'calendar' }" @click="viewMode = 'calendar'">
          <i class="fas fa-calendar"></i>
        </button>
        <button class="new-btn" @click="showNewModal = true">
          <i class="fas fa-plus"></i>
          New Appointment
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total</p>
          <p class="stat-value">{{ appointmentStats.total }}</p>
        </div>
        <div class="stat-icon total">
          <i class="fas fa-calendar"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Confirmed</p>
          <p class="stat-value" style="color: #059669;">{{ appointmentStats.confirmed }}</p>
        </div>
        <div class="stat-icon confirmed">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Pending</p>
          <p class="stat-value" style="color: #d97706;">{{ appointmentStats.pending }}</p>
        </div>
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Completed</p>
          <p class="stat-value" style="color: #3b82f6;">{{ appointmentStats.completed }}</p>
        </div>
        <div class="stat-icon completed">
          <i class="fas fa-calendar-check"></i>
        </div>
      </div>
    </div>

    <!-- Appointments List -->
    <div class="appointments-section">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading appointments...</p>
      </div>

      <div v-else-if="Object.keys(groupedAppointments).length === 0" class="empty-state">
        <i class="fas fa-calendar-day"></i>
        <p>No appointments found.</p>
      </div>

      <div v-else v-for="(dateGroup, date) in groupedAppointments" :key="date" class="date-group">
        <h3 class="date-header">{{ formatDateHeader(date) }}</h3>

        <div class="appointments-list">
          <div v-for="rdv in dateGroup" :key="rdv.id_rdv" class="appointment-card">
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
            <div class="appointment-actions">
              <span :class="['status-badge', getStatusClass(rdv.statut)]">
                {{ formatStatus(rdv.statut) }}
              </span>
              <button class="view-btn" @click="viewAppointment(rdv)">
                View
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getInfirmierAppointments } from '@/services/api.js';

const appointments = ref([]);
const loading = ref(true);
const viewMode = ref('list');
const showNewModal = ref(false);

const appointmentStats = computed(() => {
  const total = appointments.value.length;
  const confirmed = appointments.value.filter(a => a.statut === 'confirme').length;
  const pending = appointments.value.filter(a => a.statut === 'en attente').length;
  const completed = appointments.value.filter(a => a.statut === 'termine').length;
  return { total, confirmed, pending, completed };
});

const groupedAppointments = computed(() => {
  const groups = {};
  appointments.value.forEach(rdv => {
    const date = rdv.date_rdv;
    if (!groups[date]) groups[date] = [];
    groups[date].push(rdv);
  });
  // Sort dates descending
  const sortedKeys = Object.keys(groups).sort((a, b) => new Date(b) - new Date(a));
  const sorted = {};
  sortedKeys.forEach(key => {
    sorted[key] = groups[key];
  });
  return sorted;
});

const formatDateHeader = (dateStr) => {
  const date = new Date(dateStr);
  const today = new Date();
  const isToday = date.toDateString() === today.toDateString();

  if (isToday) return 'Today';

  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const formatStatus = (status) => {
  if (!status) return 'pending';
  return status;
};

const getStatusClass = (status) => {
  if (!status) return 'pending';
  const s = status.toLowerCase();
  if (s.includes('confirme')) return 'confirmed';
  if (s.includes('attente')) return 'pending';
  if (s.includes('annule')) return 'cancelled';
  if (s.includes('termine')) return 'completed';
  return 'pending';
};

const viewAppointment = (rdv) => {
  alert(`View appointment for ${rdv.patient_prenom} ${rdv.patient_nom}`);
};

const fetchAppointments = async () => {
  loading.value = true;
  try {
    const res = await getInfirmierAppointments();
    if (res.data.success) {
      appointments.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching appointments:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAppointments();
});
</script>

<style scoped>
.appointments-page {
  width: 100%;
  max-width: none;
  box-sizing: border-box;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.toggle-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.toggle-btn:hover,
.toggle-btn.active {
  background: #1a1a2e;
  color: white;
  border-color: #1a1a2e;
}

.new-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3b8d99;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.new-btn:hover {
  background: #2c6e7a;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  margin: 0 0 8px 0;
  font-weight: 500;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
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

.stat-icon.total {
  background: #effafd;
  color: #3b8d99;
}

.stat-icon.confirmed {
  background: #f0fdf4;
  color: #059669;
}

.stat-icon.pending {
  background: #fef3c7;
  color: #d97706;
}

.stat-icon.completed {
  background: #eff6ff;
  color: #3b82f6;
}

/* Date Groups */
.appointments-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.date-group {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
}

.date-header {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 16px 0;
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
  font-size: 13px;
  color: #64748b;
  margin: 0 0 4px 0;
}

.appointment-type {
  font-size: 12px;
  color: #94a3b8;
  margin: 0;
}

.appointment-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: lowercase;
}

.status-badge.confirmed {
  background: #d1fae5;
  color: #059669;
}

.status-badge.pending {
  background: #fef3c7;
  color: #d97706;
}

.status-badge.cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.status-badge.completed {
  background: #e0e7ff;
  color: #4f46e5;
}

.view-btn {
  padding: 6px 14px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.view-btn:hover {
  border-color: #3b8d99;
  color: #3b8d99;
}

/* Loading & Empty */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px;
  color: #94a3b8;
  gap: 12px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
}

.loading-state i {
  font-size: 24px;
  color: #3b8d99;
}

.empty-state i {
  font-size: 32px;
  color: #cbd5e1;
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

  .page-header {
    flex-direction: column;
    gap: 16px;
  }
}
</style>
