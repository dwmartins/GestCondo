<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import { Avatar, Button, Card, Checkbox, Column, DataTable, Dialog, Divider, IconField, InputIcon, InputText, Select, Tag, ToggleSwitch, useToast } from 'primevue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import userService from '../../../services/user.service';
import { createAlert } from '../../../helpers/alert';
import AppLoadingData from '../../../components/AppLoadingData.vue';
import AppEmpty from '../../../components/AppEmpty.vue';
import { default_avatar, path_avatars } from '../../../helpers/constants';
import { useRouter } from 'vue-router';
import { useUserStore } from '../../../stores/userStore';
import { checkPermission, defaultPermissions, ROLE_DEFINITIONS, ROLE_SINDICO, ROLE_SUB_SINDICO, ROLE_SUPORTE } from '../../../helpers/auth';

const router = useRouter();
const showAlert = createAlert(useToast());

const userStore = useUserStore();
const auth = userStore.user;

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
const modalSettings = ref(false);

const condominiumStore = useCondominiumStore();
const users = ref([]);
const userToDelete = ref(null);

const formData = reactive({});

const loading = ref({
    delete: false,
    getAll: true,
    getUser: false,
    settings: false,
});

const filteredRoles = computed(() => {
    const allRoles = Object.entries(ROLE_DEFINITIONS).map(([code, name]) => ({
        code,
        name
    }));
    
    if (auth.role === ROLE_SUPORTE) {
        return allRoles;
    }
    
    return allRoles.filter(role => role.code !== ROLE_SUPORTE);
})

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

const setUser = (item) => {
    const itens_boolean = ['account_status', 'accepts_emails'];

    for (const key in item) {
        if (itens_boolean.includes(key)) {
            formData[key] = !!item[key];
            continue;
        }

        if (key === 'permissions') {
            continue;
        }

        formData[key] = item[key];
    }

    formData.permissions = mergePermissions(defaultPermissions, item.permissions || {});
}

const openModal = (action, data) => {
    clearFormData();
    
    if(action === 'delete') {
        userToDelete.value = data;
        modalDelete.value = true;
        return;
    }

    if(action == 'settings') {
        getById(data.id);
        modalSettings.value = true;
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
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.delete = false;
    }
}

const changeSettings = async () => {
    try {
        loading.value.settings = true;
        const response = await userService.settings(formData);
        const userUpdated = response.data.user;

        const index = users.value.findIndex(c => c.id === userUpdated.id);
        if (index !== -1) {
            users.value[index] = userUpdated;
        }

        showAlert('success', 'Sucesso', response.data.message);
        modalSettings.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.settings = false;
    }
}

const removeUser= (id) => {
    users.value = users.value.filter(c => c.id !== id);
}

const getById = async (id) => {
    try {
        loading.value.getUser = true;
        const response = await userService.getById(id);
        console.log(response.data)
        setUser(response.data)
    } catch (error) {
        showAlert('error', 'Error', error.response?.data);
    } finally {
        loading.value.getUser = false;
    }
}

const mergePermissions = (defaults, userPerms) => {
    const merged = {};

    for (const module in defaults) {
        merged[module] = { ...defaults[module] };

        if (userPerms && userPerms[module]) {
            for (const action in defaults[module]) {
                if (typeof userPerms[module][action] === 'boolean') {
                    merged[module][action] = userPerms[module][action];
                }
            }
        }
    }

    return merged;
}

const onPermissionChange = (module, action) => {
    if (action === 'visualizar' && !formData.permissions[module][action]) {
        formData.permissions[module]['criar'] = false;
        formData.permissions[module]['editar'] = false;
        formData.permissions[module]['excluir'] = false;
    }
}

const clearFormData = () => {
    for (const key in formData) {
        if (typeof formData[key] === 'boolean') {
            formData[key] = false;
        } else if (typeof formData[key] === 'object' && !Array.isArray(formData[key])) {
            formData[key] = {};
        } else {
            formData[key] = null;
        }
    }
};

const showActions = () => {
    return checkPermission('moradores', 'editar') || checkPermission('moradores', 'excluir');
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
                   
                    <router-link v-if="checkPermission('moradores', 'criar')" to="/app/moradores/morador/novo">
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
                    <DataTable v-if="users.length" :value="users" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable v-show="!loading.getAll">
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
                                        v-if="checkPermission('moradores', 'editar')"
                                        icon="pi pi-pen-to-square" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        size="small"
                                        rounded
                                        @click="updateUser(data)"
                                    />
                                    <Button 
                                        v-if="checkPermission('moradores', 'editar')"
                                        icon="pi pi-cog" 
                                        variant="text" 
                                        aria-label="Filter" 
                                        severity="secondary"
                                        size="small"
                                        rounded
                                        @click="openModal('settings', data)"
                                    />
                                    <Button
                                        v-if="checkPermission('moradores', 'excluir')"
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

        <Dialog v-model:visible="modalSettings" modal header="Configurações do usuário"  :style="{ width: '35rem' }">
            <AppLoadingData v-if="loading.getUser">
                Buscando informações do usuário...
            </AppLoadingData>

            <form v-if="!loading.getUser" @submit.prevent="changeSettings()" novalidate>
                
                <div class="d-flex flex-column">
                    <label for="account_status" class="mb-2">Status da Conta</label>
                    <div class="d-flex align-items-center gap-3">
                        <ToggleSwitch v-model="formData.account_status" id="account_status" />
                        <span :class="['d-flex align-items-center fw-semibold', formData.account_status ? 'text-success' : 'text-danger']">
                            {{ formData.account_status ? 'Ativo' : 'Inativo' }}
                            <i :class="['ms-2', formData.account_status ? 'pi pi-check-circle' : 'pi pi-times-circle']"></i>
                        </span>
                    </div>
                    <small class="text-muted mt-1">Define se o usuário pode acessar o sistema</small>
                </div>

                <Divider />

                <div class="d-flex flex-column">
                    <label for="accepts_emails" class="mb-2">Preferência de E-mails</label>
                    <div class="d-flex align-items-center gap-3">
                        <ToggleSwitch v-model="formData.accepts_emails" id="accepts_emails" />
                        <span :class="['d-flex align-items-center fw-semibold', formData.accepts_emails ? 'text-success' : 'text-danger']">
                            {{ formData.accepts_emails ? 'Receber e-mails' : 'Não receber' }}
                            <i :class="['ms-2', formData.accepts_emails ? 'pi pi-envelope' : 'pi pi-ban']"></i>
                        </span>
                    </div>
                    <small class="text-muted mt-1">Define se o usuário receberá comunicações por e-mail</small>
                </div>

                <Divider />

                <div class="d-flex flex-column mb-2">
                    <label class="mb-2">Tipo</label>
                    <Select v-model="formData.role" :options="filteredRoles" optionLabel="name" optionValue="code" style="max-width: 240px;" :pt="{ root: { id: 'role' } }" />
                </div>

                <div v-if="formData.role == ROLE_SUB_SINDICO" class="d-flex flex-column mb-4">
                    <Divider />

                    <label class="mb-2">Permissões</label>

                    <div
                        v-for="(actions, module) in formData.permissions"
                        :key="module"
                        class="mb-3 border rounded p-3"
                    >
                        <h6 class="text-uppercase mb-2">{{ module }}</h6>

                        <div class="d-flex flex-wrap gap-3">
                            <div
                                v-for="(value, action) in actions"
                                :key="action"
                                class="d-flex align-items-center gap-2"
                            >
                                <Checkbox
                                    :inputId="`${module}-${action}`"
                                    :binary="true"
                                    v-model="formData.permissions[module][action]"
                                    size="small"
                                    :disabled="action !== 'visualizar' && !formData.permissions[module]['visualizar']"
                                    @change="onPermissionChange(module, action)"
                                />
                                <label :for="`${module}-${action}`" class="capitalize text-sm">
                                    {{ action }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <small class="text-muted mt-1">Configure as permissões específicas para o usuário</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <Button 
                        type="button" 
                        label="Cancelar" 
                        severity="secondary" 
                        @click="modalSettings = false"
                        size="small"
                        :disabled="loading.settings"
                    />

                    <Button 
                        type="submit" 
                        label="Salvar"
                        size="small"
                        :loading="loading.settings"
                    />
                </div>
            </form>
        </Dialog>
    </section>
</template>

<style>
.status-label {
    font-weight: 500;
    transition: all 0.3s ease;
}

.toggle-switch-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background-color 0.3s ease;
}

.toggle-switch-container:hover {
    background-color: #f8f9fa;
}
</style>
