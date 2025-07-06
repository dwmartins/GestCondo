import { reactive } from "vue";

export const loadingStore = reactive({
    message: '',
    isLoading: false,

    show(message = '') {
        this.message = message;
        this.isLoading = true;
    },

    hide() {
        this.isLoading = false;
        this.message = '';
    }
});