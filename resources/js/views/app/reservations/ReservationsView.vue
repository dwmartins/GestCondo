<script setup>
import { onMounted, reactive, ref } from 'vue';
import BaseCard from '../../../components/BaseCard.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { Button, Card, InputText, Paginator, Select, Tag, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import commonSpacesService from '../../../services/commonSpaces.service';
import AppSpinner from '../../../components/AppSpinner.vue';
import AppEmpty from '../../../components/AppEmpty.vue';
import { defaultImage } from '../../../helpers/constants';

const showAlert = createAlert(useToast());

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'reservas',
        to: '/app/reservas'
    }
];

const loading = ref(false); 
const commonSpaces = ref([]);

const pagination = ref({});
const summary = ref({});
const currentPage = ref(1);
const itemsPerPage = ref(5);

const filters = reactive({
    global: null,
    status: null
});

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

</script>
<template>
    <section class="container">
        <Breadcrumb
            :items="breadcrumbItens"
        />

        <BaseCard>
            <div class="mb-3">
                <h2 class="fs-6">Reservas</h2>
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

            <div v-if="loading" class="loading d-flex justify-content-center align-items-center mt-5 mb-5">
                <AppSpinner />
            </div>

            <div v-if="commonSpaces.length && !loading" class="row">
                <div
                    v-for="space in commonSpaces"
                    :key="space.id"
                    class="col-12 col-md-3 mb-4"
                >
                    <Card style="overflow: hidden; height: 100%;">
                        <template #header>
                            <div class="position-relative">
                                <img :alt="space.name" :src="space.photo_url ?? defaultImage" class="space-image"/>
                                <div class="card-status">
                                    <Tag
                                        :value="space.status ? 'Disponível' : 'Indisponível'" 
                                        :severity="space.status ? 'success' : 'warn'"
                                    />
                                </div>
                            </div>
                        </template>
                        <template #title>{{ space.name }}</template>

                        <template #footer>
                            <div class="d-flex gap-2 mt-1">
                                <Button 
                                    label="Saber mais" 
                                    severity="secondary" 
                                    variant="outlined" 
                                    size="small"
                                    class="w-100" 
                                />
                                <Button 
                                    label="Reservar" 
                                    class="w-100"
                                    size="small" 
                                />
                            </div>
                        </template>
                    </Card>
                </div>

                <Paginator
                    :rows="itemsPerPage" 
                    :totalRecords="pagination.total" 
                    :rowsPerPageOptions="[5, 7, 10, 20, 50]"
                    :first="(currentPage - 1) * itemsPerPage"
                    @page="onPage"
                />
            </div>

            <AppEmpty v-if="!loading && !commonSpaces.length"/>
        </BaseCard>
    </section>
</template>

<style scoped>
.space-image {
    width: 100%;
    min-height: 220px;
}

.card-status {
    position: absolute;
    top: 8px;
    right: 8px;
}
</style>