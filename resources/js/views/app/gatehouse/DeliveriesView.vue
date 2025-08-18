<script setup>
import { ref } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import BaseCard from '../../../components/BaseCard.vue';
import { Button } from 'primevue';
import { checkPermission } from '../../../helpers/auth';
import CreateOrUpdate from '../../../components/modals/delivery/CreateOrUpdate.vue';

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