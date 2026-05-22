<<template>
  <div class="page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Invoices</h1>
        <p class="page-subtitle">Manage billing and payments</p>
      </div>
      <button @click="openAddModal" class="btn-generate">
        <i class="fas fa-file-invoice"></i>
        Generate Invoice
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Total Revenue</span>
          <span class="stat-value">MAD{{ formatNumber(montantTotal) }}</span>
        </div>
        <div class="stat-icon blue">
          <i class="fas fa-dollar-sign"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Paid</span>
          <span class="stat-value green">MAD{{ formatNumber(montantPaye) }}</span>
        </div>
        <div class="stat-icon green">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Pending</span>
          <span class="stat-value orange">MAD{{ formatNumber(montantPending) }}</span>
        </div>
        <div class="stat-icon orange">
          <i class="fas fa-clock"></i>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Overdue</span>
          <span class="stat-value red">MAD{{ formatNumber(montantOverdue) }}</span>
        </div>
        <div class="stat-icon red">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="table-container">
      <h3 class="table-title">All Invoices</h3>
      
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>Loading invoices...</span>
      </div>

      <table v-else-if="filteredFactures.length > 0" class="data-table">
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
          <tr v-for="facture in filteredFactures" :key="facture.id_facture">
            <td>
              <span class="invoice-id">INV-{{ String(facture.id_facture).padStart(3, '0') }}</span>
            </td>
            <td>
              <div class="patient-name">
                {{ facture.consultation?.rendez_vous?.patient?.prenom || '' }} 
                {{ facture.consultation?.rendez_vous?.patient?.nom || 'Unknown' }}
              </div>
            </td>
            <td>{{ formatDate(facture.date) }}</td>
            <td class="amount">MAD{{ facture.montant_total }}</td>
            <td>
              <span :class="['status-badge', getStatusClass(facture.statut_paiement)]">
                {{ getStatusLabel(facture.statut_paiement) }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <button @click="viewFacture(facture)" class="btn-action btn-view">
                  <i class="fas fa-eye"></i>
                  View
                </button>
                <button @click="downloadPDF(facture)" class="btn-action btn-pdf">
                  <i class="fas fa-download"></i>
                  PDF
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty-state">
        <i class="fas fa-file-invoice-dollar empty-icon"></i>
        <p>No invoices found</p>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ isEditing ? '✏️ Edit Invoice' : '➕ Generate Invoice' }}</h2>
          <button @click="closeModal" class="btn-close">&times;</button>
        </div>
        <form @submit.prevent="saveFacture" class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label>Consultation ID *</label>
              <input v-model="form.id_consultation" type="number" placeholder="e.g. 1" required />
            </div>
            <div class="form-group">
              <label>Date *</label>
              <input v-model="form.date" type="date" required />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Amount *</label>
              <input v-model="form.montant_total" type="number" step="0.01" placeholder="250.00" required />
            </div>
            <div class="form-group">
              <label>Status</label>
              <select v-model="form.statut_paiement">
                <option value="non payé">Pending</option>
                <option value="payé">Paid</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Payment Method</label>
            <select v-model="form.mode_paiement">
              <option value="">-- Select --</option>
              <option value="Cash">Cash</option>
              <option value="Credit Card">Credit Card</option>
              <option value="Check">Check</option>
              <option value="Bank Transfer">Bank Transfer</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">Cancel</button>
            <button type="submit" class="btn-save">
              <i class="fas fa-check"></i>
              {{ isEditing ? 'Update' : 'Generate' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="showViewModal = false">
      <div class="modal modal-view">
        <div class="modal-header">
          <h2>Invoice Details</h2>
          <button @click="showViewModal = false" class="btn-close">&times;</button>
        </div>
        <div class="invoice-details" v-if="selectedFacture">
          <div class="detail-row">
            <span class="detail-label">Invoice ID:</span>
            <span class="detail-value">INV-{{ String(selectedFacture.id_facture).padStart(3, '0') }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Patient:</span>
            <span class="detail-value">
              {{ selectedFacture.consultation?.rendez_vous?.patient?.prenom || '' }} 
              {{ selectedFacture.consultation?.rendez_vous?.patient?.nom || 'Unknown' }}
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Date:</span>
            <span class="detail-value">{{ formatDate(selectedFacture.date) }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Amount:</span>
            <span class="detail-value amount">MAD{{ selectedFacture.montant_total }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Status:</span>
            <span :class="['status-badge', getStatusClass(selectedFacture.statut_paiement)]">
              {{ getStatusLabel(selectedFacture.statut_paiement) }}
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Payment Method:</span>
            <span class="detail-value">{{ selectedFacture.mode_paiement || 'N/A' }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showViewModal = false" class="btn-cancel">Close</button>
          <button @click="downloadPDF(selectedFacture)" class="btn-save">
            <i class="fas fa-download"></i>
            Download PDF
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getFactures, addFacture, updateFacture, deleteFacture as apiDeleteFacture } from '@/services/api.js';

const factures = ref([]);
const loading = ref(false);
const showModal = ref(false);
const showViewModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const selectedFacture = ref(null);
const searchQuery = ref('');

const form = ref({
  id_consultation: '',
  date: '',
  montant_total: '',
  statut_paiement: 'non payé',
  mode_paiement: ''
});

onMounted(() => loadFactures());

const loadFactures = async () => {
  loading.value = true;
  try {
    const response = await getFactures();
    factures.value = response.data.data || [];
  } catch (error) {
    console.error('Error:', error);
    alert('Unable to load invoices');
  } finally {
    loading.value = false;
  }
};

const filteredFactures = computed(() => {
  if (!searchQuery.value) return factures.value;
  const query = searchQuery.value.toLowerCase();
  return factures.value.filter(f => {
    const patientName = `${f.consultation?.rendez_vous?.patient?.prenom || ''} ${f.consultation?.rendez_vous?.patient?.nom || ''}`.toLowerCase();
    const invoiceId = `inv-${f.id_facture}`.toLowerCase();
    return patientName.includes(query) || invoiceId.includes(query);
  });
});

const montantTotal = computed(() => {
  return factures.value.reduce((sum, f) => sum + parseFloat(f.montant_total || 0), 0);
});

const montantPaye = computed(() => {
  return factures.value
    .filter(f => f.statut_paiement === 'payé')
    .reduce((sum, f) => sum + parseFloat(f.montant_total || 0), 0);
});

const montantPending = computed(() => {
  return factures.value
    .filter(f => f.statut_paiement === 'non payé')
    .reduce((sum, f) => sum + parseFloat(f.montant_total || 0), 0);
});

const montantOverdue = computed(() => {
  const today = new Date();
  return factures.value
    .filter(f => {
      if (f.statut_paiement === 'payé') return false;
      const factureDate = new Date(f.date);
      const diffDays = Math.floor((today - factureDate) / (1000 * 60 * 60 * 24));
      return diffDays > 30;
    })
    .reduce((sum, f) => sum + parseFloat(f.montant_total || 0), 0);
});

const formatNumber = (num) => {
  return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const getStatusClass = (status) => {
  switch (status) {
    case 'payé': return 'status-paid';
    case 'non payé': return 'status-pending';
    default: return 'status-pending';
  }
};

const getStatusLabel = (status) => {
  switch (status) {
    case 'payé': return 'paid';
    case 'non payé': return 'pending';
    default: return 'pending';
  }
};

const openAddModal = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showModal.value = true;
};

const editFacture = (facture) => {
  isEditing.value = true;
  editingId.value = facture.id_facture;
  form.value = {
    id_consultation: facture.id_consultation,
    date: facture.date,
    montant_total: facture.montant_total,
    statut_paiement: facture.statut_paiement,
    mode_paiement: facture.mode_paiement || ''
  };
  showModal.value = true;
};

const viewFacture = (facture) => {
  selectedFacture.value = facture;
  showViewModal.value = true;
};

const saveFacture = async () => {
  try {
    if (isEditing.value) {
      await updateFacture(editingId.value, form.value);
      alert('✅ Invoice updated successfully!');
    } else {
      await addFacture(form.value);
      alert('✅ Invoice generated successfully!');
    }
    closeModal();
    await loadFactures();
  } catch (error) {
    console.error('Full error:', error);
    
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      let message = '❌ Validation Error:\n\n';
      for (let field in errors) {
        message += `• ${field}: ${errors[field].join(', ')}\n`;
      }
      alert(message);
    } else if (error.response?.status === 401) {
      alert('❌ Session expired. Please login again.');
      localStorage.removeItem('token');
      window.location.href = '/login';
    } else {
      alert('❌ Error: ' + (error.response?.data?.message || 'Unknown error'));
    }
  }
};

const downloadPDF = (facture) => {
  alert(`PDF download for INV-${String(facture.id_facture).padStart(3, '0')} - Feature coming soon!`);
};

const closeModal = () => {
  showModal.value = false;
  showViewModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    id_consultation: '',
    date: '',
    montant_total: '',
    statut_paiement: 'non payé',
    mode_paiement: ''
  };
};
</script>

<style scoped>
.page {
  padding: 0;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 28px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #94a3b8;
  margin: 0;
}

.btn-generate {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #2563eb;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-generate:hover {
  background: #1d4ed8;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-label {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 500;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.stat-value.green { color: #10b981; }
.stat-value.orange { color: #f59e0b; }
.stat-value.red { color: #ef4444; }

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.blue { background: #eff6ff; color: #2563eb; }
.stat-icon.green { background: #ecfdf5; color: #10b981; }
.stat-icon.orange { background: #fffbeb; color: #f59e0b; }
.stat-icon.red { background: #fef2f2; color: #ef4444; }

.table-container {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.table-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 20px 0;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead th {
  text-align: left;
  padding: 12px 16px;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  border-bottom: 1px solid #f1f5f9;
}

.data-table tbody td {
  padding: 16px;
  font-size: 14px;
  color: #475569;
  border-bottom: 1px solid #f8fafc;
}

.data-table tbody tr:hover {
  background: #f8fafc;
}

.invoice-id {
  font-weight: 600;
  color: #1e293b;
}

.patient-name {
  font-weight: 500;
  color: #334155;
}

.amount {
  font-weight: 600;
  color: #1e293b;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: lowercase;
}

.status-paid {
  background: #d1fae5;
  color: #065f46;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-overdue {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  transition: all 0.2s;
}

.btn-action:hover {
  background: #f8fafc;
}

.btn-view:hover {
  border-color: #2563eb;
  color: #2563eb;
}

.btn-pdf:hover {
  border-color: #10b981;
  color: #10b981;
}

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
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

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
  backdrop-filter: blur(4px);
}

.modal {
  background: white;
  border-radius: 16px;
  width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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
  margin: 0;
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
  margin-bottom: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 6px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  color: #1e293b;
  background: white;
  transition: border-color 0.2s;
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
  padding: 0 24px 24px;
}

.btn-cancel {
  padding: 10px 20px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f8fafc;
}

.btn-save {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-save:hover {
  background: #1d4ed8;
}

.invoice-details {
  padding: 24px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 14px;
  color: #1e293b;
  font-weight: 600;
}

.detail-value.amount {
  font-size: 18px;
  color: #2563eb;
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
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>




<style scoped>
.page {
  padding: 0;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 28px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #94a3b8;
  margin: 0;
}

.btn-generate {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #2563eb;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-generate:hover {
  background: #1d4ed8;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-label {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 500;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.stat-value.green { color: #10b981; }
.stat-value.orange { color: #f59e0b; }
.stat-value.red { color: #ef4444; }

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.blue { background: #eff6ff; color: #2563eb; }
.stat-icon.green { background: #ecfdf5; color: #10b981; }
.stat-icon.orange { background: #fffbeb; color: #f59e0b; }
.stat-icon.red { background: #fef2f2; color: #ef4444; }

.table-container {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.table-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 20px 0;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead th {
  text-align: left;
  padding: 12px 16px;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  border-bottom: 1px solid #f1f5f9;
}

.data-table tbody td {
  padding: 16px;
  font-size: 14px;
  color: #475569;
  border-bottom: 1px solid #f8fafc;
}

.data-table tbody tr:hover {
  background: #f8fafc;
}

.invoice-id {
  font-weight: 600;
  color: #1e293b;
}

.patient-name {
  font-weight: 500;
  color: #334155;
}

.amount {
  font-weight: 600;
  color: #1e293b;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: lowercase;
}

.status-paid {
  background: #d1fae5;
  color: #065f46;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-overdue {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  transition: all 0.2s;
}

.btn-action:hover {
  background: #f8fafc;
}

.btn-view:hover {
  border-color: #2563eb;
  color: #2563eb;
}

.btn-pdf:hover {
  border-color: #10b981;
  color: #10b981;
}

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
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

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
  backdrop-filter: blur(4px);
}

.modal {
  background: white;
  border-radius: 16px;
  width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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
  margin: 0;
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
  margin-bottom: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 6px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  color: #1e293b;
  background: white;
  transition: border-color 0.2s;
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
  padding: 0 24px 24px;
}

.btn-cancel {
  padding: 10px 20px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f8fafc;
}

.btn-save {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-save:hover {
  background: #1d4ed8;
}

.invoice-details {
  padding: 24px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 14px;
  color: #1e293b;
  font-weight: 600;
}

.detail-value.amount {
  font-size: 18px;
  color: #2563eb;
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
  .form-row {
    grid-template-columns: 1fr;
  }
}</style>