<script setup>
import { Avatar, Button, Column, DataTable, IconField, InputIcon, InputText, Tag, useToast } from 'primevue';
import BaseCard from '../../../components/BaseCard.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import AppLoadingData from '../../../components/AppLoadingData.vue';
import { computed, onMounted, ref, watch } from 'vue';
import AppEmpty from '../../../components/AppEmpty.vue';
import { checkPermission, ROLE_DEFINITIONS } from '../../../helpers/auth';
import employeeService from '../../../services/employee.service';
import { default_avatar, path_avatars } from '../../../helpers/constants';
import { capitalizeFirstLetter } from '../../../helpers/functions';
import { createAlert } from '../../../helpers/alert';
import CreateOrUpdateEmployee from '../../../components/modals/employee/CreateOrUpdateEmployee.vue';
import DeleteEmployee from '../../../components/modals/employee/DeleteEmployee.vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import ChangeStatus from '../../../components/modals/employee/ChangeStatus.vue';

const showAlert = createAlert(useToast());

const condominiumStore = useCondominiumStore();

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
    'address',
    'phone',
    'employee.occupation',
    'employee.status'
]);

const statusTag = [
    {severity: 'success', name: 'ativo', label: 'Ativo',},
    {severity: 'info', name: 'ferias', label: 'férias'},
    {severity: 'secondary', name: 'licenca', label: 'Licença'},
    {severity: 'secondary', name: 'afastado', label: 'Afastado'},
    {severity: 'danger', name: 'desligado', label: 'Desligado'},
    {severity: 'warn', name: 'suspenso', label: 'Suspenso'},
];

const modalEditOrCreateVisible = ref(false);
const modalEditOrCreateMode = ref('create');
const employeeToEdit = ref(null);

const employeeToDelete = ref(null);
const modalDeleteVisible = ref(false);

const modalChangeStatusVisible = ref(false);
const employeeId = ref(null);

onMounted(async () => {
    await getEmployees();
});

const getTagInfo = (status) => {
    return statusTag.find(s => s.name === status) || { severity: 'secondary', label: capitalizeFirstLetter(status) };
};

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
        return;
    }

    if(action === 'update') {
        modalEditOrCreateMode.value = action;
        modalEditOrCreateVisible.value = true;
        employeeToEdit.value = data;
        return;
    }

    if(action === 'delete') {
        employeeToDelete.value = data;
        modalDeleteVisible.value = true;
        return
    }

    if(action === 'changeStatus') {
        employeeId.value = data.id;
        modalChangeStatusVisible.value = true;
        return;
    }
}

const onSavedFromModal = (employee) => {
    if (modalEditOrCreateMode.value === 'create') {
        employees.value.push(employee);
    } else {
        const index = employees.value.findIndex(e => e.id === employee.id);
        if (index !== -1) {
            employees.value[index] = JSON.parse(JSON.stringify(employee));
        }
    }
};

const onDeletedFromModal = (employeeId) => {
    employees.value = employees.value.filter(e => e.id !== employeeId);
}

const onChangeSettingsModal = (employee) => {
    const index = employees.value.findIndex(e => e.id === employee.id);
    if (index !== -1) {
        employees.value[index] = JSON.parse(JSON.stringify(employee));
    }
}

const showActions = () => {
    return checkPermission('funcionarios', 'editar') || checkPermission('funcionarios', 'excluir');
}

watch(() => condominiumStore.currentCondominiumId, async (newId) => {
    if (newId) {
        await getEmployees();
    }
})

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <BaseCard class="mb-3">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="fs-6">Funcionários</h2>
                <Button
                    v-if="checkPermission('funcionarios', 'criar')"
                    label="Novo"
                    icon="pi pi-user-plus"
                    @click="openModal('create')"
                    size="small"
                />
            </div>

            <Transition name="fade">
                <AppLoadingData v-if="loading.getAll">
                    Buscando funcionários...
                </AppLoadingData>
            </Transition>

            <Transition name="fade">
                <div v-if="employees.length && !loading.getAll" class="container">
                    <div class="row g-3 mb-3">
                        <div class="card-count col-12 col-sm-3 mb-1">
                            <div class="d-flex align-items-center gap-2 p-3 border rounded-3">
                                <div class="icon-helmet">
                                    <div class="active-employees">
                                        <i class="fa-solid fa-helmet-safety"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="fw-bold fs-5">{{ employees.filter(e => e.employee.status == 'ativo').length }}</p>
                                    <p class="fw-light">Ativos</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-count col-12 col-sm-3 mb-1">
                            <div class="d-flex align-items-center gap-2 p-3 border rounded-3">
                                <div class="icon-helmet">
                                    <div class="vacation-employees">
                                        <i class="fa-solid fa-helmet-safety"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="fw-bold fs-5">{{ employees.filter(e => e.employee.status == 'ferias').length }}</p>
                                    <p class="fw-light">Férias</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-count col-12 col-sm-3 mb-1">
                            <div class="d-flex align-items-center gap-2 p-3 border rounded-3">
                                <div class="icon-helmet">
                                    <div class="license-employees">
                                        <i class="fa-solid fa-helmet-safety"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="fw-bold fs-5">{{ employees.filter(e => e.employee.status == 'licenca').length }}</p>
                                    <p class="fw-light">Licença</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-count col-12 col-sm-3 mb-1">
                            <div class="d-flex align-items-center gap-2 p-3 border rounded-3">
                                <div class="icon-helmet">
                                    <div class="away-employees">
                                        <i class="fa-solid fa-helmet-safety"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="fw-bold fs-5">{{ employees.filter(e => e.employee.status == 'afastado').length }}</p>
                                    <p class="fw-light">Afastados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <DataTable :value="employees" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="6" scrollable>
                        <template #header>
                            <div class="row">
                                <div class="col-12 col-sm-9 mb-3">
                                    <Tag :value="employees.filter(e => e.employee.status !== 'desligado').length + ' Funcionários'" rounded></Tag>
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

                                    <span class="text-nowrap">{{ data.name }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="email" header="E-mail" sortable>
                            <template #body="{ data }">
                                <span class="text-nowrap">{{ data.email }}</span>
                            </template>
                        </Column>
                        <Column field="employee.occupation" header="Ocupação" sortable style="min-width: 100px">
                            <template #body="{ data }">
                                {{ capitalizeFirstLetter(data.employee.occupation) }}
                            </template>
                        </Column>
                        <Column field="employee.status" header="Status" sortable>
                            <template #body="{ data }">
                                <Tag 
                                    :severity="getTagInfo(data.employee.status).severity" 
                                    :value="getTagInfo(data.employee.status).label" 
                                    style="font-size: 12px; padding: 2px 6px;"
                                />
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
                                        rounded
                                        @click="openModal('update', data)"
                                    />
                                    <Button 
                                        v-if="checkPermission('funcionarios', 'editar')"
                                        icon="pi pi-cog" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="secondary"
                                        rounded
                                        @click="openModal('changeStatus', data)"
                                    />
                                    <Button
                                        v-if="checkPermission('funcionarios', 'excluir')"
                                        icon="pi pi-trash" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="danger"
                                        rounded
                                        @click="openModal('delete', data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </Transition>

            <AppEmpty v-if="!loading.getAll && !employees.length"/>
        </BaseCard>

        <CreateOrUpdateEmployee
            v-model="modalEditOrCreateVisible"
            :mode="modalEditOrCreateMode"
            :employeeData="employeeToEdit"
            @saved="onSavedFromModal"
        />

        <DeleteEmployee
            v-model="modalDeleteVisible"
            :employeeData="employeeToDelete"
            @delete="onDeletedFromModal"
        />

        <ChangeStatus 
            v-model="modalChangeStatusVisible"
            :employeeId="employeeId"
            @changeSettings="onChangeSettingsModal"
        />
    </section>
</template>

<style scoped>
.card-count .icon-helmet > div {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
    border-radius: 50px;
}

.card-count .icon-helmet .active-employees {
    background-color: #dcfce7;
    color: #15803d;
}

.card-count .icon-helmet .vacation-employees {
    background-color: #e0f2fe;
    color: #0369a1;
}

.card-count .icon-helmet .license-employees {
    background-color: #f1f5f9;
    color: #475569;
}

.card-count .icon-helmet .away-employees {
    background-color: #ffedd5;
    color: #c2410c;
}
</style>