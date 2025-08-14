import './bootstrap';
import './axios';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import AppLoading from './components/AppLoading.vue';

import 'bootstrap/dist/css/bootstrap-utilities.min.css'
import 'bootstrap/dist/css/bootstrap-grid.min.css'

import PrimeVue from 'primevue/config';
import { pt } from './locales/primevue/pt';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';
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
app.directive('tooltip', Tooltip);

initTheme();
app.mount('#app');