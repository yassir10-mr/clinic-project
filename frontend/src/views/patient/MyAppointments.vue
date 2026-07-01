<template>
  <div class="appointments-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mes Rendez-vous</h1>
        <p class="page-subtitle">Consultez et gérez vos rendez-vous</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Total</p>
          <p class="stat-value">{{ stats.total }}</p>
        </div>
        <div class="stat-icon total"><i class="fas fa-calendar"></i></div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Confirmé</p>
          <p class="stat-value" style="color: #059669;">{{ stats.confirme }}</p>
        </div>
        <div class="stat-icon confirmed"><i class="fas fa-check-circle"></i></div>
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
          <p class="stat-label">Terminé</p>
          <p class="stat-value" style="color: #3b82f6;">{{ stats.termine }}</p>
        </div>
        <div class="stat-icon completed"><i class="fas fa-calendar-check"></i></div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <p class="stat-label">Annulé</p>
          <p class="stat-value" style="color: #dc2626;">{{ stats.annule }}</p>
        </div>
        <div class="stat-icon cancelled"><i class="fas fa-times-circle"></i></div>
      </div>
    </div>

    <div class="appointments-section">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Chargement des rendez-vous...</p>
      </div>

      <div v-else-if="Object.keys(groupedAppointments).length === 0" class="empty-state">
        <i class="fas fa-calendar-day"></i>
        <p>Aucun rendez-vous trouvé.</p>
      </div>

      <div v-else v-for="(dateGroup, date) in groupedAppointments" :key="date" class="date-group">
        <h3 class="date-header">{{ formatDateHeader(date) }}</h3>
        <div class="appointments-list">
          <div v-for="rdv in dateGroup" :key="rdv.id_rdv" class="appointment-card">
            <div class="appointment-icon"><i class="fas fa-clock"></i></div>
            <div class="appointment-info">
              <p class="appointment-patient">Dr. {{ rdv.medecin_prenom }} {{ rdv.medecin_nom }}</p>
              <p class="appointment-details">{{ rdv.heure }} &bull; {{ rdv.specialite }}</p>
              <p class="appointment-type">{{ rdv.motif }}</p>
            </div>
            <div class="appointment-actions">
              <span :class="['status-badge', getStatusClass(rdv.statut)]">{{ formatStatus(rdv.statut) }}</span>
              <button v-if="rdv.statut !== 'Annulé' && rdv.statut !== 'terminé' && rdv.statut !== 'termine'" class="cancel-btn" @click="cancelAppointment(rdv.id_rdv)">Annuler</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
        <span>{{ toast.message }}</span>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const API = '/api';
const loading = ref(true);
const rendezVous = ref([]);
const toast = ref({ show: false, type: 'success', message: '' });

const user = JSON.parse(localStorage.getItem('patient_user') || '{}');
const idPatient = user.id || user.id_patient;

const stats = computed(() => {
  const total = rendezVous.value.length;
  const confirme = rendezVous.value.filter(r => {
    const s = (r.statut || '').toLowerCase();
    return s === 'confirmé' || s === 'confirme';
  }).length;
  const enAttente = rendezVous.value.filter(r => {
    const s = (r.statut || '').toLowerCase();
    return s === 'en attente';
  }).length;
  const termine = rendezVous.value.filter(r => {
    const s = (r.statut || '').toLowerCase();
    return s === 'terminé' || s === 'termine';
  }).length;
  const annule = rendezVous.value.filter(r => {
    const s = (r.statut || '').toLowerCase();
    return s === 'annulé' || s === 'annule';
  }).length;
  return { total, confirme, enAttente, termine, annule };
});

const groupedAppointments = computed(() => {
  const groups = {};
  const sorted = [...rendezVous.value].sort((a, b) => new Date(b.date_rdv) - new Date(a.date_rdv));
  for (const rdv of sorted) {
    const key = rdv.date_rdv;
    if (!groups[key]) groups[key] = [];
    groups[key].push(rdv);
  }
  return groups;
});

const formatDateHeader = (dateStr) => {
  const d = new Date(dateStr + 'T00:00:00');
  const today = new Date();
  today.setHours(0,0,0,0);
  const diff = (d - today) / 86400000;
  if (diff === 0) return "Aujourd'hui";
  if (diff === 1) return 'Demain';
  if (diff === -1) return 'Hier';
  return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const getStatusClass = (statut) => {
  const s = (statut || '').toLowerCase();
  if (s === 'confirmé' || s === 'confirme') return 'confirmed';
  if (s === 'en attente') return 'pending';
  if (s === 'annulé' || s === 'annule') return 'cancelled';
  if (s === 'terminé' || s === 'termine') return 'completed';
  return '';
};

const formatStatus = (statut) => {
  const s = (statut || '').toLowerCase();
  if (s === 'confirme') return 'Confirmé';
  if (s === 'en attente') return 'En attente';
  if (s === 'annule') return 'Annulé';
  if (s === 'termine') return 'Terminé';
  return statut;
};

const showToast = (type, message) => {
  toast.value = { show: true, type, message };
  setTimeout(() => { toast.value.show = false; }, 3000);
};

const fetchAppointments = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${API}/patient/${idPatient}/rendez-vous`);
    const data = await res.json();
    if (data.success) rendezVous.value = data.rendezVous;
  } catch (e) {
    console.error('fetch appointments error', e);
  } finally {
    loading.value = false;
  }
};

const cancelAppointment = async (id) => {
  try {
    const res = await fetch(`${API}/patient/rendez-vous/${id}/annuler`, { method: 'PATCH' });
    const data = await res.json();
    if (data.success) {
      showToast('success', 'Rendez-vous annulé avec succès.');
      fetchAppointments();
    } else {
      showToast('error', data.message || 'Erreur lors de l\'annulation.');
    }
  } catch (e) {
    showToast('error', 'Erreur réseau.');
  }
};

onMounted(() => {
  fetchAppointments();
});
</script>

<style scoped>
.appointments-page {
  height: 100%;
  display: flex;
  flex-direction: column;
}
.page-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; }
.page-subtitle { font-size: 14px; color: #64748b; margin: 4px 0 0; }
.stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-top: 24px; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
.stat-label { font-size: 13px; color: #475569; margin: 0; }
.stat-value { font-size: 28px; font-weight: 700; color: #1a1a2e; margin: 4px 0 0; }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.stat-icon.total { background: #eff6ff; color: #3b82f6; }
.stat-icon.confirmed { background: #ecfdf5; color: #059669; }
.stat-icon.pending { background: #fffbeb; color: #d97706; }
.stat-icon.cancelled { background: #fef2f2; color: #dc2626; }
.stat-icon.completed { background: #eff6ff; color: #3b82f6; }
.appointments-section { flex: 1; margin-top: 24px; overflow-y: auto; }
.date-group { margin-bottom: 24px; }
.date-header { font-size: 14px; font-weight: 600; color: #475569; margin: 0 0 12px; text-transform: capitalize; }
.appointments-list { display: flex; flex-direction: column; gap: 8px; }
.appointment-card { display: flex; align-items: center; gap: 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
.appointment-icon { width: 40px; height: 40px; border-radius: 10px; background: #f0f9ff; color: #3b8d99; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.appointment-info { flex: 1; min-width: 0; }
.appointment-patient { font-size: 14px; font-weight: 600; color: #1a1a2e; margin: 0; }
.appointment-details { font-size: 13px; color: #475569; margin: 2px 0; }
.appointment-type { font-size: 12px; color: #64748b; margin: 0; }
.appointment-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.status-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: capitalize; }
.status-badge.confirmed { background: #ecfdf5; color: #059669; }
.status-badge.pending { background: #fffbeb; color: #d97706; }
.status-badge.cancelled { background: #fef2f2; color: #dc2626; }
.status-badge.completed { background: #eff6ff; color: #3b82f6; }
.cancel-btn { padding: 6px 14px; border-radius: 8px; border: 1px solid #fca5a5; background: #fff; color: #dc2626; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.cancel-btn:hover { background: #fef2f2; }
.loading-state, .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 0; color: #64748b; gap: 12px; font-size: 14px; }
.loading-state i, .empty-state i { font-size: 40px; }
.toast { position: fixed; top: 24px; right: 24px; padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; z-index: 1000; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
.toast.success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.toast.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(20px); }
</style>
