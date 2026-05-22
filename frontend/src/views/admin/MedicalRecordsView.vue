<<template>
  <div class="page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Medical Records</h1>
        <p class="page-subtitle">Access and manage patient medical history</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Consultations</span>
          <span class="stat-value blue">{{ totalConsultations }}</span>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-heartbeat"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Lab Reports</span>
          <span class="stat-value green">{{ totalLabReports }}</span>
        </div>
        <div class="stat-icon green">
          <i class="fas fa-file-medical-alt"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Imaging</span>
          <span class="stat-value purple">{{ totalImaging }}</span>
        </div>
        <div class="stat-icon purple">
          <i class="fas fa-x-ray"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Prescriptions</span>
          <span class="stat-value orange">{{ totalPrescriptions }}</span>
        </div>
        <div class="stat-icon orange">
          <i class="fas fa-pills"></i>
        </div>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="filter-card">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search records..."
        />
      </div>
      <select v-model="patientFilter" class="filter-select">
        <option value="">All Patients</option>
        <option v-for="patient in patients" :key="patient.id_patient" :value="patient.id_patient">
          {{ patient.prenom }} {{ patient.nom }}
        </option>
      </select>
      <select v-model="typeFilter" class="filter-select">
        <option value="">All Types</option>
        <option value="consultation">Consultation</option>
        <option value="lab">Lab Report</option>
        <option value="imaging">Imaging</option>
        <option value="prescription">Prescription</option>
      </select>
    </div>

    <!-- Records List -->
    <div class="records-card">
      <h3 class="records-title">Medical Records ({{ filteredRecords.length }})</h3>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>Loading records...</span>
      </div>

      <div v-else-if="filteredRecords.length > 0" class="records-list">
        <div v-for="record in filteredRecords" :key="record.id" class="record-item">
          <div class="record-icon" :class="record.type">
            <i :class="getIcon(record.type)"></i>
          </div>
          <div class="record-content">
            <div class="record-header">
              <h4 class="record-title">{{ record.title }}</h4>
              <span :class="['type-badge', record.type]">{{ record.type }}</span>
            </div>
            <p class="record-description">{{ record.description }}</p>
            <div class="record-meta">
              <span class="patient-name">
                <i class="fas fa-user"></i>
                Patient: {{ record.patient?.prenom }} {{ record.patient?.nom }}
              </span>
              <span class="separator">•</span>
              <span class="record-date">{{ formatDate(record.date) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <i class="fas fa-folder-open empty-icon"></i>
        <p>No medical records found</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getPatients, getConsultations } from '@/services/api.js';

const patients = ref([]);
const consultations = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const patientFilter = ref('');
const typeFilter = ref('');

onMounted(() => {
  loadData();
});

const loadData = async () => {
  loading.value = true;
  try {
    const [patientsRes, consultationsRes] = await Promise.all([
      getPatients(),
      getConsultations()
    ]);
    
    patients.value = patientsRes.data.data || [];
    consultations.value = consultationsRes.data.data || [];
  } catch (error) {
    console.error('Error loading data:', error);
    alert('Unable to load medical records');
  } finally {
    loading.value = false;
  }
};

// Transform consultations into records format
const records = computed(() => {
  const allRecords = [];
  
  // Add consultations as records
  consultations.value.forEach(consultation => {
    allRecords.push({
      id: `consultation-${consultation.id_consultation}`,
      type: 'consultation',
      title: consultation.diagnostic || 'General Checkup',
      description: consultation.traitement || 'No treatment recorded',
      date: consultation.date,
      patient: consultation.rendez_vous?.patient
    });
  });
  
  // You can add more record types here (lab reports, imaging, prescriptions)
  // For now using mock data for demo
  if (allRecords.length === 0) {
    allRecords.push(
      {
        id: 'demo-1',
        type: 'consultation',
        title: 'General Checkup',
        description: 'Annual physical examination completed. All vitals normal.',
        date: '2026-03-15',
        patient: { prenom: 'John', nom: 'Smith' }
      },
      {
        id: 'demo-2',
        type: 'lab',
        title: 'Blood Work',
        description: 'Complete blood count, lipid panel. Results within normal range.',
        date: '2026-03-10',
        patient: { prenom: 'John', nom: 'Smith' }
      },
      {
        id: 'demo-3',
        type: 'prescription',
        title: 'Medication Prescribed',
        description: 'Lisinopril 10mg for hypertension management.',
        date: '2026-02-20',
        patient: { prenom: 'John', nom: 'Smith' }
      }
    );
  }
  
  return allRecords.sort((a, b) => new Date(b.date) - new Date(a.date));
});

const filteredRecords = computed(() => {
  let result = [...records.value];
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(r => 
      r.title?.toLowerCase().includes(query) ||
      r.description?.toLowerCase().includes(query) ||
      r.patient?.nom?.toLowerCase().includes(query)
    );
  }
  
  if (patientFilter.value) {
    result = result.filter(r => r.patient?.id_patient === parseInt(patientFilter.value));
  }
  
  if (typeFilter.value) {
    result = result.filter(r => r.type === typeFilter.value);
  }
  
  return result;
});

// Statistics
const totalConsultations = computed(() => records.value.filter(r => r.type === 'consultation').length);
const totalLabReports = computed(() => records.value.filter(r => r.type === 'lab').length);
const totalImaging = computed(() => records.value.filter(r => r.type === 'imaging').length);
const totalPrescriptions = computed(() => records.value.filter(r => r.type === 'prescription').length);

const getIcon = (type) => {
  switch (type) {
    case 'consultation': return 'fas fa-heartbeat';
    case 'lab': return 'fas fa-file-medical-alt';
    case 'imaging': return 'fas fa-x-ray';
    case 'prescription': return 'fas fa-pills';
    default: return 'fas fa-file-medical';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<style scoped>
.page {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.stat-value.blue { color: #3b82f6; }
.stat-value.green { color: #10b981; }
.stat-value.purple { color: #8b5cf6; }
.stat-value.orange { color: #f59e0b; }

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.stat-icon.blue { background: #eff6ff; color: #3b82f6; }
.stat-icon.green { background: #ecfdf5; color: #10b981; }
.stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.stat-icon.orange { background: #fffbeb; color: #f59e0b; }

/* Filter Card */
.filter-card {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  background: white;
  padding: 16px 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  align-items: center;
}

.search-box {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8fafc;
  padding: 10px 14px;
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
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  color: #64748b;
  cursor: pointer;
  min-width: 140px;
  outline: none;
}

.filter-select:focus {
  border-color: #3b82f6;
}

/* Records Card */
.records-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 24px;
}

.records-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 20px 0;
}

.records-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.record-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  border-radius: 10px;
  background: #f8fafc;
  transition: background 0.2s;
}

.record-item:hover {
  background: #f1f5f9;
}

.record-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.record-icon.consultation { background: #eff6ff; color: #3b82f6; }
.record-icon.lab { background: #ecfdf5; color: #10b981; }
.record-icon.imaging { background: #f5f3ff; color: #8b5cf6; }
.record-icon.prescription { background: #fffbeb; color: #f59e0b; }

.record-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.record-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.record-title {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.type-badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: lowercase;
}

.type-badge.consultation { background: #dbeafe; color: #1d4ed8; }
.type-badge.lab { background: #d1fae5; color: #065f46; }
.type-badge.imaging { background: #e9d5ff; color: #6b21a8; }
.type-badge.prescription { background: #fef3c7; color: #92400e; }

.record-description {
  font-size: 13px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.record-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #94a3b8;
}

.patient-name {
  display: flex;
  align-items: center;
  gap: 4px;
}

.patient-name i {
  font-size: 10px;
}

.separator {
  color: #cbd5e1;
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px;
  gap: 16px;
  color: #94a3b8;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f1f5f9;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Empty */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px;
  gap: 12px;
  color: #94a3b8;
}

.empty-icon {
  font-size: 48px;
  color: #d1d5db;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filter-card {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-select {
    width: 100%;
  }
}
</style>





<style scoped>
.page {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.stat-value.blue { color: #3b82f6; }
.stat-value.green { color: #10b981; }
.stat-value.purple { color: #8b5cf6; }
.stat-value.orange { color: #f59e0b; }

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.stat-icon.blue { background: #eff6ff; color: #3b82f6; }
.stat-icon.green { background: #ecfdf5; color: #10b981; }
.stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.stat-icon.orange { background: #fffbeb; color: #f59e0b; }

/* Filter Card */
.filter-card {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  background: white;
  padding: 16px 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  align-items: center;
}

.search-box {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8fafc;
  padding: 10px 14px;
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
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  color: #64748b;
  cursor: pointer;
  min-width: 140px;
  outline: none;
}

.filter-select:focus {
  border-color: #3b82f6;
}

/* Records Card */
.records-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 24px;
}

.records-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 20px 0;
}

.records-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.record-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  border-radius: 10px;
  background: #f8fafc;
  transition: background 0.2s;
}

.record-item:hover {
  background: #f1f5f9;
}

.record-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.record-icon.consultation { background: #eff6ff; color: #3b82f6; }
.record-icon.lab { background: #ecfdf5; color: #10b981; }
.record-icon.imaging { background: #f5f3ff; color: #8b5cf6; }
.record-icon.prescription { background: #fffbeb; color: #f59e0b; }

.record-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.record-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.record-title {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.type-badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: lowercase;
}

.type-badge.consultation { background: #dbeafe; color: #1d4ed8; }
.type-badge.lab { background: #d1fae5; color: #065f46; }
.type-badge.imaging { background: #e9d5ff; color: #6b21a8; }
.type-badge.prescription { background: #fef3c7; color: #92400e; }

.record-description {
  font-size: 13px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.record-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #94a3b8;
}

.patient-name {
  display: flex;
  align-items: center;
  gap: 4px;
}

.patient-name i {
  font-size: 10px;
}

.separator {
  color: #cbd5e1;
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px;
  gap: 16px;
  color: #94a3b8;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f1f5f9;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Empty */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px;
  gap: 12px;
  color: #94a3b8;
}

.empty-icon {
  font-size: 48px;
  color: #d1d5db;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filter-card {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-select {
    width: 100%;
  }
}
</style>