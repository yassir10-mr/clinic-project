<template>
  <div class="chat-assistant">
    <div class="chat-header">
      <div class="chat-header-left">
        <h1 class="chat-title">Assistant Médical IA</h1>
        <p class="chat-subtitle">Posez vos questions sur les dossiers patients, les protocoles ou l'organisation.</p>
      </div>
      <button v-if="messages.length" class="reset-btn" @click="resetChat" title="Nouvelle conversation">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256"><path d="M224,128a96,96,0,0,1-94.71,96H128a95.38,95.38,0,0,1-65.9-26.2,8,8,0,0,1,11-11.62A80,80,0,1,0,71.43,71.39l-.1.1-25.69,26H80a8,8,0,0,1,0,16H24a8,8,0,0,1-8-8V48a8,8,0,0,1,16,0V81.43L58.59,61.69l.09-.09A96,96,0,0,1,224,128Z"/></svg>
      </button>
    </div>

    <div class="messages-area" ref="messagesContainer">
      <div v-if="messages.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-robot"></i>
        </div>
        <p class="empty-text">Demandez-moi n'importe quoi sur la clinique...</p>
        <p class="empty-hint">Je peux vous aider avec les dossiers patients, les rendez-vous, les protocoles médicaux et plus encore.</p>
      </div>

      <div v-for="(msg, i) in messages" :key="i" :class="['message', msg.role === 'user' ? 'message-user' : 'message-ai']">
        <div class="message-content" :class="{ 'message-markdown': msg.role === 'ai' }" v-if="msg.role === 'ai'" v-html="renderMarkdown(msg.text)"></div>
        <div class="message-content" v-else>{{ msg.text }}</div>
        <div v-if="msg.role === 'ai'" class="message-time">IA · Médical</div>
      </div>

      <div v-if="loading" class="message message-ai">
        <div class="typing-indicator">
          <span></span><span></span><span></span>
        </div>
      </div>
    </div>

    <div class="input-area">
      <div class="input-wrapper">
        <input v-model="query" type="text" placeholder="Demandez quelque chose à l'IA..." @keyup.enter="sendMessage" />
        <button class="send-btn" :class="{ active: query.trim() }" :disabled="!query.trim()" @click="sendMessage">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256">
            <path d="M227.32,28.68a16,16,0,0,0-15.66-4.08l-.15,0L34.34,98.51a15.93,15.93,0,0,0-2.6,29.76l80.29,35.72L147.73,224a15.88,15.88,0,0,0,14.27,8,16,16,0,0,0,13.64-8.09l71.09-118.22a16.24,16.24,0,0,0,3.35-11.63A16.19,16.19,0,0,0,247,82.56l-.11-.17ZM227,81.31,155.89,199.53,120.35,119.2l48.16-20.68a8,8,0,0,0-6.38-14.68L112.3,105.15,44.74,77.69Z"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { marked } from 'marked';

const query = ref('');
const messages = ref([]);
const loading = ref(false);
const messagesContainer = ref(null);

const userRole = localStorage.getItem('user_role') || '';

const webhookConfig = (() => {
  if (userRole === 'admin') {
    const u = JSON.parse(localStorage.getItem('admin_user') || '{}');
    return { url: '/n8n/webhook/admn', id: u.id || null, idKey: 'admin_id' };
  }
  if (userRole === 'patient') {
    const u = JSON.parse(localStorage.getItem('patient_user') || '{}');
    return { url: '/n8n/webhook/ptt', id: u.id || u.id_patient || null, idKey: 'patient_id' };
  }
  return null;
})();

const roleLabels = {
  admin: 'Admin',
  secretaire: 'Secrétaire',
  infirmier: 'Infirmier',
  patient: 'Patient',
};

const mockResponses = [
  "Je peux vous aider à consulter les dossiers patients, vérifier les rendez-vous du jour ou retrouver des protocoles médicaux. Que souhaitez-vous savoir ?",
  "D'après les informations disponibles, je vous recommande de vérifier le dossier médical complet du patient pour plus de détails.",
  "Les protocoles standards pour ce type de cas sont disponibles dans la section des dossiers médicaux. Je peux vous y rediriger si nécessaire.",
  "Voulez-vous que je consulte le planning des rendez-vous ou les informations d'un patient en particulier ?",
  "Je peux également vous aider avec les statistiques du tableau de bord ou les dernières mises à jour des patients.",
];

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const sendMessage = async () => {
  const text = query.value.trim();
  if (!text || loading.value) return;

  messages.value.push({ role: 'user', text });
  query.value = '';
  loading.value = true;
  scrollToBottom();

  if (webhookConfig) {
    try {
      const res = await fetch(webhookConfig.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: text,
          [webhookConfig.idKey]: webhookConfig.id,
          role: userRole
        })
      });
      if (!res.ok) {
        messages.value.push({ role: 'ai', text: 'Désolé, une erreur est survenue. Veuillez réessayer plus tard.' });
        loading.value = false;
        scrollToBottom();
        return;
      }
      const data = await res.json();
      const reply = data?.output || 'Désolé, je n\'ai pas pu traiter votre demande.';
      messages.value.push({ role: 'ai', text: reply });
    } catch {
      messages.value.push({ role: 'ai', text: 'Désolé, une erreur est survenue. Veuillez réessayer plus tard.' });
    }
  } else {
    await new Promise((resolve) => setTimeout(resolve, 1000 + Math.random() * 1000));
    const response = mockResponses[Math.floor(Math.random() * mockResponses.length)];
    messages.value.push({ role: 'ai', text: response });
  }

  loading.value = false;
  scrollToBottom();
};

const resetChat = () => {
  messages.value = [];
};

marked.setOptions({
  breaks: true,
  gfm: true
});

const renderMarkdown = (text) => {
  return marked.parse(text);
};
</script>

<style scoped>
.chat-assistant {
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%;
}

.chat-header {
  padding: 12px 24px 10px;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.chat-header-left {
  min-width: 0;
}

.chat-title {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 4px;
}

.chat-subtitle {
  font-size: 13px;
  color: #475569;
  margin: 0;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 16px 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #f8fafc;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  padding: 20px;
}

.empty-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #effafd 0%, #e0f2fe 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #3b8d99;
  margin-bottom: 16px;
}

.empty-text {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 8px;
}

.empty-hint {
  font-size: 13px;
  color: #475569;
  margin: 0;
  max-width: 360px;
}

.message {
  display: flex;
  flex-direction: column;
  max-width: 75%;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

.message-user {
  align-self: flex-end;
  align-items: flex-end;
}

.message-ai {
  align-self: flex-start;
  align-items: flex-start;
}

.message-content {
  padding: 12px 18px;
  font-size: 14px;
  line-height: 1.6;
  color: #1a1a2e;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.message-user .message-content {
  background: #3b8d99;
  color: #ffffff;
  border: none;
  border-radius: 18px;
  border-bottom-right-radius: 4px;
  box-shadow: 0 2px 8px rgba(59, 141, 153, 0.2);
}

.message-time {
  font-size: 11px;
  color: #475569;
  margin-top: 4px;
  padding: 0 4px;
}

.message-markdown {
  line-height: 1.7;
}

.message-markdown p {
  margin: 0 0 8px;
}

.message-markdown p:last-child {
  margin-bottom: 0;
}

.message-markdown ul,
.message-markdown ol {
  margin: 4px 0 8px;
  padding-left: 20px;
}

.message-markdown li {
  margin-bottom: 4px;
}

.message-markdown li:last-child {
  margin-bottom: 0;
}

.message-markdown strong {
  font-weight: 600;
  color: #0f172a;
}

.message-markdown code {
  background: #f1f5f9;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 13px;
  color: #334155;
  font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, monospace;
}

.message-markdown br {
  display: block;
  content: '';
  margin: 4px 0;
}

.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 14px 18px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  border-bottom-left-radius: 4px;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  background: #94a3b8;
  border-radius: 50%;
  animation: typing 1.4s infinite ease-in-out;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% { opacity: 0.3; transform: scale(0.8); }
  30% { opacity: 1; transform: scale(1); }
}

.input-area {
  padding: 12px 24px;
  border-top: 1px solid #e2e8f0;
  background: #ffffff;
  flex-shrink: 0;
}

.input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 4px 4px 4px 20px;
  transition: border-color 0.2s;
}

.input-wrapper:focus-within {
  border-color: #3b8d99;
  box-shadow: 0 0 0 3px rgba(59, 141, 153, 0.08);
}

.input-wrapper input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  color: #1a1a2e;
  padding: 10px 0;
  font-family: inherit;
}

.input-wrapper input::placeholder {
  color: #475569;
}

.send-btn {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: none;
  background: #e2e8f0;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.send-btn.active {
  background: #3b8d99;
  color: #ffffff;
}

.send-btn.active:hover {
  background: #2c6e7a;
}

.send-btn:disabled {
  cursor: not-allowed;
}

.reset-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
  margin-top: 2px;
}

.reset-btn:hover {
  color: #3b8d99;
  border-color: #3b8d99;
  background: #f0fdfa;
}
</style>
