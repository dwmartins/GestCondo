<script setup>
import { Dialog } from 'primevue';
import { ref, defineExpose, onMounted } from 'vue';

const visible = ref(false);
const condominiums = ref([]);

const open = () => {
    visible.value = true;
    loadCondominiums(); // carrega ao abrir
};

const close = () => {
    visible.value = false;
};

const loadCondominiums = async () => {
    console.log('Carregado condomínios')
};

const selectCondominium = (condo) => {
    console.log('Selecionado:', condo);
    close();
};

defineExpose({ open });
</script>

<template>
    <Dialog v-model:visible="visible" header="Alterar Condomínio" modal style="width: 40vw">
        lista de condomínios
    </Dialog>
</template>