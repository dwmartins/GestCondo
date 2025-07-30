<script setup>
import { onMounted, ref, watch } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import { Avatar, Button, Card, Column, DataTable, IconField, InputIcon, InputText, Tag, useToast } from 'primevue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import userService from '../../../services/user.service';
import { createAlert } from '../../../helpers/alert';
import AppLoadingData from '../../../components/AppLoadingData.vue';
import AppEmpty from '../../../components/AppEmpty.vue';
import { default_avatar, path_avatars } from '../../../helpers/constants';
import { useRouter } from 'vue-router';

const router = useRouter();
const showAlert = createAlert(useToast());

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'Moradores',
        to: '/app/moradores'
    }
];

const condominiumStore = useCondominiumStore();
const loading = ref(true);
const users = ref([]);

const roles = {
    suporte: 'Suporte',
    sindico: 'SíndSíndicoico',
    morador: 'Morador'
}

const filters = ref({
    global: { value: '', matchMode: 'contains' }
});

const searchFields = ref([
    'name',
    'last_name',
    'email',
    'address'
]);

onMounted(async () => {
    await getAll()
});

const getAll = async () => {
    try {
        loading.value = true;
        const response = await userService.getAll();
        users.value = response.data.data;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const updateUser = (user) => {
    router.push({
        name: 'user',
        params: { action: 'atualizar' },
        query: { id: user.id}
    });
}

watch(() => condominiumStore.currentCondominiumId, async (newId) => {
    if (newId) {
        await getAll();
    }
})

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <Card>
            <template #content>
                <div class="d-flex justify-content-between mb-4">
                    <h2 class="fs-6">Moradores</h2>
                   
                    <router-link to="/app/moradores/morador/novo">
                        <Button
                            label="Adicionar"
                            size="small"
                            icon="pi pi-user-plus"
                        />
                    </router-link>
                </div>

                <Transition name="fade">
                    <AppLoadingData v-if="loading">
                        Buscando moradores...
                    </AppLoadingData>
                </Transition>

                <Transition name="fade">
                    <DataTable v-if="users.length && !loading" :value="users" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable>
                        <template #header>
                            <div class="row">
                                <div class="col">
                                    <Tag :value="users.length + ' Moradores'" rounded></Tag>
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
                                {{ roles[data.role] }}
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
                        <Column field="" header="Ações" header-class="d-flex justify-content-center">
                            <template #body="{ data }">
                                <div class="d-flex justify-content-center gap-2">
                                    <Button 
                                        icon="pi pi-pen-to-square" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        size="small"
                                        rounded
                                        @click="updateUser(data)"
                                    />
                                    <Button 
                                        icon="pi pi-cog" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="secondary"
                                        size="small"
                                        rounded
                                    />
                                    <Button 
                                        icon="pi pi-trash" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="danger"
                                        size="small"
                                        rounded
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </Transition>

                <AppEmpty v-if="!loading && !users.length"/>
            </template>
        </Card>
    </section>
</template>
