<template>
  <div class="ai-assistant-page">
    <ChatAssistant />
  </div>
</template>

<script setup>
import ChatAssistant from '@/components/ChatAssistant.vue';
</script>

<style scoped>
.ai-assistant-page {
  height: 100%;
  display: flex;
  flex-direction: column;
  width: 100%;
}
</style>
