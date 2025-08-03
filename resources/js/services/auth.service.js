import axios from 'axios';
import { useCondominiumStore } from '../stores/condominiumStore';
import { useUserStore } from '../stores/userStore';
import { ROLE_SINDICO, ROLE_SUPORTE } from '../helpers/auth';

export default {
    async login(email, password, rememberMe) {
        try {
            const response = await axios.post('/api/auth/login', {
                email,
                password,
                rememberMe
            });

            if(response.data.access_token) {
                this.setAuth(response.data);
            }

            return response.data;
        } catch (error) {
            this.clearAuth();
            throw error;
        }
    },

    async validateToken() {
        try {
            const response = await axios.get('/api/auth/validate-token', {
                headers: this.getAuthHeader()
            });

            return response.data;
        } catch (error) {
            this.clearAuth();
            throw error;
        }
    },

    setAuth(authData) {
        const userStore = useUserStore();
        const auth = {
            user: authData.user,
            access_token: authData.access_token
        }

        localStorage.setItem('auth', JSON.stringify(auth));
        axios.defaults.headers.common['Authorization'] = `Bearer ${auth.access_token}`;

        if([ROLE_SINDICO, ROLE_SUPORTE].includes(auth.user.role)) {
            this.setCondominiumId(authData.lastViewedCondominiumId)
        } else {
            this.setCondominiumId(authData.user.condominium_id);
        }

        userStore.update(authData.user);
    },

    async refreshAuthenticatedUser(userData) {
        const userStore = useUserStore();
        const auth = JSON.parse(localStorage.getItem('auth'));
        const token = auth.access_token;

        const data = {
            user: userData.user,
            access_token: token
        }

        localStorage.setItem('auth', JSON.stringify(data));
        axios.defaults.headers.common['Authorization'] = `Bearer ${auth.access_token}`;

        userStore.update(data.user);

        if([ROLE_SINDICO, ROLE_SUPORTE].includes(auth.user.role)) {
            this.setCondominiumId(userData.lastViewedCondominiumId)
        } else {
            this.setCondominiumId(userData.user.condominium_id);
        }
    },

    async updateLastViewedCondominium(condominiumId) {
        try {
            const response = await axios.patch('/api/auth/last-viewed-condominium', { condominium_id: condominiumId });

            return response.data;
        } catch (error) {
            throw error;
        }
    },

    setCondominiumId(id) {
        const condominiumStore = useCondominiumStore();
        condominiumStore.setCondominiumId(id);
    },

    clearAuth() {
        localStorage.removeItem('auth');
        delete axios.defaults.headers.common['Authorization'];
        const userStore = useUserStore();
        userStore.clean();

        const condominiumStore = useCondominiumStore();
        condominiumStore.clearCondominium();
    },

    getAuthHeader() {
        const auth = JSON.parse(localStorage.getItem('auth'));
        return {
            'Authorization': `Bearer ${auth?.access_token}`,
            'Accept': 'application/json'
        };
    },

    isLocallyAuthenticated() {
        const authData = JSON.parse(localStorage.getItem('auth'));
        return !!authData?.access_token;
    },

    async isFullyAuthenticated() {
        if (!this.isLocallyAuthenticated()) return false;
        
        try {
            const result = await this.validateToken();

            const condominiumStore = useCondominiumStore();
            condominiumStore.setCondominiumId(result.lastViewedCondominiumId);

            this.refreshAuthenticatedUser(result);
            return result;
        } catch (error) {
            this.clearAuth();
            return false;
        }
    },

    async logout() {
        try {
            const response = await axios.post('/api/auth/logout');
            this.clearAuth();
            return response;
        } catch (error) {
            throw error;
        }
    },

    async changePassword(data, userId) {
        try {
            return await axios.post(`/api/auth/${userId}/change-password`, data);
        } catch (error) {
            throw error;
        }
    }
};