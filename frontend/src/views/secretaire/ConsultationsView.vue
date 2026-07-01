<<template>
  <div class="consultations-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Completed Consultations</h1>
        <p class="subtitle">View consultations and manage invoices</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total Consultations</p>
          <h2 class="stat-value">{{ stats.total || 0 }}</h2>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-stethoscope"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Today</p>
          <h2 class="stat-value">{{ stats.today || 0 }}</h2>
        </div>
        <div class="stat-icon green">
          <i class="fas fa-calendar-day"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">With Prescription</p>
          <h2 class="stat-value">{{ stats.withOrdonnance || 0 }}</h2>
        </div>
        <div class="stat-icon purple">
          <i class="fas fa-prescription"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Pending Invoices</p>
          <h2 class="stat-value">{{ stats.pendingInvoices || 0 }}</h2>
        </div>
        <div class="stat-icon yellow">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search patient, doctor..." 
        />
      </div>
      <select v-model="filterDate" class="filter-select">
        <option value="">All Dates</option>
        <option value="today">Today</option>
        <option value="week">This Week</option>
        <option value="month">This Month</option>
      </select>
    </div>

    <!-- Consultations Table -->
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Diagnosis</th>
            <th>Treatment</th>
            <th>Prescription</th>
            <th>Invoice</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="consult in filteredConsultations" :key="consult.id_consultation">
            <td>
              <div class="date-cell">
                <span class="date">{{ formatDate(consult.date) }}</span>
                <span class="time">{{ consult.rendez_vous?.heure?.substring(0,5) || '--:--' }}</span>
              </div>
            </td>
            <td>
              <div class="patient-cell">
                <div class="avatar">{{ getInitials(consult.rendez_vous?.patient) }}</div>
                <div>
<p class="name">{{ consult.rendez_vous?.patient?.prenom }} {{ consult.rendez_vous?.patient?.nom }}</p>
                  <p class="phone">{{ consult.rendez_vous?.patient?.telephone }}</p>
                </div>
              </div>
            </td>
            <td>
              <span class="doctor-badge">
                <i class="fas fa-user-md"></i>
                Dr. {{ consult.medecin?.nom }}
              </span>
            </td>
            <td>
              <p class="truncate" :title="consult.diagnostic">{{ consult.diagnostic || '-' }}</p>
            </td>
            <td>
              <p class="truncate" :title="consult.traitement">{{ consult.traitement || '-' }}</p>
            </td>
            <td>
              <span :class="['badge', consult.ordonnance ? 'badge-success' : 'badge-empty']">
                <i :class="consult.ordonnance ? 'fas fa-check' : 'fas fa-minus'"></i>
                {{ consult.ordonnance ? 'Yes' : 'No' }}
              </span>
            </td>
            <td>
              <span :class="['badge', consult.facture ? 'badge-success' : 'badge-warning']">
                <i :class="consult.facture ? 'fas fa-check' : 'fas fa-clock'"></i>
                {{ consult.facture ? consult.facture.statut_paiement : 'Pending' }}
              </span>
            </td>
            <td>
              <div class="actions">
                <button @click="viewDetails(consult)" class="btn-icon btn-view" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button 
                  v-if="!consult.facture" 
                  @click="openInvoiceModal(consult)" 
                  class="btn-icon btn-invoice" 
                  title="Create Invoice"
                >
                  <i class="fas fa-file-invoice-dollar"></i>
                </button>
                <button 
                  v-else
                  @click="viewInvoice(consult.facture)" 
                  class="btn-icon btn-view" 
                  title="View Invoice"
                >
                  <i class="fas fa-file-alt"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="!filteredConsultations.length" class="empty-state">
        <i class="fas fa-stethoscope"></i>
        <p>No consultations found</p>
      </div>
    </div>

    <!-- View Details Modal -->
    <div v-if="showDetails" class="modal-overlay" @click="showDetails = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Consultation Details</h2>
          <button class="btn-close" @click="showDetails = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body details-body" v-if="selectedConsult">
          <div class="detail-section">
            <h4><i class="fas fa-user"></i> Patient</h4>

            <p>{{ selectedConsult.rendez_vous?.patient?.prenom }} {{ selectedConsult.rendez_vous?.patient?.nom }}</p>
          </div>

          <div class="detail-section">
            <h4><i class="fas fa-phone"></i> Telephone</h4>
            <p >{{ selectedConsult.rendez_vous?.patient?.telephone }}</p>
          </div>






          
          <div class="detail-section">
            <h4><i class="fas fa-user-md"></i> Doctor</h4>
            <p>Dr. {{ selectedConsult.medecin?.nom }} {{ selectedConsult.medecin?.prenom }}</p>
            <p class="text-muted">{{ selectedConsult.medecin?.specialite }}</p>
          </div>

          <div class="detail-section">
            <h4><i class="fas fa-calendar"></i> Date</h4>
            <p>{{ formatDate(selectedConsult.date) }}</p>
          </div>

          <div class="detail-section">
            <h4><i class="fas fa-stethoscope"></i> Diagnostics</h4>
            <p>{{ selectedConsult.diagnostic }}</p>
          </div>

          <div class="detail-section">
            <h4><i class="fas fa-pills"></i> Treatment</h4>
            <p>{{ selectedConsult.traitement }}</p>
          </div>

          <div class="detail-section" v-if="selectedConsult.observations">
            <h4><i class="fas fa-notes-medical"></i> Observations</h4>
            <p>{{ selectedConsult.observations }}</p>
          </div>

          <div class="detail-section" v-if="selectedConsult.ordonnance">
            <h4><i class="fas fa-prescription"></i> Prescription</h4>
            <div class="prescription-box">
              <p><strong>Medications:</strong> {{ selectedConsult.ordonnance.medicaments }}</p>
              <p><strong>Dosage:</strong> {{ selectedConsult.ordonnance.posologie }}</p>
            </div>
          </div>

          <div class="detail-section" v-if="selectedConsult.facture">
            <h4><i class="fas fa-file-invoice-dollar"></i> Invoice</h4>
            <div class="invoice-box">
              <p><strong>Amount:</strong> {{ selectedConsult.facture.montant_total }} DH</p>

              <p><strong>Status:</strong> 
                <span :class="selectedConsult.facture.statut_paiement === 'payé' ? 'text-success' : 'text-warning'">
                  {{ selectedConsult.facture.statut_paiement }}
                </span>
              </p>
              <p v-if="selectedConsult.facture.mode_paiement"><strong>Method:</strong> {{ selectedConsult.facture.mode_paiement }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Invoice Modal -->
    <div v-if="showInvoiceModal" class="modal-overlay" @click="showInvoiceModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Create Invoice</h2>
          <button class="btn-close" @click="showInvoiceModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="saveInvoice">
          <div class="modal-body">
            <div class="form-group">
              <label>Patient</label>
              <input :value="invoiceForm.patientName" type="text" disabled />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Amount (DH) <span class="required">*</span></label>
                <input 
                  v-model="invoiceForm.montant_total" 
                  type="number" 
                  min="0" 
                  step="0.01" 
                  required
                  placeholder="0.00"
                />
              </div>
              <div class="form-group">
                <label>Payment Status</label>
                <select v-model="invoiceForm.statut_paiement">
                  <option value="non payé">Unpaid</option>
                  <option value="payé">Paid</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Payment Method</label>
              <select v-model="invoiceForm.mode_paiement">
                <option value="">Select method</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="insurance">Insurance</option>
                <option value="cheque">Cheque</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="showInvoiceModal = false">Cancel</button>
            <button type="submit" class="btn-save" :disabled="saving">
              {{ saving ? 'Creating...' : 'Create Invoice' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getConsultations, getFactures, addFacture } from '@/services/api.js'

const consultations = ref([])
const showDetails = ref(false)
const showInvoiceModal = ref(false)
const selectedConsult = ref(null)
const saving = ref(false)
const searchQuery = ref('')
const filterDate = ref('')

const invoiceForm = ref({
  id_consultation: '',
  patientName: '',
  date: '',
  montant_total: '',
  statut_paiement: 'non payé',
  mode_paiement: ''
})

const stats = computed(() => {
  const total = consultations.value.length
  const today = new Date().toISOString().split('T')[0]
  const todayCount = consultations.value.filter(c => c.date?.startsWith(today)).length
  const withOrdonnance = consultations.value.filter(c => c.ordonnance).length
  const pendingInvoices = consultations.value.filter(c => !c.facture).length
  return { total, today: todayCount, withOrdonnance, pendingInvoices }
})

const filteredConsultations = computed(() => {
  let result = [...consultations.value]
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(c => 
      c.rendezVous?.patient?.nom?.toLowerCase().includes(q) ||
      c.rendezVous?.patient?.prenom?.toLowerCase().includes(q) ||
      c.medecin?.nom?.toLowerCase().includes(q) ||
      c.diagnostic?.toLowerCase().includes(q)
    )
  }
  
  if (filterDate.value) {
    const today = new Date()
    result = result.filter(c => {
      const cDate = new Date(c.date)
      if (filterDate.value === 'today') {
        return cDate.toDateString() === today.toDateString()
      }
      if (filterDate.value === 'week') {
        const weekAgo = new Date(today - 7 * 24 * 60 * 60 * 1000)
        return cDate >= weekAgo
      }
      if (filterDate.value === 'month') {
        return cDate.getMonth() === today.getMonth() && cDate.getFullYear() === today.getFullYear()
      }
      return true
    })
  }
  
  return result.sort((a, b) => new Date(b.date) - new Date(a.date))
})

const loadData = async () => {
  try {
    const res = await getConsultations()
    console.log('=== FULL API RESPONSE ===')
    console.log(JSON.stringify(res.data, null, 2))
    
    consultations.value = res.data.data || []
    
    // Debug first consultation
    if (consultations.value[0]) {
      console.log('First consult keys:', Object.keys(consultations.value[0]))
      console.log('rendezVous:', consultations.value[0].rendezVous)
      console.log('rendez_vous:', consultations.value[0].rendez_vous)
    }
  } catch (err) {
    console.error('Error loading consultations:', err)
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getInitials = (patient) => {
  if (!patient) return '?'
  return `${patient.prenom?.[0] || ''}${patient.nom?.[0] || ''}`.toUpperCase()
}

const viewDetails = (consult) => {
  selectedConsult.value = consult
  showDetails.value = true
}

const viewInvoice = (facture) => {
  // Could open invoice details or print
  console.log('Invoice:', facture)
}

const openInvoiceModal = (consult) => {
  invoiceForm.value = {
    id_consultation: consult.id_consultation,
    patientName: `${consult.rendezVous?.patient?.prenom} ${consult.rendezVous?.patient?.nom}`,
    date: consult.date,
    montant_total: '',
    statut_paiement: 'non payé',
    mode_paiement: ''
  }
  showInvoiceModal.value = true
}

const saveInvoice = async () => {
  saving.value = true
  try {
    await addFacture({
      id_consultation: invoiceForm.value.id_consultation,
      date: invoiceForm.value.date,
      montant_total: parseFloat(invoiceForm.value.montant_total),
      statut_paiement: invoiceForm.value.statut_paiement,
      mode_paiement: invoiceForm.value.mode_paiement
    })
    showInvoiceModal.value = false
    loadData()
  } catch (err) {
    console.error('Error creating invoice:', err)
    alert('Error creating invoice: ' + (err.response?.data?.message || err.message))
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.consultations-page {
  max-width: 1400px;
}

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
.stat-icon.yellow { background: #fef3c7; color: #d97706; }

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
  min-width: 150px;
}

.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 16px 20px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  color: #1e293b;
}

.data-table tr:hover {
  background: #f8fafc;
}

.date-cell { display: flex; flex-direction: column; gap: 2px; }
.date { font-weight: 600; }
.time { font-size: 12px; color: #475569; }

.patient-cell { display: flex; align-items: center; gap: 12px; }
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #dbeafe;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 13px;
}
.name { font-weight: 600; margin: 0; }
.phone { font-size: 12px; color: #475569; margin: 2px 0 0; }

.doctor-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #f0fdf4;
  color: #166534;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
}

.truncate {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin: 0;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-empty { background: #f1f5f9; color: #475569; }

.actions { display: flex; gap: 8px; }

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-view { background: #dbeafe; color: #2563eb; }
.btn-view:hover { background: #bfdbfe; }

.btn-invoice { background: #fef3c7; color: #d97706; }
.btn-invoice:hover { background: #fde68a; }

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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
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
  transition: all 0.2s;
}

.btn-close:hover { background: #f1f5f9; color: #475569; }

.modal-body { padding: 24px; }
.details-body { padding-bottom: 24px; }

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 16px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.required { color: #ef4444; }

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

.form-group input:disabled {
  opacity: 0.7;
  cursor: not-allowed;
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
}

.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }

.detail-section {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f5f9;
}

.detail-section:last-child { border-bottom: none; margin-bottom: 0; }

.detail-section h4 {
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-section p { margin: 4px 0; color: #1e293b; }
.text-muted { color: #475569; font-size: 14px; }
.text-success { color: #059669; }
.text-warning { color: #d97706; }

.prescription-box,
.invoice-box {
  background: #f8fafc;
  border-radius: 10px;
  padding: 16px;
  border-left: 4px solid #2563eb;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .form-row { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .stats-grid { grid-template-columns: 1fr; }
  .data-table { display: block; overflow-x: auto; }
}
</style>