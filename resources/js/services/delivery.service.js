import axios from "axios";

export default {
    async getAll(page = 1, perPage = 7, filters = {}) {
        try {
            return await axios.get('/api/delivery', {
                params: {
                    page,
                    perPage,
                    ...filters
                }
            });
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
    },

    async update(data) {
        try {
            const response = await axios.put('/api/delivery', data);
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async getById(id) {
        try {
            return await axios.get(`/api/delivery/${id}`);
        } catch (error) {
            throw error;
        }
    },

    async delete(id) {
        try {
            const response = await axios.delete(`/api/delivery/${id}`);
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async changeStatus(data) {
        try {
            const response = await axios.put(`/api/delivery/${data.id}/change-status`, data);
            return response.data;
        } catch (error) {
            throw error;
        }
    },

    async markAsReceivedByResident(id) {
        try {
            const response = await axios.put(`/api/delivery/${id}/mark-received`);
            return response.data;
        } catch (error) {
            throw error;
        }
    }
}