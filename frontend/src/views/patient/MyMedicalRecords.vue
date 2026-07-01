<template>
  <div class="medical-records-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mon Dossier Médical</h1>
        <p class="page-subtitle">Consultez vos antécédents, allergies et informations médicales</p>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Chargement de votre dossier médical...</p>
    </div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Antécédents</p>
            <p class="stat-value" style="color: #3b82f6;">{{ dossier?.antecedents ? 'Présents' : 'Aucun' }}</p>
          </div>
          <div class="stat-icon history"><i class="fas fa-notes-medical"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Allergies</p>
            <p class="stat-value" style="color: #d97706;">{{ dossier?.allergies ? 'Répertoriées' : 'Aucune' }}</p>
          </div>
          <div class="stat-icon allergies"><i class="fas fa-allergies"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Groupe Sanguin</p>
            <p class="stat-value" style="color: #059669;">{{ profile?.groupe_sanguin || 'Non renseigné' }}</p>
          </div>
          <div class="stat-icon blood"><i class="fas fa-tint"></i></div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Date du dossier</p>
            <p class="stat-value" style="font-size: 18px; color: #64748b;">{{ dossier?.date_creation || '—' }}</p>
          </div>
          <div class="stat-icon date"><i class="fas fa-calendar-alt"></i></div>
        </div>
      </div>

      <div class="records-grid">
        <div class="record-card antecedents-card">
          <div class="record-card-accent"></div>
          <div class="record-card-body">
            <h2 class="card-title">
              <span class="card-title-icon notes"><i class="fas fa-notes-medical"></i></span>
              Antécédents Médicaux
            </h2>
            <div v-if="dossier?.antecedents" class="record-content">
              <div class="timeline-item" v-for="(item, i) in splitText(dossier.antecedents)" :key="i">
                <div class="timeline-dot"></div>
                <span class="timeline-text">{{ item }}</span>
              </div>
            </div>
            <div v-else class="record-empty-state">
              <i class="fas fa-folder-open empty-icon"></i>
              <p>Aucun antécédent enregistré.</p>
            </div>
          </div>
        </div>

        <div class="record-card allergies-card">
          <div class="record-card-accent"></div>
          <div class="record-card-body">
            <h2 class="card-title">
              <span class="card-title-icon allergy"><i class="fas fa-allergies"></i></span>
              Allergies
            </h2>
            <div v-if="dossier?.allergies" class="record-content">
              <div class="allergies-list">
                <span v-for="(allergy, i) in splitText(dossier.allergies)" :key="i" class="allergy-pill">
                  <i class="fas fa-exclamation-circle pill-icon"></i>
                  {{ allergy }}
                </span>
              </div>
            </div>
            <div v-else class="record-empty-state">
              <i class="fas fa-check-circle empty-icon"></i>
              <p>Aucune allergie enregistrée.</p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const API = '/api';
const loading = ref(true);
const dossier = ref(null);
const profile = ref(null);

const user = JSON.parse(localStorage.getItem('patient_user') || '{}');
const idPatient = user.id || user.id_patient;

const fetchData = async () => {
  loading.value = true;
  try {
    const [dossierRes, profileRes] = await Promise.all([
      fetch(`${API}/patient/${idPatient}/dossier-medical`),
      fetch(`${API}/patient/${idPatient}/profile`)
    ]);
    const dossierData = await dossierRes.json();
    const profileData = await profileRes.json();
    if (dossierData.success) dossier.value = dossierData.dossier;
    if (profileData.success) profile.value = profileData.patient;
  } catch (e) {
    console.error('fetch medical records error', e);
  } finally {
    loading.value = false;
  }
};

const splitText = (text) => {
  if (!text) return [];
  return text.split(',').map(s => s.trim()).filter(Boolean);
};

onMounted(() => { fetchData(); });
</script>

<style scoped>
.medical-records-page { height: 100%; display: flex; flex-direction: column; }
.page-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; }
.page-subtitle { font-size: 14px; color: #64748b; margin: 4px 0 0; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 24px; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
.stat-label { font-size: 13px; color: #475569; margin: 0; }
.stat-value { font-size: 24px; font-weight: 700; color: #1a1a2e; margin: 4px 0 0; }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.stat-icon.history { background: #eff6ff; color: #3b82f6; }
.stat-icon.allergies { background: #fffbeb; color: #d97706; }
.stat-icon.blood { background: #ecfdf5; color: #059669; }
.stat-icon.date { background: #f5f3ff; color: #7c3aed; }

/* Records grid */
.records-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px; flex: 1; align-content: start; }

/* Record cards */
.record-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; }
.record-card-accent { height: 4px; flex-shrink: 0; }
.antecedents-card .record-card-accent { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.allergies-card .record-card-accent { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

.record-card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }

.card-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0 0 16px; display: flex; align-items: center; gap: 10px; }
.card-title-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.card-title-icon.notes { background: linear-gradient(135deg, #3b8d99, #2c6e7a); color: #fff; box-shadow: 0 4px 10px rgba(59, 141, 153, 0.25); }
.card-title-icon.allergy { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.25); }

/* Timeline style for antecedents */
.record-content { flex: 1; }
.timeline-item { display: flex; align-items: flex-start; gap: 12px; padding: 8px 0; position: relative; }
.timeline-item + .timeline-item { border-top: 1px solid #f1f5f9; }
.timeline-dot { width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; margin-top: 6px; flex-shrink: 0; }
.timeline-text { font-size: 14px; color: #475569; line-height: 1.6; }

/* Allergy pills */
.allergies-list { display: flex; flex-wrap: wrap; gap: 8px; }
.allergy-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 20px; font-size: 13px; font-weight: 500; color: #5b21b6; }
.pill-icon { font-size: 11px; color: #8b5cf6; }

/* Empty state inside cards */
.record-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 0; color: #94a3b8; gap: 8px; font-size: 13px; flex: 1; }
.record-empty-state .empty-icon { font-size: 32px; }
.record-empty-state p { margin: 0; }

.loading-state { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; color: #64748b; gap: 12px; font-size: 14px; }
.loading-state i { font-size: 40px; }
</style>
