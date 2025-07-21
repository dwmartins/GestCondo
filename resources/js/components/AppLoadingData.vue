<script setup>
import { ProgressSpinner } from 'primevue';

</script>

<template>
    <div>
        <div class="d-flex align-items-center gap-3 app-loading-data">
            <div>
                <ProgressSpinner 
                    style="width: 35px; height: 35px" 
                    strokeWidth="8" 
                    fill="transparent"
                    animationDuration=".5s" 
                    aria-label="Carregando" 
                />
            </div>
            <span>
                <slot></slot>
            </span>
        </div>
    </div>
</template>

<style scoped>
.app-loading-data {
    background-color: var(--p-primary-100);
    padding: 10px;
    border-radius: 12px;
    font-weight: 600;
}
</style>