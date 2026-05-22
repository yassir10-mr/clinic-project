<template>
  <div class="infirmier-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <!-- Logo -->
      <div class="sidebar-logo">
        <div class="logo-icon">
          <img :src="logoImage" alt="MediCare Logo" class="logo-img" />
        </div>
        <span class="logo-text">MediCare</span>
      </div>

      <!-- Navigation -->
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

      <!-- Profile -->
      <div class="sidebar-profile">
        <div class="profile-avatar">{{ userInitials }}</div>
        <div class="profile-info">
          <p class="profile-name">{{ userFullName }}</p>
          <p class="profile-role">Nurse</p>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <main class="main-content">
      <!-- Header -->
      <header class="top-header">
        <div class="header-search">
          <i class="fas fa-search search-icon"></i>
          <input type="text" placeholder="Search patients, appointments..." v-model="searchQuery" />
        </div>
        <div class="header-actions">
          <div class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
          </div>
          <div class="header-profile">
            <div class="header-avatar">{{ userInitials }}</div>
            <span class="header-name">{{ userFullName }}</span>
          </div>
        </div>
      </header>

      <!-- Content -->
      <div class="content-area">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import logoImage from '@/assets/logo-medicare.png';

const route = useRoute();
const searchQuery = ref('');

const menuItems = [
  { path: '/infirmier/dashboard', label: 'Dashboard', icon: 'fas fa-th-large' },
  { path: '/infirmier/patients', label: 'Patients', icon: 'fas fa-user-injured' },
  { path: '/infirmier/appointments', label: 'Appointments', icon: 'fas fa-calendar-check' },
  { path: '/infirmier/medical-records', label: 'Medical Records', icon: 'fas fa-folder-open' },
];

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/');
};

const userData = computed(() => {
  const user = localStorage.getItem('infirmier_user');
  if (user) {
    try {
      return JSON.parse(user);
    } catch (e) {
      return { nom: 'El Bouazzati', prenom: 'Raed' };
    }
  }
  return { nom: 'El Bouazzati', prenom: 'Raed' };
});

const userFullName = computed(() => {
  return `${userData.value.prenom} ${userData.value.nom}`.trim();
});

const userInitials = computed(() => {
  const prenom = userData.value.prenom?.charAt(0) || 'R';
  const nom = userData.value.nom?.charAt(0) || 'E';
  return `${prenom}${nom}`.toUpperCase();
});
</script>

<style scoped>
/* ========== LAYOUT ========== */
.infirmier-layout {
  display: flex;
  height: 100vh;
  width: 100%;
  max-width: none;
  overflow: hidden;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ========== SIDEBAR ========== */
.sidebar {
  width: 260px;
  background: #ffffff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 100;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.logo-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-img {
  width: 56px;
  height: 56px;
  object-fit: contain;
}

.logo-text {
  font-family: 'Playfair Display', 'Times New Roman', Georgia, serif;
  font-size: 28px;
  font-weight: 800;
  color: #1a1a2e;
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
  border-radius: 10px;
  color: #64748b;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.nav-item:hover {
  background: #f8fafc;
  color: #3b8d99;
}

.nav-item.active {
  background: #effafd;
  color: #3b8d99;
}

.nav-icon {
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
  flex-shrink: 0;
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
  color: #1a1a2e;
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

/* ========== MAIN CONTENT ========== */
.main-content {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  background: #f5f7fa;
  width: calc(100% - 260px);
  min-width: 0;
  max-width: none;
}

/* ========== TOP HEADER ========== */
.top-header {
  height: 64px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  flex-shrink: 0;
}

.header-search {
  position: relative;
  width: 420px;
}

.header-search input {
  width: 100%;
  padding: 10px 16px 10px 40px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  color: #1a1a2e;
  background: #f8fafc;
  outline: none;
  transition: all 0.2s;
}

.header-search input:focus {
  border-color: #3b8d99;
  background: #ffffff;
}

.header-search input::placeholder {
  color: #94a3b8;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 14px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.notification-bell {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  background: #f8fafc;
  color: #64748b;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.notification-bell:hover {
  background: #effafd;
  color: #3b8d99;
}

.notification-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 16px;
  height: 16px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 600;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.header-profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b8d99 0%, #2c6e7a 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
}

.header-name {
  font-size: 14px;
  font-weight: 500;
  color: #1a1a2e;
}

/* ========== CONTENT AREA ========== */
.content-area {
  flex: 1;
  width: 100%;
  max-width: none;
  padding: 24px 32px 0;
  overflow-y: auto;
  background: #f5f7fa;
  box-sizing: border-box;
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

  .logo-text,
  .profile-info,
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
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    width: 260px;
  }

  .main-content {
    margin-left: 0;
  }
}
</style>
