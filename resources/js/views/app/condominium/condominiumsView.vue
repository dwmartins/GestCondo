<script setup>
import { Button, Card, Dialog, InputNumber, InputText, Select, useToast } from 'primevue';
import BaseCard from '../../../components/BaseCard.vue';
import { reactive, ref } from 'vue';
import axios from 'axios';
import { createAlert } from '../../../helpers/alert';

const showAlert = createAlert(useToast());
const modalVisible = ref(false);

const formData = reactive({
    name: '',
    cnpj: '',
    company_type: '',
    postal_code: '',
    street: '',
    number: '',
    neighborhood: '',
    city: '',
    state: '',
    phone: null,
    email: '',
});

const condominiumTypes = ref([
    {name: 'Horizontal', code: 'horizontal'},
    {name: 'Vertical', code: 'vertical'},
    {name: 'Misto', code: 'misto'}
]);

const fieldErrors = reactive({});
const requiredFields = [
    {id: 'name', label: 'Nome do condomínio'},
    {id: 'cnpj', label: 'CNPJ'},
    {id: 'company_type', label: 'Tipo'},
    {id: 'phone', label: 'Telefone'}
];

const searchCEP = async () => {
    const clearCEP = formData.postal_code.replace(/\D/g, '');

    if (clearCEP.length !== 8) return;

    try {
        const response = await axios.get(`https://viacep.com.br/ws/${clearCEP}/json/`);
        formData.street = response.data.logradouro || '';
        formData.neighborhood = response.data.bairro || '';
        formData.city = response.data.localidade || '';
        formData.state = response.data.uf || '';
        formData.complement = response.data.complemento || '';
    } catch (error) {
        console.error(error);
    }
}

const submit = async () => {
    if(!validateFields()) return;

    showAlert('success', 'Sucesso', 'Condomínio adicionado com sucesso.');
    modalVisible.value = false;
}

const validateFields = () => {
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });
    
    let isValid = true;
    const newErrors = {};

    requiredFields.forEach(({id, label}) => {
        if(!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
    });

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

const openModal = () => {
    Object.keys(formData).forEach(key => {
        formData[key] = null;
    });
    
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    modalVisible.value = true;
}

</script>

<template>
    <Card>
        <template #content>
            <div class="d-flex justify-content-between">
                <h2 class="fs-6">Condomínios</h2>
                <Button 
                    label="Adicionar"
                    size="small"
                    icon="pi pi-plus"
                    @click="openModal"
                />
            </div>
        </template>
    </Card>

    <Dialog v-model:visible="modalVisible" :draggable="false" modal header="Adicionar condomínio" :style="{ width: '48rem' }">
        <form class="row g-3" @submit.prevent="submit" novalidate> 
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="name" class="mb-2"><span class="text-danger me-1">*</span>Nome do condomínio</label>
                <InputText type="text" name="name" v-model="formData.name" id="name" :invalid="!!fieldErrors.name" @input="cleanFieldInvalids('name')"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="cnpj" class="mb-2"><span class="text-danger me-1">*</span>CNPJ</label>
                <InputText type="text" v-model="formData.cnpj" id="cnpj" :invalid="!!fieldErrors.cnpj" @input="cleanFieldInvalids('cnpj')"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label class="mb-2"><span class="text-danger me-1">*</span>Tipo</label>
                <Select v-model="formData.company_type" placeholder="Tipo de Condomínio" :options="condominiumTypes" optionLabel="name" optionValue="code" class="w-100" :invalid="!!fieldErrors.company_type" @change="cleanFieldInvalids('company_type')" :pt="{ root: { id: 'company_type' } }"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="postal_code" class="mb-2">CEP</label>
                <InputText type="text" v-model="formData.postal_code" id="postal_code" @input="searchCEP"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="street" class="mb-2">Logradouro</label>
                <InputText type="text" v-model="formData.street" id="street"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="number" class="mb-2">Número</label>
                <InputText type="text" v-model="formData.number" id="number"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="neighborhood" class="mb-2">Bairro</label>
                <InputText type="text" v-model="formData.neighborhood" id="neighborhood"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="city" class="mb-2">Cidade</label>
                <InputText type="text" v-model="formData.city" id="city"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="state" class="mb-2">Estado</label>
                <InputText type="text" v-model="formData.state" id="state"/>
            </div>
            <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                <label for="phone" class="mb-2"><span class="text-danger me-1">*</span>Telefone</label>
                <InputNumber v-model="formData.phone" inputId="phone" :useGrouping="false" fluid :invalid="!!fieldErrors.phone" @input="cleanFieldInvalids('phone')"/>
            </div>
            <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                <label for="email" class="mb-2">E-mail</label>
                <InputText type="email" v-model="formData.email" id="email"/>
            </div>

            <div class="d-flex justify-content-end gap-2 w-100">
                <Button 
                    type="button" 
                    label="Cancelar" 
                    severity="secondary" 
                    @click="modalVisible = false"
                    size="small"
                />

                <Button 
                    type="submit" 
                    label="Salvar"
                    size="small"
                    :loading="false"
                />
            </div>
        </form>
    </Dialog>
</template>