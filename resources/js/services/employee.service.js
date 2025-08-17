import axios from "axios";

export default {
    async getAll() {
        try {
            return await axios.get('/api/employee');
        } catch (error) {
            throw error;
        }
    },

    async getEmployee(id) {
        try {
            const response = await axios.get(`/api/employee/${id}`);
            return response.data;
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
    },

    async changeStatus(data, userId) {
        try {
            const response = await axios.patch(`/api/employee/${userId}/change-settings`, data);
            return response.data;
        } catch (error) {
            throw error;
        }
    }
}