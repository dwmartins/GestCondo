import axios from "axios"

export default {
    async getAll(limit = null) {
        try {
            const params = {};

            if(limit) {
                params.limit = limit
            }
            
            return await axios.get(`/api/notification`, {params});
        } catch (error) {
            throw error
        }
    }
}