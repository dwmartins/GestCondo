<script setup>
import { Button, Card, Dialog, Divider, Message, ScrollPanel, Tag, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import { computed, reactive, ref, watch } from 'vue';
import deliveryService from '../../../services/delivery.service';
import AppSpinner from '../../AppSpinner.vue';

const showAlert = createAlert(useToast());

const props = defineProps({
    modelValue: Boolean,
    deliveryId: {
        type: Number,
        required: true
    }
});

const searchingDelivery = ref(false);
const deliveryStatus = ref('');

const emit = defineEmits(['update:modelValue', 'saved']);

const formData = reactive({});

const statusMap = {
    entregue: { label: 'Entregue', severity: 'success', icon: 'pi pi-check-circle' },
    devolvido: { label: 'Devolvido', severity: 'danger', icon: 'pi pi-arrow-circle-left'},
    pendente: { label: 'Pendente', severity: 'warn', icon: 'pi pi-hourglass' }
};

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const getDelivery = async () => {
    try {
        searchingDelivery.value = true;
        const response = await deliveryService.getById(props.deliveryId);
        setDelivery(response.data);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        searchingDelivery.value = false;
    }
}

const setDelivery = (item) => {
    for(const key in item) {
        formData[key] = item[key];
    }

    if(formData.received_at) {
        formData.received_at = new Date(item.received_at);
    }

    if(formData.delivered_at) {
        formData.delivered_at = new Date(item.delivered_at);
    }

    deliveryStatus.value = item.status;
}

const statusSeverity = (status) => {
    switch (status) {
        case 'pendente': return 'warning'
        case 'entregue': return 'success'
        case 'cancelado': return 'danger'
        default: return 'info'
    }
}

watch(() => props.modelValue, async (visible) => {
    for(const key in formData) {
        formData[key] = null;
    }

    if(visible) {
        await getDelivery();
    }
})

</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        :style="{ width: '42rem'}"
        :draggable="false"
    >
        <template #header>
            <h3>📦 Detalhes da Entrega</h3>
        </template>

        <div v-if="searchingDelivery" class="d-flex justify-content-center mb-5">
            <AppSpinner />
        </div>

        <div v-if="!searchingDelivery && formData.id">
            <div class="mb-3 mt-1">
                <Message :severity="statusMap[formData.status].severity">
                    <div class="d-flex gap-1">
                        <div>
                            <i :class="statusMap[formData.status].icon"></i>
                        </div>
                        <div>
                            <p>{{ statusMap[formData.status].label }}</p>
                            <small v-if="formData.status === 'pendente'">Disponível para retirada na portaria.</small>
                        </div>
                    </div>
                </Message>
            </div>

            <div v-if="formData.status === 'entregue'" class="row">
                <div class="col-6">
                    <p class="fw-semibold mb-1"><i class="pi pi-calendar text-secondary me-1"></i>Entregue em:</p>
                    {{ formData.delivered_at ? new Date(formData.delivered_at).toLocaleString() : '-' }}
                </div>
                <div class="col-6">
                    <p class="fw-semibold mb-1"><i class="pi pi-user text-secondary me-1"></i>Entregue para:</p>
                    {{ formData.delivered_to_name || '-'}}
                </div>
            </div>
            <Divider/>

            <div class="d-flex flex-column">
                <p class="fw-semibold mb-1"><i class="pi pi-calendar text-secondary me-1"></i>Data de recebimento:</p>
                {{ formData.received_at ? new Date(formData.received_at).toLocaleString() : '-' }}
            </div>
            <Divider/>

            <div>
                <p class="fw-semibold mb-1"><i class="pi pi-user text-secondary me-1"></i>Recebido por:</p>
                {{ formData.employee_name || '-' }}
            </div>
            <Divider/>

            <div>
                <p class="fw-semibold mb-1"><i class="pi pi-tag text-secondary me-1"></i>Descrição:</p>
                <Card>
                    <template #content>
                        {{ formData.item_description || '-' }}
                    </template>
                </Card>
            </div>
            <Divider/>

            <div class="mb-3">
                <p class="fw-semibold mb-1"><i class="pi pi-file-edit text-secondary me-1"></i>Notas:</p>
                <ScrollPanel style="max-height:200px" class="border rounded-3 p-2">
                    <div v-html="formData.notes || '<em>Sem notas adicionais</em>'"></div>
                </ScrollPanel>
            </div>

            <div v-if="formData.status === 'pendente'" class="d-flex justify-content-end">
                <Button
                    label="Marcar como recebido"
                    severity="success"
                    size="small"
                    icon="pi pi-check"
                />
            </div>
        </div>
    </Dialog>
</template>
