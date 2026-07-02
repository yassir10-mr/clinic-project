<template>
  <div class="invoices-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Invoices</h1>
        <p class="subtitle">Manage billing and payments</p>
      </div>
      <button class="btn-add" @click="openModal()">
        <i class="fas fa-file-invoice"></i>
        Generate Invoice
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total Revenue</p>
          <h2 class="stat-value dark">{{ formatMoney(stats.total_revenue) }}</h2>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-dollar-sign"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Paid</p>
          <h2 class="stat-value green">{{ formatMoney(stats.paid) }}</h2>
        </div>
        <div class="stat-icon green-bg">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Pending</p>
          <h2 class="stat-value orange">{{ formatMoney(stats.pending) }}</h2>
        </div>
        <div class="stat-icon orange-bg">
          <i class="fas fa-clock"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Overdue</p>
          <h2 class="stat-value red">{{ formatMoney(stats.overdue) }}</h2>
        </div>
        <div class="stat-icon red-bg">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="table-card">
      <div class="table-header">
        <h3>All Invoices</h3>
      </div>

      <div class="table-container">
        <table v-if="invoices.length">
          <thead>
            <tr>
              <th>Invoice ID</th>
              <th>Patient Name</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices" :key="invoice.id_facture">
              <td>
                <span class="invoice-id">{{ formatInvoiceId(invoice.id_facture) }}</span>
              </td>
              <td>
            <td>
    <span class="patient-name">
        {{ invoice.patient_name || 'Unknown Patient' }}
    </span>
</td>
              </td>
              <td>
                <span class="date">{{ formatDate(invoice.date) }}</span>
              </td>
              <td>
                <span class="amount">{{ formatMoney(invoice.montant_total) }}</span>
              </td>
              <td>
                <span :class="['status-badge', getStatusClass(invoice.statut_paiement)]">
                  {{ invoice.statut_paiement }}
                </span>
              </td>
              <td>
                <div class="actions">
                  <button class="btn-view" @click="viewInvoice(invoice)" title="View">
                    <i class="fas fa-eye"></i>
                    View
                  </button>
                  <button class="btn-pdf" @click="downloadPDF(invoice)" title="PDF">
                    <i class="fas fa-download"></i>
                    PDF
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state">
          <i class="fas fa-file-invoice-dollar"></i>
          <p>No invoices found</p>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>{{ editingInvoice ? 'Edit Invoice' : 'Generate Invoice' }}</h2>
          <button class="btn-close" @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="saveInvoice">
          <div class="modal-body">
            <div class="form-group">
              <label>Consultation</label>
              <select v-model="form.id_consultation" required>
                <option value="">Select consultation</option>
                <option v-for="c in consultations" :key="c.id_consultation" :value="c.id_consultation">
                  {{ c.rendezVous?.patient?.prenom }} {{ c.rendezVous?.patient?.nom }} - 
                  {{ formatDate(c.date) }} - Dr. {{ c.medecin?.nom }}
                </option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Date</label>
                <input v-model="form.date" type="date" required />
              </div>
              <div class="form-group">
                <label>Amount</label>
                <input v-model="form.montant_total" type="number" step="0.01" min="0" required placeholder="0.00" />
              </div>
            </div>
            
                        <div class="form-group">
                <label>Payment Method</label>
                <select v-model="form.mode_paiement">
                    <option value="">Select method</option>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Check">Check</option>
                </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Payment Status</label>
                <select v-model="form.statut_paiement" required>
                  <option value="payé">Paid</option>
                  <option value="non payé">Unpaid</option>
                </select>
              </div>
              <div class="form-group">
                <label>Payment Method</label>
                <select v-model="form.mode_paiement">
                  <option value="">Select method</option>
                  <option value="Cash">Cash</option>
                  <option value="Card">Card</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                  <option value="Insurance">Insurance</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="closeModal">Cancel</button>
            <button type="submit" class="btn-save" :disabled="saving">
              {{ saving ? 'Saving...' : (editingInvoice ? 'Update' : 'Generate') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Invoice Modal -->
    <div v-if="showViewModal" class="modal-overlay" @click="showViewModal = false">
      <div class="modal modal-view" @click.stop>
        <div class="modal-header">
          <h2>Invoice Details</h2>
          <button class="btn-close" @click="showViewModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body view-body" v-if="selectedInvoice">
          <div class="invoice-view">
            <div class="invoice-header-view">
              <div>
                <h3>{{ formatInvoiceId(selectedInvoice.id_facture) }}</h3>
                <p class="invoice-date">{{ formatDate(selectedInvoice.date) }}</p>
              </div>
              <span :class="['status-badge-lg', getStatusClass(selectedInvoice.statut_paiement)]">
                {{ selectedInvoice.statut_paiement }}
              </span>
            </div>
            <div class="invoice-details">
              <div class="detail-row">
                <span class="label">Patient</span>
                <span class="value">
                  {{ selectedInvoice.consultation?.rendezVous?.patient?.prenom }}
                  {{ selectedInvoice.consultation?.rendezVous?.patient?.nom }}
                </span>
              </div>
              <div class="detail-row">
                <span class="label">Doctor</span>
                <span class="value">Dr. {{ selectedInvoice.consultation?.medecin?.nom }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Consultation Date</span>
                <span class="value">{{ formatDate(selectedInvoice.consultation?.date) }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Payment Method</span>
                <span class="value">{{ selectedInvoice.mode_paiement || 'N/A' }}</span>
              </div>
              <div class="detail-divider"></div>
              <div class="detail-row total">
                <span class="label">Total Amount</span>
                <span class="value amount">{{ formatMoney(selectedInvoice.montant_total) }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="showViewModal = false">Close</button>
          <button class="btn-save" @click="downloadPDF(selectedInvoice)">
            <i class="fas fa-download"></i>
            Download PDF
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getFactures, addFacture, updateFacture, deleteFacture, getConsultations } from '@/services/api.js'

const invoices = ref([])
const consultations = ref([])
const showModal = ref(false)
const showViewModal = ref(false)
const editingInvoice = ref(null)
const selectedInvoice = ref(null)
const saving = ref(false)
const getPatientName = (invoice) => {
    const patient = invoice.consultation?.rendezVous?.patient
    if (patient) {
        return `${patient.prenom || ''} ${patient.nom || ''}`.trim()
    }
    return 'Unknown Patient'
}
const form = ref({
  id_consultation: '',
  date: '',
  montant_total: '',
  statut_paiement: 'non payé',
  mode_paiement: ''
})

const stats = computed(() => {
  const total_revenue = invoices.value.reduce((sum, inv) => sum + Number(inv.montant_total || 0), 0)
  const paid = invoices.value
    .filter(inv => inv.statut_paiement === 'payé')
    .reduce((sum, inv) => sum + Number(inv.montant_total || 0), 0)
  const pending = invoices.value
    .filter(inv => inv.statut_paiement === 'non payé')
    .reduce((sum, inv) => sum + Number(inv.montant_total || 0), 0)
  // Overdue = unpaid invoices older than 30 days
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)
  const overdue = invoices.value
    .filter(inv => inv.statut_paiement === 'non payé' && new Date(inv.date) < thirtyDaysAgo)
    .reduce((sum, inv) => sum + Number(inv.montant_total || 0), 0)
  
  return { total_revenue, paid, pending, overdue }
})

const loadData = async () => {
  try {
    const [facturesRes, consultationsRes] = await Promise.all([
      getFactures(),
      getConsultations()
    ])
    invoices.value = facturesRes.data.data || []
    consultations.value = consultationsRes.data.data || []
  } catch (err) {
    console.error('Error loading data:', err)
  }
}

const formatMoney = (amount) => {
  if (!amount) return '0.00 DH'
  return Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DH'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-GB')
}

const formatInvoiceId = (id) => {
  return `INV-${String(id).padStart(3, '0')}`
}

const getStatusClass = (status) => {
  const classes = {
    'payé': 'paid',
    'non payé': 'unpaid'
  }
  return classes[status] || 'unpaid'
}

const viewInvoice = (invoice) => {
  selectedInvoice.value = invoice
  showViewModal.value = true
}

const downloadPDF = (invoice) => {
  // Placeholder for PDF generation
  alert(`PDF download for ${formatInvoiceId(invoice.id_facture)} - Coming soon!`)
}

const openModal = (invoice = null) => {
  if (invoice) {
    editingInvoice.value = invoice
    form.value = { ...invoice }
  } else {
    editingInvoice.value = null
    form.value = {
      id_consultation: '',
      date: new Date().toISOString().split('T')[0],
      montant_total: '',
      statut_paiement: 'non payé',
      mode_paiement: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingInvoice.value = null
}

const saveInvoice = async () => {
  saving.value = true
  try {
    if (editingInvoice.value) {
      await updateFacture(editingInvoice.value.id_facture, form.value)
    } else {
      await addFacture(form.value)
    }
    closeModal()
    loadData()
  } catch (err) {
    console.error('Error saving invoice:', err)
    alert('Error saving invoice. Please try again.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.invoices-page {
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
  font-size: 28px;
  font-weight: 700;
}

.stat-value.dark {
  color: #1e293b;
}

.stat-value.green {
  color: #10b981;
}

.stat-value.orange {
  color: #f59e0b;
}

.stat-value.red {
  color: #ef4444;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.blue {
  background: #eff6ff;
  color: #2563eb;
}

.stat-icon.green-bg {
  background: #d1fae5;
  color: #10b981;
}

.stat-icon.orange-bg {
  background: #fef3c7;
  color: #f59e0b;
}

.stat-icon.red-bg {
  background: #fee2e2;
  color: #ef4444;
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
  font-size: 13px;
  font-weight: 600;
  color: #475569;
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

/* Invoice ID */
.invoice-id {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

/* Patient Name */
.patient-name {
  color: #475569;
  font-size: 14px;
}

/* Date */
.date {
  color: #475569;
  font-size: 14px;
}

/* Amount */
.amount {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

/* Status Badge */
.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: lowercase;
}

.status-badge.paid {
  background: #d1fae5;
  color: #059669;
}

.status-badge.unpaid {
  background: #fef3c7;
  color: #d97706;
}

.status-badge-lg {
  padding: 8px 20px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge-lg.paid {
  background: #d1fae5;
  color: #059669;
}

.status-badge-lg.unpaid {
  background: #fef3c7;
  color: #d97706;
}

/* Actions */
.actions {
  display: flex;
  gap: 8px;
}

.btn-view, .btn-pdf {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-view:hover {
  background: #eff6ff;
  border-color: #2563eb;
  color: #2563eb;
}

.btn-pdf:hover {
  background: #fef2f2;
  border-color: #ef4444;
  color: #ef4444;
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

.modal-view {
  max-width: 500px;
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

.view-body {
  padding: 0 24px 24px;
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
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-save:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Invoice View */
.invoice-view {
  background: #f8fafc;
  border-radius: 12px;
  padding: 24px;
}

.invoice-header-view {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.invoice-header-view h3 {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.invoice-date {
  color: #475569;
  font-size: 14px;
  margin: 0;
}

.invoice-details {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-row .label {
  color: #475569;
  font-size: 14px;
}

.detail-row .value {
  color: #1e293b;
  font-size: 14px;
  font-weight: 500;
}

.detail-row.total .label {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
}

.detail-row.total .value.amount {
  font-size: 24px;
  font-weight: 700;
  color: #2563eb;
}

.detail-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 8px 0;
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
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
}
</style>