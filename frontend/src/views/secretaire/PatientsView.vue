<template>
  <div class="patients-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Patients Management</h1>
        <p class="subtitle">Manage and view all patient records</p>
      </div>
      <button class="btn-add" @click="openModal()">
        <i class="fas fa-plus"></i>
        Add Patient
      </button>
    </div>
    
    <!-- Search & Filters -->
    <div class="filters-card">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search by name, email, or phone..."
          @input="filterPatients"
        />
      </div>
      <select v-model="statusFilter" class="status-filter" @change="filterPatients">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <!-- Patients Table -->
    <div class="table-card">
      <div class="table-header">
        <h3>All Patients ({{ filteredPatients.length }})</h3>
      </div>

      <div class="table-container">
        <table v-if="filteredPatients.length">
          <thead>
            <tr>
              <th>PATIENT NAME</th>
              <th>CONTACT</th>
              <th>AGE</th>
              <th>BLOOD TYPE</th>
              <th>STATUS</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="patient in filteredPatients" :key="patient.id_patient">
              <td>
                <div class="patient-info">
                  <div class="patient-avatar">
                    {{ getInitials(patient.nom, patient.prenom) }}
                  </div>
                  <div class="patient-details">
                    <p class="patient-name">{{ patient.prenom }} {{ patient.nom }}</p>
                    <p class="patient-id">ID: {{ patient.id_patient }}</p>
                  </div>
                </div>
              </td>
              <td>
                <div class="contact-info">
                  <p><i class="fas fa-phone"></i> {{ patient.telephone }}</p>
                  <p><i class="fas fa-envelope"></i> {{ patient.email || 'N/A' }}</p>
                </div>
              </td>
              <td>
                <span class="age">{{ calculateAge(patient.date_naissance) }}</span>
              </td>
              <td>
                <span class="blood-type">{{ patient.groupe_sanguin || '-' }}</span>
              </td>
              <td>
                <span :class="['status-badge', 'active']">active</span>
              </td>
              <td>
                <div class="actions">
                  <!-- NEW: Medical Record Button -->
                  <button class="btn-record" @click="viewMedicalRecord(patient)" title="Medical Record">
                    <i class="fas fa-file-medical"></i>
                  </button>
                  <button class="btn-edit" @click="openModal(patient)" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn-delete" @click="deletePatient(patient.id_patient)" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state">
          <i class="fas fa-users"></i>
          <p>No patients found</p>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>{{ editingPatient ? 'Edit Patient' : 'Add New Patient' }}</h2>
          <button class="btn-close" @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="savePatient">
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>First Name</label>
                <input v-model="form.prenom" type="text" required placeholder="Enter first name" />
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input v-model="form.nom" type="text" required placeholder="Enter last name" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Date of Birth</label>
                <input v-model="form.date_naissance" type="date" required />
              </div>
              <div class="form-group">
                <label>Gender</label>
                <select v-model="form.sexe" required>
                  <option value="">Select gender</option>
                  <option value="M">Male</option>
                  <option value="F">Female</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Phone</label>
                <input v-model="form.telephone" type="tel" required placeholder="Enter phone number" />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input v-model="form.email" type="email" placeholder="Enter email (optional)" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Address</label>
                <input v-model="form.adresse" type="text" placeholder="Enter address (optional)" />
              </div>
              <div class="form-group">
                <label>Blood Type</label>
                <select v-model="form.groupe_sanguin">
                  <option value="">Select blood type</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="closeModal">Cancel</button>
            <button type="submit" class="btn-save" :disabled="saving">
              {{ saving ? 'Saving...' : (editingPatient ? 'Update' : 'Save') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- NEW: Medical Record Modal -->
    <div v-if="showMedicalRecord" class="modal-overlay" @click="showMedicalRecord = false">
      <div class="modal modal-lg" @click.stop>
        <div class="modal-header">
          <h2>Medical Record</h2>
          <button class="btn-close" @click="showMedicalRecord = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body" v-if="selectedPatient">
          <!-- Patient Header -->
          <div class="patient-header">
            <div class="patient-avatar-large">{{ getInitials(selectedPatient.nom, selectedPatient.prenom) }}</div>
            <div class="patient-header-info">
              <h3>{{ selectedPatient.prenom }} {{ selectedPatient.nom }}</h3>
              <p class="patient-meta">
                <span><i class="fas fa-venus-mars"></i> {{ selectedPatient.sexe }}</span>
                <span><i class="fas fa-birthday-cake"></i> {{ formatDate(selectedPatient.date_naissance) }}</span>
                <span><i class="fas fa-phone"></i> {{ selectedPatient.telephone }}</span>
              </p>
              <p class="patient-meta" v-if="selectedPatient.email">
                <span><i class="fas fa-envelope"></i> {{ selectedPatient.email }}</span>
              </p>
            </div>
          </div>

          <!-- Blood Type Badge -->
          <div class="blood-type-badge" v-if="selectedPatient.groupe_sanguin">
            <i class="fas fa-tint"></i>
            <span>Blood Type: <strong>{{ selectedPatient.groupe_sanguin }}</strong></span>
          </div>

          <!-- Medical Info Grid -->
          <div class="medical-grid">
            <div class="medical-card">
              <h4><i class="fas fa-allergies"></i> Allergies</h4>
              <p>{{ selectedPatient.dossierMedical?.allergies || 'No allergies recorded' }}</p>
            </div>
            <div class="medical-card">
              <h4><i class="fas fa-history"></i> Medical History</h4>
              <p>{{ selectedPatient.dossierMedical?.antecedents || 'No medical history recorded' }}</p>
            </div>
          </div>

          <!-- Address -->
          <div class="info-section" v-if="selectedPatient.adresse">
            <h4><i class="fas fa-map-marker-alt"></i> Address</h4>
            <p>{{ selectedPatient.adresse }}</p>
          </div>

          <!-- Consultation History -->
          <div class="consultation-history" v-if="patientConsultations.length">
            <h4><i class="fas fa-clipboard-list"></i> Consultation History</h4>
            <div class="history-list">
              <div v-for="consult in patientConsultations" :key="consult.id_consultation" class="history-item">
                <div class="history-date">
                  <span class="date">{{ formatDate(consult.date) }}</span>
                  <span class="time">{{ consult.rendezVous?.heure?.substring(0,5) || '' }}</span>
                </div>
                <div class="history-details">
                  <p class="doctor">Dr. {{ consult.medecin?.nom }}</p>
                  <p class="diagnosis">{{ consult.diagnostic }}</p>
                  <p class="treatment" v-if="consult.traitement">{{ consult.traitement }}</p>
                </div>
                <div class="history-badges">
                  <span v-if="consult.ordonnance" class="badge badge-blue">
                    <i class="fas fa-prescription"></i> Rx
                  </span>
                  <span v-if="consult.facture" class="badge badge-green">
                    <i class="fas fa-check"></i> Billed
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="no-history">
            <i class="fas fa-info-circle"></i>
            <p>No consultation history available</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { getPatients, addPatient, updatePatient, deletePatient as apiDeletePatient, getConsultations } from '@/services/api.js'

const patients = ref([])
const filteredPatients = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const editingPatient = ref(null)
const saving = ref(false)

// NEW: Medical record refs
const showMedicalRecord = ref(false)
const selectedPatient = ref(null)
const patientConsultations = ref([])

const form = ref({
  nom: '',
  prenom: '',
  date_naissance: '',
  sexe: '',
  telephone: '',
  email: '',
  adresse: '',
  groupe_sanguin: ''
})

const loadPatients = async () => {
  try {
    const response = await getPatients()
    patients.value = response.data.data || []
    filteredPatients.value = patients.value
  } catch (err) {
    console.error('Error loading patients:', err)
  }
}

const filterPatients = () => {
  let result = patients.value
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(p => 
      p.nom?.toLowerCase().includes(query) ||
      p.prenom?.toLowerCase().includes(query) ||
      p.email?.toLowerCase().includes(query) ||
      p.telephone?.includes(query)
    )
  }
  
  filteredPatients.value = result
}

const getInitials = (nom, prenom) => {
  return `${(prenom?.charAt(0) || '')}${(nom?.charAt(0) || '')}`.toUpperCase()
}

const calculateAge = (dateNaissance) => {
  if (!dateNaissance) return '-'
  const birthDate = new Date(dateNaissance)
  const today = new Date()
  let age = today.getFullYear() - birthDate.getFullYear()
  const monthDiff = today.getMonth() - birthDate.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--
  }
  return age
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-GB')
}

// NEW: View medical record
const viewMedicalRecord = async (patient) => {
  selectedPatient.value = patient
  showMedicalRecord.value = true
  
  // Load consultations for this patient
  try {
    const res = await getConsultations()
    const allConsultations = res.data.data || []
    patientConsultations.value = allConsultations.filter(c => 
      c.rendezVous?.id_patient === patient.id_patient
    )
  } catch (err) {
    console.error('Error loading consultations:', err)
    patientConsultations.value = []
  }
}

const openModal = (patient = null) => {
  if (patient) {
    editingPatient.value = patient
    form.value = { ...patient }
  } else {
    editingPatient.value = null
    form.value = {
      nom: '',
      prenom: '',
      date_naissance: '',
      sexe: '',
      telephone: '',
      email: '',
      adresse: '',
      groupe_sanguin: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingPatient.value = null
}

const savePatient = async () => {
  saving.value = true
  try {
    if (editingPatient.value) {
      await updatePatient(editingPatient.value.id_patient, form.value)
    } else {
      await addPatient(form.value)
    }
    closeModal()
    loadPatients()
  } catch (err) {
    console.error('Error saving patient:', err)
    alert('Error saving patient. Please try again.')
  } finally {
    saving.value = false
  }
}

const deletePatient = async (id) => {
  if (!confirm('Are you sure you want to delete this patient?')) return
  
  try {
    await apiDeletePatient(id)
    loadPatients()
  } catch (err) {
    console.error('Error deleting patient:', err)
    alert('Error deleting patient. Please try again.')
  }
}

onMounted(() => {
  loadPatients()
})
</script>

<style scoped>
.patients-page {
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

/* Filters Card */
.filters-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  display: flex;
  gap: 16px;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}

.search-box {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px 16px;
}

.search-box i {
  color: #475569;
  font-size: 14px;
}

.search-box input {
  border: none;
  background: none;
  outline: none;
  font-size: 14px;
  color: #475569;
  width: 100%;
}

.search-box input::placeholder {
  color: #475569;
}

.status-filter {
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  color: #475569;
  font-size: 14px;
  cursor: pointer;
  min-width: 140px;
}

/* Table Card */
.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
  overflow: hidden;
}

.table-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
}

.table-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead th {
  text-align: left;
  padding: 14px 24px;
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

tbody td {
  padding: 16px 24px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

tbody tr:hover {
  background: #f8fafc;
}

tbody tr:last-child td {
  border-bottom: none;
}

/* Patient Info */
.patient-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.patient-avatar {
  width: 40px;
  height: 40px;
  background: #dbeafe;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
  font-weight: 600;
  font-size: 14px;
}

.patient-details {
  display: flex;
  flex-direction: column;
}

.patient-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
  margin: 0;
}

.patient-id {
  color: #475569;
  font-size: 12px;
  margin: 2px 0 0;
}

/* Contact Info */
.contact-info p {
  margin: 0;
  font-size: 13px;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 6px;
}

.contact-info p:first-child {
  margin-bottom: 4px;
}

.contact-info i {
  font-size: 12px;
  color: #475569;
  width: 14px;
}

/* Badges */
.age, .blood-type {
  font-size: 14px;
  color: #475569;
  font-weight: 500;
}

.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: lowercase;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.date {
  font-size: 14px;
  color: #475569;
}

/* Actions */
.actions {
  display: flex;
  gap: 8px;
}

.btn-edit, .btn-delete, .btn-record {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-edit {
  background: #eff6ff;
  color: #2563eb;
}

.btn-edit:hover {
  background: #dbeafe;
}

.btn-record {
  background: #f0fdf4;
  color: #059669;
}

.btn-record:hover {
  background: #dcfce7;
}

.btn-delete {
  background: #fef2f2;
  color: #ef4444;
}

.btn-delete:hover {
  background: #fee2e2;
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

.modal-lg {
  max-width: 700px;
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

/* NEW: Medical Record Modal Styles */
.patient-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}

.patient-avatar-large {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #dbeafe;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 700;
}

.patient-header-info h3 {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.patient-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  color: #475569;
  font-size: 14px;
}

.patient-meta span {
  display: flex;
  align-items: center;
  gap: 6px;
}

.patient-meta i {
  color: #475569;
  font-size: 13px;
}

.blood-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  background: #fef2f2;
  color: #dc2626;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 24px;
}

.blood-type-badge i {
  font-size: 18px;
}

.medical-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

.medical-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
}

.medical-card h4 {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.medical-card h4 i {
  color: #2563eb;
}

.medical-card p {
  color: #475569;
  font-size: 14px;
  line-height: 1.6;
}

.info-section {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}

.info-section h4 {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-section h4 i {
  color: #2563eb;
}

.info-section p {
  color: #475569;
  font-size: 14px;
}

.consultation-history h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.consultation-history h4 i {
  color: #2563eb;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.history-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 10px;
}

.history-date {
  min-width: 80px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.history-date .date {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.history-date .time {
  font-size: 12px;
  color: #475569;
}

.history-details {
  flex: 1;
}

.history-details .doctor {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
  margin-bottom: 4px;
}

.history-details .diagnosis {
  color: #475569;
  font-size: 13px;
  margin-bottom: 2px;
}

.history-details .treatment {
  color: #475569;
  font-size: 12px;
}

.history-badges {
  display: flex;
  gap: 8px;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}

.badge-blue {
  background: #dbeafe;
  color: #2563eb;
}

.badge-green {
  background: #d1fae5;
  color: #059669;
}

.no-history {
  text-align: center;
  padding: 40px;
  color: #475569;
}

.no-history i {
  font-size: 32px;
  margin-bottom: 12px;
  display: block;
}

.no-history p {
  font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .filters-card {
    flex-direction: column;
  }
  
  .search-box {
    width: 100%;
  }
  
  .medical-grid {
    grid-template-columns: 1fr;
  }
}
</style>