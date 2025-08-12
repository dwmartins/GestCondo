<script setup>
import { Button, DataTable, IconField, InputIcon, InputText, Tag } from 'primevue';
import BaseCard from '../../../components/BaseCard.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import AppLoadingData from '../../../components/AppLoadingData.vue';
import { ref } from 'vue';
import AppEmpty from '../../../components/AppEmpty.vue';
import { checkPermission, ROLE_DEFINITIONS } from '../../../helpers/auth';

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'Funcionários',
        to: '/app/funcionarios'
    }
];

const loading = ref({
    getAll: false
});

const employees = ref([]);

const filters = ref({
    global: { value: '', matchMode: 'contains' }
});

const searchFields = ref([
    'name',
    'last_name',
    'email',
    'address'
]);

const openModal = (action, data) => {

}

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <BaseCard>
            <div class="d-flex justify-content-between">
                <h2 class="fs-6">Funcionários</h2>
                <Button
                    label="Novo"
                    size="small"
                    icon="pi pi-user-plus"
                />
            </div>

            <Transition name="fade">
                <AppLoadingData v-if="loading.getAll">
                    Buscando funcionários...
                </AppLoadingData>
            </Transition>

            <Transition name="fade">
                <DataTable v-if="employees.length" :value="employees" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable v-show="!loading.getAll">
                    <template #header>
                        <div class="row">
                            <div class="col">
                                <Tag :value="employees.length + ' funcionários'" rounded></Tag>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <IconField>
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText v-model="filters.global.value" placeholder="Buscar..." size="small"/>
                                </IconField>
                            </div>
                        </div>
                    </template>
                    <Column field="name" header="Nome" sortable style="min-width: 100px">
                        <template #body="{ data }">
                            <div class="d-flex gap-2 align-items-center">
                                <Avatar 
                                    :image="data.avatar ? `${path_avatars}/${data.avatar}` : default_avatar" 
                                    shape="circle" 
                                />

                                <span>{{ data.name }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="email" header="E-mail" sortable style="min-width: 100px"></Column>
                    <Column field="role" header="Tipo" sortable style="min-width: 100px">
                        <template #body="{ data }">
                            {{ ROLE_DEFINITIONS[data.role] }}
                        </template>
                    </Column>
                    <Column field="account_status" header="Status" sortable style="width: 10px">
                        <template #body="{ data }">
                            <div class="text-center">
                                <Tag v-if="data.account_status" severity="success" value="Ativo" style="font-size: 12px; padding: 2px 6px;"></Tag>
                                <Tag v-if="!data.account_status" severity="danger" value="Inativo" style="font-size: 12px; padding: 2px 6px;"></Tag>
                            </div>
                        </template>
                    </Column>
                    <Column v-if="showActions()" field="" header="Ações" header-class="d-flex justify-content-center">
                        <template #body="{ data }">
                            <div class="d-flex justify-content-center gap-2">
                                <Button 
                                    v-if="checkPermission('funcionarios', 'editar')"
                                    icon="pi pi-pen-to-square" 
                                    variant="text" 
                                    aria-label="Filter" 
                                    size="small"
                                    rounded
                                    @click="openModal('update', data)"
                                />
                                <Button 
                                    v-if="checkPermission('funcionarios', 'editar')"
                                    icon="pi pi-cog" 
                                    variant="text" 
                                    aria-label="Filter" 
                                    severity="secondary"
                                    size="small"
                                    rounded
                                    @click="openModal('settings', data)"
                                />
                                <Button
                                    v-if="checkPermission('funcionarios', 'excluir')"
                                    icon="pi pi-trash" 
                                    variant="text" 
                                    aria-label="Filter" 
                                    severity="danger"
                                    size="small"
                                    rounded
                                    @click="openModal('delete', data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </Transition>

            <AppEmpty v-if="!loading.getAll && !employees.length"/>
        </BaseCard>
    </section>
</template>