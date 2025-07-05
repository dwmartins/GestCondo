<script setup>
import { ref } from 'vue';

    const value1 = ref(true);
</script>

<template>
    <h1 class="">login</h1>
    <button class="btn btn-primary">teste</button>
    <br>
    <button type="button" class="btn btn-outline-primary">Primary</button>
    <br>
    <el-button type="primary">Primary</el-button>

    <el-switch v-model="value1" />
</template>
