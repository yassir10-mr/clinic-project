import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

// Import global styles
import './assets/main.css';
import './assets/dark-mode.css';

const app = createApp(App);

app.use(router);

app.mount('#app');

// Theme toggle - accessible as $toggleTheme() in all Vue templates
(function () {
  const html = document.documentElement;
  const stored = localStorage.getItem('theme');
  if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    html.setAttribute('data-theme', 'dark');
  }
  app.config.globalProperties.$toggleTheme = function () {
    const isDark = html.getAttribute('data-theme') === 'dark';
    const next = isDark ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
  };
})();
