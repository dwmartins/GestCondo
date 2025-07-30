<script setup>
import { onMounted, ref, watch } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import { Avatar, Button, Card, Column, DataTable, Dialog, IconField, InputIcon, InputText, Tag, useToast } from 'primevue';
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

const modalDelete = ref(false);
const condominiumStore = useCondominiumStore();
const users = ref([]);
const userToDelete = ref(null);

const loading = ref({
    delete: false,
    getAll: true,
});

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
        loading.value.getAll = true;
        const response = await userService.getAll();
        users.value = response.data.data;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.getAll = false;
    }
}

const updateUser = (user) => {
    router.push({
        name: 'user',
        params: { action: 'atualizar' },
        query: { id: user.id}
    });
}

const openModal = (action, data) => {
    if(action === 'delete') {
        userToDelete.value = data;
        modalDelete.value = true;
        return;
    }
}

const deleteUser = async () => {
    try {
        loading.value.delete = true;
        const response = await userService.delete(userToDelete.value.id);
        modalDelete.value = false;
        removeUser(userToDelete.value.id);
        userToDelete.value = null;

        showAlert('success', 'Sucesso', response.data.message);
    } catch (error) {
        console.log(error);
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.delete = false;
    }
}

const removeUser= (id) => {
    users.value = users.value.filter(c => c.id !== id);
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
                    <AppLoadingData v-if="loading.getAll">
                        Buscando moradores...
                    </AppLoadingData>
                </Transition>

                <Transition name="fade">
                    <DataTable v-if="users.length && !loading.getAll" :value="users" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable>
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
                                        @click="openModal('delete', data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </Transition>

                <AppEmpty v-if="!loading.getAll && !users.length"/>
            </template>
        </Card>

        <Dialog v-model:visible="modalDelete" modal header="Excluir morador"  :style="{ width: '28rem' }">
            <div class="d-flex align-items-center gap-3">
                <i class="pi pi-info-circle" style="font-size: 1.8rem"></i>
                <p>Tem certeza que deseja excluir {{ userToDelete.name }}? Essa ação é irreversível.</p>
            </div>

            <template #footer>
                <Button 
                    label="Cancelar" 
                    icon="pi pi-times" 
                    class="p-button-text"
                    size="small" 
                    :disabled="loading.delete" 
                    @click="modalDelete = false" 
                />

                <Button label="Confirmar exclusão"
                    icon="pi pi-trash"
                    severity="danger"
                    size="small"
                    :loading="loading.delete"
                    @click="deleteUser()" 
                />
            </template>
        </Dialog>
    </section>
</template>
