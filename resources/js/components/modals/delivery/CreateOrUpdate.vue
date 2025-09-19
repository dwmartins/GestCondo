<script setup>
import { Button, DatePicker, Dialog, InputText, Select, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import { computed, reactive, ref, watch } from 'vue';
import userService from '../../../services/user.service';
import AppSpinner from '../../AppSpinner.vue';
import Editor from 'primevue/editor';
import { label } from '@primeuix/themes/aura/metergroup';
import { isDateInFuture, toMysqlDateTime } from '../../../helpers/dates';
import deliveryService from '../../../services/delivery.service';
import { useUserStore } from '../../../stores/userStore';
import { useCondominiumStore } from '../../../stores/condominiumStore';

const showAlert = createAlert(useToast());

const auth = useUserStore().user;
const condominiumSelectedId = useCondominiumStore().getCondominiumId();

const props = defineProps({
    modelValue: Boolean,
    deliveryData: Object,
    mode: {Type: String, default: 'create'}
});

const loading = ref(false);
const lookingResidents = ref(false);
const lookingDelivery = ref(false);

const emit = defineEmits(['update:modelValue', 'saved']);

const formData = reactive({
    id: null,
    condominium_id: null,
    user_id: null,
    employee_id: null,
    item_description: null,
    status: 'pendente',
    received_at: null,
    delivered_to_name: null,
    delivered_at: null,
    notes: null,
});

const requiredFields = [
    {id: 'item_description', label: 'Descrição do item'},
    {id: 'received_at', label: 'Data de recebimento'},
];
const fieldErrors = reactive({});

const residents = ref([]);

const getResidents = async () => {
    try {
        lookingResidents.value = true;
        const response = await userService.getResidents();
        residents.value = response.data;

        if(residents.value) {
            residents.value = residents.value.map(r => ({
                ...r,
                full_name: `${r.name} ${r.last_name}`
            }));
        }

        console.log(residents.value);

    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        lookingResidents.value = false;
    }
}

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

    formData.received_at = new Date(item.received_at);
}

const save = async () => {
    if(!validateFields()) return;
    loading.value = true;

    formData.condominium_id = condominiumSelectedId;
    formData.employee_id = auth.id;

    const data = {...formData};
    data.received_at = toMysqlDateTime(data.received_at);

    try {
        const response = await deliveryService.create(data);
        showAlert('success', 'Sucesso', response.message);

        emit('saved', response.data);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const update = async () => {
    if(!validateFields()) return;
    loading.value = true;

    const data = {...formData};
    data.received_at = toMysqlDateTime(data.received_at); 

    try {
        const response = await deliveryService.update(data);
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

    let isValid = true;
    const newErrors = {};

    for (const {id, label} of requiredFields) {
        if(!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
    }

    if(formData.received_at) {
        const dateIsFuture = isDateInFuture(formData.received_at);
        if(dateIsFuture) {
            isValid = false;
            newErrors['received_at'] = ['A data de recebimento não pode ser no futuro.'];
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
        if(props.mode === 'create') {
            Object.keys(formData).forEach(key => {
                formData[key] = null;
            });
            
            formData.status = 'pendente';
            await getResidents();
        } else {
            await Promise.all([
                getResidents(),
                getDelivery(props.deliveryData.id)
            ]);
        }

    }
});
</script>

<template>
    <Dialog 
        v-model:visible="visible"
        modal
        :header="props.mode === 'create' ? 'Registrar entrega' : 'Editar entrega'"
        :style="{ width: '38rem'}"
        :draggable="false"
    >

        <div v-if="(props.mode == 'update') && (lookingDelivery || lookingResidents)" class="d-flex justify-content-center mb-5">
            <AppSpinner/>
        </div>

        <form v-if="props.mode === 'create' || (props.mode === 'update' && !lookingDelivery && !lookingResidents)" @submit.prevent="save()" class="row">
            <div class="col-12 col-sm-6 mb-3">
                <label for="user_id" class="d-block mb-2">Destinatário</label>
                <Select v-model="formData.user_id" :loading="lookingResidents" :options="residents" filter :filter-fields="['name', 'last_name', 'full_name']" optionLabel="name" optionValue="id" placeholder="Selecionar morador" class="w-100">
                    <template #value="slotProps">
                        <div v-if="slotProps.value">
                            {{ residents.find(r => r.id === slotProps.value)?.name }}
                            {{ residents.find(r => r.id === slotProps.value)?.last_name }}
                        </div>
                        <span v-else>
                            {{ slotProps.placeholder }}
                        </span>
                    </template>
                    <template #option="slotProps">
                        <div>
                            {{ slotProps.option.name }} {{ slotProps.option.last_name }}
                        </div>
                    </template>
                </Select>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label for="received_at" class="d-block mb-2"><span class="text-danger me-1">*</span>Recebido em</label>
                <DatePicker v-model="formData.received_at" showTime hourFormat="24" date-format="dd/mm/yy" placeholder="Selecione a data e hora" fluid showIcon iconDisplay="input" :invalid="!!fieldErrors.received_at" @update:model-value="cleanFieldInvalids('received_at')"/>
            </div>
            <div class="col-12 mb-3">
                <label for="item_description" class="d-flex mb-2"><span class="text-danger me-1">*</span>Descrição do item</label>
                <InputText type="text" v-model="formData.item_description" id="item_description" fluid :invalid="!!fieldErrors.item_description" @change="cleanFieldInvalids('item_description')"/>
            </div>
            <div class="col-12 mb-3">
                <label for="notes" class="d-flex mb-2">Notas</label>
                <Editor v-model="formData.notes" editorStyle="height: 180px">
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

        <template #footer v-if="props.mode === 'create' || (props.mode === 'update' && !lookingDelivery && !lookingResidents)">
            <Button 
                label="Cancelar" 
                icon="pi pi-times" 
                @click="visible = false" 
                class="p-button-text"
                :disabled="loading"
                severity=""
            />
            <Button
                :label="loading ? 'Aguarde...' : 'Salvar'" 
                icon="pi pi-check" 
                @click="props.mode === 'create'? save() : update()"  
                :loading="loading"
            />
        </template>
    </Dialog>
</template>