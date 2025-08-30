import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';

export const useCondominiumStore = defineStore('condominium', () => {
    const currentCondominiumId = ref(localStorage.getItem('current_condominium_id') || null);

    const currentCondominium = reactive({
        id: null,
        name: null
    });

    function setCondominium(condominium) {
        localStorage.removeItem('current_condominium_id');

        if(condominium.id) {
            for(const key in condominium) {
                currentCondominium[key] = condominium[key];
            }

            currentCondominiumId.value = condominium.id;
            localStorage.setItem('current_condominium_id', condominium.id);
        }
    }

    function getCondominiumId() {
        return currentCondominiumId.value ?? null;
    }

    function clearCondominium() {
        currentCondominiumId.value = null;
        localStorage.removeItem('current_condominium_id');

        for (let key in currentCondominium) {
            currentCondominium[key] = null;
        }
    }

    return { currentCondominiumId, currentCondominium, setCondominium, clearCondominium, getCondominiumId };
});
