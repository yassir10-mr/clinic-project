<template>
  <div class="page">
    <h1>👩‍💼 Gestion des Secrétaires</h1>
    
    <button @click="openAddModal" class="btn-add">+ Ajouter une Secrétaire</button>

    <div v-if="loading" class="loading">Chargement...</div>

    <table v-else-if="secretaires.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Téléphone</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="secretaire in secretaires" :key="secretaire.id_secretaire">
          <td>{{ secretaire.id_secretaire }}</td>
          <td>{{ secretaire.nom }}</td>
          <td>{{ secretaire.prenom }}</td>
          <td>{{ secretaire.telephone }}</td>
          <td>{{ secretaire.email || '-' }}</td>
          <td>
            <button @click="editSecretaire(secretaire)" class="btn-edit">✏️</button>
            <button @click="removeSecretaire(secretaire.id_secretaire)" class="btn-delete">🗑️</button>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else class="empty">Aucune secrétaire enregistrée</p>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h2>{{ isEditing ? '✏️ Modifier' : '➕ Ajouter' }} une Secrétaire</h2>
        <form @submit.prevent="saveSecretaire">
          <div class="form-group">
            <label>Nom *</label>
            <input v-model="form.nom" placeholder="Nom" required />
          </div>
          <div class="form-group">
            <label>Prénom *</label>
            <input v-model="form.prenom" placeholder="Prénom" required />
          </div>
          <div class="form-group">
            <label>Téléphone *</label>
            <input v-model="form.telephone" placeholder="0555123456" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" placeholder="email@exemple.com" type="email" />
          </div>
          
          <div class="modal-buttons">
            <button type="submit" class="btn-save">💾 {{ isEditing ? 'Modifier' : 'Ajouter' }}</button>
            <button type="button" @click="closeModal" class="btn-cancel">❌ Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getSecretaires, addSecretaire, updateSecretaire, deleteSecretaire } from '@/services/api.js';

const secretaires = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({ nom: '', prenom: '', telephone: '', email: '' });

onMounted(() => loadSecretaires());

const loadSecretaires = async () => {
  loading.value = true;
  try {
    const response = await getSecretaires();
    secretaires.value = response.data.data;
  } catch (error) {
    console.error('Erreur:', error);
    alert('Impossible de charger les secrétaires');
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showModal.value = true;
};

const editSecretaire = (secretaire) => {
  isEditing.value = true;
  editingId.value = secretaire.id_secretaire;
  form.value = { ...secretaire };
  showModal.value = true;
};

const saveSecretaire = async () => {
  try {
    if (isEditing.value) {
      await updateSecretaire(editingId.value, form.value);
      alert('✅ Secrétaire modifiée !');
    } else {
      await addSecretaire(form.value);
      alert('✅ Secrétaire ajoutée !');
    }
    closeModal();
    await loadSecretaires();
  } catch (error) {
    alert('❌ Erreur lors de l\'enregistrement');
  }
};

const removeSecretaire = async (id) => {
  if (!confirm('🗑️ Supprimer cette secrétaire ?')) return;
  try {
    await deleteSecretaire(id);
    alert('✅ Secrétaire supprimée !');
    await loadSecretaires();
  } catch (error) {
    alert('❌ Erreur lors de la suppression');
  }
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = { nom: '', prenom: '', telephone: '', email: '' };
};
</script>

<style scoped>
/* Mêmes styles que MedecinsView.vue */
.page { padding: 30px; max-width: 1400px; margin: 0 auto; }
h1 { color: #2c3e50; margin-bottom: 20px; }
.btn-add { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-bottom: 20px; }
.loading { text-align: center; padding: 50px; color: #7f8c8d; font-size: 18px; }
table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
th { background: #4a5568; color: white; padding: 15px; text-align: left; }
td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; }
tr:hover { background: #f7fafc; }
.btn-edit, .btn-delete { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; margin-right: 5px; font-size: 14px; }
.btn-edit { background: #4299e1; color: white; }
.btn-delete { background: #f56565; color: white; }
.empty { text-align: center; padding: 60px; color: #a0aec0; font-size: 18px; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal { background: white; padding: 30px; border-radius: 15px; width: 500px; max-height: 90vh; overflow-y: auto; }
.modal h2 { margin-bottom: 25px; color: #2d3748; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; color: #4a5568; font-weight: 500; font-size: 14px; }
.form-group input { width: 100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 15px; }
.form-group input:focus { outline: none; border-color: #667eea; }
.modal-buttons { display: flex; gap: 12px; margin-top: 25px; }
.btn-save, .btn-cancel { flex: 1; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; }
.btn-save { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
.btn-cancel { background: #e2e8f0; color: #4a5568; }

</style>




<style scoped>
/* Mêmes styles que MedecinsView.vue */
.page { padding: 30px; max-width: 1400px; margin: 0 auto; }
h1 { color: #2c3e50; margin-bottom: 20px; }
.btn-add { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-bottom: 20px; }
.loading { text-align: center; padding: 50px; color: #7f8c8d; font-size: 18px; }
table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
th { background: #4a5568; color: white; padding: 15px; text-align: left; }
td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; }
tr:hover { background: #f7fafc; }
.btn-edit, .btn-delete { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; margin-right: 5px; font-size: 14px; }
.btn-edit { background: #4299e1; color: white; }
.btn-delete { background: #f56565; color: white; }
.empty { text-align: center; padding: 60px; color: #a0aec0; font-size: 18px; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal { background: white; padding: 30px; border-radius: 15px; width: 500px; max-height: 90vh; overflow-y: auto; }
.modal h2 { margin-bottom: 25px; color: #2d3748; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; color: #4a5568; font-weight: 500; font-size: 14px; }
.form-group input { width: 100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 15px; }
.form-group input:focus { outline: none; border-color: #667eea; }
.modal-buttons { display: flex; gap: 12px; margin-top: 25px; }
.btn-save, .btn-cancel { flex: 1; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; }
.btn-save { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
.btn-cancel { background: #e2e8f0; color: #4a5568; }
</style>