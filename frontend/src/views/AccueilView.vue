<template>
  <div class="accueil">
    <!-- Header avec bouton login -->
    <header class="header">
      <div class="header-logo">
        <img :src="logoImage" alt="MediCare" class="logo-img" />
        <span class="logo-text">MediCare</span>
      </div>
      <div class="nav-buttons">
        <router-link to="/login" class="btn-login">Sign In</router-link>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
      <h1>Welcome to MediCare</h1>
      <p class="subtitle">Professional Clinic Management System</p>
      <p class="description">
        Streamline your clinic operations with our comprehensive management solution.
        Manage patients, appointments, consultations, and billing all in one place.
      </p>
      <router-link to="/login" class="btn-get-started">Get Started →</router-link>
    </section>

    <!-- Features -->
    <section class="features">
      <h2>Our Features</h2>
      <div class="features-grid">
        <div class="feature-card">
          <div class="icon">👥</div>
          <h3>Patient Management</h3>
          <p>Complete patient records and medical history tracking</p>
        </div>
        <div class="feature-card">
          <div class="icon">👨‍⚕️</div>
          <h3>Doctor Scheduling</h3>
          <p>Efficient appointment and doctor management</p>
        </div>
        <div class="feature-card">
          <div class="icon">📅</div>
          <h3>Appointments</h3>
          <p>Easy booking and calendar management</p>
        </div>
        <div class="feature-card">
          <div class="icon">💰</div>
          <h3>Billing & Invoicing</h3>
          <p>Automated billing and payment tracking</p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <p>© 2026 MediCare. All rights reserved.</p>
    </footer>
  </div>
</template>

<script setup>
import logoImage from '@/assets/logo-medicare.png';
</script>

<style scoped>
.accueil {
  min-height: 100vh;
  background: linear-gradient(180deg, #f0f7ff 0%, #ffffff 100%);
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 60px;
  background: white;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.header-logo {
  display: flex;
  align-items: center;
  gap: 12px;
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
  color: #1e293b;
  letter-spacing: -0.5px;
}

.nav-buttons {
  display: flex;
  gap: 16px;
}

.btn-login {
  padding: 10px 24px;
  background: #2563eb;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-login:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

/* Hero */
.hero {
  text-align: center;
  padding: 80px 20px 60px;
  max-width: 800px;
  margin: 0 auto;
}

.hero h1 {
  font-size: 48px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 16px;
}

.subtitle {
  font-size: 20px;
  color: #2563eb;
  font-weight: 500;
  margin-bottom: 20px;
}

.description {
  font-size: 16px;
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 32px;
}

.btn-get-started {
  display: inline-block;
  padding: 16px 32px;
  background: #2563eb;
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-get-started:hover {
  background: #1d4ed8;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
}

/* Features */
.features {
  padding: 60px 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.features h2 {
  text-align: center;
  font-size: 32px;
  color: #1e293b;
  margin-bottom: 40px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.feature-card {
  background: white;
  padding: 32px 24px;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  transition: all 0.3s;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}

.feature-card .icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.feature-card h3 {
  font-size: 18px;
  color: #1e293b;
  margin-bottom: 8px;
}

.feature-card p {
  font-size: 14px;
  color: #64748b;
  line-height: 1.5;
}

/* Footer */
.footer {
  text-align: center;
  padding: 40px 20px;
  color: #94a3b8;
  font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
  .header {
    padding: 16px 20px;
  }
  
  .hero h1 {
    font-size: 32px;
  }
  
  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>