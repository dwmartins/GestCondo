import axios from 'axios';
import { storeToRefs } from 'pinia';
import { useCondominiumStore } from './stores/condominiumStore';
import authService from './services/auth.service';
import router from './router';

// Request interceptor
axios.interceptors.request.use(config => {
    const store = useCondominiumStore();
    const { currentCondominiumId } = storeToRefs(store);

    if (currentCondominiumId.value) {
        config.headers['X-Condominium-Id'] = currentCondominiumId.value;
    }

    return config;
});

// Force logout
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401 &&
            error.response?.data?.message === 'Unauthenticated.') {
            
            authService.clearAuth();
            router.push({ name: 'login' });
        }

        return Promise.reject(error);
    }
);

// check if the request has low priority
axios.interceptors.request.use(config => {
    if (config.meta?.lowPriority) {
        return new Promise(resolve => {
            // throw to the end of the event queue
            setTimeout(() => resolve(config), 200);
        });
    }
    return config;
});
