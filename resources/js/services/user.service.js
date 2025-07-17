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
    }
}