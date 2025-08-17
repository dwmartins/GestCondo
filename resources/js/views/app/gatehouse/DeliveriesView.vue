<script setup>
import { ref } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import BaseCard from '../../../components/BaseCard.vue';
import { Button } from 'primevue';

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
</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <BaseCard>
            <div class="d-flex justify-content-between mb-3">
                <h2 class="fs-6">Registros de entregas</h2>
                <Button 
                    label="Registrar"
                    size="small"
                    icon="pi pi-plus"
                />
            </div>
        </BaseCard>
    </section>
</template>