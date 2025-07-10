
<template>
    <button :class="['btn', `btn-primary`, width ? `btn-${width}` : '', 'd-flex', 'justify-content-center']" :type="type" :disabled="loading">
        <template v-if="loading">
            <AppSpinner :message="textLoading" :width="width" color="text-white" />
        </template>
        <template v-else>
            <div class="d-flex align-items-center p-0">
                {{ text }}
                <i v-if="icon" :class="[icon, 'ms-1']"></i>
            </div>
        </template>
    </button>
</template>

<script setup>
import { defineProps } from 'vue';
import AppSpinner from '../AppSpinner.vue';

defineProps({
    text: {
        type: String,
        required: false,
        default: 'Salvar'
    },
    textLoading: {
        type: String,
        required: false,
        default: 'Aguarde...'
    },
    width: {
        type: String,
        require: false,
    },
    loading: {
        type: Boolean,
        require: true,
        default: true
    },
    type: {
        type: String,
        require: false,
        default: "button"
    },
    icon: {
        type: String,
        require: false,
    }
});

</script>

<style scoped>
button {
    width: auto;
}
</style>
