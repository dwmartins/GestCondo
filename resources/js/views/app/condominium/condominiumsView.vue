<script setup>
import { Button, Card, Column, DataTable, Dialog, IconField, InputIcon, InputMask, InputNumber, InputText, Select, Tag, useToast } from 'primevue';
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { createAlert } from '../../../helpers/alert';
import condominiumService from '../../../services/condominium.service';
import { formatDate } from '../../../helpers/dates';
import AppLoadingData from '@components/AppLoadingData.vue';

const showAlert = createAlert(useToast());

const modalVisible = ref(false);
const modalAction = ref(null);

const condominiums = ref([]);
const total = ref(0);
const filters = ref({
    global: { value: '', matchMode: 'contains' }
});

const searchFields = ref([
    'name',
    'phone',
    'city',
    'cnpj',
    'company_type',
    'postal_code',
    'street',
    'number',
    'neighborhood',
    'state',
    'email',
]);

const loadings = ref({
    search: true,
    updateOrCreate: false,
});

const formData = reactive({
    id: null,
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
    created_at: '',
    updated_at: ''
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

onMounted(async () => {
    await getAll();
});

const getAll = async () => {
    try {
        loadings.value.search = true;
        const response = await condominiumService.getAll();
        condominiums.value = response.data.data;
        total.value = response.data.total;
    } catch (error) {
        showAlert('error', 'Falha', error.response.data)
    } finally {
        loadings.value.search = false;
    }
    
}

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

    const payload = {
        ...formData,
        cnpj: formData.cnpj.replace(/\D/g, '')
    }

    try {
        loadings.value.updateOrCreate = true;
        if(modalAction.value === 'create') {
            const response = await condominiumService.create(payload);
            showAlert('success', 'Sucesso', response.data.message);
        } else {
            const response = await condominiumService.update(payload);
            showAlert('success', 'Sucesso', response.data.message);
        }

        getAll();
        modalVisible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response.data)
    } finally {
        loadings.value.updateOrCreate = false;
    }
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

const openModal = (action, item = null) => {
    Object.keys(formData).forEach(key => {
        formData[key] = null;
    });

    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    if(action === 'create') {
        modalAction.value = 'create';
    } else {
        modalAction.value = 'update';
        Object.keys(item).forEach(key => {
            formData[key] = item[key] ?? null;
        });
    }

    modalVisible.value = true;
}

const deleteItem = (item) => {

}

</script>

<template>
    <section class="container">
        <Card>
            <template #content>
                <div class="d-flex justify-content-between mb-4">
                    <h2 class="fs-6">Condomínios</h2>
                    <Button 
                        label="Adicionar"
                        size="small"
                        icon="pi pi-plus"
                        @click="openModal('create')"
                    />
                </div>
                <Transition name="fade">
                    <AppLoadingData v-if="loadings.search">
                        Buscando condomínios...
                    </AppLoadingData>
                </Transition>

                <Transition name="fade">
                    <DataTable :value="condominiums" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable v-show="!loadings.search">
                        <template #header>
                            <div class="d-flex justify-content-between">
                                <Tag severity="secondary" :value="total + ' Condomínios'" rounded></Tag>

                                <IconField>
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText v-model="filters.global.value" placeholder="Buscar..." size="small"/>
                                </IconField>
                            </div>
                        </template>
                        
                        <Column field="name" header="Nome" sortable style="min-width: 100px"></Column>
                        <Column field="phone" header="Telefone" sortable style="min-width: 100px"></Column>
                        <Column field="city" header="Cidade" sortable style="min-width: 100px"></Column>
                        <Column field="created_at" header="Adicionado em" sortable style="min-width: 200px">
                            <template #body="{ data }">
                                {{ formatDate(data.created_at) }}
                            </template>
                        </Column>
                        <Column field="" header="Ações" style="min-width: 100px">
                            <template #body="{ data }">
                                <div class="d-flex gap-2">
                                    <Button 
                                        icon="pi pi-pen-to-square" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        size="small"
                                        rounded
                                        @click="openModal('update', data)"
                                    />
                                    <Button 
                                        icon="pi pi-trash" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="danger"
                                        size="small"
                                        rounded
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </Transition>
            </template>
        </Card>

        <Dialog v-model:visible="modalVisible" :draggable="false" modal :header="modalAction === 'create' ? 'Adicionar condomínio' : 'Atualizar condomínio'" :style="{ width: '48rem' }">
            <form class="row g-3" @submit.prevent="submit" novalidate> 
                <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                    <label for="name" class="mb-2"><span class="text-danger me-1">*</span>Nome do condomínio</label>
                    <InputText type="text" name="name" v-model="formData.name" id="name" :invalid="!!fieldErrors.name" @input="cleanFieldInvalids('name')"/>
                </div>
                <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                    <label for="cnpj" class="mb-2"><span class="text-danger me-1">*</span>CNPJ</label>
                    <!-- <InputText type="text" v-model="formData.cnpj" id="cnpj" :invalid="!!fieldErrors.cnpj" @input="cleanFieldInvalids('cnpj')"/> -->
                    <InputMask v-model="formData.cnpj" mask="99.999.999/9999-99" :invalid="!!fieldErrors.cnpj" id="cnpj" input="cleanFieldInvalids('cnpj')"/>
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
                        :label="modalAction === 'create' ? 'Salvar' : 'Atualizar'"
                        size="small"
                        :loading="loadings.updateOrCreate"
                    />
                </div>
            </form>
        </Dialog>
    </section>
</template>
<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>