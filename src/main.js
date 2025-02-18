import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

const app = createApp(App);

app.config.globalProperties.$user = {
  isLoggedIn: false,
  data: null
};

app.use(router).mount('#app');
