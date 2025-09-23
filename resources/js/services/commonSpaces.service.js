import axios from "axios";

export default {
    async getAll(page = 1, perPage = 7, filters = {}) {
        try {
            return await axios.get('/api/common-spaces', {
                params: {
                    page,
                    perPage,
                    ...filters
                }
            })
        } catch (error) {
            throw error;
        }
    },

    async create(data) {
        try {
            return await axios.post('/api/common-spaces', data);
        } catch (error) {
            throw error;
        }
    },

    async update(data) {
        try {
            return await axios.put('/api/common-spaces', data);
        } catch (error) {
            throw error;
        }
    },

    async delete(id) {
        try {
            return await axios.delete(`/api/common-spaces/${id}`)
        } catch (error) {
            throw error
        }
    }
}