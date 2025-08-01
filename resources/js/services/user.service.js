import axios from "axios";

export default {
    async create(formData, avatar = null) {
        const payload = new FormData();

        Object.entries(formData).forEach(([key, value]) => {
            if (key === 'avatar') {
                return;
            }

            if (value === null || value === undefined) return

            if (value instanceof Date) {
                payload.append(key, value.toISOString().split('T')[0]);
            } else if (typeof value === 'boolean') {
                payload.append(key, value ? '1' : '0');
            } else {
                payload.append(key, value);
            }

        });

        if (avatar) {
            payload.append('avatar', avatar);
        }

        try {
            const response = await axios.post('/api/user', payload,{
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            return response;
        } catch (error) {
            throw error;
        }
    },

    async update(formData, avatar = null) {
        const payload = new FormData();

        payload.append('_method', 'PUT');

        Object.entries(formData).forEach(([key, value]) => {
            if (key === 'avatar') {
                return;
            }

            if (value === null || value === undefined) return

            if (value instanceof Date) {
                payload.append(key, value.toISOString().split('T')[0]);
            } else if (typeof value === 'boolean') {
                payload.append(key, value ? '1' : '0');
            } else {
                payload.append(key, value);
            }

        });

        if (avatar) {
            payload.append('avatar', avatar);
        }

        try {
            const response = await axios.post(`/api/user/${formData.id}`, payload,{
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            return response;
        } catch (error) {
            throw error;
        }
    },
    
    async getAll() {
        try {
            return await axios.get('/api/user');
        } catch (error) {
            throw error;
        }
    },

    async getById(id) {
        try {
            return await axios.get(`/api/user/${id}`);
        } catch (error) {
            throw error;
        }
    },
    
    async delete(id) {
        try {
            return await axios.delete(`/api/user/${id}`);
        } catch (error) {
            throw error;
        }
    },

    async settings(data) {
        try {
            return await axios.patch(`/api/user/${data.id}/status`, data);
        } catch (error) {
            throw error;
        }
    },

    async changeAvatar(file, userId) {
        try {
            const payload = new FormData();
            payload.append('avatar', file);

            const response = await axios.post(`/api/user/${userId}/change-avatar`, payload,{
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            return response;
        } catch (error) {
            throw error;
        }
    }
}