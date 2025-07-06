import './bootstrap';
import '../sass/app.scss';
import 'bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import AppLoading from './components/AppLoading.vue';

const appLoading = createApp(AppLoading);
appLoading.mount('#appLoading');

const app = createApp(App);
app.use(router);
app.mount('#app');