<script setup>
import AppSpinner from './AppSpinner.vue';

</script>

<template>
    <div>
        <div class="d-flex align-items-center gap-3 app-loading-data">
            <div>
                <AppSpinner
                    width="sm"
                    color="text-primary"
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