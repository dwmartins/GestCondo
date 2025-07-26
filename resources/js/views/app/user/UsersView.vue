<script setup>
import { watch } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import { Breadcrumb, Button, Card } from 'primevue';
import { useRouter } from 'vue-router';

const router = useRouter();
const condominiumStore = useCondominiumStore();

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        command: () => router.push('/app')
    },
    {
        label: 'Moradores',
        command: () => router.push('/app/moradores')
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
        <Breadcrumb :model="breadcrumbItens" class="custom-Breadcrumb" />

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
