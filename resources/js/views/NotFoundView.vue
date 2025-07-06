<script setup>
const svgNotFound = new URL('@assets/svg/404.svg', import.meta.url).href;
</script>

<template>
    <section class="vh-100 item_center container">
        <div>
            <img :src="svgNotFound" alt="not found">
            <div class="mt-4 text-center">
                <h2>Ops! Página não encontrada.</h2>
                <p>A página que você está tentando acessar não existe ou foi movida.</p>

                <router-link to="/app/dashboard" class="outline_none">Voltar para o início</router-link>
            </div>
        </div>
    </section>
</template>

<style scoped>
img {
    width: 100%;
    max-width: 450px;
}
</style>