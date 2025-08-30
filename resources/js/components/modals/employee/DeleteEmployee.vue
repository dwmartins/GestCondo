<script setup>
import { Button, Dialog, useToast } from 'primevue';
import { computed, ref, watch } from 'vue';
import { createAlert } from '../../../helpers/alert';
import employeeService from '../../../services/employee.service';

const showAlert = createAlert(useToast());

const props = defineProps({
    modelValue: Boolean,
    employeeData: Object,
});

const emit = defineEmits(['update:modelValue', 'delete']);

const loading = ref(false);

const employeeName = ref('');

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const deleteEmployee = async () => {
    try {
        loading.value = true;
        const response = await employeeService.delete(props.employeeData.id);
        showAlert('success', 'Sucesso', response.message)

        emit('delete', props.employeeData.id);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

watch(() => props.modelValue, (visible) => {
    if(visible) {
        employeeName.value = props.employeeData.name;
    }
});

</script>

<template>
    <Dialog v-model:visible="visible" modal header="Excluir funcionário"  :style="{ width: '28rem' }">
        <div class="d-flex align-items-center gap-3">
            <i class="pi pi-info-circle" style="font-size: 1.8rem"></i>
            <p>Tem certeza que deseja excluir {{ employeeName }}? Essa ação é irreversível.</p>
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
                @click="deleteEmployee()" 
            />
        </template>
    </Dialog>
</template>