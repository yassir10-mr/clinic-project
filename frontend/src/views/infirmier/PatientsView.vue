<template>
  <div class="patients-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Patients Management</h1>
        <p class="page-subtitle">Manage and view all patient records</p>
      </div>
      <button class="add-btn" @click="showAddModal = true">
        <i class="fas fa-plus"></i>
        Add Patient
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search by name, email, or phone..."
        />
      </div>
      <select class="status-filter" v-model="statusFilter">
        <option value="all">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <!-- Patients Table -->
    <div class="table-container">
      <div class="table-header">
        <h3 class="table-title">All Patients ({{ filteredPatients.length }})</h3>
      </div>

      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading patients...</p>
      </div>

      <div v-else-if="filteredPatients.length === 0" class="empty-state">
        <i class="fas fa-users"></i>
        <p>No patients found matching your search.</p>
      </div>

      <table v-else class="patients-table">
        <thead>
          <tr>
            <th>Patient Name</th>
            <th>Contact</th>
            <th>Age</th>
            <th>Blood Type</th>
            <th>Status</th>
            <th>Registration Date</th>
            <th class="actions-col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="patient in filteredPatients" :key="patient.id_patient">
            <td>
              <div class="patient-name">
                {{ patient.prenom }} {{ patient.nom }}
              </div>
              <div class="patient-id">ID: {{ patient.id_patient }}</div>
            </td>
            <td>
              <div class="contact-item">
                <i class="fas fa-phone"></i>
                {{ patient.telephone || 'N/A' }}
              </div>
              <div class="contact-item">
                <i class="fas fa-envelope"></i>
                {{ patient.email || 'N/A' }}
              </div>
            </td>
            <td>
              <span class="age-value">{{ calculateAge(patient.date_naissance) }}</span>
            </td>
            <td>
              <span class="blood-badge">{{ patient.groupe_sanguin || 'N/A' }}</span>
            </td>
            <td>
              <span :class="['status-badge', getStatus(patient)]">
                {{ getStatus(patient) }}
              </span>
            </td>
            <td>
              <span class="date-value">{{ formatDate(patient.date_naissance) }}</span>
            </td>
            <td class="actions-col">
              <div class="actions-dropdown" @click.stop="toggleDropdown(patient.id_patient)" ref="dropdownRef">
                <button class="actions-btn">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <div v-if="openDropdown === patient.id_patient" class="dropdown-menu">
                  <button class="dropdown-item" @click="viewPatient(patient)">
                    <i class="fas fa-eye"></i>
                    View Details
                  </button>
                  <button class="dropdown-item" @click="editPatient(patient)">
                    <i class="fas fa-edit"></i>
                    Edit
                  </button>
                  <button class="dropdown-item delete" @click="deletePatient(patient)">
                    <i class="fas fa-trash"></i>
                    Delete
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Toast Notification -->
    <Transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
        <span>{{ toast.message }}</span>
      </div>
    </Transition>

    <!-- Add Patient Modal -->
    <Transition name="modal">
      <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
        <div class="modal">
          <div class="modal-header">
            <h2 class="modal-title">Add New Patient</h2>
            <button class="modal-close" @click="showAddModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-body" @submit.prevent="submitPatient">
            <div class="form-row">
              <div class="form-group">
                <label>First Name *</label>
                <input v-model="newPatient.prenom" placeholder="First name" required />
              </div>
              <div class="form-group">
                <label>Last Name *</label>
                <input v-model="newPatient.nom" placeholder="Last name" required />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Date of Birth</label>
                <input v-model="newPatient.date_naissance" type="date" />
              </div>
              <div class="form-group">
                <label>Gender</label>
                <select v-model="newPatient.sexe">
                  <option value="">Select...</option>
                  <option value="M">Male</option>
                  <option value="F">Female</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <input v-model="newPatient.adresse" placeholder="Full address" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Phone</label>
                <input v-model="newPatient.telephone" placeholder="Phone number" />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input v-model="newPatient.email" type="email" placeholder="Email address" />
              </div>
            </div>
            <div class="form-group">
              <label>Blood Type</label>
              <select v-model="newPatient.groupe_sanguin">
                <option value="">Select...</option>
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
            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="showAddModal = false">Cancel</button>
              <button type="submit" class="btn-submit" :disabled="submitting">
                <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                {{ submitting ? 'Saving...' : 'Save Patient' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { getInfirmierPatients, addPatient } from '@/services/api.js';

const patients = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const statusFilter = ref('all');
const openDropdown = ref(null);
const showAddModal = ref(false);
const submitting = ref(false);

const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer);
  toast.value = { show: true, message, type };
  toastTimer = setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const newPatient = ref({
  nom: '',
  prenom: '',
  date_naissance: '',
  sexe: '',
  adresse: '',
  telephone: '',
  email: '',
  groupe_sanguin: ''
});

const resetForm = () => {
  newPatient.value = {
    nom: '', prenom: '', date_naissance: '', sexe: '',
    adresse: '', telephone: '', email: '', groupe_sanguin: ''
  };
};

const filteredPatients = computed(() => {
  let result = patients.value;

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(p =>
      `${p.prenom} ${p.nom}`.toLowerCase().includes(q) ||
      (p.email && p.email.toLowerCase().includes(q)) ||
      (p.telephone && p.telephone.includes(q))
    );
  }

  if (statusFilter.value !== 'all') {
    result = result.filter(p => getStatus(p) === statusFilter.value);
  }

  return result;
});

const calculateAge = (dateNaissance) => {
  if (!dateNaissance) return 'N/A';
  const birth = new Date(dateNaissance);
  const diff = Date.now() - birth.getTime();
  return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const getStatus = (patient) => {
  return patient.statut || 'active';
};

const toggleDropdown = (id) => {
  openDropdown.value = openDropdown.value === id ? null : id;
};

const closeDropdown = (e) => {
  if (!e.target.closest('.actions-dropdown')) {
    openDropdown.value = null;
  }
};

const viewPatient = (patient) => {
  openDropdown.value = null;
  showToast(`Viewing details for ${patient.prenom} ${patient.nom}`, 'success');
};

const editPatient = (patient) => {
  openDropdown.value = null;
  showToast(`Edit mode for ${patient.prenom} ${patient.nom}`, 'success');
};

const deletePatient = (patient) => {
  openDropdown.value = null;
  if (confirm(`Delete patient ${patient.prenom} ${patient.nom}?`)) {
    showToast('Patient deleted (demo)', 'success');
  }
};

const submitPatient = async () => {
  submitting.value = true;
  try {
    const res = await addPatient(newPatient.value);
    if (res.data.success || res.status === 201) {
      showToast('Patient saved successfully!', 'success');
      showAddModal.value = false;
      resetForm();
      await fetchPatients();
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to save patient. Please try again.';
    showToast(msg, 'error');
  } finally {
    submitting.value = false;
  }
};

const fetchPatients = async () => {
  loading.value = true;
  try {
    const res = await getInfirmierPatients();
    if (res.data.success) {
      patients.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching patients:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchPatients();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
  if (toastTimer) clearTimeout(toastTimer);
});
</script>

<style scoped>
.patients-page {
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

.add-btn {
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

.add-btn:hover {
  background: #2c6e7a;
}

/* Filters */
.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  background: white;
  padding: 16px 20px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
}

.search-box {
  flex: 1;
  position: relative;
}

.search-box i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 14px;
}

.search-box input {
  width: 100%;
  padding: 10px 16px 10px 40px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  background: #f8fafc;
  transition: all 0.2s;
}

.search-box input:focus {
  border-color: #3b8d99;
  background: white;
}

.status-filter {
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #64748b;
  background: white;
  outline: none;
  cursor: pointer;
  min-width: 140px;
}

/* Table */
.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.table-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
}

.table-title {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

.patients-table {
  width: 100%;
  border-collapse: collapse;
}

.patients-table thead {
  background: #f8fafc;
}

.patients-table th {
  padding: 14px 20px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: none;
  border-bottom: 1px solid #e2e8f0;
}

.patients-table td {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}

.patients-table tbody tr:hover {
  background: #f8fafc;
}

.patient-name {
  font-size: 14px;
  font-weight: 600;
  color: #1a1a2e;
}

.patient-id {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 2px;
}

.contact-item {
  font-size: 13px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}

.contact-item i {
  font-size: 12px;
  color: #94a3b8;
  width: 14px;
}

.age-value,
.date-value {
  font-size: 14px;
  color: #64748b;
}

.blood-badge {
  display: inline-block;
  padding: 4px 10px;
  background: #f1f5f9;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: lowercase;
}

.status-badge.active {
  background: #d1fae5;
  color: #059669;
}

.status-badge.inactive {
  background: #f1f5f9;
  color: #64748b;
}

/* Actions */
.actions-col {
  text-align: right;
  width: 60px;
}

.actions-dropdown {
  position: relative;
  display: inline-block;
}

.actions-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.actions-btn:hover {
  background: #f1f5f9;
  color: #1a1a2e;
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 4px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
  border: 1px solid #e2e8f0;
  padding: 6px 0;
  min-width: 160px;
  z-index: 50;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  width: 100%;
  border: none;
  background: none;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
  text-align: left;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: #f8fafc;
}

.dropdown-item.delete {
  color: #dc2626;
}

.dropdown-item i {
  font-size: 13px;
  width: 16px;
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
}

.loading-state i {
  font-size: 24px;
  color: #3b8d99;
}

.empty-state i {
  font-size: 32px;
  color: #cbd5e1;
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
  color: #94a3b8;
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
  color: #94a3b8;
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
  color: #64748b;
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

/* Modal transitions */
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
  .form-row {
    grid-template-columns: 1fr;
  }

  .modal {
    max-width: 100%;
    margin: 10px;
  }
}
</style>
