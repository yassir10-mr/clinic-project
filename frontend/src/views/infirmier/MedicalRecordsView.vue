<template>
  <div class="medical-records-page">
    <!-- Header -->
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
          <p class="stat-label">Consultations</p>
          <p class="stat-value" style="color: #3b8d99;">{{ stats.consultations }}</p>
        </div>
        <div class="stat-icon consultations">
          <i class="fas fa-stethoscope"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Lab Reports</p>
          <p class="stat-value" style="color: #059669;">0</p>
        </div>
        <div class="stat-icon lab">
          <i class="fas fa-flask"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Imaging</p>
          <p class="stat-value" style="color: #8b5cf6;">0</p>
        </div>
        <div class="stat-icon imaging">
          <i class="fas fa-image"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Prescriptions</p>
          <p class="stat-value" style="color: #f97316;">{{ stats.prescriptions }}</p>
        </div>
        <div class="stat-icon prescriptions">
          <i class="fas fa-pills"></i>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" v-model="searchQuery" placeholder="Search records..." />
      </div>
      <select class="filter-select" v-model="patientFilter">
        <option value="all">All Patients</option>
      </select>
      <select class="filter-select" v-model="typeFilter">
        <option value="all">All Types</option>
        <option value="consultation">Consultation</option>
        <option value="prescription">Prescription</option>
        <option value="record">Medical Record</option>
      </select>
    </div>

    <!-- Records List -->
    <div class="records-section">
      <div class="section-header">
        <h3 class="section-title">Medical Records ({{ filteredRecords.length }})</h3>
      </div>

      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading medical records...</p>
      </div>

      <div v-else-if="filteredRecords.length === 0" class="empty-state">
        <i class="fas fa-folder-open"></i>
        <p>No medical records found.</p>
      </div>

      <div v-else class="records-list">
        <div v-for="record in filteredRecords" :key="record.id || record.id_consultation || record.id_ordonnance || record.id_dossier" class="record-card">
          <div class="record-icon" :class="record.type">
            <i :class="getIconForType(record.type)"></i>
          </div>
          <div class="record-info">
            <p class="record-title">{{ getRecordTitle(record) }}</p>
            <p class="record-description">{{ getRecordDescription(record) }}</p>
            <p class="record-meta">
              Patient: {{ record.patient_prenom }} {{ record.patient_nom }} &bull; {{ formatDate(record.date || record.date_creation) }}
            </p>
          </div>
          <div class="record-badge" :class="record.type">
            {{ record.type }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getInfirmierMedicalRecords } from '@/services/api.js';

const records = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const patientFilter = ref('all');
const typeFilter = ref('all');

const stats = computed(() => {
  const consultations = records.value.filter(r => r.type === 'consultation').length;
  const prescriptions = records.value.filter(r => r.type === 'prescription').length;
  return { consultations, prescriptions };
});

const filteredRecords = computed(() => {
  let result = records.value;

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(r =>
      getRecordTitle(r).toLowerCase().includes(q) ||
      getRecordDescription(r).toLowerCase().includes(q) ||
      `${r.patient_prenom} ${r.patient_nom}`.toLowerCase().includes(q)
    );
  }

  if (typeFilter.value !== 'all') {
    result = result.filter(r => r.type === typeFilter.value);
  }

  return result;
});

const getIconForType = (type) => {
  const icons = {
    consultation: 'fas fa-stethoscope',
    prescription: 'fas fa-pills',
    record: 'fas fa-file-medical'
  };
  return icons[type] || 'fas fa-file';
};

const getRecordTitle = (record) => {
  if (record.type === 'consultation') return record.diagnostic || 'General Checkup';
  if (record.type === 'prescription') {
    const meds = record.medicaments ? record.medicaments.split(',').slice(0, 2).join(', ') : 'Prescription';
    return meds;
  }
  if (record.type === 'record') return record.antecedents || 'Medical Record';
  return 'Record';
};

const getRecordDescription = (record) => {
  if (record.type === 'consultation') return record.traitement || 'Consultation completed.';
  if (record.type === 'prescription') return record.posologie || 'Medication prescribed.';
  if (record.type === 'record') return record.allergies || 'Patient medical record.';
  return '';
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const fetchRecords = async () => {
  loading.value = true;
  try {
    const res = await getInfirmierMedicalRecords();
    if (res.data.success) {
      const { consultations, ordonnances, dossiers } = res.data.data;
      // Merge all records with type
      const allRecords = [
        ...(consultations || []).map(c => ({ ...c, type: 'consultation', id: c.id_consultation })),
        ...(ordonnances || []).map(o => ({ ...o, type: 'prescription', id: o.id_ordonnance })),
        ...(dossiers || []).map(d => ({ ...d, type: 'record', id: d.id_dossier }))
      ];
      // Sort by date descending
      allRecords.sort((a, b) => {
        const dateA = new Date(a.date || a.date_creation || 0);
        const dateB = new Date(b.date || b.date_creation || 0);
        return dateB - dateA;
      });
      records.value = allRecords;
    }
  } catch (error) {
    console.error('Error fetching medical records:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRecords();
});
</script>

<style scoped>
.medical-records-page {
  width: 100%;
  max-width: none;
  box-sizing: border-box;
}

/* Header */
.page-header {
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

.stat-icon.consultations {
  background: #effafd;
  color: #3b8d99;
}

.stat-icon.lab {
  background: #f0fdf4;
  color: #059669;
}

.stat-icon.imaging {
  background: #f5f3ff;
  color: #8b5cf6;
}

.stat-icon.prescriptions {
  background: #fff7ed;
  color: #f97316;
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
  color: #475569;
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

.filter-select {
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #475569;
  background: white;
  outline: none;
  cursor: pointer;
  min-width: 140px;
}

/* Records Section */
.records-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.section-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

/* Record Cards */
.records-list {
  display: flex;
  flex-direction: column;
}

.record-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.2s;
}

.record-card:hover {
  background: #f8fafc;
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

.record-icon.consultation {
  background: #effafd;
  color: #3b8d99;
}

.record-icon.prescription {
  background: #fff7ed;
  color: #f97316;
}

.record-icon.record {
  background: #f0fdf4;
  color: #059669;
}

.record-info {
  flex: 1;
  min-width: 0;
}

.record-title {
  font-size: 15px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 4px 0;
}

.record-description {
  font-size: 13px;
  color: #475569;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.record-meta {
  font-size: 12px;
  color: #475569;
  margin: 0;
}

.record-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: lowercase;
  flex-shrink: 0;
}

.record-badge.consultation {
  background: #dbeafe;
  color: #2563eb;
}

.record-badge.prescription {
  background: #d1fae5;
  color: #059669;
}

.record-badge.record {
  background: #fef3c7;
  color: #d97706;
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

  .filters-bar {
    flex-wrap: wrap;
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
