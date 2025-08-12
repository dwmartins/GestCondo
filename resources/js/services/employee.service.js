import axios from "axios";

export default {
    async getAll() {
        try {
            return await axios.get('/api/employee');
        } catch (error) {
            throw error;
        }
    }
}