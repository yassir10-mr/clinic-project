<<template>
  <div class="app">
    <!-- Sidebar -->
    <aside v-if="isLoggedIn && !isAuthPage" class="sidebar">
      <!-- Logo -->
      <div class="sidebar-logo">
        <div class="logo-icon-small">
          <i class="fas fa-stethoscope"></i>
        </div>
        <span class="logo-text-small">MediCare</span>
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
    <main :class="['main-content', isAuthPage ? 'full-width' : '']">
      <!-- Header -->
      <header v-if="isLoggedIn && !isAuthPage" class="top-header">
        <div class="header-actions">
          <div class="header-profile">
            <span class="header-name">{{ userFullName }}</span>
            <div class="header-avatar">{{ userInitials }}</div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <div :class="['content-area', isAuthPage ? 'auth-page' : '']">
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

// Hide sidebar on login/landing pages
const isAuthPage = computed(() => {
  return ['/login', '/', '/accueil'].includes(route.path);
});

// Auth
const isLoggedIn = computed(() => {
  return (
    !!localStorage.getItem('admin_token') ||
    !!localStorage.getItem('secretaire_token') ||
    !!localStorage.getItem('token')
  );
});

const userRole = computed(() => {
  return localStorage.getItem('user_role') || 'admin';
});

const isAdmin = computed(() => userRole.value === 'admin');

const userRoleLabel = computed(() => {
  return userRole.value === 'admin' ? 'Admin' : 'Secrétaire';
});

// User data
const userData = computed(() => {
  const key =
    userRole.value === 'admin'
      ? 'admin_user'
      : 'secretaire_user';

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

const handleClickOutside = (event) => {
  if (profileRef.value && !profileRef.value.contains(event.target)) {
    showProfileMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
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
  background: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 100;
  overflow-y: auto; /* Sidebar scrolls if too long */
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.logo-icon-small {
  width: 36px;
  height: 36px;
  background: #2563eb;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 16px;
}

.logo-text-small {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}

/* Sidebar Navigation */
.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow-y: auto; /* Nav scrolls if too many items */
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  color: #64748b;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  flex-shrink: 0;
}

.nav-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.nav-item.active {
  background: #eff6ff;
  color: #2563eb;
}

.nav-item i {
  width: 20px;
  text-align: center;
  font-size: 16px;
}

/* Sidebar Profile */
.sidebar-profile {
  padding: 16px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  position: relative;
  transition: background 0.2s;
  flex-shrink: 0;
}

.sidebar-profile:hover {
  background: #f8fafc;
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
  color: #1e293b;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-role {
  font-size: 12px;
  color: #94a3b8;
  margin: 0;
}

.profile-arrow {
  font-size: 12px;
  color: #94a3b8;
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
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  border: 1px solid #e2e8f0;
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
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
}

.dropdown-divider {
  height: 1px;
  background: #f1f5f9;
  margin: 8px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  color: #374151;
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
  background: #f8fafc;
}

.dropdown-item.logout {
  color: #dc2626;
}

.dropdown-item i {
  width: 16px;
  text-align: center;
  font-size: 14px;
}

/* ========== MAIN CONTENT ========== */
.main-content {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  height: 100vh; /* Full height */
  overflow: hidden; /* Prevent scroll here, handle in content-area */
}

/* Full width for auth pages (login/accueil) */
.main-content.full-width {
  margin-left: 0;
}

/* ========== TOP HEADER ========== */
.top-header {
  height: 64px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 32px;
  flex-shrink: 0; /* Prevent header from shrinking */
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.header-profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-name {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
}

/* ========== CONTENT AREA ========== */
.content-area {
  flex: 1;
  padding: 24px 32px;
  overflow-y: auto; /* 🔥 KEY FIX: Enable scrolling here */
  background: #f8fafc;
}

/* No padding for auth pages */
.content-area.auth-page {
  padding: 0;
  overflow-y: auto; /* Keep scroll for auth pages too */
}

/* ========== RESPONSIVE ========== */
@media (max-width: 1024px) {
  .sidebar {
    width: 70px;
  }

  .sidebar-logo {
    justify-content: center;
    padding: 20px 0;
  }

  .logo-text-small,
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

  .main-content {
    margin-left: 70px;
  }

  .main-content.full-width {
    margin-left: 0;
  }
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    width: 260px;
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
  }
}
</style>