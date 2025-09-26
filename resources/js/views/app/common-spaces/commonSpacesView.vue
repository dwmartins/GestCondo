<script setup>
import { Button, Column, DataTable, Divider, InputText, Select, Tag, useToast } from 'primevue';
import BaseCard from '../../../components/BaseCard.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { checkPermission } from '../../../helpers/auth';
import { onMounted, reactive, ref } from 'vue';
import AppLoadingData from '../../../components/AppLoadingData.vue';
import { createAlert } from '../../../helpers/alert';
import commonSpacesService from '../../../services/commonSpaces.service';
import AppEmpty from '../../../components/AppEmpty.vue';
import CreateOrUpdate from '../../../components/modals/commonSpace/CreateOrUpdate.vue';
import DeleteCommonSpace from '../../../components/modals/commonSpace/DeleteCommonSpace.vue';

const showAlert = createAlert(useToast());

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'Áreas comuns',
        to: '/app/areas-comuns'
    }
];

const loading = ref(false);
const commonSpaces = ref([]);

const pagination = ref({});
const summary = ref({});
const currentPage = ref(1);
const itemsPerPage = ref(7);

const commonSpaceToEdit = ref(null);
const modalCreateOrUpdate = reactive({
    mode: 'create',
    visible: false
});

const modalDeleteVisible = ref(false);
const commonSpaceToDelete = ref(null);

const filters = reactive({
    global: null,
    status: null
});

const statusMap = {
    true: { label: 'Disponível', severity: 'success' },
    false: { label: 'Indisponível', severity: 'warn' },
};

const modals = reactive({
    createOrUpdate: {
        mode: 'create',
        visible: false
    }
})

onMounted(async () => {
    await getAll();
});

const getAll = async (page = 1) => {
    loading.value = true;

    try {
        const response = await commonSpacesService.getAll(page, itemsPerPage.value, filters);
        commonSpaces.value = response.data?.data ?? [];

        handlePagination(response.data);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const handlePagination = (response) => {
    pagination.value = response.pagination ?? {};
    summary.value = response.summary ?? {};
    currentPage.value = (response.pagination?.current_page) ?? 1;
}

const onPage = (event) => {
    const page = event.page + 1;
    itemsPerPage.value = event.rows;
    getAll(page);
};

const onClearSearch = () => {
    for (const key in filters) {
        filters[key] = '';
    }

    getAll();
}

const openModal = (type, data = null) => {
    commonSpaceToEdit.value = null;
    commonSpaceToDelete.value = null;
    let item = null;

    if(data) {
        item = JSON.parse(JSON.stringify(data));
    }

    if(type === 'create' || type === 'update') {
        if(data) commonSpaceToEdit.value = item;
        modalCreateOrUpdate.mode = item ? 'update' : 'create';
        modalCreateOrUpdate.visible = true;
        return;
    }

    if(type === 'delete') {
        commonSpaceToDelete.value = item;
        modalDeleteVisible.value = true;
        return;
    }
}

const onCloseModal = () => {
    getAll();
}

</script>
<template>
    <section class="container">
        <Breadcrumb 
            :items="breadcrumbItens"
        />

        <BaseCard class="mb-4">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="fs-6">Áreas comuns</h2>

                <Button
                    v-if="checkPermission('espacosComuns', 'criar')"
                    label="Adicionar"
                    icon="pi pi-plus"
                    size="small"
                    @click="openModal('create')"
                />
            </div>

            <form @submit.prevent="getAll()" class="row mb-4">
                <div class="col-12 col-md-4 mb-3">
                    <InputText 
                        v-model="filters.global" 
                        placeholder="Buscar por descrição..." 
                        fluid
                    />
                </div>
                <div class="col-12 col-md-2 mb-3">
                    <Select
                        v-model="filters.status"
                        optionLabel="name"
                        optionValue="code"
                        class="w-100"
                        :pt="{ root: { id: 'status' } }"
                        :options="[
                            { name: 'Disponível', code: 1 },
                            { name: 'Indisponível', code: 0 },
                        ]"
                        placeholder="Status"
                    />
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-3 justify-content-end justify-content-md-start">
                        <Button
                            icon="pi pi-filter"
                            label="Filtrar"
                            type="submit"
                            @click="getAll()"
                        />
                        <Button
                            severity="secondary"
                            icon="pi pi-filter-slash"
                            label="Limpar"
                            @click="onClearSearch()"
                        />
                    </div>
                </div>
            </form>

            <Divider class="mb-4"/>

            <Transition name="fade">
                <AppLoadingData v-if="loading">
                    Buscando áreas comuns...
                </AppLoadingData>
            </Transition>

            <div v-if="commonSpaces.length && !loading">
                <DataTable
                    :value="commonSpaces"
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
                <Column field="name" header="Nome" header-class="no-wrap-header-table" style="max-width: 210px">
                    <template #body="{ data }">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-truncate d-inline-block" v-tooltip.top="data.name" style="max-width: 210px">
                                {{ data.name }}
                            </span>
                        </div>
                    </template>
                </Column>
                <Column field="status">
                    <template #header>
                        <span class="fw-semibold text-center w-100">Status</span>
                    </template>
                    <template #body="{ data }">
                        <div class="text-center">
                            <Tag v-if="data.status" value="Disponível" severity="success"/>
                            <Tag v-if="!data.status" value="Indisponível" severity="warn"/>
                        </div>
                    </template>
                </Column>
                <Column field="manual_approval" header-class="no-wrap-header-table">
                    <template #header>
                        <span class="fw-semibold text-center w-100">Aprovação manual</span>
                    </template>
                    <template #body="{ data }">
                        <div class="text-center">
                            <Tag 
                                :value="data.manual_approval ? 'Sim' : 'Não'" 
                                :severity="data.manual_approval ? 'info' : 'secondary'" 
                                :icon="data.manual_approval ? 'pi pi-check' : 'pi pi-times'"
                            />
                        </div>
                    </template>
                </Column>
                <Column field="updated_at" header="Última atualização" header-class="no-wrap-header-table">
                    <template #body="{ data }">
                        <span class="text-truncate d-inline-block">
                            {{ new Date(data.updated_at).toLocaleString() }}
                        </span>
                    </template>
                </Column>
                <Column v-if="checkPermission('espacosComuns', 'excluir') || checkPermission('espacosComuns', 'editar')" header-class="text-center">
                    <template #header>
                        <span class="fw-semibold w-100">Ações<br></span>
                    </template>
                    <template #body="{ data }">
                        <div class="d-flex justify-content-center gap-2">
                            <Button 
                                v-if="checkPermission('espacosComuns', 'editar')"
                                icon="pi pi-pen-to-square" 
                                variant="text" 
                                aria-label="Filter" 
                                rounded
                                @click="openModal('update', data)"
                            />
                            <Button
                                v-if="checkPermission('espacosComuns', 'excluir')"
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

            <AppEmpty v-if="!loading && !commonSpaces.length"/>
        </BaseCard>

        <CreateOrUpdate
            v-model="modalCreateOrUpdate.visible"
            :commonSpaceData="commonSpaceToEdit"
            :mode="modalCreateOrUpdate.mode"
            @saved="onCloseModal()"
        />

        <DeleteCommonSpace
            v-model="modalDeleteVisible"
            :commonSpaceData="commonSpaceToDelete"
            @delete="onCloseModal()"
        />
    </section>
</template>