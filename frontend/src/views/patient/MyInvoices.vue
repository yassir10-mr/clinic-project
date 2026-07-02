<template>
  <div class="invoices-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mes Factures</h1>
        <p class="page-subtitle">Consultez l'historique de vos factures</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total</p>
          <p class="stat-value">{{ factures.length }}</p>
        </div>
        <div class="stat-icon total"><i class="fas fa-file-invoice"></i></div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Payé</p>
          <p class="stat-value" style="color: #059669;">{{ stats.paye }}</p>
        </div>
        <div class="stat-icon paid"><i class="fas fa-check-circle"></i></div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">En attente</p>
          <p class="stat-value" style="color: #d97706;">{{ stats.enAttente }}</p>
        </div>
        <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Montant total</p>
          <p class="stat-value" style="font-size: 20px;">{{ stats.montantTotal }} DH</p>
        </div>
        <div class="stat-icon amount"><i class="fas fa-coins"></i></div>
      </div>
    </div>

    <div class="card">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Chargement des factures...</p>
      </div>

      <div v-else-if="factures.length === 0" class="empty-state">
        <i class="fas fa-file-invoice-dollar"></i>
        <p>Aucune facture trouvée.</p>
      </div>

      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>N° Facture</th>
              <th>Date</th>
              <th>Montant</th>
              <th>Statut</th>
              <th>Mode de paiement</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="f in factures" :key="f.id_facture">
              <td class="td-id">#{{ f.id_facture }}</td>
              <td class="td-date">{{ f.date }}</td>
              <td class="td-amount">{{ Number(f.montant_total).toFixed(2) }} DH</td>
              <td>
                <span :class="['badge', getBadgeClass(f.statut_paiement)]">{{ formatStatut(f.statut_paiement) }}</span>
              </td>
              <td>{{ f.mode_paiement || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const API = '/api';
const loading = ref(true);
const factures = ref([]);

const user = JSON.parse(localStorage.getItem('patient_user') || '{}');
const idPatient = user.id || user.id_patient;

const stats = computed(() => {
  const normalize = (s) => (s || '').toLowerCase();
  const paye = factures.value.filter(f => normalize(f.statut_paiement) === 'payé' || normalize(f.statut_paiement) === 'paye').length;
  const enAttente = factures.value.filter(f => normalize(f.statut_paiement) !== 'payé' && normalize(f.statut_paiement) !== 'paye').length;
  const montantTotal = factures.value.reduce((sum, f) => sum + Number(f.montant_total), 0);
  return { paye, enAttente, montantTotal };
});

const getBadgeClass = (statut) => {
  const s = (statut || '').toLowerCase();
  if (s === 'payé' || s === 'paye') return 'badge-paid';
  if (s === 'non paye' || s === 'non payé') return 'badge-pending';
  return 'badge-pending';
};

const formatStatut = (statut) => {
  const s = (statut || '').toLowerCase();
  if (s === 'paye') return 'Payé';
  if (s === 'non paye') return 'Non payé';
  return statut;
};

const fetchFactures = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${API}/patient/${idPatient}/factures`);
    const data = await res.json();
    if (data.success) factures.value = data.factures;
  } catch (e) {
    console.error('fetch factures error', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => { fetchFactures(); });
</script>

<style scoped>
.invoices-page { height: 100%; display: flex; flex-direction: column; }
.page-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; }
.page-subtitle { font-size: 14px; color: #64748b; margin: 4px 0 0; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 24px; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
.stat-label { font-size: 13px; color: #475569; margin: 0; }
.stat-value { font-size: 28px; font-weight: 700; color: #1a1a2e; margin: 4px 0 0; }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.stat-icon.total { background: #eff6ff; color: #3b82f6; }
.stat-icon.paid { background: #ecfdf5; color: #059669; }
.stat-icon.pending { background: #fffbeb; color: #d97706; }
.stat-icon.amount { background: #f5f3ff; color: #7c3aed; }
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-top: 24px; flex: 1; overflow: hidden; display: flex; flex-direction: column; }
.table-wrapper { overflow-x: auto; flex: 1; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { text-align: left; padding: 12px 16px; font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.data-table td { padding: 14px 16px; color: #475569; border-bottom: 1px solid #f1f5f9; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #f8fafc; }
.td-id { font-weight: 600; color: #3b8d99; }
.td-date { white-space: nowrap; font-weight: 500; color: #1a1a2e; }
.td-amount { font-weight: 600; color: #1a1a2e; }
.badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-paid { background: #ecfdf5; color: #059669; }
.badge-pending { background: #fffbeb; color: #d97706; }
.loading-state, .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 0; color: #64748b; gap: 12px; font-size: 14px; }
.loading-state i, .empty-state i { font-size: 40px; }
</style>
