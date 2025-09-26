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

    async create(data, photo = null) {
        const payload = new FormData();

        Object.entries(data).forEach(([key, value]) => {
            if (value === null || value === undefined) return;

            if (key === 'photo') {
                return;
            }

            if(typeof value === 'boolean') {
                payload.append(key, value ? 1 : 0);
                return;
            }

            if (key === "rules" && Array.isArray(value)) {
                value.forEach((rule, index) => {
                    payload.append(`rules[${index}]`, rule);
                });
                return;
            }

            payload.append(key, value);
        });

        if (photo) {
            payload.append('photo', photo);
        }

        try {
            return await axios.post('/api/common-spaces', payload, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
        } catch (error) {
            throw error;
        }
    },

    async update(data, photo = null) {
        const payload = new FormData();
        payload.append('_method', 'PUT');

        Object.entries(data).forEach(([key, value]) => {
            if (value === null || value === undefined) return;

            if (key === 'photo') {
                return;
            }

            if(typeof value === 'boolean') {
                payload.append(key, value ? 1 : 0);
                return;
            }

            if (key === "rules" && Array.isArray(value)) {
                value.forEach((rule, index) => {
                    payload.append(`rules[${index}]`, rule);
                });
                return;
            }

            payload.append(key, value);
        });

        if (photo) {
            payload.append('photo', photo);
        }

        try {
            return await axios.post('/api/common-spaces', payload, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
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