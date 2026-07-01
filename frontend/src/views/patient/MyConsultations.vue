<template>
  <div class="consultations-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">
          <i class="fas fa-stethoscope title-icon"></i>
          Mes Consultations
        </h1>
        <p class="page-subtitle">Historique complet de vos consultations</p>
      </div>
    </div>

    <div v-if="error" class="error-banner">
      <i class="fas fa-exclamation-triangle"></i>
      <span>{{ error }}</span>
      <button @click="fetchConsultations" class="retry-btn">Réessayer</button>
    </div>

    <div v-if="!loading && !error" class="consultations-stats">
      <div class="cstat">
        <span class="cstat-value">{{ consultations.length }}</span>
        <span class="cstat-label">Total consultations</span>
      </div>
      <div class="cstat-divider"></div>
      <div class="cstat">
        <span class="cstat-value">{{ uniqueDoctors }}</span>
        <span class="cstat-label">Médecins consultés</span>
      </div>
    </div>

    <div class="card">
      <div class="card-accent"></div>

      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Chargement des consultations...</p>
      </div>

      <div v-else-if="consultations.length === 0" class="empty-state">
        <i class="fas fa-stethoscope"></i>
        <p>Aucune consultation enregistrée.</p>
      </div>

      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>
                <i class="fas fa-calendar-alt th-icon"></i>
                Date
              </th>
              <th>
                <i class="fas fa-user-md th-icon"></i>
                Médecin
              </th>
              <th>
                <i class="fas fa-stethoscope th-icon"></i>
                Diagnostic
              </th>
              <th>
                <i class="fas fa-prescription th-icon"></i>
                Traitement
              </th>
              <th>
                <i class="fas fa-notes-medical th-icon"></i>
                Observations
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in consultations" :key="c.id_consultation">
              <td class="td-date">
                <span class="date-badge">{{ c.date }}</span>
              </td>
              <td class="td-doctor">
                <span class="doctor-avatar">D</span>
                Dr. {{ c.medecin_prenom }} {{ c.medecin_nom }}
              </td>
              <td>
                <span v-if="c.diagnostic" class="diag-tag">{{ c.diagnostic }}</span>
                <span v-else class="null-value">—</span>
              </td>
              <td>
                <span v-if="c.traitement" class="trait-text">{{ c.traitement }}</span>
                <span v-else class="null-value">—</span>
              </td>
              <td>
                <span v-if="c.observations" class="obs-text">{{ c.observations }}</span>
                <span v-else class="null-value">—</span>
              </td>
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
const consultations = ref([]);
const error = ref('');

const user = JSON.parse(localStorage.getItem('patient_user') || '{}');
const idPatient = user.id || user.id_patient;

const uniqueDoctors = computed(() => {
  const docs = new Set(consultations.value.map(c => `${c.medecin_prenom} ${c.medecin_nom}`));
  return docs.size;
});

const fetchConsultations = async () => {
  loading.value = true;
  error.value = '';
  consultations.value = [];
  const url = `${API}/patient/${idPatient}/consultations`;
  try {
    const res = await fetch(url);
    if (!res.ok) { error.value = `Erreur HTTP ${res.status}`; return; }
    const data = await res.json();
    if (data.success) consultations.value = data.consultations;
    else error.value = data.message || 'Erreur API';
  } catch (e) {
    error.value = `Erreur réseau: ${e.message}`;
  } finally {
    loading.value = false;
  }
};

onMounted(() => { fetchConsultations(); });
</script>

<style scoped>
.consultations-page { height: 100%; display: flex; flex-direction: column; }

.page-header { margin-bottom: 0; }
.title-icon { color: #3b8d99; font-size: 20px; margin-right: 10px; }
.page-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; display: flex; align-items: center; }
.page-subtitle { font-size: 14px; color: #64748b; margin: 4px 0 0; }

/* Stats bar — compact */
.consultations-stats { display: flex; align-items: center; gap: 16px; margin-top: 16px; padding: 10px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; }
.cstat { display: flex; flex-direction: column; gap: 1px; }
.cstat-value { font-size: 18px; font-weight: 700; color: #475569; line-height: 1.2; }
.cstat-label { font-size: 11px; color: #94a3b8; }
.cstat-divider { width: 1px; height: 24px; background: #e2e8f0; }

/* Card */
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 0; margin-top: 20px; flex: 1; overflow: hidden; display: flex; flex-direction: column; position: relative; }
.card-accent { height: 4px; background: linear-gradient(90deg, #3b8d99, #5eead4); border-radius: 16px 16px 0 0; flex-shrink: 0; }

.table-wrapper { overflow-x: auto; flex: 1; padding: 0 24px 24px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { text-align: left; padding: 16px 16px 12px; font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
.th-icon { color: #3b8d99; margin-right: 6px; font-size: 11px; }
.data-table td { padding: 14px 16px; color: #475569; border-bottom: 1px solid #f1f5f9; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: background 0.2s ease; }
.data-table tbody tr:hover td { background: #f0fdfa; }
.data-table tbody tr { border-left: 3px solid transparent; transition: border-color 0.2s ease, background 0.2s ease; }
.data-table tbody tr:hover { border-left-color: #3b8d99; }

.td-date { white-space: nowrap; }
.date-badge { display: inline-block; padding: 2px 10px; background: #f0fdfa; color: #2c6e7a; border-radius: 6px; font-size: 12px; font-weight: 600; }
.td-doctor { font-weight: 500; color: #475569; display: flex; align-items: center; gap: 8px; }
.doctor-avatar { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #3b8d99, #2c6e7a); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }

.diag-tag { display: inline-block; padding: 2px 10px; background: #f0fdf4; color: #059669; border-radius: 6px; font-size: 12px; font-weight: 500; }
.trait-text { font-weight: 500; color: #475569; }
.obs-text { color: #94a3b8; font-style: italic; font-size: 12px; }
.null-value { color: #cbd5e1; }

.loading-state, .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 0; color: #64748b; gap: 12px; font-size: 14px; }
.loading-state i, .empty-state i { font-size: 40px; }
.error-banner { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; color: #dc2626; font-size: 13px; margin: 16px 0; }
.retry-btn { margin-left: auto; padding: 6px 14px; border-radius: 8px; border: 1px solid #fca5a5; background: #fff; color: #dc2626; font-size: 12px; font-weight: 500; cursor: pointer; font-family: inherit; }
.retry-btn:hover { background: #fef2f2; }
</style>
