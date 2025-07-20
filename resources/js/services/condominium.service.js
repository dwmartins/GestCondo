import axios from "axios";

export default {
    async getAll() {
        try {
            return await axios.get('/api/condominium');
        } catch (error) {
            throw error;
        }
    }
}