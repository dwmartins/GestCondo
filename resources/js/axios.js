import axios from 'axios';
import { storeToRefs } from 'pinia';
import { useCondominiumStore } from './stores/condominiumStore';
import authService from './services/auth.service';
import router from './router';

// Interceptor de requisição
axios.interceptors.request.use(config => {
    const store = useCondominiumStore();
    const { currentCondominiumId } = storeToRefs(store);

    if (currentCondominiumId.value) {
        config.headers['X-Condominium-Id'] = currentCondominiumId.value;
    }

    return config;
});

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
