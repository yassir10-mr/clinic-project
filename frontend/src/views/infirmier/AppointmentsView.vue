<template>
  <div class="appointments-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Appointments</h1>
        <p class="page-subtitle">Schedule and manage appointments</p>
      </div>
      <div class="header-actions">
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
          <div v-for="rdv in dateGroup" :key="rdv.id_rdv" :class="['appointment-card', rdv.statut === 'terminé' || rdv.statut === 'termine' ? 'completed' : '']">
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

    <!-- Toast Notification -->
    <Transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
        <span>{{ toast.message }}</span>
      </div>
    </Transition>

    <!-- New Appointment Modal -->
    <Transition name="modal">
      <div v-if="showNewModal" class="modal-overlay" @click.self="showNewModal = false">
        <div class="modal">
          <div class="modal-header">
            <h2 class="modal-title">New Appointment</h2>
            <button class="modal-close" @click="showNewModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-body" @submit.prevent="submitAppointment">
            <div class="form-group">
              <label>Patient *</label>
              <select v-model="newAppointment.id_patient" required>
                <option value="">Select a patient...</option>
                <option v-for="p in patientsList" :key="p.id_patient" :value="p.id_patient">
                  {{ p.prenom }} {{ p.nom }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Doctor *</label>
              <select v-model="newAppointment.id_medecin" required>
                <option value="">Select a doctor...</option>
                <option v-for="m in medecinsList" :key="m.id_medecin" :value="m.id_medecin">
                  Dr. {{ m.prenom }} {{ m.nom }} ({{ m.specialite }})
                </option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Date *</label>
                <input v-model="newAppointment.date_rdv" type="date" required />
              </div>
              <div class="form-group">
                <label>Time *</label>
                <input v-model="newAppointment.heure" type="time" required />
              </div>
            </div>
            <div class="form-group">
              <label>Reason for Visit *</label>
              <input v-model="newAppointment.motif" placeholder="e.g. General checkup, follow-up..." required />
            </div>
            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="showNewModal = false">Cancel</button>
              <button type="submit" class="btn-submit" :disabled="submitting">
                <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                {{ submitting ? 'Creating...' : 'Create Appointment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Add/Edit Patient Modal -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getInfirmierAppointments, addRendezVous, getInfirmierPatients, getMedecins, updateRendezVousStatus } from '@/services/api.js';

const appointments = ref([]);
const loading = ref(true);
const viewMode = ref('list');
const showNewModal = ref(false);
const submitting = ref(false);

const patientsList = ref([]);
const medecinsList = ref([]);

const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer);
  toast.value = { show: true, message, type };
  toastTimer = setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const newAppointment = ref({
  id_patient: '',
  id_medecin: '',
  date_rdv: '',
  heure: '',
  motif: ''
});

const resetForm = () => {
  newAppointment.value = {
    id_patient: '', id_medecin: '', date_rdv: '', heure: '', motif: ''
  };
};

const appointmentStats = computed(() => {
  const total = appointments.value.length;
  const confirmed = appointments.value.filter(a => a.statut === 'confirmé' || a.statut === 'confirme').length;
  const pending = appointments.value.filter(a => a.statut === 'en attente').length;
  const completed = appointments.value.filter(a => a.statut === 'terminé' || a.statut === 'termine').length;
  return { total, confirmed, pending, completed };
});

const groupedAppointments = computed(() => {
  const groups = {};
  appointments.value.forEach(rdv => {
    const date = rdv.date_rdv;
    if (!groups[date]) groups[date] = [];
    groups[date].push(rdv);
  });
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
  const s = status.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  if (s.includes('confirme')) return 'confirmed';
  if (s.includes('attente')) return 'pending';
  if (s.includes('annule')) return 'cancelled';
  if (s.includes('termine')) return 'completed';
  return 'pending';
};

const viewAppointment = async (rdv) => {
  try {
    await updateRendezVousStatus(rdv.id_rdv, 'terminé');
    rdv.statut = 'terminé';
    showToast(`Appointment for ${rdv.patient_prenom} ${rdv.patient_nom} marked as completed`, 'success');
  } catch (error) {
    showToast('Failed to update appointment status', 'error');
  }
};

const submitAppointment = async () => {
  submitting.value = true;
  try {
    const res = await addRendezVous(newAppointment.value);
    if (res.data.success || res.status === 201) {
      showToast('Appointment created successfully!', 'success');
      showNewModal.value = false;
      resetForm();
      await fetchAppointments();
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to create appointment. Please try again.';
    showToast(msg, 'error');
  } finally {
    submitting.value = false;
  }
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

const fetchDropdownData = async () => {
  try {
    const [patientsRes, medecinsRes] = await Promise.all([
      getInfirmierPatients(),
      getMedecins()
    ]);
    if (patientsRes.data.success) patientsList.value = patientsRes.data.data;
    if (medecinsRes.data.success) medecinsList.value = medecinsRes.data.data;
  } catch (error) {
    console.error('Error fetching dropdown data:', error);
  }
};

onMounted(() => {
  fetchAppointments();
  fetchDropdownData();
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
  color: #475569;
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
  color: #475569;
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
  color: #475569;
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

.appointment-card + .appointment-card {
  border-top: 1px solid #e2e8f0;
  border-radius: 0;
}

.appointment-card:first-child {
  border-radius: 10px 10px 0 0;
}

.appointment-card:last-child {
  border-radius: 0 0 10px 10px;
}

.appointment-card:only-child {
  border-radius: 10px;
}

.appointment-card:hover {
  background: #f1f5f9;
}

.appointment-card.completed {
  background: #ecfdf5;
}

.appointment-card.completed .appointment-icon {
  background: #d1fae5;
  color: #059669;
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
  color: #475569;
  margin: 0 0 4px 0;
}

.appointment-type {
  font-size: 12px;
  color: #475569;
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
  color: #475569;
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
  color: #475569;
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

/* ========== TOAST NOTIFICATION ========== */
.toast {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
  min-width: 280px;
  max-width: 420px;
}

.toast.success {
  background: #ecfdf5;
  color: #059669;
  border: 1px solid #d1fae5;
}

.toast.error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fee2e2;
}

.toast i {
  font-size: 18px;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* ========== MODAL ========== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
}

.modal-title {
  font-size: 18px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

.modal-close {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: #475569;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #f1f5f9;
  color: #1a1a2e;
}

.modal-body {
  padding: 20px 24px;
}

.modal-details {
  max-width: 560px;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.detail-group.full-width {
  grid-column: 1 / -1;
}

.detail-group label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 14px;
  color: #1a1a2e;
  margin: 0;
  font-weight: 500;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}

.form-group input,
.form-group select {
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #1a1a2e;
  outline: none;
  background: #f8fafc;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #3b8d99;
  background: white;
}

.form-group input::placeholder {
  color: #475569;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.btn-cancel {
  padding: 10px 20px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.btn-submit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  background: #3b8d99;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-submit:hover {
  background: #2c6e7a;
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal,
.modal-leave-to .modal {
  transform: scale(0.95);
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .page-header {
    flex-direction: column;
    gap: 16px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal {
    max-width: 100%;
    margin: 10px;
  }
}
</style>
