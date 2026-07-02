<template>
  <div class="patient-layout" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
    <aside class="sidebar">
      <div class="sidebar-logo">
        <span class="logo-text">MediCare</span>
        <button class="sidebar-toggle" @click="toggleSidebar" :title="sidebarCollapsed ? 'Ouvrir' : 'Fermer'">
          <i :class="sidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ active: isActive(item.path) }"
        >
          <div class="nav-icon">
            <i :class="item.icon"></i>
          </div>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <div class="sidebar-profile" @click="toggleLogoutDropdown">
        <div class="profile-avatar">{{ userInitials }}</div>
        <div class="profile-info">
          <p class="profile-name">{{ userFullName }}</p>
          <p class="profile-role">Patient</p>
        </div>
        <i class="fas fa-chevron-down profile-arrow"></i>

        <Transition name="dropdown">
          <div v-if="showLogoutDropdown" class="profile-dropdown">
            <button @click.stop="handleLogout" class="dropdown-item">
              <i class="fas fa-sign-out-alt"></i>
              <span>Logout</span>
            </button>
          </div>
        </Transition>
      </div>
    </aside>

    <main class="main-content">
      <header class="top-header">
        <div class="header-decoration">
          <div class="header-logo">
            <img src="/src/assets/logo-medicare.png" alt="MediCare" class="header-logo-img" />
          </div>
          <span class="header-tagline">Votre santé, notre priorité</span>
        </div>
        <div class="header-actions">
          <button class="theme-toggle" @click="$toggleTheme()" title="Toggle theme">
            <i class="fas fa-moon"></i>
          </button>
        </div>
      </header>

      <div :class="['content-area', isAIAssistantPage ? 'ai-assistant-mode' : '']">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const showLogoutDropdown = ref(false);
const sidebarCollapsed = ref(localStorage.getItem('patientSidebarCollapsed') === 'true');

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  localStorage.setItem('patientSidebarCollapsed', sidebarCollapsed.value);
};

const isAIAssistantPage = computed(() => route.path === '/patient/ai-assistant');

const menuItems = [
  { path: '/patient/dashboard', label: 'Dashboard', icon: 'fas fa-th-large' },
  { path: '/patient/appointments', label: 'Mes Rendez-vous', icon: 'fas fa-calendar-check' },
  { path: '/patient/medical-records', label: 'Dossier Médical', icon: 'fas fa-folder-open' },
  { path: '/patient/consultations', label: 'Consultations', icon: 'fas fa-stethoscope' },
  { path: '/patient/invoices', label: 'Factures', icon: 'fas fa-file-invoice-dollar' },
  { path: '/patient/profile', label: 'Mon Profil', icon: 'fas fa-user' },
  { path: '/patient/ai-assistant', label: 'Assistant IA', icon: 'fas fa-robot' },
];

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/');
};

const userData = computed(() => {
  const user = localStorage.getItem('patient_user');
  if (user) {
    try {
      return JSON.parse(user);
    } catch (e) {
      return { nom: 'Patient', prenom: 'Cher' };
    }
  }
  return { nom: 'Patient', prenom: 'Cher' };
});

const userFullName = computed(() => {
  return `${userData.value.prenom} ${userData.value.nom}`.trim();
});

const userInitials = computed(() => {
  const prenom = userData.value.prenom?.charAt(0) || 'P';
  const nom = userData.value.nom?.charAt(0) || 'T';
  return `${prenom}${nom}`.toUpperCase();
});

const toggleLogoutDropdown = () => {
  showLogoutDropdown.value = !showLogoutDropdown.value;
};

const handleLogout = () => {
  showLogoutDropdown.value = false;
  localStorage.removeItem('patient_token');
  localStorage.removeItem('patient_user');
  localStorage.removeItem('user_role');
  router.push('/login');
};

const handleClickOutside = (e) => {
  if (showLogoutDropdown.value) {
    const profile = document.querySelector('.sidebar-profile');
    if (profile && !profile.contains(e.target)) {
      showLogoutDropdown.value = false;
    }
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.patient-layout {
  display: flex;
  height: 100vh;
  width: 100%;
  overflow: hidden;
  font-family: 'Playfair Display', 'Times New Roman', Georgia, serif;
}

.sidebar {
  width: 260px;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--sidebar-border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 64px;
  padding: 0 24px;
  flex-shrink: 0;
}

.logo-text {
  font-family: 'Playfair Display', 'Times New Roman', Georgia, serif;
  font-size: 28px;
  font-weight: 700;
  color: var(--sidebar-logo);
  letter-spacing: -0.4px;
}

.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  color: var(--nav-text);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.nav-item:hover {
  background: var(--nav-hover-bg);
  color: var(--nav-hover-text);
}

.nav-item.active {
  background: var(--nav-active-bg);
  color: var(--nav-hover-text);
}

.nav-item span {
  font-family: 'Playfair Display', 'Times New Roman', Georgia, serif;
}

.nav-icon {
  width: 20px;
  text-align: center;
  font-size: 16px;
}

.sidebar-profile {
  padding: 16px;
  border-top: 1px solid var(--sidebar-border);
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
  cursor: pointer;
  position: relative;
  user-select: none;
}

.sidebar-profile:hover {
  background: var(--nav-hover-bg);
}

.profile-arrow {
  font-size: 10px;
  color: var(--nav-text);
  transition: transform 0.2s;
}

.profile-dropdown {
  position: absolute;
  bottom: 100%;
  left: 12px;
  right: 12px;
  background: var(--dropdown-bg);
  border: 1px solid var(--sidebar-border);
  border-radius: 10px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  overflow: hidden;
  z-index: 50;
}

.dropdown-item {
  width: 100%;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  border: none;
  background: none;
  color: #ef4444;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  font-family: inherit;
}

.dropdown-item:hover {
  background: #fef2f2;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(4px);
}

.profile-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b8d99 0%, #2c6e7a 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 600;
  flex-shrink: 0;
}

.profile-info {
  flex: 1;
  min-width: 0;
}

.profile-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--sidebar-logo);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-role {
  font-size: 12px;
  color: var(--nav-text);
  margin: 0;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  background: var(--content-bg);
}

.top-header {
  height: 64px;
  background: var(--header-bg);
  border-bottom: 1px solid var(--header-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  flex-shrink: 0;
}

.header-decoration {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-tagline {
  font-family: 'Playfair Display', serif;
  font-size: 14px;
  font-style: italic;
  color: var(--nav-text);
}

.header-actions {
  display: flex;
  align-items: center;
}

.header-logo {
  display: flex;
  align-items: center;
}

.header-logo-img {
  height: 36px;
  width: auto;
}

.content-area {
  flex: 1;
  width: 100%;
  padding: 24px 32px 0;
  overflow-y: auto;
  background: var(--content-bg);
  box-sizing: border-box;
}

.content-area.ai-assistant-mode {
  padding: 0;
  overflow: hidden;
}

@media (max-width: 1024px) {
  .sidebar {
    width: 70px;
  }

  .sidebar-logo {
    justify-content: center;
    padding: 0;
  }

  .logo-text,
  .profile-info,
  .profile-arrow,
  .nav-item span {
    display: none;
  }

  .nav-item {
    justify-content: center;
    padding: 14px;
  }

  .sidebar-profile {
    justify-content: center;
    padding: 16px 0;
  }
}

@media (max-width: 768px) {
  .sidebar {
    display: none;
  }
}

/* ========== THEME TOGGLE ========== */
.theme-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid var(--sidebar-border);
  background: var(--toggle-bg);
  color: var(--toggle-color);
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.theme-toggle:hover {
  background: var(--toggle-hover);
  color: var(--sidebar-hover-text);
}

/* ========== SIDEBAR TOGGLE ========== */
.sidebar-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid var(--sidebar-border);
  background: transparent;
  color: var(--toggle-color);
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
  font-size: 13px;
}

.sidebar-toggle:hover {
  background: var(--toggle-hover);
  color: var(--sidebar-hover-text);
}

/* Sidebar collapsed — icon-only */
.sidebar { transition: width 0.3s ease; }

.sidebar-collapsed .sidebar { width: 70px; }

.sidebar-collapsed .sidebar-logo { justify-content: center; padding: 0; }
.sidebar-collapsed .logo-text,
.sidebar-collapsed .profile-info,
.sidebar-collapsed .profile-arrow,
.sidebar-collapsed .nav-item span { display: none; }

.sidebar-collapsed .nav-item { justify-content: center; padding: 14px; }
.sidebar-collapsed .sidebar-profile { justify-content: center; padding: 16px 0; }
.sidebar-collapsed .profile-dropdown { left: 74px; right: auto; width: 180px; }
</style>
