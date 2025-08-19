import axios from "axios";

export default {
    async getAll() {
        try {
            return await axios.get('/api/delivery');
        } catch (error) {
            throw error;
        }
    },

    async create(data) {
        try {
            const response = await axios.post('/api/delivery', data);
            return response.data;
        } catch (error) {
            throw error;
        }
    }
}