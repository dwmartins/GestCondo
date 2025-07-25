import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useCondominiumStore = defineStore('condominium', () => {
    const currentCondominiumId = ref(localStorage.getItem('current_condominium_id') || null);

    function setCondominiumId(id) {
        if(id) {
            currentCondominiumId.value = id;
            localStorage.setItem('current_condominium_id', id);
        }
    }

    function getCondominiumId() {
        return currentCondominiumId.value ?? null;
    }

    function clearCondominium() {
        currentCondominiumId.value = null;
        localStorage.removeItem('current_condominium_id');
    }

    return { currentCondominiumId, setCondominiumId, clearCondominium, getCondominiumId };
});
