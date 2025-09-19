import axios from "axios"

export default {
    async getAll(limit = null) {
        const lowPriority = true;

        try {
            const params = {};

            if(limit) {
                params.limit = limit
            }
            
            return await axios.get(`/api/notification`, {
                params,
                meta: { lowPriority }
            });
        } catch (error) {
            throw error
        }
    },

    async markAsRead(notification) {
        try {
            return await axios.put(`/api/notification/${notification.id}/mark-read`, notification);
        } catch (error) {
            throw error
        }
    },

    async markAllAsRead() {
        try {
            return await axios.post('/api/notification/mark-all-read');
        } catch (error) {
            throw error
        }
    }
}