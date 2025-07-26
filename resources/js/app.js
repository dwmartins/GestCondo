import './bootstrap';
import './axios';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import AppLoading from './components/AppLoading.vue';

import PrimeVue from 'primevue/config';
import { pt } from './locales/primevue/pt';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import 'primeicons/primeicons.css';
import { initTheme } from './helpers/theme';
import { createPinia } from 'pinia';

const appLoading = createApp(AppLoading);
appLoading.mount('#appLoading');

const app = createApp(App);
const pinia = createPinia();

app.use(router);
app.use(pinia);

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.dark-mode',
        }
    },
    locale: pt
});
app.use(ToastService, {
    toastProps: {
        autofocus: false,
    }
});
app.use(ConfirmationService);

initTheme();
app.mount('#app');