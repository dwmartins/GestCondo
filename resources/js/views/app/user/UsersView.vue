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
import CreateOrUpdateResident from '../../../components/modals/resident/CreateOrUpdateResident.vue';
import { formatPhone } from '../../../helpers/functions';

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

const modalEditOrCreateResident = ref(false);
const modalEditOrCreateResidentMode = ref('create');
const residentToEdit = ref(null);

const condominiumStore = useCondominiumStore();

const users = ref([]);
const pagination = ref({});
const summary = ref({});
const currentPage = ref(1);
const itemsPerPage = ref(7);

const filters = reactive({
    global: null,
    account_status: null,
    role: null
});

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
});

onMounted(async () => {
    await getAll()
});

const getAll = async (page = 1) => {
    try {
        loading.value.getAll = true;

        const response = await userService.getAll(page, itemsPerPage.value, filters);

        users.value = response.data.data;
        pagination.value = response.data.pagination;
        summary.value = response.data.summary;
        currentPage.value = pagination.value.current_page;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.getAll = false;
    }
}

const onPage = (event) => {
    const page = event.page + 1;
    itemsPerPage.value = event.rows;
    getAll(page);
};

const onClearSearch = () => {
    for (const key in filters) {
        filters[key] = '';
    }

    getAll();
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

const openModal = (action, data = null) => {
    clearFormData();
    residentToEdit.value = null;
    
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

    if(action == 'create' || action == 'update') {
        residentToEdit.value = data;
        modalEditOrCreateResidentMode.value = action;
        modalEditOrCreateResident.value = true;
        return;
    }

}

const onCloseModal = () => {
    getAll();
}

const deleteUser = async () => {
    try {
        loading.value.delete = true;
        const response = await userService.delete(userToDelete.value.id);
        getAll(currentPage.value);

        modalDelete.value = false;
        userToDelete.value = null;
        showAlert('success', 'Sucesso', response.data.message);
    } catch (error) {
        console.log(error);
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.delete = false;
    }
}

const changeSettings = async () => {
    try {
        loading.value.settings = true;
        const response = await userService.settings(formData);
        getAll(currentPage.value);

        showAlert('success', 'Sucesso', response.data.message);
        modalSettings.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.settings = false;
    }
}

const getById = async (id) => {
    try {
        loading.value.getUser = true;
        const response = await userService.getById(id);
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

        <Card class="mb-4">
            <template #content>
                <div class="d-flex justify-content-between mb-4">
                    <h2 class="fs-6">Moradores</h2>
                   
                    <Button
                        label="Adicionar"
                        icon="pi pi-user-plus"
                        size="small"
                        @click="openModal('create')"
                    />
                </div>

                <div class="row g-3 mb-">
                    <div class="col-12 col-md-4">
                        <InputText 
                            v-model="filters.global" 
                            placeholder="Buscar por nome ou e-mail" 
                            fluid
                        />
                    </div>
                    <div class="col-12 col-md-2">
                        <Select
                            v-model="filters.account_status"
                            optionLabel="name"
                            optionValue="code"
                            class="w-100"
                            :pt="{ root: { id: 'account_status' } }"
                            :options="[
                                { name: 'Todos', code: null },
                                { name: 'Ativo', code: 1 },
                                { name: 'Inativo', code: 0 }
                            ]"
                            placeholder="Status"
                        />
                    </div>
                    <div class="col-12 col-md-2">
                        <Select
                            v-model="filters.role"
                            placeholder="Tipo"
                            optionLabel="name"
                            optionValue="code"
                            class="w-100"
                            :pt="{ root: { id: 'role' } }"
                            :options="filteredRoles"
                        />
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex gap-3 justify-content-end justify-content-md-start">
                            <Button
                                icon="pi pi-filter"
                                label="Filtrar"
                                @click="getAll()"
                            />
                            <Button
                                severity="secondary"
                                icon="pi pi-filter-slash"
                                label="Limpar"
                                @click="onClearSearch()"
                            />
                        </div>
                    </div>
                </div>

                <Divider class="mb-4"/>

                <Transition name="fade">
                    <AppLoadingData v-if="loading.getAll">
                        Buscando moradores...
                    </AppLoadingData>
                </Transition>

                <Transition name="fade">
                    <div v-if="users.length && !loading.getAll">
                        <div class="row g-3 mb-4">
                            <div class="card-count col-12 col-sm-4 mb-1">
                                <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                                    <div class="icon-user">
                                        <div class="all-users">
                                            <i class="pi pi-users fs-3"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="fw-bold fs-5">{{ summary.total }}</p>
                                        <p class="fw-light">Total</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-count col-12 col-sm-4 mb-1">
                                <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                                    <div class="icon-user">
                                        <div class="active-users">
                                            <i class="pi pi-users fs-3"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="fw-bold fs-5">{{ summary.active }}</p>
                                        <p class="fw-light">
                                            {{ summary.active > 1 ? 'Ativos' : 'Ativo'}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-count col-12 col-sm-4 mb-1">
                                <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                                    <div class="icon-user">
                                        <div class="inactive-users">
                                            <i class="pi pi-users fs-3"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="fw-bold fs-5">{{ summary.inactive }}</p>
                                        <p class="fw-light">{{ summary.inactive > 1 ? 'Inativos' : 'Inativo' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <DataTable 
                            :value="users" 
                            :lazy="true" 
                            :totalRecords="pagination.total"
                            :first="(currentPage - 1) * itemsPerPage"
                            filterDisplay="menu" 
                            paginator 
                            :rows="itemsPerPage" 
                            scrollable
                            @page="onPage"
                            :paginatorDropdown="true"
                            :rowsPerPageOptions="[5, 7, 10, 20, 50]"
                            >

                            <Column field="name" header="Nome" style="min-width: 100px">
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
                            <Column field="email" header="E-mail" style="min-width: 100px"></Column>
                            <Column field="phone" header="Telefone" style="min-width: 100px">
                                <template #body="{ data }">
                                    <span class="text-truncate">{{ formatPhone(data.phone) }}</span>
                                </template>
                            </Column>
                            <Column field="role" header="Tipo" style="min-width: 100px">
                                <template #body="{ data }">
                                    {{ ROLE_DEFINITIONS[data.role] }}
                                </template>
                            </Column>
                            <Column field="account_status" header="Status" style="width: 10px">
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
                                            rounded
                                            @click="openModal('update', data)"
                                        />
                                        <Button 
                                            v-if="checkPermission('moradores', 'editar')"
                                            icon="pi pi-cog" 
                                            variant="text" 
                                            aria-label="Filter" 
                                            severity="secondary"
                                            rounded
                                            @click="openModal('settings', data)"
                                        />
                                        <Button
                                            v-if="checkPermission('moradores', 'excluir')"
                                            icon="pi pi-trash" 
                                            variant="text" 
                                            aria-label="Filter" 
                                            severity="danger"
                                            rounded
                                            @click="openModal('delete', data)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </Transition>

                <AppEmpty v-if="!loading.getAll && !users.length"/>
            </template>
        </Card>

        <Dialog v-model:visible="modalDelete" modal header="Excluir morador"  :style="{ width: '28rem' }">
            <div class="d-flex align-items-center gap-3 mb-2">
                <i class="pi pi-info-circle" style="font-size: 1.8rem"></i>
                <p>Confirma a exclusão de <strong>{{ userToDelete.name }}</strong>? Essa ação é definitiva e não poderá ser revertida.</p>
            </div>

            <template #footer>
                <Button 
                    label="Cancelar" 
                    icon="pi pi-times" 
                    class="p-button-text" 
                    :disabled="loading.delete" 
                    @click="modalDelete = false" 
                />

                <Button label="Confirmar exclusão"
                    icon="pi pi-trash"
                    severity="danger"
                    :loading="loading.delete"
                    @click="deleteUser()" 
                />
            </template>
        </Dialog>

        <Dialog v-model:visible="modalSettings" modal header="Configurações do usuário" :draggable="false"  :style="{ width: '35rem' }">
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
                        <h6 class="text-uppercase mb-2">{{ actions.label }}</h6>

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
                                    :disabled="action !== 'visualizar' && !formData.permissions[module]['visualizar']"
                                    @change="onPermissionChange(module, action)"
                                    v-if="action !== 'label'"
                                />
                                <label v-if="action !== 'label'" :for="`${module}-${action}`" class="capitalize text-sm">
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
                        :disabled="loading.settings"
                    />

                    <Button 
                        type="submit" 
                        label="Salvar"
                        :loading="loading.settings"
                    />
                </div>
            </form>
        </Dialog>

        <CreateOrUpdateResident
            v-model="modalEditOrCreateResident"
            :mode="modalEditOrCreateResidentMode"
            :residentData="residentToEdit"
            @saved="onCloseModal()"
        />
    </section>
</template>

<style scoped>
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

.card-count .icon-user > div {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50px;
}

.card-count .icon-user .active-users {
    background-color: #dcfce7;
    color: #15803d;
}

.card-count .icon-user .all-users {
    background-color: #e0f2fe;
    color: #0369a1;
}

.card-count .icon-user .inactive-users {
    background-color: #fee2e2;
    color: #b91c1c;
}
</style>
