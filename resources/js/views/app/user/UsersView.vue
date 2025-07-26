<script setup>
import { watch } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import { Button, Card } from 'primevue';
import Breadcrumb from '../../../components/Breadcrumb.vue';

const condominiumStore = useCondominiumStore();

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'Moradores',
        to: '/app/moradores'
    }
];

const getAll = async (condominiumId) => {
    // get all users
    console.log(condominiumId)
}

watch(() => condominiumStore.currentCondominiumId, async (newId) => {
    if (newId) {
        await getAll(newId)
    }
})

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <Card>
            <template #content>
                <div class="d-flex justify-content-between mb-4">
                    <h2 class="fs-6">Moradores</h2>
                   
                    <router-link to="/app/moradores/morador/novo">
                        <Button
                            label="Adicionar"
                            size="small"
                            icon="pi pi-user-plus"
                        />
                    </router-link>
                </div>
            </template>
        </Card>
    </section>
</template>
