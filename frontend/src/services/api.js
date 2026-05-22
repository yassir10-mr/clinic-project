import axios from "axios";

const API_URL = '/api';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// ==================== REQUEST INTERCEPTOR ====================
api.interceptors.request.use((config) => {
    // Support admin + secretaire tokens
    const token =
        localStorage.getItem('admin_token') ||
        localStorage.getItem('secretaire_token') ||
        localStorage.getItem('token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

// ==================== RESPONSE INTERCEPTOR ====================
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            console.error('🚫 401 Unauthorized - Token invalide ou expiré');

            // Clear auth storage
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');

            localStorage.removeItem('secretaire_token');
            localStorage.removeItem('secretaire_user');

            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('user_role');

            window.location.href = '/login';
        }

        return Promise.reject(error);
    }
);

// ==================== HELPERS ====================
const getRole = () => localStorage.getItem('user_role') || 'admin';

const getBasePath = () => {
    const role = getRole();
    return role === 'secretaire' ? '/secretaire' : '/admin';
};

// ==================== AUTH ====================
export const loginAdmin = (credentials) =>
    api.post('/admin/login', credentials);

export const loginSecretaire = (credentials) =>
    api.post('/secretaire/login', credentials);

// Backward compatibility
export const login = loginAdmin;

export const logout = () =>
    api.post(`${getBasePath()}/logout`);

export const getUser = () => {
    const role = getRole();

    if (role === 'secretaire') {
        return api.get('/secretaire/profile');
    }

    return api.get('/admin/user');
};

// ==================== PROFILE & PASSWORD ====================
export const updateProfile = (data) =>
    api.put('/admin/profile', data);

export const changePassword = (data) =>
    api.put('/admin/password', data);

// ==================== PATIENTS ====================
export const getPatients = () =>
    api.get(`${getBasePath()}/patients`);

export const addPatient = (data) =>
    api.post(`${getBasePath()}/patients`, data);

export const updatePatient = (id, data) =>
    api.put(`${getBasePath()}/patients/${id}`, data);

export const deletePatient = (id) =>
    api.delete(`${getBasePath()}/patients/${id}`);

// ==================== MEDECINS ====================
export const getMedecins = () => {
    const role = getRole();

    if (role === 'secretaire') {
        return api.get('/secretaire/medecins');
    }

    return api.get('/admin/medecins');
};

export const addMedecin = (data) =>
    api.post('/admin/medecins', data);

export const updateMedecin = (id, data) =>
    api.put(`/admin/medecins/${id}`, data);

export const deleteMedecin = (id) =>
    api.delete(`/admin/medecins/${id}`);

// ==================== SECRETAIRES ====================
export const getSecretaires = () =>
    api.get('/admin/secretaires');

export const addSecretaire = (data) =>
    api.post('/admin/secretaires', data);

export const updateSecretaire = (id, data) =>
    api.put(`/admin/secretaires/${id}`, data);

export const deleteSecretaire = (id) =>
    api.delete(`/admin/secretaires/${id}`);

// ==================== INFIRMIERS ====================
export const getInfirmiers = () =>
    api.get('/admin/infirmiers');

export const addInfirmier = (data) =>
    api.post('/admin/infirmiers', data);

export const updateInfirmier = (id, data) =>
    api.put(`/admin/infirmiers/${id}`, data);

export const deleteInfirmier = (id) =>
    api.delete(`/admin/infirmiers/${id}`);

// ==================== RENDEZ-VOUS ====================
export const getRendezVous = () =>
    api.get(`${getBasePath()}/rendez-vous`);

export const addRendezVous = (data) =>
    api.post(`${getBasePath()}/rendez-vous`, data);

export const updateRendezVous = (id, data) =>
    api.put(`${getBasePath()}/rendez-vous/${id}`, data);

export const deleteRendezVous = (id) =>
    api.delete(`${getBasePath()}/rendez-vous/${id}`);

export const updateRendezVousStatus = (id, status) => {
    const role = getRole();

    const endpoint =
        role === 'secretaire'
            ? `/secretaire/rendez-vous/${id}/status`
            : `/admin/rendez-vous/${id}/status`;

    return api.patch(endpoint, { statut: status });
};

// ==================== CONSULTATIONS ====================

export const getConsultations = () => {
    const role = getRole();

    if (role === 'secretaire') {
        return api.get('/secretaire/consultations');
    }

    return api.get('/admin/consultations');
};

export const addConsultation = (data) =>
    api.post('/admin/consultations', data);

// ==================== FACTURES ====================
export const getFactures = () =>
    api.get(`${getBasePath()}/factures`);

export const addFacture = (data) =>
    api.post(`${getBasePath()}/factures`, data);

export const updateFacture = (id, data) =>
    api.put(`${getBasePath()}/factures/${id}`, data);

export const deleteFacture = (id) =>
    api.delete(`${getBasePath()}/factures/${id}`);

// ==================== DASHBOARD ====================
export const getDashboardStats = () =>
    api.get(`${getBasePath()}/dashboard`);

export default api;