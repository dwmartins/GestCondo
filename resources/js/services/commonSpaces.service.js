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
    }
}