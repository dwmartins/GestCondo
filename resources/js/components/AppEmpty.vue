<script setup>
const emptyImage = new URL('@assets/svg/empty.svg', import.meta.url).href;
</script>

<template>
    <div class="w-100 d-flex flex-column align-items-center mb-4">
        <img :src="emptyImage" alt="Vazio" class="mb-3">
        <h3>Ops, nada por aqui...</h3>
        <p>Verifique os filtros aplicados ou tente ajustar sua busca.</p>
    </div>
</template>

<style scoped>
img {
    width: 100%;
    max-width: 200px;
}
</style>