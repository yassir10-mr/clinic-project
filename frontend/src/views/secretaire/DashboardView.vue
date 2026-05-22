<<template>
  <div class="dashboard">
    <div class="page-header">
      <div>
        <h1>Dashboard</h1>
        <p class="welcome-text">Welcome back, {{ userName }}</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total Patients</p>
          <h2 class="stat-value">{{ stats.total_patients || 0 }}</h2>
          <p class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 12% from last month
          </p>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-users"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Today's Appointments</p>
          <h2 class="stat-value">{{ stats.rdv_aujourdhui || 0 }}</h2>
          <p class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 8% from last month
          </p>
        </div>
        <div class="stat-icon green">
          <i class="fas fa-calendar-check"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Monthly Revenue</p>
          <h2 class="stat-value">{{ formatMoney(stats.montant_total_factures) }}</h2>
          <p class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 15% from last month
          </p>
        </div>
        <div class="stat-icon teal">
          <i class="fas fa-dollar-sign"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Active Doctors</p>
          <h2 class="stat-value">{{ stats.total_medecins || 0 }}</h2>
          <p class="stat-change neutral">
            <i class="fas fa-minus"></i> No change
          </p>
        </div>
        <div class="stat-icon purple">
          <i class="fas fa-user-md"></i>
        </div>
      </div>
    </div>

    <!-- Today's Appointments -->
    <div class="section-card">
      <div class="section-header">
        <h3>Today's Appointments</h3>
        <router-link to="/secretaire/appointments" class="view-all">
          View All
        </router-link>
      </div>

      <div v-if="todayAppointments.length" class="appointments-list">
        <div 
          v-for="rdv in todayAppointments" 
          :key="rdv.id_rdv"
          class="appointment-item"
        >
          <div class="appointment-time">
            <div class="time-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="time-info">
              <p class="time">{{ formatTime(rdv.heure) }}</p>
              <p class="doctor">Dr. {{ rdv.medecin?.nom }}</p>
            </div>
          </div>
          <div class="appointment-patient">
            <p class="patient-name">{{ rdv.patient?.prenom }} {{ rdv.patient?.nom }}</p>
            <p class="motif">{{ rdv.motif || 'General Checkup' }}</p>
          </div>
          <span :class="['status-badge', getStatusClass(rdv.statut)]">
            {{ rdv.statut }}
          </span>
        </div>
      </div>
      <div v-else class="empty-state">
        <i class="fas fa-calendar-day"></i>
        <p>No appointments today</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onActivated, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { getDashboardStats, getRendezVous } from '@/services/api.js'

const route = useRoute()

const user = ref(JSON.parse(localStorage.getItem('secretaire_user') || '{}'))

const userName = computed(() => {
  return `${user.value.prenom || ''} ${user.value.nom || ''}`.trim() || 'Secrétaire'
})

const stats = ref({})
const todayAppointments = ref([])

const loadDashboard = async () => {
  try {
    const statsRes = await getDashboardStats()
    stats.value = statsRes.data.data || {}
    
    const rdvsRes = await getRendezVous()
    const allRdvs = rdvsRes.data.data || []
    
    console.log('ALL RDVs received:', allRdvs) // <-- ADD THIS
    console.log('Today date:', new Date().toISOString().split('T')[0]) // <-- ADD THIS
    
    // Filter today's appointments
    const today = new Date().toISOString().split('T')[0]
    todayAppointments.value = allRdvs.filter(rdv => {
      console.log('Checking rdv:', rdv.id_rdv, 'date:', rdv.date_rdv) // <-- ADD THIS
      return rdv.date_rdv?.startsWith(today)
    })
    
    console.log('Filtered today appointments:', todayAppointments.value) // <-- ADD THIS
  } catch (err) {
    console.error('Error loading dashboard:', err)
  }
}

const formatMoney = (amount) => {
  if (!amount) return '0MAD'
  return 'MAD' + Number(amount).toLocaleString()
}

const formatTime = (time) => {
  if (!time) return '--:--'
  return time.substring(0, 5)
}

const getStatusClass = (status) => {
  const classes = {
    'en attente': 'en-attente',
    'confirmé': 'confirmé',
    'annulé': 'annulé',
    'terminé': 'terminé'
  }
  return classes[status] || 'en-attente'
}

// Refresh when tab becomes visible again
const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible' && route.path === '/secretaire/dashboard') {
    loadDashboard()
  }
}

let refreshInterval = null

onMounted(() => {
  loadDashboard()
  
  // Reload when tab becomes visible
  document.addEventListener('visibilitychange', handleVisibilityChange)
  
  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(() => {
    if (route.path === '/secretaire/dashboard') {
      loadDashboard()
    }
  }, 30000)
})

// Reload when navigating back to this page (if using <KeepAlive>)
onActivated(() => {
  loadDashboard()
})

onUnmounted(() => {
  clearInterval(refreshInterval)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})
</script>

<style scoped>
.dashboard {
  max-width: 1200px;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.welcome-text {
  color: #64748b;
  font-size: 14px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
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

.stat-info {
  flex: 1;
}

.stat-label {
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.stat-change {
  font-size: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
}

.stat-change.positive {
  color: #10b981;
}

.stat-change.neutral {
  color: #94a3b8;
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

.stat-icon.blue {
  background: #dbeafe;
  color: #2563eb;
}

.stat-icon.green {
  background: #d1fae5;
  color: #059669;
}

.stat-icon.teal {
  background: #ccfbf1;
  color: #0d9488;
}

.stat-icon.purple {
  background: #f3e8ff;
  color: #9333ea;
}

/* Section Card */
.section-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
}

.view-all {
  color: #2563eb;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

.view-all:hover {
  text-decoration: underline;
}

/* Appointments List */
.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.appointment-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 10px;
  transition: background 0.2s;
}

.appointment-item:hover {
  background: #f1f5f9;
}

.appointment-time {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 140px;
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

.time-info {
  display: flex;
  flex-direction: column;
}

.time {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
  margin: 0;
}

.doctor {
  color: #64748b;
  font-size: 12px;
  margin: 2px 0 0;
}

.appointment-patient {
  flex: 1;
}

.patient-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
  margin: 0;
}

.motif {
  color: #64748b;
  font-size: 13px;
  margin: 2px 0 0;
}

.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
  white-space: nowrap;
}

.status-badge.en-attente {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.confirmé {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.annulé {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.terminé {
  background: #dbeafe;
  color: #1e40af;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 12px;
  display: block;
}

.empty-state p {
  font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .appointment-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>