import axios from "axios";

export default {
    async getAll() {
        try {
            return await axios.get('/api/employee');
        } catch (error) {
            throw error;
        }
    },
    
    async create(data) {
        try {
            const response = await axios.post('/api/employee', data);
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async update(data) {
        try {
            const response = await axios.put('/api/employee', data);
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async delete(id) {
        try {
            const response = await axios.delete(`/api/employee/${id}`);
            return response.data;
        } catch (error) {
            throw error;
        }
    }
}