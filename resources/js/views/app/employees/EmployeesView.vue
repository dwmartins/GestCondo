<script setup>
import { Avatar, Button, Column, DataTable, IconField, InputIcon, InputText, Tag, useToast } from 'primevue';
import BaseCard from '../../../components/BaseCard.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import AppLoadingData from '../../../components/AppLoadingData.vue';
import { onMounted, ref } from 'vue';
import AppEmpty from '../../../components/AppEmpty.vue';
import { checkPermission, ROLE_DEFINITIONS } from '../../../helpers/auth';
import employeeService from '../../../services/employee.service';
import { default_avatar } from '../../../helpers/constants';
import { capitalizeFirstLetter } from '../../../helpers/functions';
import { createAlert } from '../../../helpers/alert';
import CreateOrUpdateEmployee from '../../../components/modals/condominium/CreateOrUpdateEmployee.vue';

const showAlert = createAlert(useToast());

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'Funcionários',
        to: '/app/funcionarios'
    }
];

const loading = ref({
    getAll: false
});

const employees = ref([]);

const filters = ref({
    global: { value: '', matchMode: 'contains' }
});

const searchFields = ref([
    'name',
    'last_name',
    'email',
    'address'
]);

const modalEditOrCreateVisible = ref(false);
const modalEditOrCreateMode = ref('create');
const employeeToEdit = ref(null);

const selectedEmployee = ref(null);

onMounted(async () => {
    await getEmployees();
});

const getEmployees = async () => {
    try {
        loading.value.getAll = true;
        const response = await employeeService.getAll();
        employees.value = response.data;
        
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.getAll = false;
    }
}

const openModal = (action, data) => {
    employeeToEdit.value = null;

    if(action === 'create') {
        modalEditOrCreateMode.value = action;
        modalEditOrCreateVisible.value = true;
    }
}

const onSavedFromModal = (employee) => {
    if (modalEditOrCreateMode.value === 'create') {
        const newEmployee = JSON.parse(JSON.stringify(employee.data));
        employees.value.push(newEmployee);
    } else {
        const index = employees.value.findIndex(e => e.id === employee.data.id);
        if (index !== -1) {
            employees.value[index] = JSON.parse(JSON.stringify(employee.data));
        }
    }
};


const showActions = () => {
    return checkPermission('funcionarios', 'editar') || checkPermission('funcionarios', 'excluir');
}

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <BaseCard>
            <div class="d-flex justify-content-between">
                <h2 class="fs-6">Funcionários</h2>
                <Button
                    label="Novo"
                    size="small"
                    icon="pi pi-user-plus"
                    @click="openModal('create')"
                />
            </div>

            <Transition name="fade">
                <AppLoadingData v-if="loading.getAll">
                    Buscando funcionários...
                </AppLoadingData>
            </Transition>

            <Transition name="fade">
                <DataTable v-if="employees.length" :value="employees" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable v-show="!loading.getAll">
                    <template #header>
                        <div class="row">
                            <div class="col-12 col-sm-9 mb-3">
                                <Tag :value="employees.length + ' funcionários'" rounded></Tag>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div>
                                    <IconField>
                                        <InputIcon>
                                            <i class="pi pi-search" />
                                        </InputIcon>
                                        <InputText v-model="filters.global.value" placeholder="Buscar..." size="small" class="w-100"/>
                                    </IconField>
                                </div>
                            </div>
                        </div>
                    </template>
                    <Column field="name" header="Nome" sortable style="min-width: 100px">
                        <template #body="{ data }">
                            <div class="d-flex gap-2 align-items-center">
                                <Avatar 
                                    :image="data.avatar ? `${path_avatars}/${data.avatar}` : default_avatar" 
                                    shape="circle" 
                                />

                                <span>{{ data.name }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="occupation" header="Ocupação" sortable style="min-width: 100px">
                        <template #body="{ data }">
                            {{ capitalizeFirstLetter(data.employee.occupation) }}
                        </template>
                    </Column>
                    <Column field="status" header="Status do funcionário" sortable :style="{ whiteSpace: 'nowrap' }">
                        <template #body="{ data }">
                            <Tag :severity="data.employee.status === 'desligado' ? 'danger' : 'success'" :value="capitalizeFirstLetter(data.employee.status)" style="font-size: 12px; padding: 2px 6px;"></Tag>
                        </template>
                    </Column>
                    <Column field="account_status" header="Status da conta" sortable :style="{ whiteSpace: 'nowrap' }">
                        <template #body="{ data }">
                            <Tag v-if="data.account_status" severity="success" value="Ativa" style="font-size: 12px; padding: 2px 6px;"></Tag>
                            <Tag v-if="!data.account_status" severity="danger" value="Inativa" style="font-size: 12px; padding: 2px 6px;"></Tag>
                        </template>
                    </Column>
                    <Column v-if="showActions()" field="" header="Ações" header-class="d-flex justify-content-center">
                        <template #body="{ data }">
                            <div class="d-flex justify-content-center gap-2">
                                <Button 
                                    v-if="checkPermission('funcionarios', 'editar')"
                                    icon="pi pi-pen-to-square" 
                                    variant="text" 
                                    aria-label="Filter" 
                                    size="small"
                                    rounded
                                    @click="openModal('update', data)"
                                />
                                <Button 
                                    v-if="checkPermission('funcionarios', 'editar')"
                                    icon="pi pi-cog" 
                                    variant="text" 
                                    aria-label="Filter" 
                                    severity="secondary"
                                    size="small"
                                    rounded
                                    @click="openModal('settings', data)"
                                />
                                <Button
                                    v-if="checkPermission('funcionarios', 'excluir')"
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

            <AppEmpty v-if="!loading.getAll && !employees.length"/>
        </BaseCard>

        <CreateOrUpdateEmployee
            v-model="modalEditOrCreateVisible"
            :mode="modalEditOrCreateMode"
            :employeeData="employeeToEdit"
            @saved="onSavedFromModal"
        />
    </section>
</template>