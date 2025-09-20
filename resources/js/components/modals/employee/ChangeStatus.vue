<script setup>
import { Button, Checkbox, Dialog, Divider, Select, ToggleSwitch, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import { computed, reactive, ref, watch } from 'vue';
import employeeService from '../../../services/employee.service';
import AppLoadingData from '../../AppLoadingData.vue';
import { defaultPermissions } from '../../../helpers/auth';

const showAlert = createAlert(useToast());

const props = defineProps({
    modelValue: Boolean,
    employeeId: Number,
});

const emit = defineEmits(['update:modelValue', 'changeSettings']);

const loading = ref(false);
const searching = ref(true);

const formData = reactive({});

const visible = computed({
    get() {
        return props.modelValue
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const filteredEmployeeStatus = [
    {code: 'ativo', name: 'Ativo'},
    {code: 'ferias', name: 'Ferias'},
    {code: 'licenca', name: 'Licença'},
    {code: 'afastado', name: 'Afastado'},
    {code: 'desligado', name: 'Desligado'},
    {code: 'suspenso', name: 'Suspenso'},
];

const setUser = (item) => {
    const itens_boolean = ['account_status', 'accepts_email'];

    for (const key in item) {
        if (itens_boolean.includes(key)) {
            formData[key] = !!item[key];
            continue;
        }

        if (key === 'employee' && item.employee) {
            if (!formData.employee) formData.employee = {};
            formData.employee = { ...item.employee };
            continue;
        }

        if (key === 'permissions' && item.permissions) {
            if (!formData.permissions) formData.permissions = {};
            formData.permissions = { ...item.permissions };
            continue;
        }
    }

    formData.permissions.permissions = mergePermissions(defaultPermissions, item.permissions.permissions || {});
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
    if (action === 'visualizar' && !formData.permissions.permissions[module][action]) {
        formData.permissions.permissions[module]['criar'] = false;
        formData.permissions.permissions[module]['editar'] = false;
        formData.permissions.permissions[module]['excluir'] = false;
    }
}

const getEmployee = async (id) => {
    try {
        searching.value = true;
        const response = await employeeService.getEmployee(id);
        setUser(response);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        searching.value = false;
    }
}

const changeSettings = async () => {
    try {
        loading.value = true;

        const response = await employeeService.changeStatus({
            account_status: formData.account_status,
            employee: {
                status: formData.employee.status
            },
            permissions: formData.permissions.permissions
        }, props.employeeId);

        showAlert('success', 'Sucesso', response.message);

        emit('changeSettings', response.data);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.message);
    } finally {
        loading.value = false;
    }
}

watch( () => props.modelValue, async (visible) => {
    if(visible) {
        await getEmployee(props.employeeId);
    }

}, {immediate: true});
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        header="Alterar configurações do funcionário"
        :style="{ width: '55rem' }"
        :draggable="false"
    >

        <AppLoadingData v-if="searching">
            Buscando informações do funcionário...
        </AppLoadingData>

        <form v-if="!searching && Object.keys(formData).length" @submit.prevent="changeSettings()">
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

            <div class="d-flex flex-column mb-2">
                <label class="mb-2">Status do funcionário</label>
                <Select v-model="formData.employee.status" :options="filteredEmployeeStatus" optionLabel="name" optionValue="code" style="max-width: 240px;" :pt="{ root: { id: 'role' } }" />
            </div>

            <div class="mb-4">
                <Divider />

                <p class="mb-2">Permissões</p>

                <div class="row g-3">
                    <div class="col-12 col-sm-6" v-for="(actions, module) in formData.permissions.permissions" :key="module">
                        <div class="border rounded p-3 h-100">
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
                                        v-model="formData.permissions.permissions[module][action]"
                                        size="small"
                                        :disabled="action !== 'visualizar' && !formData.permissions.permissions[module]['visualizar']"
                                        @change="onPermissionChange(module, action)"
                                        v-if="action !== 'label'"
                                    />
                                    <label v-if="action !== 'label'" :for="`${module}-${action}`" class="capitalize text-sm">
                                        {{ action }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <small class="text-muted mt-1">Configure as permissões específicas para o usuário</small>
            </div>


        </form>

        <template #footer v-if="!searching && Object.keys(formData).length">
            <Button 
                label="Cancelar" 
                icon="pi pi-times" 
                @click="visible = false" 
                class="p-button-text"
                :disabled="loading"
                severity="danger"
            />
            <Button 
                :label="loading ? 'Aguarde...' : 'Salvar'" 
                icon="pi pi-check" 
                @click="changeSettings()"  
                :loading="loading"
            />
        </template>

    </Dialog>
</template>

<style scoped>
.status-label {
    font-weight: 500;
    transition: all 0.3s ease;
}
</style>

