<script setup>
import { watch } from 'vue';
import BaseCard from '../../../components/BaseCard.vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';

const condominiumStore = useCondominiumStore();

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
    <BaseCard>
        <div class="d-flex justify-content-between">
            <h2 class="fs-5">Moradores</h2>
            <router-link to="/app/moradores/morador/create" class="btn btn-sm btn-primary">
                Novo
                <i class="fa-solid fa-user-plus ms-1"></i>
            </router-link>
        </div>
    </BaseCard>
</template>
