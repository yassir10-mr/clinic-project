<template>
  <div class="login-page">
    <!-- Logo -->
    <div class="logo-header">
      <div class="logo-icon">
        <i class="fas fa-stethoscope"></i>
      </div>
      <h1 class="logo-text">MediCare</h1>
    </div>
    <p class="tagline">Clinic Management System</p>

    <!-- Carte Login -->
    <div class="login-card">
      <h2 class="welcome">Welcome Back</h2>
      <p class="subtitle">Sign in to access your dashboard</p>

      <!-- Role Selector (Mis à jour avec TOUS les rôles !) -->
      <div class="grid grid-cols-2 gap-2 mb-6">
        <button 
          :class="['role-btn', { active: role === 'admin' }]" 
          @click="role = 'admin'"
          type="button"
        >
          <i class="fas fa-user-shield text-xs"></i> Admin
        </button>
        <button 
          :class="['role-btn', { active: role === 'secretaire' }]" 
          @click="role = 'secretaire'"
          type="button"
        >
          <i class="fas fa-user-tie text-xs"></i> Secrétaire
        </button>
        <button 
          :class="['role-btn', { active: role === 'infirmier' }]" 
          @click="role = 'infirmier'"
          type="button"
        >
          <i class="fas fa-user-nurse text-xs"></i> Infirmier
        </button>
        <button 
          :class="['role-btn', { active: role === 'patient' }]" 
          @click="role = 'patient'"
          type="button"
        >
          <i class="fas fa-user-injured text-xs"></i> Patient
        </button>
      </div>

      <form @submit.prevent="handleLogin">
        <!-- Email -->
        <div class="form-group">
          <label>Email</label>
          <div class="input-wrapper">
            <i class="fas fa-envelope input-icon"></i>
            <input 
              v-model="form.email" 
              type="email" 
              :placeholder="role === 'admin' ? 'admin@clinic.com' : role === 'secretaire' ? 'secretaire@clinic.com' : role === 'infirmier' ? 'infirmier@clinic.com' : 'patient@clinic.com'"
              required 
            />
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label>Password</label>
          <div class="input-wrapper">
            <i class="fas fa-lock input-icon"></i>
            <input 
              v-model="form.password" 
              type="password" 
              placeholder="••••••••"
              required 
            />
          </div>
        </div>

        <!-- Options -->
        <div class="options">
          <label class="remember">
            <input type="checkbox" v-model="rememberMe" />
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot">Forgot password?</a>
        </div>

        <!-- Bouton -->
        <button type="submit" class="btn-signin" :disabled="loading">
          {{ loading ? 'Signing in...' : 'Sign In as ' + role.toUpperCase() }}
        </button>

        <!-- Erreur -->
        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>

    <!-- Footer -->
    <p class="footer">© 2026 MediCare. All rights reserved.</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api.js';

const router = useRouter();
const loading = ref(false);
const error = ref('');
const rememberMe = ref(false);
const role = ref('admin');

const form = ref({
  email: '',
  password: ''
});

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    // Si c'est l'infirmier ou le patient, on fait une redirection simplifiée pour le moment
    if (role.value === 'infirmier') {
      router.push('/infirmiers');
      return;
    }
    if (role.value === 'patient') {
      router.push('/mon-espace-patient');
      return;
    }

    // Sinon, on passe par l'authentification de Yassir
    const endpoint = role.value === 'admin' ? '/admin/login' : '/secretaire/login';
    const response = await api.post(endpoint, {
      email: form.value.email,
      password: form.value.password
    });
    
    if (response.data.success && response.data.token) {
      const tokenKey = role.value === 'admin' ? 'admin_token' : 'secretaire_token';
      const userKey = role.value === 'admin' ? 'admin_user' : 'secretaire_user';
      
      localStorage.setItem(tokenKey, response.data.token);
      localStorage.setItem(userKey, JSON.stringify(response.data.user));
      localStorage.setItem('user_role', role.value);
      
      api.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
      
      if (role.value === 'admin') {
        router.push('/dashboard');
      } else {
        router.push('/secretaire/dashboard');
      }
    }
  } catch (err) {
    error.value = 'Unable to sign in. Please verify your credentials and try again.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Conserver tout le superbe CSS d'origine de Yassir */
.login-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(180deg, #f0f7ff 0%, #ffffff 100%);
  padding: 20px;
}
.logo-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}
.logo-icon {
  width: 44px;
  height: 44px;
  background: #2563eb;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.logo-icon i {
  color: white;
  font-size: 22px;
}
.logo-text {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  letter-spacing: -0.5px;
}
.tagline {
  color: #64748b;
  font-size: 14px;
  margin-bottom: 32px;
}
.login-card {
  background: white;
  padding: 40px;
  border-radius: 16px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 20px 25px -5px rgba(0, 0, 0, 0.05);
}
.welcome {
  font-size: 24px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 4px;
}
.subtitle {
  color: #64748b;
  font-size: 14px;
  margin-bottom: 28px;
}
.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}
.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.input-icon {
  position: absolute;
  left: 12px;
  color: #9ca3af;
  font-size: 14px;
}
.input-wrapper input {
  width: 100%;
  padding: 12px 12px 12px 40px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: #f9fafb;
  transition: all 0.2s;
}
.input-wrapper input:focus {
  outline: none;
  border-color: #2563eb;
  background: white;
}
.options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.remember {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
}
.remember input {
  width: 16px;
  height: 16px;
  accent-color: #2563eb;
}
.forgot {
  font-size: 14px;
  color: #2563eb;
  text-decoration: none;
}
.btn-signin {
  width: 100%;
  padding: 14px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
}
.error {
  color: #dc2626;
  font-size: 14px;
  text-align: center;
  margin-top: 12px;
}
.footer {
  margin-top: 24px;
  font-size: 13px;
  color: #94a3b8;
}

/* Grille de sélection des rôles moderne */
.role-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.role-btn:hover, .role-btn.active {
  border-color: #2563eb;
  color: #2563eb;
  background: #eff6ff;
}
</style>