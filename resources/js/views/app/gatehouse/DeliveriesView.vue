<script setup>
import { onMounted, ref } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import BaseCard from '../../../components/BaseCard.vue';
import { Button, useToast } from 'primevue';
import { checkPermission } from '../../../helpers/auth';
import CreateOrUpdate from '../../../components/modals/delivery/CreateOrUpdate.vue';
import deliveryService from '../../../services/delivery.service';
import { createAlert } from '../../../helpers/alert';

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

const onSavedFromModal = () => {}

const openModal = (action, data = null) => {
    deliveryToEdit.value = null;

    switch (action) {
        case 'create':
            modalEditOrCreateMode.value = 'create';
            modalEditOrCreateVisible.value = true;
            break;
        default:
            break;
    }
}

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <BaseCard>
            <div class="d-flex justify-content-between mb-3">
                <h2 class="fs-6">Registros de entregas</h2>
                <Button 
                    v-if="checkPermission('entregas', 'criar')"
                    label="Registrar"
                    size="small"
                    icon="pi pi-plus"
                    @click="openModal('create')"
                />
            </div>
        </BaseCard>

        <CreateOrUpdate 
            v-model="modalEditOrCreateVisible"
            :mode="modalEditOrCreateMode"
            :deliveryData="deliveryToEdit"
            @saved="onSavedFromModal"
        />
    </section>
</template>