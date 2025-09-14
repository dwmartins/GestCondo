<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import BaseCard from '../../../components/BaseCard.vue';
import { Button, Column, DataTable, Divider, IconField, InputIcon, InputText, Select, Tag, useToast } from 'primevue';
import { checkPermission } from '../../../helpers/auth';
import CreateOrUpdate from '../../../components/modals/delivery/CreateOrUpdate.vue';
import deliveryService from '../../../services/delivery.service';
import { createAlert } from '../../../helpers/alert';
import AppLoadingData from '../../../components/AppLoadingData.vue';
import AppEmpty from '../../../components/AppEmpty.vue';
import { formatDateTime } from '../../../helpers/dates';
import DeleteDelivery from '../../../components/modals/delivery/DeleteDelivery.vue';
import ChangeStatus from '../../../components/modals/delivery/ChangeStatus.vue';

const showAlert = createAlert(useToast());

const condominiumStore = useCondominiumStore();

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'entregas',
        to: '/app/portaria/entregas'
    }
];

const deliveries = ref([]);
const pagination = ref({});
const summary = ref({});
const currentPage = ref(1);
const itemsPerPage = ref(7);

const loading = ref(false);

const modalEditOrCreateVisible = ref(false);
const modalEditOrCreateMode = ref('create');
const deliveryToEdit = ref(null);

const modalDeleteVisible = ref(false);
const deliveryToDelete = ref(null);

const modalChangeStatusVisible = ref(false);
const modalChangeStatusMode = ref('changeStatus');

const filters = reactive({
    global: null,
    status: null
});

const statusMap = {
    entregue: { label: 'Entregue', severity: 'success' },
    devolvido: { label: 'Devolvido', severity: 'danger' },
    pendente: { label: 'Pendente', severity: 'warn' }
};

onMounted(async () => {
    await getDeliveries();
});

const getDeliveries = async (page = 1) => {
    loading.value = true;

    try {
        const response = await deliveryService.getAll(page, itemsPerPage.value, filters);
        deliveries.value = response.data.data;
        pagination.value = response.data.pagination;
        summary.value = response.data.summary;
        currentPage.value = pagination.value.current_page;
    } catch (error) {
        console.log(error);
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const onCloseModal = () => {
    getDeliveries();
}

const openModal = (action, data = null) => {
    deliveryToEdit.value = null;

    switch (action) {
        case 'create':
            modalEditOrCreateMode.value = 'create';
            modalEditOrCreateVisible.value = true;
            break;
        case 'update':
            modalEditOrCreateMode.value = 'update';
            modalEditOrCreateVisible.value = true;
            deliveryToEdit.value = data;
            break;
        case 'delete':
            deliveryToDelete.value = data;
            modalDeleteVisible.value = true;
            break;
        case 'changeStatus':
            deliveryToEdit.value = data;
            modalChangeStatusVisible.value = true;
            modalChangeStatusMode.value = 'changeStatus';
            break;
        default:
            break;
    }
}

const showActions = () => {
    return checkPermission('entregas', 'editar') || checkPermission('entregas', 'excluir');
}

const onPage = (event) => {
    const page = event.page + 1;
    itemsPerPage.value = event.rows;
    getDeliveries(page);
};

const onClearSearch = () => {
    for (const key in filters) {
        filters[key] = '';
    }

    getDeliveries();
}

watch(() => condominiumStore.currentCondominiumId, async (newId) => {
    if(newId) {
        await getDeliveries();
    }
});

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <BaseCard class="mb-4">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="fs-6">Registros de entregas</h2>
                <Button 
                    v-if="checkPermission('entregas', 'criar')"
                    label="Registrar"
                    icon="pi pi-plus"
                    size="small"
                    @click="openModal('create')"
                />
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <InputText 
                        v-model="filters.global" 
                        placeholder="Buscar por descrição..." 
                        fluid
                        size="small"
                    />
                </div>
                <div class="col-12 col-md-2">
                    <Select
                        v-model="filters.status"
                        optionLabel="name"
                        optionValue="code"
                        class="w-100"
                        :pt="{ root: { id: 'status' } }"
                        :options="[
                            { name: 'Entregue', code: 'entregue' },
                            { name: 'Devolvido', code: 'devolvido' },
                            { name: 'Pendente', code: 'pendente' }
                        ]"
                        placeholder="Status"
                    />
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-3 justify-content-end justify-content-md-start">
                        <Button
                            icon="pi pi-filter"
                            label="Filtrar"
                            size="small"
                            @click="getDeliveries()"
                        />
                        <Button
                            severity="secondary"
                            icon="pi pi-filter-slash"
                            label="Limpar"
                            size="small"
                            @click="onClearSearch()"
                        />
                    </div>
                </div>
            </div>

            <Divider class="mb-4"/>

            <Transition name="fade">
                <AppLoadingData v-if="loading">
                    Buscando registros de entregas...
                </AppLoadingData>
            </Transition>

            <div v-if="deliveries.length && !loading">
                <div class="row g-3 mb-4">
                    <div class="card-count col-12 col-sm-6 col-md-3 mb-1">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                            <div class="icon-box">
                                <div class="all-deliveries">
                                    <i class="pi pi-box fs-3"></i>
                                </div>
                            </div>
                            <div>
                                <p class="fw-bold fs-5">{{ summary.total }}</p>
                                <p class="fw-light">Total</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-count col-12 col-sm-6 col-md-3 mb-1">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                            <div class="icon-box">
                                <div class="delivered-deliveries">
                                    <i class="pi pi-box fs-3"></i>
                                </div>
                            </div>
                            <div>
                                <p class="fw-bold fs-5">{{ summary.totalEntregue }}</p>
                                <p class="fw-light">Entregues</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-count col-12 col-sm-6 col-md-3 mb-1">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                            <div class="icon-box">
                                <div class="pending-deliveries">
                                    <i class="pi pi-box fs-3"></i>
                                </div>
                            </div>
                            <div>
                                <p class="fw-bold fs-5">{{ summary.totalPendente }}</p>
                                <p class="fw-light">Pendentes</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-count col-12 col-sm-6 col-md-3 mb-1">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                            <div class="icon-box">
                                <div class="returned-deliveries">
                                    <i class="pi pi-box fs-3"></i>
                                </div>
                            </div>
                            <div>
                                <p class="fw-bold fs-5">{{ summary.totalDevolvido }}</p>
                                <p class="fw-light">Devolvidos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <DataTable 
                    :value="deliveries" 
                    :lazy="true"
                    :totalRecords="pagination.total"
                    :first="(currentPage - 1) * itemsPerPage"
                    filterDisplay="menu" 
                    paginator 
                    :rows="itemsPerPage" 
                    scrollable 
                    @page="onPage"
                    :paginatorDropdown="true"
                    :rowsPerPageOptions="[5, 7, 10, 20, 50]"
                    >

                    <Column field="item_description" header="Descrição do item" header-class="no-wrap-header-table" style="max-width: 210px">
                        <template #body="{ data }">
                            <span class="text-truncate d-inline-block" v-tooltip.top="data.item_description" style="max-width: 210px">{{ data.item_description }}</span>
                        </template>
                    </Column>
                    <Column field="status" header="Status" style="width: 10px">
                        <template #body="{ data }">
                            <div class="text-center">
                                <Tag 
                                    :value="statusMap[data.status].label" 
                                    :severity="statusMap[data.status].severity" 
                                    style="font-size: 12px; padding: 2px 6px;"
                                />
                            </div>
                        </template>
                    </Column>
                    <Column field="received_at" header-class="no-wrap-header-table">
                        <template #header>
                            <span class="fw-semibold">Data/Hora <br>Recebimento</span>
                        </template>
                        <template #body="{ data }">
                            <span class="text-nowrap">{{ formatDateTime(data.received_at) }}</span>
                        </template>
                    </Column>
                    <Column field="user_name" header="Destinatário" header-class="no-wrap-header-table">
                        <template #body="{ data }">
                            <span class="text-truncate">{{ data.user_name }} {{ data.user_last_name }}</span>
                        </template>
                    </Column>
                    <Column field="employee_name" header="Recebido por" header-class="no-wrap-header-table"/>
                    <Column v-if="showActions()" header-class="text-center">
                        <template #header>
                            <span class="fw-semibold w-100">Ações<br></span>
                        </template>
                        <template #body="{ data }">
                            <div class="d-flex justify-content-center gap-2">
                                <Button 
                                    v-if="checkPermission('entregas', 'editar')"
                                    icon="pi pi-pen-to-square" 
                                    variant="text" 
                                    aria-label="Filter" 
                                    rounded
                                    @click="openModal('update', data)"
                                />
                                <Button 
                                    v-if="checkPermission('entregas', 'editar')"
                                    icon="pi pi-cog" 
                                    variant="text" 
                                    aria-label="Filter" 
                                    severity="secondary"
                                    rounded
                                    @click="openModal('changeStatus', data)"
                                />
                                <Button
                                    v-if="checkPermission('entregas', 'excluir')"
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

            <AppEmpty v-if="!loading && !deliveries.length"/>
        </BaseCard>

        <CreateOrUpdate 
            v-model="modalEditOrCreateVisible"
            :mode="modalEditOrCreateMode"
            :deliveryData="deliveryToEdit"
            @saved="onCloseModal"
        />

        <DeleteDelivery
            v-model="modalDeleteVisible"
            :deliveryData="deliveryToDelete"
            @delete="onCloseModal"
        />

        <ChangeStatus 
            v-model="modalChangeStatusVisible"
            :deliveryData="deliveryToEdit"
            :mode="modalChangeStatusMode"
            @saved="onCloseModal"
        />
    </section>
</template>
<style scoped>
.card-count .icon-box > div {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50px;
}

.card-count .icon-box .all-deliveries {
    background-color: #e0f2fe;
    color: #0369a1;
}

.card-count .icon-box .delivered-deliveries {
    background-color: #dcfce7;
    color: #15803d;
}

.card-count .icon-box .pending-deliveries {
    background-color: #ffedd5;
    color: #c2410c;
}

.card-count .icon-box .returned-deliveries {
    background-color: #fee2e2;
    color: #b91c1c;
}
</style>