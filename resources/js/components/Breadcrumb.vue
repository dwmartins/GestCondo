<script setup>
import { toRefs } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => [],
    },
    separatorIcon: {
        type: String,
        default: 'pi pi-chevron-right',
    },
});

const { items } = toRefs(props);
</script>

<template>
    <nav aria-label="breadcrumb" class="pt-1 pb-2">
        <ol class="d-flex">
            <li
                v-for="(item, index) in items"
                :key="index"
                class=""
            >
                <RouterLink
                    v-if="item.to"
                    :to="item.to"
                    class=""
                >
                    <i v-if="item.icon" :class="item.icon" />
                    <span>{{ item.label }}</span>
                </RouterLink>

                <span
                    v-else
                    class=""
                >
                    <i v-if="item.icon" :class="item.icon" />
                    <span>{{ item.label }}</span>
                </span>

                <i
                    v-if="index < items.length - 1"
                    :class="separatorIcon"
                    class="mx-2 fs-7 separator"
                />
            </li>
        </ol>
    </nav>
</template>

<style scoped>
a {
    text-decoration: none;
    color: #64748b;
}

a:hover {
    color: #4c5869;
}

.separator {
    color: #64748b;
}
</style>
