import './bootstrap';
import '../sass/app.scss';
import 'bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import AppLoading from './components/AppLoading.vue';
import authService from './services/auth.service';

const appLoading = createApp(AppLoading);
appLoading.mount('#appLoading');

authService.setUserStore();

const app = createApp(App);
app.use(router);
app.mount('#app');