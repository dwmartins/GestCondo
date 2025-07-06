<script setup>
import { loadingStore } from '../stores/loadingStore';
</script>

<template>
    <div v-if="loadingStore.isLoading" class="appLoadingPage">
        <div class="loader"></div> 
        <p v-if="loadingStore.message" class="text-secondary">{{ loadingStore.message }}</p>
    </div>
</template>

<style scoped>
.appLoadingPage {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 10px;
    z-index: 9999;
}

.loader {
    width: 50px;
    padding: 8px;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--bs-primary);
    --_m:
        conic-gradient(#0000 10%, #000),
        linear-gradient(#000 0 0) content-box;
    -webkit-mask: var(--_m);
    mask: var(--_m);
    -webkit-mask-composite: source-out;
    mask-composite: subtract;
    animation: l3 1s infinite linear;
}

@keyframes l3 {
    to {
        transform: rotate(1turn)
    }
}
</style>