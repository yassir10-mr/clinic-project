<<template>
  <div class="doctors-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-title">
        <h1 class="page-title">Doctors</h1>
        <p class="page-subtitle">Manage medical staff and schedules</p>
      </div>
      <button @click="openAddModal" class="btn-add">
        <span class="icon">+</span>
        Add Doctor
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Total Doctors</span>
          <span class="stat-value">{{ medecins.length }}</span>
        </div>
        <div class="stat-icon blue">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Available</span>
          <span class="stat-value" style="color: #10b981;">{{ availableCount }}</span>
        </div>
        <div class="stat-icon green">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Specialties</span>
          <span class="stat-value" style="color: #a855f7;">{{ specialtiesCount }}</span>
        </div>
        <div class="stat-icon purple">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/>
            <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/>
            <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>
            <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Doctors Grid -->
    <div v-if="loading" class="loading">Loading doctors...</div>

    <div v-else-if="medecins.length > 0" class="doctors-grid">
      <div 
        v-for="medecin in medecins" 
        :key="medecin.id_medecin" 
        class="doctor-card"
      >
        <!-- Avatar & Info -->
        <div class="doctor-header">
          <div class="doctor-avatar">
            {{ getInitials(medecin.nom, medecin.prenom) }}
          </div>
          <h3 class="doctor-name">Dr. {{ medecin.prenom }} {{ medecin.nom }}</h3>
          <p class="doctor-specialty">{{ medecin.specialite }}</p>
          <span :class="['status-badge', getStatusClass(medecin)]">
            {{ getStatus(medecin) }}
          </span>
        </div>

        <!-- Contact Info -->
        <div class="doctor-contact">
          <div class="contact-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            {{ medecin.telephone }}
          </div>
          <div class="contact-item" v-if="medecin.email">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            {{ medecin.email }}
          </div>
          <div class="contact-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
              <line x1="4" x2="4" y1="22" y2="15"/>
            </svg>
            {{ medecin.matricule }}
          </div>
        </div>

        <!-- Actions -->
        <div class="doctor-actions">
          <button @click="editMedecin(medecin)" class="btn-schedule">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
              <line x1="16" x2="16" y1="2" y2="6"/>
              <line x1="8" x2="8" y1="2" y2="6"/>
              <line x1="3" x2="21" y1="10" y2="10"/>
            </svg>
            Edit
          </button>
          <button @click="removeMedecin(medecin.id_medecin)" class="btn-profile">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 6h18"/>
              <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
              <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
            </svg>
            Delete
          </button>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      <h3>No doctors registered</h3>
      <p>Add your first doctor to get started</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Edit Doctor' : 'Add Doctor' }}</h2>
          <button @click="closeModal" class="btn-close">×</button>
        </div>
        
        <form @submit.prevent="saveMedecin" class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label>Last Name <span class="required">*</span></label>
              <input v-model="form.nom" placeholder="Last name" required />
            </div>
            <div class="form-group">
              <label>First Name <span class="required">*</span></label>
              <input v-model="form.prenom" placeholder="First name" required />
            </div>
          </div>

          <div class="form-group">
            <label>Specialty <span class="required">*</span></label>
            <input v-model="form.specialite" placeholder="Cardiology, Pediatrics, etc." required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone <span class="required">*</span></label>
              <input v-model="form.telephone" placeholder="Phone number" required />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="form.email" placeholder="email@clinic.com" type="email" />
            </div>
          </div>

          <div class="form-group">
            <label>Registration Number <span class="required">*</span></label>
            <input v-model="form.matricule" placeholder="MAT-001" required />
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">Cancel</button>
            <button type="submit" class="btn-save">
              {{ isEditing ? 'Update' : 'Add' }} Doctor
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getMedecins, addMedecin, updateMedecin, deleteMedecin } from '@/services/api.js';

const medecins = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
  nom: '',
  prenom: '',
  specialite: '',
  telephone: '',
  email: '',
  matricule: ''
});

// ============ COMPUTED ============
const availableCount = computed(() => 
  medecins.value.filter(m => m.id_medecin % 3 !== 0).length
);

const specialtiesCount = computed(() => {
  const specialties = new Set(medecins.value.map(m => m.specialite));
  return specialties.size;
});

// ============ LIFECYCLE ============
onMounted(() => loadMedecins());

// ============ METHODS ============
const loadMedecins = async () => {
  loading.value = true;
  try {
    const response = await getMedecins();
    medecins.value = response.data.data || [];
  } catch (error) {
    console.error('Error:', error);
    alert('Unable to load doctors');
  } finally {
    loading.value = false;
  }
};

const getInitials = (nom, prenom) => {
  return `${(prenom?.[0] || '')}${(nom?.[0] || '')}`.toUpperCase();
};

const getStatus = (medecin) => {
  return medecin.id_medecin % 3 === 0 ? 'busy' : 'available';
};

const getStatusClass = (medecin) => {
  return getStatus(medecin) === 'available' ? 'status-available' : 'status-busy';
};

const openAddModal = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showModal.value = true;
};

const editMedecin = (medecin) => {
  isEditing.value = true;
  editingId.value = medecin.id_medecin;
  form.value = { ...medecin };
  showModal.value = true;
};

const saveMedecin = async () => {
  try {
    if (isEditing.value) {
      await updateMedecin(editingId.value, form.value);
      alert('✅ Doctor updated successfully!');
    } else {
      await addMedecin(form.value);
      alert('✅ Doctor added successfully!');
    }
    closeModal();
    await loadMedecins();
  } catch (error) {
    console.error('Error:', error);
    alert('❌ Error. Registration number may already exist.');
  }
};

const removeMedecin = async (id) => {
  if (!confirm('Delete this doctor?')) return;
  try {
    await deleteMedecin(id);
    alert('✅ Doctor deleted!');
    await loadMedecins();
  } catch (error) {
    alert('❌ Error deleting doctor');
  }
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = { nom: '', prenom: '', specialite: '', telephone: '', email: '', matricule: '' };
};
</script>

<style scoped>
.doctors-page {
  padding: 24px 32px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.header-title h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.page-subtitle {
  color: #64748b;
  font-size: 14px;
  margin: 0;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #2563eb;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-add:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-add .icon {
  font-size: 18px;
  font-weight: 400;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
}

.stat-icon.blue {
  background: #eff6ff;
  color: #3b82f6;
}

.stat-icon.green {
  background: #ecfdf5;
  color: #10b981;
}

.stat-icon.purple {
  background: #f3e8ff;
  color: #a855f7;
}

/* Doctors Grid */
.doctors-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.doctor-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.doctor-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

/* Doctor Header */
.doctor-header {
  margin-bottom: 20px;
}

.doctor-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 700;
  margin: 0 auto 16px;
}

.doctor-name {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.doctor-specialty {
  font-size: 13px;
  color: #64748b;
  margin: 0 0 12px 0;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
  text-transform: lowercase;
}

.status-available {
  background: #ecfdf5;
  color: #10b981;
}

.status-busy {
  background: #fef2f2;
  color: #ef4444;
}

/* Doctor Contact */
.doctor-contact {
  text-align: left;
  padding: 16px 0;
  border-top: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 16px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #64748b;
  margin-bottom: 8px;
}

.contact-item:last-child {
  margin-bottom: 0;
}

.contact-item svg {
  color: #94a3b8;
  flex-shrink: 0;
}

/* Doctor Actions */
.doctor-actions {
  display: flex;
  gap: 12px;
}

.btn-schedule,
.btn-profile {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-schedule {
  background: #f8fafc;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn-schedule:hover {
  background: #f1f5f9;
}

.btn-profile {
  background: white;
  color: #374151;
  border: 1px solid #e2e8f0;
}

.btn-profile:hover {
  background: #f8fafc;
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
  margin-bottom: 16px;
  color: #cbd5e1;
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
  backdrop-filter: blur(4px);
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

/* Responsive */
@media (max-width: 1024px) {
  .doctors-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .doctors-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>




<style scoped>
.doctors-page {
  padding: 24px 32px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.header-title h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.page-subtitle {
  color: #64748b;
  font-size: 14px;
  margin: 0;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #2563eb;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-add:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-add .icon {
  font-size: 18px;
  font-weight: 400;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
}

.stat-icon.blue {
  background: #eff6ff;
  color: #3b82f6;
}

.stat-icon.green {
  background: #ecfdf5;
  color: #10b981;
}

.stat-icon.purple {
  background: #f3e8ff;
  color: #a855f7;
}

/* Doctors Grid */
.doctors-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.doctor-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.doctor-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

/* Doctor Header */
.doctor-header {
  margin-bottom: 20px;
}

.doctor-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 700;
  margin: 0 auto 16px;
}

.doctor-name {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.doctor-specialty {
  font-size: 13px;
  color: #64748b;
  margin: 0 0 12px 0;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
  text-transform: lowercase;
}

.status-available {
  background: #ecfdf5;
  color: #10b981;
}

.status-busy {
  background: #fef2f2;
  color: #ef4444;
}

/* Doctor Contact */
.doctor-contact {
  text-align: left;
  padding: 16px 0;
  border-top: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 16px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #64748b;
  margin-bottom: 8px;
}

.contact-item:last-child {
  margin-bottom: 0;
}

.contact-item svg {
  color: #94a3b8;
  flex-shrink: 0;
}

/* Doctor Actions */
.doctor-actions {
  display: flex;
  gap: 12px;
}

.btn-schedule,
.btn-profile {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-schedule {
  background: #f8fafc;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn-schedule:hover {
  background: #f1f5f9;
}

.btn-profile {
  background: white;
  color: #374151;
  border: 1px solid #e2e8f0;
}

.btn-profile:hover {
  background: #f8fafc;
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
  margin-bottom: 16px;
  color: #cbd5e1;
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
  backdrop-filter: blur(4px);
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

/* Responsive */
@media (max-width: 1024px) {
  .doctors-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .doctors-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>