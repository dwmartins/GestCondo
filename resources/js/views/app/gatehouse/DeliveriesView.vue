<script setup>
import { onMounted, ref, watch } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import BaseCard from '../../../components/BaseCard.vue';
import { Button, Column, DataTable, IconField, InputIcon, InputText, Tag, useToast } from 'primevue';
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
const loading = ref(false);

const modalEditOrCreateVisible = ref(false);
const modalEditOrCreateMode = ref('create');
const deliveryToEdit = ref(null);

const modalDeleteVisible = ref(false);
const deliveryToDelete = ref(null);

const modalChangeStatusVisible = ref(false);
const modalChangeStatusMode = ref('changeStatus');

const filters = ref({
    global: { value: '', matchMode: 'contains' }
});

const searchFields = ref([
    'item_description',
    'status',
    'received_at',
    'user_name',
    'user_last_name',
    'employee_name'
]);

const statusMap = {
    entregue: { label: 'Entregue', severity: 'success' },
    devolvido: { label: 'Devolvido', severity: 'danger' },
    pendente: { label: 'Pendente', severity: 'warn' }
};

onMounted(async () => {
    await getDeliveries();
});

const getDeliveries = async () => {
    loading.value = true;

    try {
        const response = await deliveryService.getAll();
        deliveries.value = response.data;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const onSavedFromModal = (delivery) => {
    if(modalEditOrCreateMode.value === 'create') {
        deliveries.value.push(delivery);
    }

    if(modalEditOrCreateMode.value == 'update') {
        const index = deliveries.value.findIndex(e => e.id === delivery.id);
        if(index !== -1) {
            deliveries.value[index] = JSON.parse(JSON.stringify(delivery));
        }
    }
}

const onDeleteFromModal = (deliveryId) => {
    deliveries.value = deliveries.value.filter(e => e.id !== deliveryId);
}

const onChangStatusFromModal = (delivery) => {
    const index = deliveries.value.findIndex(e => e.id === delivery.id);
    if(index !== -1) {
        deliveries.value[index] = JSON.parse(JSON.stringify(delivery));
    }
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

            <Transition name="fade">
                <AppLoadingData v-if="loading">
                    Buscando registros de entregas...
                </AppLoadingData>
            </Transition>

            <Transition name="fade">
                <DataTable v-if="deliveries.length && !loading" :value="deliveries" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="6" scrollable stripedRows>
                    <template #header>
                        <div class="row">
                            <div class="col-12 col-sm-9 mb-3">
                                <Tag 
                                    :value="deliveries.filter(d => d.status === 'pendente').length + ' pendentes'" 
                                    severity="warn"
                                    rounded
                                />
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
                    <Column field="item_description" header="Descrição do item" sortable header-class="no-wrap-header-table" style="max-width: 210px">
                        <template #body="{ data }">
                            <span class="text-truncate d-inline-block" v-tooltip.top="data.item_description" style="max-width: 210px">{{ data.item_description }}</span>
                        </template>
                    </Column>
                    <Column field="status" header="Status" sortable style="width: 10px">
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
                    <Column field="received_at" sortable header-class="no-wrap-header-table">
                        <template #header>
                            <span class="fw-semibold">Data/Hora <br>Recebimento</span>
                        </template>
                        <template #body="{ data }">
                            <span class="text-nowrap">{{ formatDateTime(data.received_at) }}</span>
                        </template>
                    </Column>
                    <Column field="user_name" header="Destinatário" sortable header-class="no-wrap-header-table">
                        <template #body="{ data }">
                            <span class="text-truncate">{{ data.user_name }} {{ data.user_last_name }}</span>
                        </template>
                    </Column>
                    <Column field="employee_name" header="Recebido por" sortable header-class="no-wrap-header-table"/>
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
            </Transition>

            <AppEmpty v-if="!loading && !deliveries.length"/>
        </BaseCard>

        <CreateOrUpdate 
            v-model="modalEditOrCreateVisible"
            :mode="modalEditOrCreateMode"
            :deliveryData="deliveryToEdit"
            @saved="onSavedFromModal"
        />

        <DeleteDelivery
            v-model="modalDeleteVisible"
            :deliveryData="deliveryToDelete"
            @delete="onDeleteFromModal"
        />

        <ChangeStatus 
            v-model="modalChangeStatusVisible"
            :deliveryData="deliveryToEdit"
            :mode="modalChangeStatusMode"
            @saved="onChangStatusFromModal"
        />
    </section>
</template>