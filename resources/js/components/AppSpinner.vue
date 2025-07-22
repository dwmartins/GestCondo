<template>
    <div>
        <div class="d-flex align-items-center">
            <div class="loader"></div>
        </div>
    </div>
</template>

<script setup>

</script>

<style scoped>
.loader {
    width: 38px;
    padding: 7px;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--primary-color);
    --_m:
        conic-gradient(#0000 10%, #000),
        linear-gradient(#000 0 0) content-box;
    -webkit-mask: var(--_m);
    mask: var(--_m);
    -webkit-mask-composite: source-out;
    mask-composite: subtract;
    animation: l3 0.6s infinite linear;
}

@keyframes l3 {
    to {
        transform: rotate(1turn)
    }
}
</style>