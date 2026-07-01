<template>
  <div class="dashboard-patient">
    <div class="page-header">
      <div>
        <h1 class="page-title">Bienvenue dans votre Espace Patient</h1>
        <p class="page-subtitle">Consultez vos visites, vos factures et prenez vos rendez-vous en ligne.</p>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="grid-main">
        <div class="card">
          <h2 class="card-title">
            <i class="fas fa-stethoscope card-icon"></i>
            Mon Historique Médical
          </h2>
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Médecin</th>
                  <th>Diagnostic / Traitement</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="consultations.length === 0">
                  <td colspan="3" class="empty-row">Aucune consultation enregistrée.</td>
                </tr>
                <tr v-for="c in consultations" :key="c.id_consultation">
                  <td class="td-date">{{ c.date }}</td>
                  <td class="td-doctor">Dr. {{ c.medecin_nom }}</td>
                  <td>
                    <div class="td-diagnostic">{{ c.diagnostic }}</div>
                    <div class="td-traitement">Traitement : {{ c.traitement }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">
            <i class="fas fa-file-invoice-dollar card-icon"></i>
            Mes Factures
          </h2>
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Montant</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="factures.length === 0">
                  <td colspan="3" class="empty-row">Aucune facture disponible.</td>
                </tr>
                <tr v-for="f in factures" :key="f.id_facture">
                  <td class="td-date">{{ f.date }}</td>
                  <td class="td-amount">{{ f.montant_total }} DH</td>
                  <td>
                    <span :class="['badge', f.statut_paiement === 'Payé' ? 'badge-paid' : 'badge-pending']">
                      {{ f.statut_paiement }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="grid-side">
        <div class="card rdv-card">
          <h2 class="card-title">
            <i class="fas fa-calendar-plus card-icon"></i>
            Prendre RDV
          </h2>

          <div v-if="rdvSuccess" class="success-state">
            <div class="success-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <h3>Rendez-vous Confirmé !</h3>
            <p>Votre rendez-vous est prévu le <strong>{{ selectedDate }}/{{ currentMonth }}/{{ currentYear }}</strong> à <strong>{{ selectedTime }}</strong>.</p>
            <button class="btn-new-rdv" @click="resetForm">Nouveau rendez-vous</button>
          </div>

          <form v-else @submit.prevent="submitRDV" class="rdv-form">
            <!-- Calendar -->
            <div class="calendar-box">
              <div class="calendar-header">
                <button type="button" @click="prevMonth" class="cal-nav">
                  <i class="fas fa-chevron-left"></i>
                </button>
                <span class="cal-month">{{ monthNames[currentMonth - 1] }} {{ currentYear }}</span>
                <button type="button" @click="nextMonth" class="cal-nav">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
              <div class="cal-weekdays">
                <span v-for="d in ['Lu','Ma','Me','Je','Ve','Sa','Di']" :key="d">{{ d }}</span>
              </div>
              <div class="cal-grid">
                <div v-for="b in blankDays" :key="'b'+b" class="cal-day blank"></div>
                <button
                  v-for="d in monthDays" :key="d"
                  type="button"
                  :disabled="isPast(d)"
                  :class="['cal-day', { selected: selectedDate === d, past: isPast(d) }]"
                  @click="selectDate(d)"
                >
                  {{ d }}
                </button>
              </div>
            </div>

            <!-- Time Slots -->
            <div class="time-section">
              <label class="field-label">Créneaux disponibles</label>
              <div class="time-grid">
                <button
                  v-for="t in timeSlots" :key="t"
                  type="button"
                  :disabled="!selectedDate || isBooked(t)"
                  :class="['time-btn', { selected: selectedTime === t, booked: isBooked(t) }]"
                  @click="selectedTime = t"
                >
                  {{ t }}
                </button>
              </div>
            </div>

            <!-- Service -->
            <div class="form-group">
              <label class="field-label">Service médical <span class="required">*</span></label>
              <div class="custom-select" @click="toggleServiceDropdown" v-click-outside="closeServiceDropdown">
                <button type="button" class="custom-select-trigger" :class="{ active: serviceOpen }">
                  <span :class="['custom-select-placeholder', { filled: form.service }]">{{ form.service || 'S&eacute;lectionner...' }}</span>
                  <svg class="select-arrow" :class="{ open: serviceOpen }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <Transition name="dropdown-fade">
                  <ul v-if="serviceOpen" class="custom-select-menu">
                    <li v-for="s in services" :key="s" :class="['custom-select-item', { selected: form.service === s }]" @click.stop="selectService(s)">{{ s }}</li>
                  </ul>
                </Transition>
              </div>
            </div>

            <!-- Doctor -->
            <div class="form-group">
              <label class="field-label">Médecin <span class="required">*</span></label>
              <div class="custom-select" @click="toggleDoctorDropdown" v-click-outside="closeDoctorDropdown">
                <button type="button" class="custom-select-trigger" :class="{ active: doctorOpen }">
                  <span :class="['custom-select-placeholder', { filled: form.id_medecin }]">{{ selectedDoctorLabel || 'Choisir un m&eacute;decin...' }}</span>
                  <svg class="select-arrow" :class="{ open: doctorOpen }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <Transition name="dropdown-fade">
                  <ul v-if="doctorOpen" class="custom-select-menu">
                    <li v-for="doc in filteredDoctors" :key="doc.id_medecin" :class="['custom-select-item', { selected: form.id_medecin === doc.id_medecin }]" @click.stop="selectDoctor(doc.id_medecin)">Dr. {{ doc.prenom }} {{ doc.nom }} — {{ doc.specialite }}</li>
                    <li v-if="!filteredDoctors.length" class="custom-select-item disabled">Aucun m&eacute;decin disponible</li>
                  </ul>
                </Transition>
              </div>
            </div>

            <!-- Motif -->
            <div class="form-group">
              <label class="field-label">Motif <span class="optional">(optionnel)</span></label>
              <textarea v-model="form.motif" rows="2" class="form-textarea" placeholder="Décrivez brièvement la raison..."></textarea>
            </div>

            <button type="submit" :disabled="!selectedDate || !selectedTime || !form.service || !form.id_medecin || submitting" class="btn-submit">
              <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
              <span v-else><i class="fas fa-check"></i> Confirmer le Rendez-vous</span>
            </button>

            <p v-if="error" class="error-msg">{{ error }}</p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const API = '/api';

const consultations = ref([]);
const factures = ref([]);
const medecins = ref([]);
const bookedSlots = ref([]);

const today = new Date();
const currentMonth = ref(today.getMonth() + 1);
const currentYear = ref(today.getFullYear());
const selectedDate = ref(null);
const selectedTime = ref('');
const submitting = ref(false);
const error = ref('');
const rdvSuccess = ref(false);

const monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

const form = ref({
  id_patient: 1,
  id_medecin: '',
  service: '',
  motif: ''
});

// Custom dropdown state
const serviceOpen = ref(false);
const doctorOpen = ref(false);

const services = ['Cardiologie','Neurologie','Pédiatrie','Orthopédie','Ophtalmologie','Dermatologie','Gynécologie','Gastro-entérologie'];

const selectedDoctorLabel = computed(() => {
  if (!form.value.id_medecin) return '';
  const doc = medecins.value.find(d => d.id_medecin === form.value.id_medecin);
  return doc ? `Dr. ${doc.prenom} ${doc.nom} — ${doc.specialite}` : '';
});

const toggleServiceDropdown = () => {
  serviceOpen.value = !serviceOpen.value;
  doctorOpen.value = false;
};

const closeServiceDropdown = () => {
  serviceOpen.value = false;
};

const toggleDoctorDropdown = () => {
  if (!filteredDoctors.value.length) return;
  doctorOpen.value = !doctorOpen.value;
  serviceOpen.value = false;
};

const closeDoctorDropdown = () => {
  doctorOpen.value = false;
};

const selectService = (s) => {
  form.value.service = s;
  form.value.id_medecin = '';
  serviceOpen.value = false;
};

const selectDoctor = (id) => {
  form.value.id_medecin = id;
  doctorOpen.value = false;
};

// Click-outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.__clickOutside = (e) => {
      if (!el.contains(e.target)) binding.value();
    };
    document.addEventListener('click', el.__clickOutside);
  },
  unmounted(el) {
    document.removeEventListener('click', el.__clickOutside);
  }
};

const idPatient = computed(() => {
  const user = localStorage.getItem('patient_user');
  if (user) {
    try { const u = JSON.parse(user); return u.id || u.id_patient || 1; } catch { return 1; }
  }
  return 1;
});

const daysInMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value, 0).getDate();
});

const firstDayIndex = computed(() => {
  const d = new Date(currentYear.value, currentMonth.value - 1, 1);
  return (d.getDay() + 6) % 7;
});

const blankDays = computed(() => firstDayIndex.value);

const monthDays = computed(() => daysInMonth.value);

const timeSlots = [];
for (let h = 8; h <= 17; h++) {
  timeSlots.push(`${String(h).padStart(2, '0')}:00`);
  timeSlots.push(`${String(h).padStart(2, '0')}:30`);
}

const filteredDoctors = computed(() => {
  if (!form.value.service) return medecins.value;
  return medecins.value.filter(d =>
    d.specialite.toLowerCase().includes(form.value.service.toLowerCase())
  );
});

const isPast = (d) => {
  const date = new Date(currentYear.value, currentMonth.value - 1, d);
  const t = new Date();
  t.setHours(0,0,0,0);
  return date < t;
};

const isBooked = (t) => bookedSlots.value.includes(t);

const selectDate = (d) => {
  selectedDate.value = d;
  selectedTime.value = '';
  form.value.date_rdv = `${currentYear.value}-${String(currentMonth.value).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
  fetchAvailability();
};

const prevMonth = () => {
  if (currentMonth.value === 1) {
    currentMonth.value = 12;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
  selectedDate.value = null;
  selectedTime.value = '';
  bookedSlots.value = [];
};

const nextMonth = () => {
  if (currentMonth.value === 12) {
    currentMonth.value = 1;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
  selectedDate.value = null;
  selectedTime.value = '';
  bookedSlots.value = [];
};

const fetchAvailability = async () => {
  if (!selectedDate.value) return;
  const dateStr = `${currentYear.value}-${String(currentMonth.value).padStart(2, '0')}-${String(selectedDate.value).padStart(2, '0')}`;
  try {
    let url = `${API}/patient/disponibilites/${dateStr}`;
    if (form.value.id_medecin) url += `?medecin_id=${form.value.id_medecin}`;
    const res = await fetch(url);
    const data = await res.json();
    if (data.success) bookedSlots.value = data.reserve;
  } catch (e) {
    console.error('Failed to fetch availability', e);
  }
};

const fetchDoctors = async () => {
  try {
    const res = await fetch(`${API}/patient/medecins`);
    const data = await res.json();
    if (data.success) medecins.value = data.data;
  } catch (e) {
    console.error('Failed to fetch doctors', e);
  }
};

const loadData = async () => {
  form.value.id_patient = idPatient.value;
  try {
    const [resCons, resFact] = await Promise.all([
      fetch(`${API}/patient/${idPatient.value}/consultations`),
      fetch(`${API}/patient/${idPatient.value}/factures`)
    ]);
    const dataCons = await resCons.json();
    if (dataCons.success) consultations.value = dataCons.consultations;
    const dataFact = await resFact.json();
    if (dataFact.success) factures.value = dataFact.factures;
  } catch (e) {
    console.error("Erreur lors de la récupération des données", e);
  }
};

const submitRDV = async () => {
  submitting.value = true;
  error.value = '';
  try {
    const payload = {
      id_patient: idPatient.value,
      id_medecin: form.value.id_medecin || null,
      date_rdv: form.value.date_rdv,
      heure: selectedTime.value,
      motif: form.value.motif || form.value.service || 'Consultation',
      service: form.value.service
    };
    const res = await fetch(`${API}/patient/prendre-rdv`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      rdvSuccess.value = true;
    } else {
      error.value = data.message || 'Erreur lors de la prise de rendez-vous.';
    }
  } catch (e) {
    error.value = 'Erreur lors de la prise de rendez-vous.';
  } finally {
    submitting.value = false;
  }
};

const resetForm = () => {
  rdvSuccess.value = false;
  selectedDate.value = null;
  selectedTime.value = '';
  form.value.id_medecin = '';
  form.value.service = '';
  form.value.motif = '';
  bookedSlots.value = [];
  error.value = '';
};

onMounted(() => {
  loadData();
  fetchDoctors();
});

watch(() => form.value.id_medecin, () => {
  if (selectedDate.value) {
    selectedTime.value = '';
    fetchAvailability();
  }
});
</script>

<style scoped>
.dashboard-patient {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 6px;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  flex: 1;
}

.grid-main {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.grid-side {
  display: flex;
  flex-direction: column;
}

.card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.rdv-card {
  position: sticky;
  top: 24px;
}

.card-title {
  font-size: 16px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-icon {
  color: #3b8d99;
  font-size: 18px;
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 10px 12px;
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 12px;
  font-size: 13px;
  color: #475569;
  border-bottom: 1px solid #f1f5f9;
}

.data-table tbody tr:hover {
  background: #f8fafc;
}

.empty-row {
  text-align: center;
  color: #64748b;
  padding: 24px !important;
  font-size: 13px;
}

.td-date {
  font-weight: 600;
  color: #1a1a2e;
  white-space: nowrap;
}

.td-doctor {
  white-space: nowrap;
}

.td-diagnostic {
  font-weight: 600;
  color: #1a1a2e;
  margin-bottom: 2px;
}

.td-traitement {
  font-size: 12px;
  color: #475569;
}

.td-amount {
  font-weight: 700;
  color: #1a1a2e;
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 20px;
}

.badge-paid {
  background: #ecfdf5;
  color: #059669;
}

.badge-pending {
  background: #fef3c7;
  color: #d97706;
}

/* ========== RDV FORM ========== */
.rdv-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Calendar */
.calendar-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px;
}

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.cal-nav {
  background: none;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.cal-nav:hover {
  background: #e2e8f0;
  color: #3b8d99;
}

.cal-month {
  font-size: 13px;
  font-weight: 600;
  color: #1a1a2e;
}

.cal-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
  margin-bottom: 4px;
}

.cal-weekdays span {
  text-align: center;
  font-size: 10px;
  font-weight: 600;
  color: #475569;
  padding: 4px 0;
}

.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}

.cal-day {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 500;
  color: #1a1a2e;
  border: none;
  background: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s;
}

.cal-day:hover:not(.blank):not(.past) {
  background: #e2e8f0;
}

.cal-day.selected {
  background: #3b8d99;
  color: white;
  font-weight: 600;
}

.cal-day.past {
  opacity: 0.3;
  cursor: not-allowed;
}

.cal-day.blank {
  pointer-events: none;
}

/* Time Slots */
.time-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.time-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}

.time-btn {
  padding: 8px 4px;
  font-size: 12px;
  font-weight: 500;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
  color: #1a1a2e;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}

.time-btn:hover:not(:disabled) {
  border-color: #3b8d99;
  background: #f0fdfa;
}

.time-btn.selected {
  background: #3b8d99;
  color: white;
  border-color: #3b8d99;
}

.time-btn.booked {
  opacity: 0.3;
  cursor: not-allowed;
  text-decoration: line-through;
}

/* Form Fields */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.field-label {
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.required {
  font-weight: 600;
  color: #ef4444;
  font-size: 12px;
}

.form-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 13px;
  color: #1a1a2e;
  background: #f8fafc;
  outline: none;
  font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.form-textarea:focus {
  border-color: #3b8d99;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(59, 141, 153, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 60px;
}

/* Custom Dropdown */
.custom-select {
  position: relative;
}

.custom-select-trigger {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 13px;
  color: #1a1a2e;
  background: #f8fafc;
  cursor: pointer;
  font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  box-sizing: border-box;
  text-align: left;
  gap: 8px;
}

.custom-select-trigger:hover {
  border-color: #94b8bf;
}

.custom-select-trigger.active,
.custom-select-trigger:focus-within {
  border-color: #3b8d99;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(59, 141, 153, 0.1);
}

.custom-select-placeholder {
  color: #64748b;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.custom-select-placeholder.filled {
  color: #1a1a2e;
}

.select-arrow {
  flex-shrink: 0;
  color: #3b8d99;
  transition: transform 0.2s;
}

.select-arrow.open {
  transform: rotate(180deg);
}

.custom-select-menu {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  max-height: 220px;
  overflow-y: auto;
  z-index: 50;
  list-style: none;
  margin: 0;
  padding: 4px;
}

.custom-select-item {
  padding: 10px 12px;
  font-size: 13px;
  color: #1a1a2e;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.custom-select-item:hover {
  background: #ecfdf5;
  color: #0f766e;
}

.custom-select-item.selected {
  background: #3b8d99;
  color: #ffffff;
  font-weight: 500;
}

.custom-select-item.selected:hover {
  background: #2c6e7a;
}

.custom-select-item.disabled {
  color: #64748b;
  cursor: default;
}

.custom-select-item.disabled:hover {
  background: transparent;
  color: #64748b;
}

/* Dropdown transition */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.15s, transform 0.15s;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

/* Submit */
.btn-submit {
  width: 100%;
  padding: 12px;
  background: #3b8d99;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.2s;
  font-family: inherit;
}

.btn-submit:hover:not(:disabled) {
  background: #2c6e7a;
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-msg {
  color: #ef4444;
  font-size: 12px;
  text-align: center;
  margin: 0;
}

/* Success State */
.success-state {
  text-align: center;
  padding: 24px 0;
}

.success-icon {
  font-size: 48px;
  color: #059669;
  margin-bottom: 12px;
}

.success-state h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 8px;
}

.success-state p {
  font-size: 13px;
  color: #475569;
  margin: 0 0 20px;
}

.btn-new-rdv {
  padding: 10px 24px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #3b8d99;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.btn-new-rdv:hover {
  background: #effafd;
  border-color: #3b8d99;
}
</style>
