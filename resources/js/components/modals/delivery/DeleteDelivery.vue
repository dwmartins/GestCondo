<script setup>
import { Button, Dialog, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import { computed, ref } from 'vue';
import deliveryService from '../../../services/delivery.service';

const showAlert = createAlert(useToast());

const props = defineProps({
    modelValue: Boolean,
    deliveryData: Object,
});

const emit = defineEmits(['update:modelValue', 'delete']);

const loading = ref(false);

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const deleteDelivery = async () => {
    try {
        loading.value = true;
        const response = await deliveryService.delete(props.deliveryData.id);
        showAlert('success', 'Sucesso', response.message);

        emit('delete', props.deliveryData.id);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

</script>

<template>
    <Dialog v-model:visible="visible" modal header="Excluir registro de entrega" :style="{ width: '28rem' }">
        <div class="d-flex align-items-center gap-3 mb-3">
            <i class="pi pi-info-circle" style="font-size: 1.8rem"></i>
            <p>Tem certeza que deseja excluir esse registro? Essa ação é irreversível.</p>
        </div>

        <template #footer>
            <Button 
                label="Cancelar" 
                icon="pi pi-times" 
                class="p-button-text" 
                :disabled="loading" 
                @click="visible = false" 
            />

            <Button label="Confirmar exclusão"
                icon="pi pi-trash"
                severity="danger"
                :loading="loading"
                @click="deleteDelivery()" 
            />
        </template>
    </Dialog>
</template>