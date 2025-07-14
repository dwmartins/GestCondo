<script setup lang="ts">
import BaseCard from '../../../components/BaseCard.vue';

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
