import axios from 'axios';
import { storeToRefs } from 'pinia';
import { useCondominiumStore } from './stores/condominiumStore';

// Interceptor de requisição
axios.interceptors.request.use(config => {
    const store = useCondominiumStore();
    const { currentCondominiumId } = storeToRefs(store);

    if (currentCondominiumId.value) {
        config.headers['X-Condominium-Id'] = currentCondominiumId.value;
    }

    return config;
});
