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

    async refreshAuthenticatedUser(userData) {
        const auth = JSON.parse(localStorage.getItem('auth'));
        const token = auth.access_token;

        const data = {
            user: userData.user,
            access_token: token
        }

        localStorage.setItem('auth', JSON.stringify(data));
        axios.defaults.headers.common['Authorization'] = `Bearer ${auth.access_token}`;

        userStore.update(data.user);
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
        console.log('local')
        const authData = JSON.parse(localStorage.getItem('auth'));
        return !!authData?.access_token;
    },

    async isFullyAuthenticated() {
        if (!this.isLocallyAuthenticated()) return false;
        
        try {
            const result = await this.validateToken();
            this.refreshAuthenticatedUser(result);
            return result;
        } catch (error) {
            this.clearAuth();
            return false;
        }
    }
};