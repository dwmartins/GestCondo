import axios from "axios";

export default {
    async getAll() {
        try {
            return await axios.get('/api/condominium');
        } catch (error) {
            throw error;
        }
    },

    async create(data) {
        try {
            return await axios.post('/api/condominium', data);
        } catch (error) {
            throw error;
        }
    },

    async update(data) {
        try {
            return await axios.put(`/api/condominium/${data.id}`, data);
        } catch (error) {
            throw error;
        }
    }
}