<template>
  <div class="doctors-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Doctors Directory</h1>
        <p class="subtitle">View medical staff and their specialties</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total Doctors</p>
          <h2 class="stat-value">{{ doctors.length }}</h2>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-user-md"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Available</p>
          <h2 class="stat-value">{{ availableCount }}</h2>
        </div>
        <div class="stat-icon green">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Specialties</p>
          <h2 class="stat-value">{{ uniqueSpecialties }}</h2>
        </div>
        <div class="stat-icon purple">
          <i class="fas fa-stethoscope"></i>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search by name or specialty..." 
        />
      </div>
      <select v-model="specialtyFilter" class="filter-select">
        <option value="">All Specialties</option>
        <option v-for="spec in specialtiesList" :key="spec" :value="spec">
          {{ spec }}
        </option>
      </select>
    </div>

    <!-- Doctors Grid -->
    <div class="doctors-grid">
      <div v-for="doctor in filteredDoctors" :key="doctor.id_medecin" class="doctor-card">
        <div class="doctor-avatar">
          {{ getInitials(doctor.nom, doctor.prenom) }}
        </div>
        <div class="doctor-info">
          <h3 class="doctor-name">Dr. {{ doctor.prenom }} {{ doctor.nom }}</h3>
          <span class="doctor-specialty">{{ doctor.specialite }}</span>
          <span :class="['doctor-status', 'available']">Available</span>
        </div>
        <div class="doctor-contact">
          <p><i class="fas fa-phone"></i> {{ doctor.telephone }}</p>
          <p><i class="fas fa-envelope"></i> {{ doctor.email }}</p>
          <p><i class="fas fa-id-card"></i> Matricule: {{ doctor.matricule }}</p>
        </div>
        <div class="doctor-footer">
          <button class="btn-view" @click="viewDetails(doctor)">
            <i class="fas fa-eye"></i>
            View Details
          </button>
        </div>
      </div>
    </div>

    <div v-if="!filteredDoctors.length" class="empty-state">
      <i class="fas fa-user-md"></i>
      <p>No doctors found</p>
    </div>

    <!-- Doctor Details Modal -->
    <div v-if="showDetails" class="modal-overlay" @click="showDetails = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Doctor Details</h2>
          <button class="btn-close" @click="showDetails = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body" v-if="selectedDoctor">
          <div class="doctor-profile">
            <div class="doctor-avatar-large">
              {{ getInitials(selectedDoctor.nom, selectedDoctor.prenom) }}
            </div>
            <div class="doctor-profile-info">
              <h3>Dr. {{ selectedDoctor.prenom }} {{ selectedDoctor.nom }}</h3>
              <p class="specialty">{{ selectedDoctor.specialite }}</p>
              <span class="status-badge available">Available</span>
            </div>
          </div>

          <div class="info-sections">
            <div class="info-section">
              <h4><i class="fas fa-phone"></i> Contact</h4>
              <p><strong>Phone:</strong> {{ selectedDoctor.telephone }}</p>
              <p><strong>Email:</strong> {{ selectedDoctor.email }}</p>
            </div>

            <div class="info-section">
              <h4><i class="fas fa-id-card"></i> Professional Info</h4>
              <p><strong>Matricule:</strong> {{ selectedDoctor.matricule }}</p>
              <p><strong>Specialty:</strong> {{ selectedDoctor.specialite }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getMedecins } from '@/services/api.js'

const doctors = ref([])
const searchQuery = ref('')
const specialtyFilter = ref('')
const showDetails = ref(false)
const selectedDoctor = ref(null)

const loadDoctors = async () => {
  try {
    const res = await getMedecins()
    doctors.value = res.data.data || []
  } catch (err) {
    console.error('Error loading doctors:', err)
  }
}

const getInitials = (nom, prenom) => {
  return `${(prenom?.charAt(0) || '')}${(nom?.charAt(0) || '')}`.toUpperCase()
}

const specialtiesList = computed(() => {
  const specs = new Set(doctors.value.map(d => d.specialite).filter(Boolean))
  return Array.from(specs).sort()
})

const availableCount = computed(() => doctors.value.length)

const uniqueSpecialties = computed(() => specialtiesList.value.length)

const filteredDoctors = computed(() => {
  let result = [...doctors.value]
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(d => 
      d.nom?.toLowerCase().includes(q) ||
      d.prenom?.toLowerCase().includes(q) ||
      d.specialite?.toLowerCase().includes(q) ||
      d.email?.toLowerCase().includes(q)
    )
  }
  
  if (specialtyFilter.value) {
    result = result.filter(d => d.specialite === specialtyFilter.value)
  }
  
  return result
})

const viewDetails = (doctor) => {
  selectedDoctor.value = doctor
  showDetails.value = true
}

onMounted(() => {
  loadDoctors()
})
</script>

<style scoped>
.doctors-page {
  max-width: 1200px;
}

/* Page Header */
.page-header {
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

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 24px;
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

.stat-info { flex: 1; }
.stat-label { color: #475569; font-size: 14px; font-weight: 500; margin-bottom: 8px; }
.stat-value { font-size: 32px; font-weight: 700; color: #1e293b; }

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
.stat-icon.purple { background: #f3e8ff; color: #9333ea; }

/* Filters */
.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
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
}

.search-box input {
  width: 100%;
  padding: 12px 12px 12px 42px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  background: white;
}

.filter-select {
  padding: 12px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  background: white;
  min-width: 180px;
}

/* Doctors Grid */
.doctors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.doctor-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  transition: all 0.3s;
}

.doctor-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.08);
}

.doctor-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #4f46e5;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 16px;
}

.doctor-info {
  margin-bottom: 16px;
}

.doctor-name {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 6px;
}

.doctor-specialty {
  display: inline-block;
  padding: 4px 12px;
  background: #f0fdf4;
  color: #166534;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 8px;
}

.doctor-status {
  display: block;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
  margin: 0 auto;
}

.doctor-status.available {
  background: #d1fae5;
  color: #065f46;
}

.doctor-contact {
  width: 100%;
  text-align: left;
  padding: 16px 0;
  border-top: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 16px;
}

.doctor-contact p {
  margin: 6px 0;
  font-size: 13px;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 8px;
}

.doctor-contact i {
  color: #475569;
  font-size: 12px;
  width: 14px;
}

.doctor-footer {
  width: 100%;
}

.btn-view {
  width: 100%;
  padding: 10px;
  background: #eff6ff;
  color: #2563eb;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-view:hover {
  background: #dbeafe;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px;
  color: #475569;
}

.empty-state i { font-size: 48px; margin-bottom: 16px; display: block; }

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
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
  max-width: 500px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 0;
}

.modal-header h2 { font-size: 20px; font-weight: 700; color: #1e293b; }

.btn-close {
  background: none;
  border: none;
  color: #475569;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
}

.btn-close:hover { background: #f1f5f9; color: #475569; }

.modal-body { padding: 24px; }

.doctor-profile {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}

.doctor-avatar-large {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #4f46e5;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: 700;
}

.doctor-profile-info h3 {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.specialty {
  color: #475569;
  font-size: 14px;
  margin-bottom: 8px;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.available {
  background: #d1fae5;
  color: #065f46;
}

.info-sections {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.info-section {
  background: #f8fafc;
  border-radius: 10px;
  padding: 16px;
}

.info-section h4 {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-section h4 i { color: #2563eb; }

.info-section p {
  margin: 6px 0;
  font-size: 14px;
  color: #475569;
}

.info-section p strong {
  color: #1e293b;
}

/* Responsive */
@media (max-width: 768px) {
  .stats-grid { grid-template-columns: 1fr; }
  .doctors-grid { grid-template-columns: 1fr; }
  .filters-bar { flex-direction: column; }
}
</style>