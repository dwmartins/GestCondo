import axios from "axios";

export default {
    async create(data) {
        try {
            const response = await axios.post('/api/delivery', data);
            return response.data;
        } catch (error) {
            throw error;
        }
    }
}