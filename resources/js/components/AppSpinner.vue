<template>
    <div>
        <div class="d-flex align-items-center">
            <div id="appSpinnerLoading" class="d-flex justify-content-center">
                <div class="spinner-border" :class="[color, width]" role="status"></div>
            </div>  
            <p v-if="message" class="m-0 ms-2" :class="color">{{ message }}</p>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

defineProps({
    message: {
        type: String,
        required: false
    },
    color: {
        type: String,
        required: false,
        default: 'text-primary'
    },
    width: {
        type: String,
        required: false,
    }
});

</script>

<style scoped>
.spinner-border {
    height: 23px;
    width: 23px;
}

.sm {
    height: 18px;
    width: 18px;
}

.lg {
    height: 30px;
    width: 30px; 
}
</style>