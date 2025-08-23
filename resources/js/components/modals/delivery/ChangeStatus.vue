<script setup>
import { Button, DatePicker, Dialog, InputText, Select, Tag, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import { computed, reactive, ref, watch } from 'vue';
import deliveryService from '../../../services/delivery.service';
import { isDateInFuture, toMysqlDateTime } from '../../../helpers/dates';
import AppSpinner from '../../AppSpinner.vue';
import Editor from 'primevue/editor';

const showAlert = createAlert(useToast());

const props = defineProps({
    modelValue: Boolean,
    deliveryData: Object,
});

const loading = ref(false);
const lookingDelivery = ref(false);

const deliveryStatus = ref('');

const emit = defineEmits(['update:modelValue', 'saved']);

const formData = reactive({
    id: null,
    condominium_id: null,
    user_id: null,
    user_name: null,
    employee_id: null,
    item_description: null,
    status: 'pendente',
    received_at: null,
    delivered_to_name: null,
    delivered_at: null,
    notes: null,
});

const statusMap = {
    entregue: { label: 'Entregue', severity: 'success' },
    devolvido: { label: 'Devolvido', severity: 'danger' },
    pendente: { label: 'Pendente', severity: 'warn' }
};

const filterStatus = [
    { code: 'pendente', name: 'Pendente', severity: 'warn' },
    { code: 'devolvido', name: 'Devolvido', severity: 'danger' },
    { code: 'entregue', name: 'Entregue', severity: 'success' }
];

const requiredFields = [
    {id: 'delivered_at', label: 'Data da entrega'},
    {id: 'delivered_to_name', label: 'Recebido por'},
    {id: 'status', label: 'Status'}
];

const fieldErrors = reactive({});

const getDelivery = async (id) => {
    try {
        lookingDelivery.value = true;
        const response = await deliveryService.getById(id);
        setDelivery(response.data);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        lookingDelivery.value = false;
    }
}

const setDelivery = (item) => {
    for(const key in item) {
        formData[key] = item[key];
    }

    if(formData.delivered_at) {
        formData.delivered_at = new Date(item.delivered_at);
    }

    deliveryStatus.value = item.status;
}

const save = async () => {
    if(!validateFields()) return;
    loading.value = true;

    const data = {...formData};
    data.delivered_at = toMysqlDateTime(data.delivered_at);

    try {
        const response = await deliveryService.changeStatus(data);
        showAlert('success', 'Sucesso', response.message);

        emit('saved', response.data);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const validateFields = () => {
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    if(formData.status !== 'entregue') {
        return true;
    }

    let isValid = true;
    const newErrors = {};

    for (const {id, label} of requiredFields) {
        if(!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
    }

    if(formData.delivered_at) {
        const dateIsFuture = isDateInFuture(formData.delivered_at);
        if(dateIsFuture) {
            isValid = false;
            newErrors['delivered_at'] = ['A data de entrega não pode ser no futuro.'];
        }
    }

    Object.assign(fieldErrors, newErrors);

    if(!isValid) {
        const filteredErrors = Object.entries(fieldErrors).reduce((acc, [key, value]) => {
            if (value !== null) {
                acc[key] = value;
            }
            return acc;
        }, {});

        showAlert('error', 'Campos inválidos', {
            errors: filteredErrors
        }, 6000);
    }

    return isValid;
}

const cleanFieldInvalids = (field) => {
    if(field) {
        fieldErrors[field] = null;
    }
}

watch(() => props.modelValue, async (visible) => {
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    if(visible) {
        await getDelivery(props.deliveryData.id);
    }
});
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        :header="'Finalizar status da entrega'"
        :style="{ width: '42rem'}"
        :draggable="false"
    >
        <div v-if="lookingDelivery" class="d-flex justify-content-center mb-5">
            <AppSpinner/>
        </div>

        <form v-if="!lookingDelivery" @submit.prevent="save()" class="row">
            <div class="col-12 mb-2 d-flex flex-column">
                <p><span class="fw-semibold">Entrega para:</span> {{ formData.user_name }}</p>
                <p class="text-truncate d-inline-block" style="max-width: 300px;"><span class="fw-semibold">Item: </span>{{ formData.item_description }}</p>
                <Tag v-if="deliveryStatus === 'entregue'" :value="statusMap[deliveryStatus].label" :severity="statusMap[deliveryStatus].severity" class="px-1"/>
            </div>

            <div class="col-12 col-sm-4 mb-3">
                <label for="delivered_at" class="d-block mb-2">Data da entrega</label>
                <DatePicker 
                    v-model="formData.delivered_at" 
                    showTime 
                    hourFormat="24" 
                    date-format="dd/mm/yy" 
                    placeholder="Selecione a data e hora" 
                    fluid 
                    showIcon 
                    iconDisplay="input" 
                    :invalid="!!fieldErrors.delivered_at" 
                    @update:model-value="cleanFieldInvalids('delivered_at')"/>
            </div>
            <div class="col-12 col-sm-4 mb-3">
                <label for="delivered_to_name" class="d-block mb-2">Recebido por</label>
                <InputText 
                    type="text" 
                    v-model="formData.delivered_to_name" 
                    id="delivered_to_name" 
                    fluid 
                    :invalid="!!fieldErrors.delivered_to_name" 
                    @change="cleanFieldInvalids('delivered_to_name')"
                />
            </div>
            <div class="col-12 col-sm-4 d-flex flex-column mb-3">
                <label for="Status" class="mb-2">Status</label>
                <Select
                    v-model="formData.status"
                    :options="filterStatus"
                    optionLabel="name"
                    optionValue="code"
                    placeholder="Selecione o status"
                >
                    <template #item="slotProps">
                        <Tag :value="slotProps.option.name" :severity="slotProps.option.severity" class="px-1"/>
                    </template>

                    <template #option="slotProps">
                        <Tag :value="slotProps.option.name" :severity="slotProps.option.severity" class="px-1"/>
                    </template>
                </Select>
            </div>
            <div class="col-12 mb-3">
                <label for="notes" class="d-flex mb-2">Notas</label>
                <Editor v-model="formData.notes" editorStyle="height: 140px">
                    <template #toolbar>
                    <span class="ql-formats">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-strike"></button>
                    </span>

                    <span class="ql-formats">
                        <select class="ql-header">
                        <option value="1">Heading 1</option>
                        <option value="2">Heading 2</option>
                        <option selected>Normal</option>
                        </select>
                    </span>

                    <span class="ql-formats">
                        <button class="ql-list" value="ordered"></button>
                        <button class="ql-list" value="bullet"></button>
                        <button class="ql-indent" value="-1"></button>
                        <button class="ql-indent" value="+1"></button>
                    </span>

                    <span class="ql-formats">
                        <select class="ql-color"></select>
                        <select class="ql-background"></select>
                    </span>

                    <span class="ql-formats">
                        <button class="ql-clean"></button>
                    </span>
                    </template>
                </Editor>
            </div>
        </form>
        <template #footer v-if="!lookingDelivery">
            <Button 
                label="Cancelar" 
                icon="pi pi-times" 
                @click="visible = false" 
                class="p-button-text"
                size="small" 
                :disabled="loading"
                severity="danger"
            />
            <Button
                :label="loading ? 'Aguarde...' : 'Salvar'" 
                icon="pi pi-check" 
                @click="save()" 
                size="small"  
                :loading="loading"
            />
        </template>
    </Dialog>
</template>