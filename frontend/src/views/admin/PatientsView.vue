<template>
  <div class="patients-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Patients Management</h1>
        <p class="page-subtitle">Manage and view all patient records</p>
      </div>
      <button @click="openAddModal" class="btn-add">
        <i class="fas fa-plus"></i>
        Add Patient
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="search-filter-card">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search by name, email, or phone..."
        />
      </div>
      <select v-model="statusFilter" class="filter-select">
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

      <div v-if="loading" class="loading">Chargement...</div>

      <table v-else-if="filteredPatients.length > 0">
        <thead>
          <tr>
            <th>Patient Name</th>
            <th>Contact</th>
            <th>Age</th>
            <th>Blood Type</th>
            <th>Status</th>
            <th>Registration Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="patient in filteredPatients" :key="patient.id_patient">
            <td>
              <div class="patient-name">
                <strong>{{ patient.prenom }} {{ patient.nom }}</strong>
                <span class="patient-id">ID: {{ patient.id_patient }}</span>
              </div>
            </td>
            <td>
              <div class="contact-info">
                <span><i class="fas fa-phone"></i> {{ patient.telephone }}</span>
                <span v-if="patient.email"><i class="fas fa-envelope"></i> {{ patient.email }}</span>
              </div>
            </td>
            <td>{{ calculateAge(patient.date_naissance) }}</td>
            <td>
              <span class="blood-badge">{{ patient.groupe_sanguin || '-' }}</span>
            </td>
            <td>
              <span :class="['status-badge', patient.email ? 'active' : 'inactive']">
                {{ patient.email ? 'active' : 'inactive' }}
              </span>
            </td>
            <td>{{ formatDate(patient.date_naissance) }}</td>
            <td>
              <div class="actions">
                <button @click="editPatient(patient)" class="btn-action edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="removePatient(patient.id_patient)" class="btn-action delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <p v-else class="empty">No patients found</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h2>{{ isEditing ? 'Edit Patient' : 'Add Patient' }}</h2>
        <form @submit.prevent="savePatient">
          <div class="form-row">
            <div class="form-group">
              <label>First Name *</label>
              <input v-model="form.prenom" placeholder="First name" required />
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input v-model="form.nom" placeholder="Last name" required />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Date of Birth *</label>
              <input v-model="form.date_naissance" type="date" required />
            </div>
            <div class="form-group">
              <label>Gender *</label>
              <select v-model="form.sexe" required>
                <option value="Homme">Male</option>
                <option value="Femme">Female</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Phone *</label>
              <input v-model="form.telephone" placeholder="Phone number" required />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="form.email" placeholder="Email" type="email" />
            </div>
          </div>
          <div class="form-group">
            <label>Address</label>
            <input v-model="form.adresse" placeholder="Address" />
          </div>
          <div class="form-group">
            <label>Blood Type</label>
            <input v-model="form.groupe_sanguin" placeholder="A+, O-, etc." />
          </div>
          
          <div class="modal-buttons">
            <button type="submit" class="btn-save">
              {{ isEditing ? 'Update' : 'Add' }} Patient
            </button>
            <button type="button" @click="closeModal" class="btn-cancel">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getPatients, addPatient, updatePatient, deletePatient as apiDeletePatient } from '@/services/api.js';

const patients = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const searchQuery = ref('');
const statusFilter = ref('');

const form = ref({
  nom: '',
  prenom: '',
  date_naissance: '',
  sexe: 'Homme',
  telephone: '',
  email: '',
  adresse: '',
  groupe_sanguin: ''
});

// ============ CHARGEMENT ============
onMounted(() => {
  console.log('🚀 PatientsView monté, chargement des patients...');
  loadPatients();
});

const loadPatients = async () => {
  loading.value = true;
  try {
    console.log('📡 Appel API getPatients...');
    const response = await getPatients();
    console.log('✅ Réponse API:', response.data);
    
    if (response.data && response.data.data) {
      patients.value = response.data.data;
      console.log('📊 Patients chargés:', patients.value.length);
    } else {
      console.warn('⚠️ Format de réponse inattendu:', response.data);
      patients.value = [];
    }
  } catch (error) {
    console.error('❌ Erreur chargement:', error);
    alert('Impossible de charger les patients. Vérifiez la connexion.');
    patients.value = [];
  } finally {
    loading.value = false;
  }
};

// ============ FILTRES ============
const filteredPatients = computed(() => {
  let result = [...patients.value]; // Copie pour éviter les problèmes de réactivité
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(p => 
      p.nom?.toLowerCase().includes(query) ||
      p.prenom?.toLowerCase().includes(query) ||
      p.email?.toLowerCase().includes(query) ||
      p.telephone?.includes(query)
    );
  }
  
  if (statusFilter.value) {
    const hasEmail = statusFilter.value === 'active';
    result = result.filter(p => !!p.email === hasEmail);
  }
  
  return result;
});

// ============ UTILITAIRES ============
const calculateAge = (birthDate) => {
  if (!birthDate) return '-';
  const today = new Date();
  const birth = new Date(birthDate);
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--;
  }
  return age;
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('fr-FR');
};

// ============ MODAL ============
const openAddModal = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showModal.value = true;
};

const editPatient = (patient) => {
  isEditing.value = true;
  editingId.value = patient.id_patient;
  form.value = { ...patient };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    nom: '', prenom: '', date_naissance: '', sexe: 'Homme',
    telephone: '', email: '', adresse: '', groupe_sanguin: ''
  };
};

// ============ SAUVEGARDE ============
const savePatient = async () => {
  try {
    console.log('💾 Sauvegarde patient:', isEditing.value ? 'MODIFICATION' : 'AJOUT', form.value);
    
    if (isEditing.value) {
      const response = await updatePatient(editingId.value, form.value);
      console.log('✅ Modification réussie:', response.data);
      alert('✅ Patient modifié avec succès !');
    } else {
      const response = await addPatient(form.value);
      console.log('✅ Ajout réussi:', response.data);
      alert('✅ Patient ajouté avec succès !');
    }
    
    closeModal();
    await loadPatients(); // Recharge la liste
    
  } catch (error) {
    console.error('❌ Erreur sauvegarde:', error);
    
    let msg = '❌ Erreur lors de l\'enregistrement';
    if (error.response) {
      msg += `\nStatus: ${error.response.status}`;
      if (error.response.status === 422) {
        const errors = error.response.data.errors;
        if (errors) msg += '\n' + Object.values(errors).flat().join('\n');
      } else if (error.response.data?.message) {
        msg += `\n${error.response.data.message}`;
      }
    } else if (error.request) {
      msg += '\nPas de réponse du serveur';
    }
    
    alert(msg);
  }
};

// ============ SUPPRESSION (CORRIGÉE) ============
const removePatient = async (id) => {
  if (!confirm('🗑️ Supprimer ce patient ?')) {
    console.log('❌ Suppression annulée par l\'utilisateur');
    return;
  }
  
  console.log('🗑️ Tentative de suppression du patient ID:', id);
  
  try {
    // Appel API
    const response = await apiDeletePatient(id);
    console.log('✅ Réponse suppression:', response.data);
    
    // Vérifier que la suppression a réussi côté serveur
    if (response.data && response.data.success) {
      alert('✅ Patient supprimé !');
      
      // Supprimer localement pour éviter d'attendre le reload
      patients.value = patients.value.filter(p => p.id_patient !== id);
      console.log('📊 Patients restants:', patients.value.length);
      
      // Recharger quand même pour être sûr
      await loadPatients();
      console.log('🔄 Liste rechargée après suppression');
    } else {
      console.warn('⚠️ Réponse sans success:', response.data);
      alert('⚠️ La suppression a échoué côté serveur');
    }
    
  } catch (error) {
    console.error('❌ Erreur suppression complète:', error);
    
    let msg = '❌ Erreur lors de la suppression';
    
    if (error.response) {
      console.error('Status:', error.response.status);
      console.error('Data:', error.response.data);
      msg += `\nStatus: ${error.response.status}`;
      
      if (error.response.status === 404) {
        msg += '\nPatient non trouvé (déjà supprimé ?)';
        // Retirer de la liste locale quand même
        patients.value = patients.value.filter(p => p.id_patient !== id);
      } else if (error.response.status === 401) {
        msg += '\nNon authentifié !';
      } else if (error.response.data?.message) {
        msg += `\n${error.response.data.message}`;
      }
    } else if (error.request) {
      console.error('Pas de réponse:', error.request);
      msg += '\nPas de réponse du serveur. Vérifiez que Laravel tourne.';
    } else {
      msg += `\n${error.message}`;
    }
    
    alert(msg);
  }
};
</script>

<style scoped>
.patients-page {
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

.btn-add i {
  font-size: 12px;
}

/* Search & Filter */
.search-filter-card {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  background: white;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.search-box {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.search-box i {
  color: #94a3b8;
  font-size: 14px;
}

.search-box input {
  flex: 1;
  border: none;
  background: none;
  outline: none;
  font-size: 14px;
  color: #1e293b;
}

.search-box input::placeholder {
  color: #94a3b8;
}

.filter-select {
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  color: #64748b;
  cursor: pointer;
  min-width: 140px;
}

/* Table Card */
.table-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
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

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
}

th {
  padding: 14px 24px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f8fafc;
}

td {
  padding: 16px 24px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  color: #374151;
}

tr:hover {
  background: #f8fafc;
}

/* Patient Name */
.patient-name {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.patient-name strong {
  font-weight: 600;
  color: #1e293b;
}

.patient-id {
  font-size: 12px;
  color: #94a3b8;
}

/* Contact Info */
.contact-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.contact-info span {
  font-size: 13px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}

.contact-info i {
  font-size: 12px;
  color: #94a3b8;
  width: 14px;
}

/* Blood Badge */
.blood-badge {
  display: inline-block;
  padding: 4px 10px;
  background: #f1f5f9;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
}

/* Status Badge */
.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background: #ecfdf5;
  color: #10b981;
}

.status-badge.inactive {
  background: #f1f5f9;
  color: #64748b;
}

/* Actions */
.actions {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-action.edit {
  background: #eff6ff;
  color: #2563eb;
}

.btn-action.edit:hover {
  background: #dbeafe;
}

.btn-action.delete {
  background: #fef2f2;
  color: #ef4444;
}

.btn-action.delete:hover {
  background: #fee2e2;
}

/* Loading */
.loading {
  padding: 60px;
  text-align: center;
  color: #64748b;
}

/* Empty */
.empty {
  padding: 60px;
  text-align: center;
  color: #94a3b8;
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
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 24px;
}

.modal h2 {
  margin: 0 0 20px 0;
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
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

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.modal-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
}

.btn-save {
  padding: 10px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover {
  background: #1d4ed8;
}

.btn-cancel {
  padding: 10px 20px;
  background: #f3f4f6;
  color: #374151;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #e5e7eb;
}
</style>




<style scoped>
.patients-page {
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

.btn-add i {
  font-size: 12px;
}

/* Search & Filter */
.search-filter-card {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  background: white;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.search-box {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.search-box i {
  color: #94a3b8;
  font-size: 14px;
}

.search-box input {
  flex: 1;
  border: none;
  background: none;
  outline: none;
  font-size: 14px;
  color: #1e293b;
}

.search-box input::placeholder {
  color: #94a3b8;
}

.filter-select {
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  color: #64748b;
  cursor: pointer;
  min-width: 140px;
}

/* Table Card */
.table-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
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

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
}

th {
  padding: 14px 24px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f8fafc;
}

td {
  padding: 16px 24px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  color: #374151;
}

tr:hover {
  background: #f8fafc;
}

/* Patient Name */
.patient-name {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.patient-name strong {
  font-weight: 600;
  color: #1e293b;
}

.patient-id {
  font-size: 12px;
  color: #94a3b8;
}

/* Contact Info */
.contact-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.contact-info span {
  font-size: 13px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}

.contact-info i {
  font-size: 12px;
  color: #94a3b8;
  width: 14px;
}

/* Blood Badge */
.blood-badge {
  display: inline-block;
  padding: 4px 10px;
  background: #f1f5f9;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
}

/* Status Badge */
.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background: #ecfdf5;
  color: #10b981;
}

.status-badge.inactive {
  background: #f1f5f9;
  color: #64748b;
}

/* Actions */
.actions {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-action.edit {
  background: #eff6ff;
  color: #2563eb;
}

.btn-action.edit:hover {
  background: #dbeafe;
}

.btn-action.delete {
  background: #fef2f2;
  color: #ef4444;
}

.btn-action.delete:hover {
  background: #fee2e2;
}

/* Loading */
.loading {
  padding: 60px;
  text-align: center;
  color: #64748b;
}

/* Empty */
.empty {
  padding: 60px;
  text-align: center;
  color: #94a3b8;
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
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 24px;
}

.modal h2 {
  margin: 0 0 20px 0;
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
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

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.modal-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
}

.btn-save {
  padding: 10px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover {
  background: #1d4ed8;
}

.btn-cancel {
  padding: 10px 20px;
  background: #f3f4f6;
  color: #374151;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #e5e7eb;
}
</style>