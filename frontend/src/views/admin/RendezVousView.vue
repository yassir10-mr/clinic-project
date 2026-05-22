<template>
  <div class="appointments-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Appointments</h1>
        <p class="page-subtitle">Schedule and manage appointments</p>
      </div>
      <button @click="openAddModal" class="btn-add">
        <i class="fas fa-plus"></i>
        New Appointment
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Total</span>
          <span class="stat-value">{{ rendezVous.length }}</span>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-calendar"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Confirmed</span>
          <span class="stat-value">{{ confirmedCount }}</span>
        </div>
        <div class="stat-icon green">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Pending</span>
          <span class="stat-value">{{ pendingCount }}</span>
        </div>
        <div class="stat-icon yellow">
          <i class="fas fa-clock"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Completed</span>
          <span class="stat-value">{{ completedCount }}</span>
        </div>
        <div class="stat-icon purple">
          <i class="fas fa-check-double"></i>
        </div>
      </div>
    </div>

    <!-- Appointments by Date -->
    <div v-if="loading" class="loading">Loading appointments...</div>

    <div v-else-if="groupedAppointments.length > 0" class="appointments-list">
      <div 
        v-for="group in groupedAppointments" 
        :key="group.date" 
        class="date-group"
      >
        <div class="date-header">
          <span class="date-text">{{ formatDateGroup(group.date) }}</span>
        </div>
        
        <div class="appointment-cards">
          <div 
            v-for="rdv in group.appointments" 
            :key="rdv.id_rdv" 
            class="appointment-card"
          >
            <div class="appointment-time">
              <div class="time-icon">
                <i class="fas fa-clock"></i>
              </div>
              <span class="time-text">{{ rdv.heure }}</span>
            </div>
            
            <div class="appointment-details">
              <h4 class="patient-name">{{ rdv.patient?.prenom }} {{ rdv.patient?.nom }}</h4>
              <p class="doctor-info">
                <i class="fas fa-user-md"></i>
                Dr. {{ rdv.medecin?.prenom }} {{ rdv.medecin?.nom }}
              </p>
              <p class="motif-info" v-if="rdv.motif">
                <i class="fas fa-stethoscope"></i>
                {{ rdv.motif }}
              </p>
            </div>
            
            <div class="appointment-status">
              <span :class="['status-badge', getStatusClass(rdv.statut)]">
                {{ rdv.statut }}
              </span>
            </div>
            
            <div class="appointment-actions">
              <button 
                v-if="rdv.statut === 'en attente'" 
                @click="updateStatus(rdv.id_rdv, 'confirmé')" 
                class="btn-action confirm"
                title="Confirm"
              >
                <i class="fas fa-check"></i>
              </button>
              <button 
                v-if="rdv.statut !== 'annulé'" 
                @click="updateStatus(rdv.id_rdv, 'annulé')" 
                class="btn-action cancel"
                title="Cancel"
              >
                <i class="fas fa-times"></i>
              </button>
              <button 
                @click="editRdv(rdv)" 
                class="btn-action edit"
                title="Edit"
              >
                <i class="fas fa-edit"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <i class="fas fa-calendar-times empty-icon"></i>
      <h3>No appointments scheduled</h3>
      <p>Create your first appointment to get started</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Edit Appointment' : 'New Appointment' }}</h2>
          <button @click="closeModal" class="btn-close">×</button>
        </div>
        
        <form @submit.prevent="saveRdv" class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label>Patient <span class="required">*</span></label>
              <select v-model="form.id_patient" required>
                <option value="">Select patient</option>
                <option v-for="p in patients" :key="p.id_patient" :value="p.id_patient">
                  {{ p.prenom }} {{ p.nom }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Doctor <span class="required">*</span></label>
              <select v-model="form.id_medecin" required>
                <option value="">Select doctor</option>
                <option v-for="m in medecins" :key="m.id_medecin" :value="m.id_medecin">
                  Dr. {{ m.prenom }} {{ m.nom }} - {{ m.specialite }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Date <span class="required">*</span></label>
              <input v-model="form.date_rdv" type="date" required />
            </div>
            <div class="form-group">
              <label>Time <span class="required">*</span></label>
              <input v-model="form.heure" type="time" required />
            </div>
          </div>

          <div class="form-group">
            <label>Secretary</label>
            <select v-model="form.id_secretaire">
              <option value="">Select secretary</option>
              <option v-for="s in secretaires" :key="s.id_secretaire" :value="s.id_secretaire">
                {{ s.prenom }} {{ s.nom }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Reason</label>
            <textarea v-model="form.motif" placeholder="Reason for appointment..." rows="3"></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select v-model="form.statut">
              <option value="en attente">Pending</option>
              <option value="confirmé">Confirmed</option>
              <option value="annulé">Cancelled</option>
            </select>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">Cancel</button>
            <button type="submit" class="btn-save">
              {{ isEditing ? 'Update' : 'Create' }} Appointment
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  getRendezVous, 
  addRendezVous,
  updateRendezVous,
  updateRendezVousStatus,
  getPatients,
  getMedecins,
  getSecretaires 
} from '@/services/api.js';

const rendezVous = ref([]);
const patients = ref([]);
const medecins = ref([]);
const secretaires = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
  id_patient: '',
  id_medecin: '',
  id_secretaire: '',
  date_rdv: '',
  heure: '',
  motif: '',
  statut: 'en attente'
});

// ============ COMPUTED ============
const confirmedCount = computed(() => 
  rendezVous.value.filter(r => r.statut === 'confirmé').length
);

const pendingCount = computed(() => 
  rendezVous.value.filter(r => r.statut === 'en attente').length
);

const completedCount = computed(() => 
  rendezVous.value.filter(r => r.statut === 'terminé').length
);

const groupedAppointments = computed(() => {
  const groups = {};
  
  rendezVous.value.forEach(rdv => {
    const date = rdv.date_rdv;
    if (!groups[date]) {
      groups[date] = [];
    }
    groups[date].push(rdv);
  });
  
  // Trier par date
  return Object.keys(groups)
    .sort()
    .map(date => ({
      date,
      appointments: groups[date].sort((a, b) => a.heure.localeCompare(b.heure))
    }));
});

// ============ LIFECYCLE ============
onMounted(() => {
  loadRendezVous();
  loadSelectData();
});

// ============ METHODS ============
const loadRendezVous = async () => {
  loading.value = true;
  try {
    const response = await getRendezVous();
    rendezVous.value = response.data.data || [];
  } catch (error) {
    console.error('Error loading appointments:', error);
    alert('Unable to load appointments');
  } finally {
    loading.value = false;
  }
};

const loadSelectData = async () => {
  try {
    const [pRes, mRes, sRes] = await Promise.all([
      getPatients(),
      getMedecins(),
      getSecretaires()
    ]);
    patients.value = pRes.data.data || [];
    medecins.value = mRes.data.data || [];
    secretaires.value = sRes.data.data || [];
  } catch (error) {
    console.error('Error loading select data:', error);
  }
};

const formatDateGroup = (dateStr) => {
  const date = new Date(dateStr);
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);
  
  if (date.toDateString() === today.toDateString()) return 'Today';
  if (date.toDateString() === tomorrow.toDateString()) return 'Tomorrow';
  
  return date.toLocaleDateString('en-GB', {
    weekday: 'long',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const getStatusClass = (status) => {
  switch (status) {
    case 'confirmé': return 'status-confirmed';
    case 'en attente': return 'status-pending';
    case 'annulé': return 'status-cancelled';
    case 'terminé': return 'status-completed';
    default: return 'status-pending';
  }
};

const openAddModal = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showModal.value = true;
};

const editRdv = (rdv) => {
  isEditing.value = true;
  editingId.value = rdv.id_rdv;
  form.value = {
    id_patient: rdv.id_patient,
    id_medecin: rdv.id_medecin,
    id_secretaire: rdv.id_secretaire || '',
    date_rdv: rdv.date_rdv,
    heure: rdv.heure,
    motif: rdv.motif || '',
    statut: rdv.statut
  };
  showModal.value = true;
};

const saveRdv = async () => {
  try {
    if (isEditing.value) {
      await updateRendezVous(editingId.value, form.value);
      alert('✅ Appointment updated successfully!');
    } else {
      await addRendezVous(form.value);
      alert('✅ Appointment created successfully!');
    }
    closeModal();
    await loadRendezVous();
  } catch (error) {
    console.error('Error saving:', error);
    alert('❌ Error saving appointment');
  }
};

const updateStatus = async (id, status) => {
  if (!confirm(`Change status to "${status}"?`)) return;
  try {
    await updateRendezVousStatus(id, status);
    alert(`✅ Status updated to: ${status}`);
    await loadRendezVous();
  } catch (error) {
    console.error('Error updating status:', error);
    alert('❌ Error updating status');
  }
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    id_patient: '',
    id_medecin: '',
    id_secretaire: '',
    date_rdv: '',
    heure: '',
    motif: '',
    statut: 'en attente'
  };
};
</script>

<style scoped>
.appointments-page {
  max-width: 1400px;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

/* Stats Grid */
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
  border: 1px solid #e2e8f0;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.blue {
  background: #eff6ff;
  color: #2563eb;
}

.stat-icon.green {
  background: #ecfdf5;
  color: #10b981;
}

.stat-icon.yellow {
  background: #fefce8;
  color: #ca8a04;
}

.stat-icon.purple {
  background: #f3e8ff;
  color: #9333ea;
}

/* Appointments List */
.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.date-group {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.date-header {
  padding: 16px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
}

.date-text {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.appointment-cards {
  padding: 12px;
}

.appointment-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-radius: 10px;
  transition: background 0.2s;
}

.appointment-card:hover {
  background: #f8fafc;
}

.appointment-time {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 100px;
}

.time-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.time-text {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

.appointment-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.patient-name {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.doctor-info, .motif-info {
  font-size: 13px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 0;
}

.doctor-info i, .motif-info i {
  font-size: 12px;
  width: 14px;
}

.appointment-status {
  min-width: 100px;
}

.status-badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-confirmed {
  background: #ecfdf5;
  color: #10b981;
}

.status-pending {
  background: #fefce8;
  color: #ca8a04;
}

.status-cancelled {
  background: #fef2f2;
  color: #ef4444;
}

.status-completed {
  background: #eff6ff;
  color: #2563eb;
}

.appointment-actions {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  font-size: 13px;
}

.btn-action.confirm {
  background: #ecfdf5;
  color: #10b981;
}

.btn-action.confirm:hover {
  background: #d1fae5;
}

.btn-action.cancel {
  background: #fef2f2;
  color: #ef4444;
}

.btn-action.cancel:hover {
  background: #fee2e2;
}

.btn-action.edit {
  background: #eff6ff;
  color: #2563eb;
}

.btn-action.edit:hover {
  background: #dbeafe;
}

/* Loading */
.loading {
  padding: 60px;
  text-align: center;
  color: #64748b;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80px;
  color: #94a3b8;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  color: #475569;
  font-size: 16px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
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
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 0;
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #94a3b8;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f1f5f9;
  color: #475569;
}

.modal-form {
  padding: 24px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.required {
  color: #ef4444;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  transition: all 0.2s;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.btn-cancel {
  padding: 10px 20px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #374151;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-save {
  padding: 10px 24px;
  border: none;
  background: #2563eb;
  color: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
</style>




<style scoped>
.appointments-page {
  max-width: 1400px;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

/* Stats Grid */
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
  border: 1px solid #e2e8f0;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.blue {
  background: #eff6ff;
  color: #2563eb;
}

.stat-icon.green {
  background: #ecfdf5;
  color: #10b981;
}

.stat-icon.yellow {
  background: #fefce8;
  color: #ca8a04;
}

.stat-icon.purple {
  background: #f3e8ff;
  color: #9333ea;
}

/* Appointments List */
.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.date-group {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.date-header {
  padding: 16px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
}

.date-text {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.appointment-cards {
  padding: 12px;
}

.appointment-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-radius: 10px;
  transition: background 0.2s;
}

.appointment-card:hover {
  background: #f8fafc;
}

.appointment-time {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 100px;
}

.time-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.time-text {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

.appointment-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.patient-name {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.doctor-info, .motif-info {
  font-size: 13px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 0;
}

.doctor-info i, .motif-info i {
  font-size: 12px;
  width: 14px;
}

.appointment-status {
  min-width: 100px;
}

.status-badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-confirmed {
  background: #ecfdf5;
  color: #10b981;
}

.status-pending {
  background: #fefce8;
  color: #ca8a04;
}

.status-cancelled {
  background: #fef2f2;
  color: #ef4444;
}

.status-completed {
  background: #eff6ff;
  color: #2563eb;
}

.appointment-actions {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  font-size: 13px;
}

.btn-action.confirm {
  background: #ecfdf5;
  color: #10b981;
}

.btn-action.confirm:hover {
  background: #d1fae5;
}

.btn-action.cancel {
  background: #fef2f2;
  color: #ef4444;
}

.btn-action.cancel:hover {
  background: #fee2e2;
}

.btn-action.edit {
  background: #eff6ff;
  color: #2563eb;
}

.btn-action.edit:hover {
  background: #dbeafe;
}

/* Loading */
.loading {
  padding: 60px;
  text-align: center;
  color: #64748b;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80px;
  color: #94a3b8;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  color: #475569;
  font-size: 16px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
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
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 0;
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #94a3b8;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f1f5f9;
  color: #475569;
}

.modal-form {
  padding: 24px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.required {
  color: #ef4444;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  transition: all 0.2s;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.btn-cancel {
  padding: 10px 20px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #374151;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-save {
  padding: 10px 24px;
  border: none;
  background: #2563eb;
  color: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
</style>