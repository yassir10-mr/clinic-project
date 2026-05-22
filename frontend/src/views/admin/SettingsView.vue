<template>
  <div class="settings-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage your account and clinic preferences</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-card">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="['tab-btn', activeTab === tab.id ? 'active' : '']"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Profile Tab -->
    <div v-if="activeTab === 'profile'" class="settings-card">
      <div class="card-header">
        <i class="fas fa-user"></i>
        <div>
          <h3>Personal Information</h3>
          <p>Update your personal details and contact information</p>
        </div>
      </div>

      <form @submit.prevent="saveProfile">
        <div class="form-row">
          <div class="form-group">
            <label>Last Name (Nom) *</label>
            <input v-model="profileForm.nom" placeholder="Your last name" required />
          </div>
          <div class="form-group">
            <label>First Name (Prénom) *</label>
            <input v-model="profileForm.prenom" placeholder="Your first name" required />
          </div>
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input v-model="profileForm.email" type="email" placeholder="your.email@clinic.com" required />
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="profileLoading">
            {{ profileLoading ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Security Tab -->
    <div v-if="activeTab === 'security'" class="settings-card">
      <div class="card-header">
        <i class="fas fa-lock"></i>
        <div>
          <h3>Change Password</h3>
          <p>Update your password to keep your account secure</p>
        </div>
      </div>

      <form @submit.prevent="changePassword">
        <div class="form-group">
          <label>Current Password *</label>
          <input 
            v-model="passwordForm.current_password" 
            type="password" 
            placeholder="Enter current password"
            required 
          />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>New Password *</label>
            <input 
              v-model="passwordForm.new_password" 
              type="password" 
              placeholder="Enter new password"
              required 
            />
          </div>
          <div class="form-group">
            <label>Confirm New Password *</label>
            <input 
              v-model="passwordForm.new_password_confirmation" 
              type="password" 
              placeholder="Confirm new password"
              required 
            />
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="passwordLoading">
            {{ passwordLoading ? 'Updating...' : 'Update Password' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Alert Messages -->
    <div v-if="message" :class="['alert', messageType]">
      {{ message }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getUser, updateProfile, changePassword as apiChangePassword } from '@/services/api.js';

const activeTab = ref('profile');
const tabs = [
  { id: 'profile', label: 'Profile' },
  { id: 'security', label: 'Security' }
];

const profileLoading = ref(false);
const passwordLoading = ref(false);
const message = ref('');
const messageType = ref('');

// Profile form - correspond à la base de données (nom, prenom, email)
const profileForm = ref({
  nom: '',
  prenom: '',
  email: ''
});

// Password form
const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

// ============ CHARGEMENT DU PROFIL ============
onMounted(() => {
  loadProfile();
});

const loadProfile = async () => {
  try {
    const response = await getUser();
    console.log('✅ User loaded:', response.data);
    
    if (response.data && response.data.user) {
      const user = response.data.user;
      profileForm.value = {
        nom: user.nom || '',
        prenom: user.prenom || '',
        email: user.email || ''
      };
    }
  } catch (error) {
    console.error('❌ Error loading profile:', error);
    showMessage('Unable to load profile information', 'error');
  }
};

// ============ SAUVEGARDE PROFIL ============
const saveProfile = async () => {
  profileLoading.value = true;
  message.value = '';
  
  try {
    console.log('💾 Saving profile:', profileForm.value);
    const response = await updateProfile(profileForm.value);
    console.log('✅ Profile updated:', response.data);
    
    // Mettre à jour le localStorage
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    user.nom = profileForm.value.nom;
    user.prenom = profileForm.value.prenom;
    user.email = profileForm.value.email;
    localStorage.setItem('user', JSON.stringify(user));
    
    showMessage('Profile updated successfully!', 'success');
  } catch (error) {
    console.error('❌ Error saving profile:', error);
    
    let msg = 'Error updating profile';
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors) {
        msg = Object.values(errors).flat().join('\n');
      }
    } else if (error.response?.data?.message) {
      msg = error.response.data.message;
    }
    
    showMessage(msg, 'error');
  } finally {
    profileLoading.value = false;
  }
};

// ============ CHANGEMENT MOT DE PASSE ============
const changePassword = async () => {
  // Vérifier que les mots de passe correspondent
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    showMessage('New passwords do not match', 'error');
    return;
  }
  
  passwordLoading.value = true;
  message.value = '';
  
  try {
    console.log('🔑 Changing password...');
    const response = await apiChangePassword({
      current_password: passwordForm.value.current_password,
      new_password: passwordForm.value.new_password
    });
    console.log('✅ Password changed:', response.data);
    
    // Réinitialiser le formulaire
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    };
    
    showMessage('Password updated successfully!', 'success');
  } catch (error) {
    console.error('❌ Error changing password:', error);
    
    let msg = 'Error updating password';
    if (error.response?.status === 401) {
      msg = 'Current password is incorrect';
    } else if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors) {
        msg = Object.values(errors).flat().join('\n');
      }
    } else if (error.response?.data?.message) {
      msg = error.response.data.message;
    }
    
    showMessage(msg, 'error');
  } finally {
    passwordLoading.value = false;
  }
};

// ============ UTILITAIRE ============
const showMessage = (text, type) => {
  message.value = text;
  messageType.value = type;
  
  // Auto-hide après 5 secondes
  setTimeout(() => {
    message.value = '';
  }, 5000);
};
</script>

<style scoped>
.settings-page {
  max-width: 800px;
}

/* Page Header */
.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
}

/* Tabs */
.tabs-card {
  display: flex;
  gap: 4px;
  margin-bottom: 24px;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 8px;
  width: fit-content;
}

.tab-btn {
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.tab-btn:hover {
  color: #374151;
}

.tab-btn.active {
  background: white;
  color: #1e293b;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Settings Card */
.settings-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}

.card-header i {
  width: 40px;
  height: 40px;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.card-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.card-header p {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

/* Form */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-group input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: #f9fafb;
  transition: all 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #2563eb;
  background: white;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 8px;
}

.btn-save {
  padding: 10px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Alert Messages */
.alert {
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  margin-top: 16px;
}

.alert.success {
  background: #ecfdf5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

.alert.error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

</style>




<style scoped>
.settings-page {
  max-width: 800px;
}

/* Page Header */
.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
}

/* Tabs */
.tabs-card {
  display: flex;
  gap: 4px;
  margin-bottom: 24px;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 8px;
  width: fit-content;
}

.tab-btn {
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.tab-btn:hover {
  color: #374151;
}

.tab-btn.active {
  background: white;
  color: #1e293b;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Settings Card */
.settings-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}

.card-header i {
  width: 40px;
  height: 40px;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.card-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.card-header p {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

/* Form */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-group input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: #f9fafb;
  transition: all 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #2563eb;
  background: white;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 8px;
}

.btn-save {
  padding: 10px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Alert Messages */
.alert {
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  margin-top: 16px;
}

.alert.success {
  background: #ecfdf5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

.alert.error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}
</style>