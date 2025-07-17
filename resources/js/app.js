import './bootstrap';
import '../sass/app.scss';
import 'bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import AppLoading from './components/AppLoading.vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';

const appLoading = createApp(AppLoading);
appLoading.mount('#appLoading');

const app = createApp(App);
app.use(router);

app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
});
app.use(ToastService, {
    toastProps: {
        autofocus: false,
    }
});

app.mount('#app');