import axios from 'axios';
import router from '../router/index';
import { userStore } from '../stores/userStore';

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
        const auth = {
            user: authData.user,
            access_token: authData.access_token
        }

        localStorage.setItem('auth', JSON.stringify(auth));
        axios.defaults.headers.common['Authorization'] = `Bearer ${auth.access_token}`;

        userStore.update(authData.user);
    },

    setUserStore() {
        const auth = JSON.parse(localStorage.getItem('auth'));
        if(auth) {
            userStore.update(auth.user);
        }
    },

    clearAuth() {
        localStorage.removeItem('auth');
        delete axios.defaults.headers.common['Authorization'];
        userStore.clean();
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
            await this.validateToken();
            return true;
        } catch (error) {
            this.clearAuth();
            return false;
        }
    }
};