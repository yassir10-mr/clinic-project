<template>
  <div class="profile-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mon Profil</h1>
        <p class="page-subtitle">Gérez vos informations personnelles</p>
      </div>
    </div>

    <div class="profile-card">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Chargement du profil...</p>
      </div>

      <template v-else>
        <div v-if="alert.show" :class="['alert', alert.type]">
          <i :class="alert.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
          <span>{{ alert.message }}</span>
        </div>

        <form @submit.prevent="handleSubmit">
          <div class="form-grid">
            <div class="form-group">
              <label>Nom</label>
              <input type="text" v-model="form.nom" required />
            </div>
            <div class="form-group">
              <label>Prénom</label>
              <input type="text" v-model="form.prenom" required />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" v-model="form.email" />
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <input type="tel" v-model="form.telephone" required />
            </div>
            <div class="form-group">
              <label>Date de naissance</label>
              <input type="date" v-model="form.date_naissance" />
            </div>
            <div class="form-group">
              <label>Sexe</label>
              <select v-model="form.sexe">
                <option value="">Sélectionner...</option>
                <option value="Homme">Homme</option>
                <option value="Femme">Femme</option>
              </select>
            </div>
            <div class="form-group">
              <label>Adresse</label>
              <input type="text" v-model="form.adresse" />
            </div>
            <div class="form-group">
              <label>Groupe sanguin</label>
              <select v-model="form.groupe_sanguin">
                <option value="">Non renseigné</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
              </select>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn" :disabled="saving">
              <i class="fas fa-spinner fa-spin" v-if="saving"></i>
              <span>{{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}</span>
            </button>
          </div>
        </form>

        <hr class="section-divider" />

        <div class="password-section">
          <h2 class="card-title">
            <i class="fas fa-lock card-icon"></i>
            Changer le mot de passe
          </h2>

          <div v-if="passwordAlert.show" :class="['alert', passwordAlert.type]">
            <i :class="passwordAlert.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
            <span>{{ passwordAlert.message }}</span>
          </div>

          <form @submit.prevent="handlePasswordChange">
            <div class="password-grid">
              <div class="form-group">
                <label>Ancien mot de passe *</label>
                <input type="password" v-model="passwordForm.ancien_mot_de_passe" required placeholder="Saisir l'ancien mot de passe" />
              </div>
              <div class="form-group">
                <label>Nouveau mot de passe *</label>
                <input type="password" v-model="passwordForm.nouveau_mot_de_passe" required minlength="6" placeholder="Minimum 6 caractères" />
              </div>
              <div class="form-group">
                <label>Confirmer le mot de passe *</label>
                <input type="password" v-model="passwordForm.confirmation" required placeholder="Retaper le nouveau mot de passe" />
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="save-btn" :disabled="passwordSaving">
                <i class="fas fa-spinner fa-spin" v-if="passwordSaving"></i>
                <span>{{ passwordSaving ? 'Enregistrement...' : 'Changer le mot de passe' }}</span>
              </button>
            </div>
          </form>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const API = '/api';
const loading = ref(true);
const saving = ref(false);
const alert = ref({ show: false, type: 'success', message: '' });

const user = JSON.parse(localStorage.getItem('patient_user') || '{}');
const idPatient = user.id || user.id_patient;

const form = ref({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  date_naissance: '',
  sexe: '',
  adresse: '',
  groupe_sanguin: ''
});

const passwordForm = ref({
  ancien_mot_de_passe: '',
  nouveau_mot_de_passe: '',
  confirmation: ''
});
const passwordSaving = ref(false);
const passwordAlert = ref({ show: false, type: 'success', message: '' });

const fetchProfile = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${API}/patient/${idPatient}/profile`);
    const data = await res.json();
    if (data.success && data.patient) {
      const p = data.patient;
      form.value = {
        nom: p.nom || '',
        prenom: p.prenom || '',
        email: p.email || '',
        telephone: p.telephone || '',
        date_naissance: p.date_naissance || '',
        sexe: p.sexe || '',
        adresse: p.adresse || '',
        groupe_sanguin: p.groupe_sanguin || ''
      };
    }
  } catch (e) {
    console.error('fetch profile error', e);
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  saving.value = true;
  alert.value.show = false;
  try {
    const res = await fetch(`${API}/patient/${idPatient}/profile`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    });
    const data = await res.json();
    if (data.success) {
      alert.value = { show: true, type: 'success', message: 'Profil mis à jour avec succès.' };

      const updatedUser = { ...user, ...form.value };
      localStorage.setItem('patient_user', JSON.stringify(updatedUser));
    } else {
      alert.value = { show: true, type: 'error', message: data.message || 'Erreur lors de la mise à jour.' };
    }
  } catch (e) {
    alert.value = { show: true, type: 'error', message: 'Erreur réseau.' };
  } finally {
    saving.value = false;
  }
};

const handlePasswordChange = async () => {
  passwordAlert.value.show = false;

  if (passwordForm.value.nouveau_mot_de_passe !== passwordForm.value.confirmation) {
    passwordAlert.value = { show: true, type: 'error', message: 'Les nouveaux mots de passe ne correspondent pas.' };
    return;
  }

  passwordSaving.value = true;
  try {
    const res = await fetch(`${API}/patient/${idPatient}/password`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ancien_mot_de_passe: passwordForm.value.ancien_mot_de_passe,
        nouveau_mot_de_passe: passwordForm.value.nouveau_mot_de_passe
      })
    });
    const data = await res.json();
    if (data.success) {
      passwordAlert.value = { show: true, type: 'success', message: 'Mot de passe changé avec succès.' };
      passwordForm.value = { ancien_mot_de_passe: '', nouveau_mot_de_passe: '', confirmation: '' };
    } else {
      passwordAlert.value = { show: true, type: 'error', message: data.message || 'Erreur lors du changement.' };
    }
  } catch (e) {
    passwordAlert.value = { show: true, type: 'error', message: 'Erreur réseau.' };
  } finally {
    passwordSaving.value = false;
  }
};

onMounted(() => { fetchProfile(); });
</script>

<style scoped>
.profile-page { height: 100%; display: flex; flex-direction: column; }
.page-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; }
.page-subtitle { font-size: 14px; color: #64748b; margin: 4px 0 0; }
.profile-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; margin-top: 24px; max-width: 800px; }
.alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; }
.alert.success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.alert.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-group input, .form-group select { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #1a1a2e; background: #fff; outline: none; transition: border-color 0.2s; font-family: inherit; }
.form-group input:focus, .form-group select:focus { border-color: #3b8d99; }
.form-group input[type="date"]::-webkit-calendar-picker-indicator {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%233b8d99' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
  cursor: pointer;
}
.form-group input[type="date"] { color-scheme: light; }
.form-actions { margin-top: 28px; display: flex; justify-content: flex-end; }
.save-btn { display: flex; align-items: center; gap: 8px; padding: 12px 28px; background: #3b8d99; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; font-family: inherit; }
.save-btn:hover { background: #2c6e7a; }
.save-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.loading-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 0; color: #64748b; gap: 12px; font-size: 14px; }
.loading-state i { font-size: 40px; }
.section-divider { border: none; border-top: 1px solid #e2e8f0; margin: 32px 0; }
.password-section { margin-top: 8px; }
.card-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0 0 16px; display: flex; align-items: center; gap: 10px; }
.card-icon { color: #3b8d99; font-size: 18px; }
.password-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
</style>
