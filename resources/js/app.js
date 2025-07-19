import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import AppLoading from './components/AppLoading.vue';

import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';
import 'primeicons/primeicons.css';
import { initTheme } from './helpers/theme';

const appLoading = createApp(AppLoading);
appLoading.mount('#appLoading');

const app = createApp(App);
app.use(router);

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.dark-mode',
        }
    }
});
app.use(ToastService, {
    toastProps: {
        autofocus: false,
    }
});

initTheme();
app.mount('#app');