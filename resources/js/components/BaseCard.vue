<template>
    <div class="base-card">
        <slot></slot>
    </div>
</template>

<style scoped>
.base-card {
    color: var(--p-card-color);
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1),0 1px 2px -1px rgba(0,0,0,0.1);
}

html.dark-mode .base-card {
    background-color: var(--cards-dark);
}
</style>