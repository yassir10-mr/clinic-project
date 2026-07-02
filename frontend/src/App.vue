<template>
  <div class="app">
    <!-- Sidebar (hidden on pages with dedicated layout) -->
    <aside v-if="isLoggedIn && !isAuthPage && !isDedicatedLayoutPage" class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <!-- Logo -->
      <div class="sidebar-logo">
        <span class="logo-text">MediCare</span>
        <button class="sidebar-toggle" @click="toggleSidebar" :title="sidebarCollapsed ? 'Ouvrir' : 'Fermer'">
          <i :class="sidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ active: $route.path === item.path || $route.path.startsWith(item.path + '/') }"
        >
          <i :class="item.icon"></i>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <!-- Profile -->
      <div class="sidebar-profile" @click="toggleProfileMenu" ref="profileRef">
        <div class="profile-avatar">{{ userInitials }}</div>

        <div class="profile-info">
          <p class="profile-name">{{ userFullName }}</p>
          <p class="profile-role">{{ userRoleLabel }}</p>
        </div>

        <i class="fas fa-chevron-up profile-arrow" :class="{ open: showProfileMenu }"></i>

        <!-- Dropdown -->
        <div v-if="showProfileMenu" class="profile-dropdown">
          <div class="dropdown-header">
            <p class="dropdown-title">My Account</p>
          </div>

          <div class="dropdown-divider"></div>

          <router-link
            to="/settings"
            class="dropdown-item"
            @click="showProfileMenu = false"
            v-if="isAdmin"
          >
            <i class="fas fa-cog"></i>
            <span>Settings</span>
          </router-link>

          <div class="dropdown-divider" v-if="isAdmin"></div>

          <button @click="handleLogout" class="dropdown-item logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <main :class="['main-content', isAuthPage || isDedicatedLayoutPage ? 'full-width' : '']">
      <!-- Header (hidden on pages with dedicated layout) -->
      <header v-if="isLoggedIn && !isAuthPage && !isDedicatedLayoutPage" class="top-header">
        <div class="header-decoration">
          <div class="header-logo">
            <img src="/src/assets/logo-medicare.png" alt="MediCare" class="header-logo-img" />
          </div>
          <span class="header-tagline">"Votre santé, notre priorité"</span>
        </div>
        <div class="header-actions">
        <button class="theme-toggle" @click="toggleTheme" :title="isDark ? 'Mode clair' : 'Mode sombre'">
          <svg v-if="isDark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
        </div>
      </header>

      <!-- Content -->
      <div :class="['content-area', isAuthPage ? 'auth-page' : '', isDedicatedLayoutPage ? 'no-padding' : '', isAIAssistantPage ? 'ai-assistant-mode' : '']">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { logout } from '@/services/api.js';

const router = useRouter();
const route = useRoute();

const showProfileMenu = ref(false);
const profileRef = ref(null);
const isDark = ref(localStorage.getItem('theme') === 'dark');
const sidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');

const toggleTheme = () => {
  isDark.value = !isDark.value;
  document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

// Hide sidebar on login/landing pages OR on pages with their own dedicated layout
const isAuthPage = computed(() => {
  return ['/login', '/', '/accueil'].includes(route.path);
});

const isDedicatedLayoutPage = computed(() => {
  return route.path.startsWith('/infirmier') || route.path.startsWith('/patient');
});

const isAIAssistantPage = computed(() => {
  return route.path === '/ai-assistant';
});

// Auth
const isLoggedIn = computed(() => {
  return (
    !!localStorage.getItem('admin_token') ||
    !!localStorage.getItem('secretaire_token') ||
    !!localStorage.getItem('infirmier_token') ||
    !!localStorage.getItem('patient_token') ||
    !!localStorage.getItem('token')
  );
});

const userRole = computed(() => {
  return localStorage.getItem('user_role') || 'admin';
});

const isAdmin = computed(() => userRole.value === 'admin');

const userRoleLabel = computed(() => {
  if (userRole.value === 'admin') return 'Admin';
  if (userRole.value === 'infirmier') return 'Nurse';
  return 'Secrétaire';
});

// User data
const userData = computed(() => {
  let key = 'user';
  if (userRole.value === 'admin') key = 'admin_user';
  else if (userRole.value === 'secretaire') key = 'secretaire_user';
  else if (userRole.value === 'infirmier') key = 'infirmier_user';

  const user =
    localStorage.getItem(key) ||
    localStorage.getItem('user');

  return user
    ? JSON.parse(user)
    : { nom: 'User', prenom: 'Admin' };
});

const userFullName = computed(() => {
  return `${userData.value.prenom} ${userData.value.nom}`.trim();
});

const userInitials = computed(() => {
  const prenom = userData.value.prenom?.charAt(0) || 'A';
  const nom = userData.value.nom?.charAt(0) || 'D';

  return `${prenom}${nom}`.toUpperCase();
});

// Menu
const menuItems = computed(() => {
  if (userRole.value === 'secretaire') {
    return [
      { path: '/secretaire/dashboard', label: 'Dashboard', icon: 'fas fa-th-large' },
      { path: '/secretaire/patients', label: 'Patients', icon: 'fas fa-users' },
      { path: '/secretaire/appointments', label: 'Appointments', icon: 'fas fa-calendar-check' },
      { path: '/secretaire/consultations', label: 'Consultations', icon: 'fas fa-stethoscope' },
      { path: '/secretaire/invoices', label: 'Invoices', icon: 'fas fa-file-invoice-dollar' },
      { path: '/secretaire/doctors', label: 'Doctors', icon: 'fas fa-user-md' },
    ];
  }

  return [
    { path: '/dashboard', label: 'Dashboard', icon: 'fas fa-th-large' },
    { path: '/ai-assistant', label: 'Assistant IA', icon: 'fas fa-robot' },
    { path: '/patients', label: 'Patients', icon: 'fas fa-user-injured' },
    { path: '/rendez-vous', label: 'Appointments', icon: 'fas fa-calendar-check' },
    { path: '/consultations', label: 'Consultations', icon: 'fas fa-stethoscope' },
    { path: '/medecins', label: 'Doctors', icon: 'fas fa-user-md' },
    { path: '/factures', label: 'Invoices', icon: 'fas fa-file-invoice-dollar' },
    { path: '/dossiers', label: 'Medical Records', icon: 'fas fa-folder-open' },
    { path: '/settings', label: 'Settings', icon: 'fas fa-cog' },
  ];
});

// Dropdown
const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value;
};

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value);
};

const handleClickOutside = (event) => {
  if (profileRef.value && !profileRef.value.contains(event.target)) {
    showProfileMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  if (isDark.value) document.documentElement.dataset.theme = 'dark';
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Logout
const handleLogout = async () => {
  showProfileMenu.value = false;

  try {
    await logout();
  } catch (e) {
    console.log('Logout API error');
  }

  localStorage.removeItem('admin_token');
  localStorage.removeItem('admin_user');
  localStorage.removeItem('secretaire_token');
  localStorage.removeItem('secretaire_user');
  localStorage.removeItem('infirmier_token');
  localStorage.removeItem('infirmier_user');
  localStorage.removeItem('user_role');
  localStorage.removeItem('token');
  localStorage.removeItem('user');

  router.push('/login');
};
</script>

<style>
/* ========== GLOBAL RESET ========== */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow: hidden; /* Prevent body scroll, handle in app */
}

#app {
  height: 100vh;
}
</style>

<style scoped>
/* ========== APP LAYOUT ========== */
.app {
  display: flex;
  height: 100vh; /* Full viewport height */
  overflow: hidden; /* Prevent app scroll, handle in children */
}

/* ========== SIDEBAR ========== */
.sidebar {
  width: 260px;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--sidebar-border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  overflow-y: auto;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  height: 64px;
  padding: 0 24px;
  flex-shrink: 0;
  gap: 12px;
}

.sidebar-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 6px;
  background: var(--toggle-bg);
  color: var(--toggle-color);
  cursor: pointer;
  transition: all 0.2s;
  font-size: 12px;
  flex-shrink: 0;
  margin-left: auto;
}

.sidebar-toggle:hover {
  background: var(--toggle-hover);
  color: var(--sidebar-hover-text);
}

.logo-text {
  font-family: 'Playfair Display', 'Times New Roman', Georgia, serif;
  font-size: 28px;
  font-weight: 700;
  color: var(--sidebar-logo);
  letter-spacing: -0.4px;
}

/* Sidebar Navigation */
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
  border-radius: 8px;
  color: var(--sidebar-text);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  flex-shrink: 0;
}

.nav-item:hover {
  background: var(--sidebar-hover-bg);
  color: var(--sidebar-hover-text);
}

.nav-item.active {
  background: var(--sidebar-active-bg);
  color: var(--sidebar-active-text);
}

.nav-item span {
  font-family: 'Playfair Display', 'Times New Roman', Georgia, serif;
}

.nav-item i {
  width: 20px;
  text-align: center;
  font-size: 16px;
}

/* Sidebar Profile */
.sidebar-profile {
  padding: 16px;
  border-top: 1px solid var(--sidebar-divider);
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  position: relative;
  transition: background 0.2s;
  flex-shrink: 0;
}

.sidebar-profile:hover {
  background: var(--sidebar-hover-bg);
}

.profile-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  color: var(--profile-name);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-role {
  font-size: 12px;
  color: var(--profile-role);
  margin: 0;
}

.profile-arrow {
  font-size: 12px;
  color: var(--profile-arrow);
  transition: transform 0.2s;
  flex-shrink: 0;
}

.profile-arrow.open {
  transform: rotate(180deg);
}

/* Profile Dropdown */
.profile-dropdown {
  position: absolute;
  bottom: 100%;
  left: 12px;
  right: 12px;
  background: var(--dropdown-bg);
  border-radius: 12px;
  box-shadow: var(--dropdown-shadow);
  border: 1px solid var(--dropdown-border);
  padding: 8px 0;
  margin-bottom: 8px;
  z-index: 200;
}

.dropdown-header {
  padding: 8px 16px;
}

.dropdown-title {
  font-size: 12px;
  font-weight: 600;
  color: var(--profile-role);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
}

.dropdown-divider {
  height: 1px;
  background: var(--sidebar-divider);
  margin: 8px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  color: var(--dropdown-item);
  text-decoration: none;
  font-size: 14px;
  transition: background 0.2s;
  border: none;
  background: none;
  width: 100%;
  cursor: pointer;
  font-family: inherit;
}

.dropdown-item:hover {
  background: var(--dropdown-hover);
}

.dropdown-item.logout {
  color: var(--dropdown-item-logout);
}

.dropdown-item i {
  width: 16px;
  text-align: center;
  font-size: 14px;
}

/* ========== MAIN CONTENT ========== */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

/* ========== TOP HEADER ========== */
.top-header {
  height: 64px;
  background: var(--header-bg);
  border-bottom: 1px solid var(--header-border);
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 32px;
  flex-shrink: 0;
}

.header-decoration {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-tagline {
  font-family: 'Playfair Display', 'Times New Roman', Georgia, serif;
  font-size: 14px;
  font-style: italic;
  color: var(--header-tagline);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-left: auto;
}

.header-logo {
  display: flex;
  align-items: center;
}

.header-logo-img {
  height: 36px;
  width: auto;
}

/* ========== CONTENT AREA ========== */
.content-area {
  flex: 1;
  padding: 24px 32px;
  overflow-y: auto;
  background: var(--content-bg);
}

/* No padding for auth pages */
.content-area.auth-page {
  padding: 0;
  overflow-y: auto; /* Keep scroll for auth pages too */
}

/* No padding for pages with dedicated layout (infirmier, etc.) */
.content-area.no-padding {
  padding: 0;
  overflow-y: hidden;
}

/* Full bleed for AI assistant */
.content-area.ai-assistant-mode {
  padding: 0;
  overflow: hidden;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 1024px) {
  .sidebar:not(.collapsed) {
    width: 70px;
  }

  .sidebar:not(.collapsed) .sidebar-logo {
    justify-content: center;
    padding: 0;
  }

  .sidebar:not(.collapsed) .logo-text,
  .sidebar:not(.collapsed) .profile-info,
  .sidebar:not(.collapsed) .profile-arrow,
  .sidebar:not(.collapsed) .nav-item span {
    display: none;
  }

  .sidebar:not(.collapsed) .nav-item {
    justify-content: center;
    padding: 14px;
  }

  .sidebar:not(.collapsed) .sidebar-profile {
    justify-content: center;
    padding: 16px 0;
  }
}

@media (max-width: 768px) {
  .sidebar {
    display: none;
  }
}

/* ========== COLLAPSED SIDEBAR ========== */
.sidebar.collapsed {
  width: 70px;
}

.sidebar.collapsed .sidebar-logo {
  justify-content: center;
  padding: 0;
}

.sidebar.collapsed .logo-text,
.sidebar.collapsed .profile-info,
.sidebar.collapsed .profile-arrow,
.sidebar.collapsed .nav-item span,
.sidebar.collapsed .nav-item {
  justify-content: center;
  padding: 14px;
}

.sidebar.collapsed .sidebar-profile {
  justify-content: center;
  padding: 16px 0;
}

/* Profile dropdown in collapsed mode — extends to right */
.sidebar.collapsed .profile-dropdown {
  left: 74px;
  bottom: auto;
  top: -120px;
  width: 180px;
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
</style>