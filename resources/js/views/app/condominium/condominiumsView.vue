<script setup>
import { Button, Card, Column, ConfirmDialog, DataTable, DatePicker, Dialog, IconField, InputIcon, InputMask, InputNumber, InputSwitch, InputText, Select, Tag, ToggleButton, ToggleSwitch, useToast } from 'primevue';
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { createAlert } from '../../../helpers/alert';
import condominiumService from '../../../services/condominium.service';
import { formatDate, formatDateTime } from '../../../helpers/dates';
import AppLoadingData from '@components/AppLoadingData.vue';
import AppEmpty from '../../../components/AppEmpty.vue';

const showAlert = createAlert(useToast());

const modalDelete = ref(false);
const modalVisible = ref(false);
const modalStatus = ref(false);
const modalAction = ref(null);

const condominiums = ref([]);
const total = computed(() => {
    return condominiums.value.filter(c => c.is_active).length;
});

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
    delete: false,
    changeStatus: false
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
    is_active: false,
    expires_at: '',
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
            addCondominium(response.data.data);
            showAlert('success', 'Sucesso', response.data.message);
        } else {
            const response = await condominiumService.update(payload);
            updateCondominium(response.data.data);
            showAlert('success', 'Sucesso', response.data.message);
        }

        modalVisible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response.data)
    } finally {
        loadings.value.updateOrCreate = false;
    }
}

const changeStatus = async () => {
    try {
        loadings.value.changeStatus = true;
        const response = await condominiumService.updateStatus(formData.id, {
            is_active: formData.is_active,
            expires_at: formData.expires_at
        });

        updateCondominium(response.data.data);
        showAlert('success', 'Sucesso', response.data.message);
        modalStatus.value = false;
    } catch (error) {
        showAlert('erros', 'Erro', error.response.data);
    } finally {
        loadings.value.changeStatus = false;
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

const setItem = (item) => {
    for (const key in item) {
        if (key === 'is_active') {
            formData[key] = !!item[key];
            continue;
        }

        formData[key] = item[key];
    }
}

const openModal = (action, item = null) => {
    if(action === 'delete') {
        setItem(item);
        modalDelete.value = true;
        return;
    }

    if(action === 'settings') {
        setItem(item);
        modalStatus.value = true;
        return;
    }

    Object.keys(formData).forEach(key => {
        formData[key] = null;
    });

    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    if(action === 'create') {
        modalAction.value = 'create';
    } else if(action === 'update'){
        modalAction.value = 'update';
        setItem(item);
    }

    modalVisible.value = true;
}

const deleteItem = async () => {
    try {
        loadings.value.delete = true;
        const response = await condominiumService.delete(formData.id);
        showAlert('success', 'Item excluído', response.data.message);
        removeCondominium(formData.id);

        Object.keys(formData).forEach(key => {
            formData[key] = null;
        });

        modalDelete.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data || 'Erro desconhecido');
    } finally {
        loadings.value.delete = false;
    }
}

const addCondominium = (newItem) => {
    condominiums.value.push(newItem);
}

const updateCondominium = (updatedItem) => {
    const index = condominiums.value.findIndex(c => c.id === updatedItem.id);
    if (index !== -1) {
        condominiums.value[index] = updatedItem;
    }
}

const removeCondominium = (id) => {
    condominiums.value = condominiums.value.filter(c => c.id !== id);
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
                    <DataTable v-if="condominiums.length" :value="condominiums" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable v-show="!loadings.search">
                        <template #header>
                            <div class="row">
                                <div class="col">
                                    <Tag :value="total + ' Condomínios ativos'" rounded></Tag>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <IconField>
                                        <InputIcon>
                                            <i class="pi pi-search" />
                                        </InputIcon>
                                        <InputText v-model="filters.global.value" placeholder="Buscar..." size="small"/>
                                    </IconField>
                                </div>
                            </div>
                        </template>
                        
                        <Column field="name" header="Nome" sortable style="min-width: 100px"></Column>
                        <Column field="city" header="Cidade" sortable style="min-width: 100px"></Column>
                        <Column field="expires_at" header="Expiração" sortable style="min-width: 200px">
                            <template #body="{ data }">
                                {{ data.expires_at ? formatDateTime(data.expires_at) : 'Sem data de expiração' }}
                            </template>
                        </Column>
                        <Column field="created_at" header="Adicionado em" sortable>
                            <template #body="{ data }">
                                {{ formatDateTime(data.created_at) }}
                            </template>
                        </Column>
                        <Column field="is_active" header="Status" sortable style="width: 10px">
                            <template #body="{ data }">
                                <div class="text-center">
                                    <Tag v-if="data.is_active" severity="success" value="Ativo" style="font-size: 12px; padding: 2px 6px;"></Tag>
                                    <Tag v-if="!data.is_active" severity="danger" value="Inativo" style="font-size: 12px; padding: 2px 6px;"></Tag>
                                </div>
                            </template>
                        </Column>
                        <Column field="" header="Ações" header-class="d-flex justify-content-center">
                            <template #body="{ data }">
                                <div class="d-flex justify-content-center gap-2">
                                    <Button 
                                        icon="pi pi-pen-to-square" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        size="small"
                                        rounded
                                        @click="openModal('update', data)"
                                    />
                                    <Button 
                                        icon="pi pi-cog" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="secondary"
                                        size="small"
                                        rounded
                                        @click="openModal('settings', data)"
                                    />
                                    <Button 
                                        icon="pi pi-trash" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="danger"
                                        size="small"
                                        rounded
                                        @click="openModal('delete', data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </Transition>

                <AppEmpty v-if="!loadings.search && !condominiums.length"/>
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
                        :disabled="loadings.updateOrCreate"
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

        <Dialog v-model:visible="modalDelete" modal header="Excluir condomínio"  :style="{ width: '28rem' }">
            <div class="d-flex align-items-center gap-2">
                <i class="pi pi-info-circle" style="font-size: 1.8rem"></i>
                <p>Essa ação não poderá ser desfeita.</p>
            </div>

            <template #footer>
                <Button 
                    label="Cancelar" 
                    icon="pi pi-times" 
                    class="p-button-text"
                    size="small" 
                    :disabled="loadings.delete" 
                    @click="modalDelete = false" 
                />

                <Button label="Excluir"
                    icon="pi pi-trash"
                    severity="danger"
                    size="small"
                    :loading="loadings.delete"
                    @click="deleteItem()" 
                />
            </template>
        </Dialog>

        <Dialog v-model:visible="modalStatus" modal header="Configurações do condomínio"  :style="{ width: '28rem' }">
            <form @submit.prevent="changeStatus()" novalidate>
                <div class="mb-4 d-flex flex-column">
                    <label for="is_active" class="mb-2">Status</label>
                    <div class="d-flex gap-2">
                        <ToggleSwitch v-model="formData.is_active" id="is_active" input-id="is_active"/>
                        {{ formData.is_active ? 'Ativo' : 'Inativo' }}
                    </div>
                </div>
                <div class="mb-4 d-flex flex-column">
                    <label for="expires_at" class="mb-2">Expiração</label>
                    <DatePicker v-model="formData.expires_at" showIcon fluid iconDisplay="input" input-id="expires_at" date-format="dd/mm/yy" />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <Button 
                        type="button" 
                        label="Cancelar" 
                        severity="secondary" 
                        @click="modalStatus = false"
                        size="small"
                        :disabled="loadings.changeStatus"
                    />

                    <Button 
                        type="submit" 
                        label="Salvar"
                        size="small"
                        :loading="loadings.changeStatus"
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