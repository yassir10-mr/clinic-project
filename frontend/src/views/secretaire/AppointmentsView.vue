<<template>
  <div class="appointments-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Appointments</h1>
        <p class="subtitle">Schedule and manage appointments</p>
      </div>
      <button class="btn-add" @click="openModal()">
        <i class="fas fa-plus"></i>
        New Appointment
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total</p>
          <h2 class="stat-value">{{ stats.total || 0 }}</h2>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-calendar"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Confirmed</p>
          <h2 class="stat-value">{{ stats.confirmed || 0 }}</h2>
        </div>
        <div class="stat-icon green">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Pending</p>
          <h2 class="stat-value">{{ stats.pending || 0 }}</h2>
        </div>
        <div class="stat-icon yellow">
          <i class="fas fa-clock"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Completed</p>
          <h2 class="stat-value">{{ stats.completed || 0 }}</h2>
        </div>
        <div class="stat-icon purple">
          <i class="fas fa-check-double"></i>
        </div>
      </div>
    </div>

    <!-- Appointments List -->
    <div v-for="(group, date) in groupedAppointments" :key="date" class="date-group">
      <h3 class="date-header">{{ formatDateHeader(date) }}</h3>
      <div class="appointments-list">
        <div 
          v-for="rdv in group" 
          :key="rdv.id_rdv"
          class="appointment-card"
        >
          <div class="appointment-time">
            <div class="time-icon">
              <i class="fas fa-clock"></i>
            </div>
                    <span class="time-text">{{ formatTime(rdv.heure) }}</span>
          </div>
          
          <div class="appointment-info">
            <p class="patient-name">{{ rdv.patient?.prenom }} {{ rdv.patient?.nom }}</p>
            <div class="doctor-info">
              <i class="fas fa-user-md"></i>
              <span>Dr. {{ rdv.medecin?.nom }}</span>
            </div>
            <div class="motif-info" v-if="rdv.motif">
              <i class="fas fa-stethoscope"></i>
              <span>{{ rdv.motif }}</span>
            </div>
          </div>
          
          <span :class="['status-badge', getStatusClass(rdv.statut)]">
            {{ rdv.statut }}
          </span>
          
          <div class="actions">
            <button 
              v-if="rdv.statut === 'en attente'"
              @click="updateStatus(rdv.id_rdv, 'confirmé')" 
              class="btn-confirm"
              title="Confirm"
            >
              <i class="fas fa-check"></i>
            </button>
            <button 
              v-if="rdv.statut !== 'annulé' && rdv.statut !== 'terminé'"
              @click="updateStatus(rdv.id_rdv, 'annulé')" 
              class="btn-cancel"
              title="Cancel"
            >
              <i class="fas fa-times"></i>
            </button>
            <button 
              v-if="rdv.statut === 'confirmé'"
              @click="updateStatus(rdv.id_rdv, 'terminé')" 
              class="btn-complete"
              title="Complete"
            >
              <i class="fas fa-check-double"></i>
            </button>
            <button @click="openModal(rdv)" class="btn-edit" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!appointments.length" class="empty-state">
      <i class="fas fa-calendar-day"></i>
      <p>No appointments found</p>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>{{ editingRdv ? 'Edit Appointment' : 'New Appointment' }}</h2>
          <button class="btn-close" @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="saveAppointment">
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>Patient</label>
                <select v-model="form.id_patient" required>
                  <option value="">Select patient</option>
                  <option v-for="p in patients" :key="p.id_patient" :value="p.id_patient">
                    {{ p.prenom }} {{ p.nom }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Doctor</label>
                <select v-model="form.id_medecin" required>
                  <option value="">Select doctor</option>
                  <option v-for="m in medecins" :key="m.id_medecin" :value="m.id_medecin">
                    Dr. {{ m.nom }} {{ m.prenom }} - {{ m.specialite }}
                  </option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Date</label>
                <input v-model="form.date_rdv" type="date" required />
              </div>
              <div class="form-group">
                <label>Time</label>
                    <input v-model="form.heure" type="time" required />
              </div>
            </div>
            <div class="form-group">
              <label>Motif</label>
              <input v-model="form.motif" type="text" placeholder="Reason for appointment" />
            </div>
            <div class="form-group">
              <label>Status</label>
              <select v-model="form.statut">
                <option value="en attente">Pending</option>
                <option value="confirmé">Confirmed</option>
                <option value="annulé">Cancelled</option>
                <option value="terminé">Completed</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="closeModal">Cancel</button>
            <button type="submit" class="btn-save" :disabled="saving">
              {{ saving ? 'Saving...' : (editingRdv ? 'Update' : 'Save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getRendezVous, addRendezVous, updateRendezVous, updateRendezVousStatus, deleteRendezVous, getPatients, getMedecins } from '@/services/api.js'

const appointments = ref([])
const patients = ref([])
const medecins = ref([])
const showModal = ref(false)
const editingRdv = ref(null)
const saving = ref(false)

const form = ref({
  id_patient: '',
  id_medecin: '',
  date_rdv: '',
  heure: '',
  motif: '',
  statut: 'en attente'
})

const stats = computed(() => {
  const total = appointments.value.length
  const confirmed = appointments.value.filter(r => r.statut === 'confirmé').length
  const pending = appointments.value.filter(r => r.statut === 'en attente').length
  const completed = appointments.value.filter(r => r.statut === 'terminé').length
  return { total, confirmed, pending, completed }
})

const groupedAppointments = computed(() => {
  const groups = {}
  appointments.value.forEach(rdv => {
    const date = rdv.date_rdv
    if (!groups[date]) groups[date] = []
    groups[date].push(rdv)
  })
  // Sort dates descending
  return Object.fromEntries(
    Object.entries(groups).sort(([a], [b]) => new Date(b) - new Date(a))
  )
})

const loadData = async () => {
  try {
    const [rdvsRes, patientsRes, medecinsRes] = await Promise.all([
      getRendezVous(),
      getPatients(),
      getMedecins()
    ])
    appointments.value = rdvsRes.data.data || []
    patients.value = patientsRes.data.data || []
    medecins.value = medecinsRes.data.data || []
  } catch (err) {
    console.error('Error loading data:', err)
  }
}

const formatTime = (time) => {
  if (!time) return '--:--'
  return time.substring(0, 5)
}

const formatDateHeader = (dateStr) => {
  const date = new Date(dateStr)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  if (date.toDateString() === today.toDateString()) return 'Today'
  if (date.toDateString() === tomorrow.toDateString()) return 'Tomorrow'
  
  return date.toLocaleDateString('en-GB', {
    weekday: 'long',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    'en attente': 'pending',
    'confirmé': 'confirmed',
    'annulé': 'cancelled',
    'terminé': 'completed'
  }
  return classes[status] || 'pending'
}

const updateStatus = async (id, status) => {
  try {
    await updateRendezVousStatus(id, status)
    loadData()
  } catch (err) {
    console.error('Error updating status:', err)
    alert('Error updating appointment status')
  }
}

const openModal = (rdv = null) => {
  if (rdv) {
    editingRdv.value = rdv
    form.value = { ...rdv }
  } else {
    editingRdv.value = null
    form.value = {
      id_patient: '',
      id_medecin: '',
      date_rdv: '',
      heure_rdv: '',
      motif: '',
      statut: 'en attente'
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingRdv.value = null
}

const saveAppointment = async () => {
  saving.value = true
  try {
    if (editingRdv.value) {
      await updateRendezVous(editingRdv.value.id_rdv, form.value)
    } else {
      await addRendezVous(form.value)
    }
    closeModal()
    loadData()
  } catch (err) {
    console.error('Error saving appointment:', err)
    alert('Error saving appointment. Please try again.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.appointments-page {
  max-width: 1200px;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.subtitle {
  color: #475569;
  font-size: 14px;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
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
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}

.stat-info {
  flex: 1;
}

.stat-label {
  color: #475569;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #1e293b;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.blue { background: #dbeafe; color: #2563eb; }
.stat-icon.green { background: #d1fae5; color: #059669; }
.stat-icon.yellow { background: #fef3c7; color: #d97706; }
.stat-icon.purple { background: #f3e8ff; color: #9333ea; }

/* Date Group */
.date-group {
  margin-bottom: 24px;
}

.date-header {
  font-size: 16px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 12px;
  padding: 0 4px;
}

/* Appointments List */
.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.appointment-card {
  background: white;
  border-radius: 12px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
  transition: all 0.2s;
}

.appointment-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Time */
.appointment-time {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 100px;
}

.time-icon {
  width: 40px;
  height: 40px;
  background: #dbeafe;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
  font-size: 16px;
}

.time-text {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

/* Info */
.appointment-info {
  flex: 1;
  min-width: 0;
}

.patient-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 15px;
  margin-bottom: 6px;
}

.doctor-info, .motif-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #475569;
  margin-bottom: 2px;
}

.doctor-info i, .motif-info i {
  font-size: 12px;
  color: #475569;
}

/* Status Badge */
.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
  white-space: nowrap;
}

.status-badge.pending {
  background: #fef3c7;
  color: #d97706;
}

.status-badge.confirmed {
  background: #d1fae5;
  color: #059669;
}

.status-badge.cancelled {
  background: #fee2e2;
  color: #ef4444;
}

.status-badge.completed {
  background: #dbeafe;
  color: #2563eb;
}

/* Actions */
.actions {
  display: flex;
  gap: 8px;
}

.actions button {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-confirm {
  background: #d1fae5;
  color: #059669;
}

.btn-confirm:hover {
  background: #a7f3d0;
}

.btn-cancel {
  background: #fee2e2;
  color: #ef4444;
}

.btn-cancel:hover {
  background: #fecaca;
}

.btn-complete {
  background: #dbeafe;
  color: #2563eb;
}

.btn-complete:hover {
  background: #bfdbfe;
}

.btn-edit {
  background: #f1f5f9;
  color: #475569;
}

.btn-edit:hover {
  background: #e2e8f0;
  color: #475569;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #475569;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 16px;
  display: block;
}

.empty-state p {
  font-size: 16px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
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
  max-width: 600px;
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
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
}

.btn-close {
  background: none;
  border: none;
  color: #475569;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f1f5f9;
  color: #475569;
}

.modal-body {
  padding: 24px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input,
.form-group select {
  padding: 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #1e293b;
  background: #f8fafc;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #2563eb;
  background: white;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 0 24px 24px;
}

.btn-cancel {
  padding: 12px 24px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f8fafc;
}

.btn-save {
  padding: 12px 24px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .appointment-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .actions {
    width: 100%;
    justify-content: flex-end;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>