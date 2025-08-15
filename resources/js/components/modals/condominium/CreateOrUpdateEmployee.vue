<script setup>
import { Button, DatePicker, Dialog, Divider, InputNumber, InputText, Password, Select, Textarea, useToast } from 'primevue';
import { computed, reactive, ref, watch } from 'vue';
import { createAlert } from '../../../helpers/alert';
import { isDateInFuture } from '../../../helpers/dates';
import employeeService from '../../../services/employee.service';

const showAlert = createAlert(useToast());

const props = defineProps({
    modelValue: Boolean,
    employeeData: Object,
    mode: { type: String, default: 'create' }
});

const emit = defineEmits(['update:modelValue', 'saved']);

const setUser = (item) => {
    const itens_boolean = ['account_status', 'accepts_emails'];

    for (const key in item) {
        if (itens_boolean.includes(key)) {
            formData[key] = !!item[key];
            continue;
        }

        if (key === 'employee' && item.employee) {
            for (const empKey in item.employee) {
                formData.employee[empKey] = item.employee[empKey];
            }
            continue;
        }

        formData[key] = item[key];
    }
};

const formData = reactive({
    id: null,
    name: "",
    last_name: null,
    email: "",
    password: "",
    role: "funcionario",
    account_status: true,
    description: '',
    phone: null,
    date_of_birth: null,
    address: null,
    complement: null,
    city: null,
    zip_code: null,
    state: null,
    country: null,
    avatar: null,
    accepts_emails: true,
    last_login_at: null,
    remember_token: null,
    created_at: null,
    updated_at: null,
    employee: {
        id: null,
        occupation: "",
        admission_date: null,
        resignation_date: null,
        employee_description: "",
        status: "ativo",
        created_at: null,
        updated_at: null
    }
});

const loading = ref(false);

const fieldErrors = reactive({});
const requiredFields = [
    {id: 'name', label: 'Nome'},
    {id: 'last_name', label: 'Sobrenome'},
    {id: 'email', label: 'E-mail'},
    {id: 'phone', label: 'Telefone'},
    {id: 'password', label: 'Senha'},

    {id: 'employee.occupation', label: 'Ocupação'},
    {id: 'employee.admission_date', label: 'Data de admissão'}
];

const filteredStatus = [
    {code: 'ativo', name: 'Ativo'},
    {code: 'ferias', name: 'Ferias'},
    {code: 'licenca', name: 'Licença'},
    {code: 'afastado', name: 'Afastado'},
    {code: 'desligado', name: 'Desligado'},
    {code: 'suspenso', name: 'Suspenso'},
];

const accountStatus = [
    {code: true, name: 'Ativa'},
    {code: false, name: 'Inativa'}
];

if (props.employeeData) {
    setUser(props.employeeData);
}

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const save = async () => {
    if(!validateFields()) return;
    loading.value = true;

    formData.date_of_birth = formatDate(formData.date_of_birth);
    formData.employee.admission_date = formatDate(formData.employee.admission_date);
    formData.employee.resignation_date = formatDate(formData.employee.resignation_date);

    try {
        const response = await employeeService.create(formData);
        showAlert('success', 'Sucesso', response.message)

        emit('saved', response.data);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
};

const update = async () => {
    if(!validateFields()) return;
    loading.value = true;

    formData.date_of_birth = formatDate(formData.date_of_birth);
    formData.employee.admission_date = formatDate(formData.employee.admission_date);
    formData.employee.resignation_date = formatDate(formData.employee.resignation_date);

    try {
        const response = await employeeService.update(formData);
        showAlert('success', 'Sucesso', response.message);

        emit('saved', response.data);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const getValueByPath = (obj, path) => {
    return path.split('.').reduce((o, key) => (o ? o[key] : undefined), obj);
}

const validateFields = () => {
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    let isValid = true;
    const newErrors = {};

    for (const {id, label} of requiredFields) {
        if (id === 'password' && props.mode === 'update') continue;

        const value = getValueByPath(formData, id);

        if (!value) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
    }

    const passwordLength = formData.password?.length || 0;

    if (
        (props.mode === 'create' && passwordLength < 8) ||
        (props.mode === 'update' && passwordLength > 0 && passwordLength < 8)
    ) {
        isValid = false;
        newErrors['password'] = ['A senha deve conter no mínimo 8 caracteres.'];
    }

    if(formData.date_of_birth) {
        const dateIsFuture = isDateInFuture(formData.date_of_birth);
        if(dateIsFuture) {
            isValid = false;
            newErrors['date_of_birth'] = ['A data de nascimento não pode ser no futuro.'];
        }
    }

    Object.assign(fieldErrors, newErrors);

    if(!isValid) {
        const filteredErrors = Object.entries(fieldErrors).reduce((acc, [key, value]) => {
            if (value !== null) {
                acc[key] = value;
            }
            return acc;
        }, {});

        showAlert('error', 'Campos inválidos', {
            errors: filteredErrors
        }, 6000);
    }

    return isValid;
}

const cleanFieldInvalids = (field) => {
    if(field) {
        fieldErrors[field] = null;
    }
}

const countDescription = () => {
    if(formData.employee.employee_description.length > 500) {
        formData.employee.employee_description = formData.employee.employee_description.substring(0, 500);
    }
}

const formatDate = (value) => {
    if(!value) return;
    if (!(value instanceof Date)) return value;
    return value.toISOString().split('T')[0];
}

watch(() => props.modelValue, (visible) => {
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    if (visible) {
        if (props.mode === 'create') {
            Object.keys(formData).forEach(key => {
                if (typeof formData[key] === 'object' && formData[key] !== null) {
                Object.keys(formData[key]).forEach(subKey => formData[key][subKey] = null);
                } else {
                formData[key] = key === 'role' ? 'funcionario' : null;
                }
            });
            formData.account_status = true;
            formData.accepts_emails = true;
            formData.employee.status = 'ativo';
        } else if (props.employeeData) {
            setUser(props.employeeData);
        }
    }
}, { immediate: true });

</script>

<template>
    <Dialog 
        v-model:visible="visible" 
        modal 
        :header="props.mode === 'create' ? 'Adicionar funcionário' : 'Editar funcionário'" 
        :style="{ width: '55rem' }"
        :draggable="false"
    >
        <form @submit.prevent="save()" method="post" class="row">
            <h4 class="mb-3">Informações básicas</h4>

            <div class="mb-3 col-12 col-md-4">
                <label for="name"><span class="text-danger me-1">*</span>Nome</label>
                <InputText type="text" v-model="formData.name" id="name" class="mt-2" fluid :invalid="!!fieldErrors.name" @input="cleanFieldInvalids('name')"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="las_name"><span class="text-danger me-1">*</span>Sobrenome</label>
                <InputText type="text" v-model="formData.last_name" id="las_name" class="mt-2" fluid :invalid="!!fieldErrors.last_name" @input="cleanFieldInvalids('last_name')"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="occupation"><span class="text-danger me-1">*</span>Ocupação</label>
                <InputText type="text" v-model="formData.employee.occupation" id="occupation" placeholder="Porteiro" class="mt-2" fluid :invalid="!!fieldErrors['employee.occupation']" @input="cleanFieldInvalids('employee.occupation')"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="phone"><span class="text-danger me-1">*</span>Telefone</label>
                <InputNumber v-model="formData.phone" inputId="phone" :useGrouping="false" fluid class="mt-2" :invalid="!!fieldErrors.phone" @input="cleanFieldInvalids('phone')"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="date_of_birth">Nascimento</label>
                <DatePicker v-model="formData.date_of_birth" showIcon fluid iconDisplay="input" input-id="date_of_birth" date-format="dd/mm/yy" input-class="mt-2"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="admission_date">Data de admissão</label>
                <DatePicker v-model="formData.employee.admission_date" showIcon fluid iconDisplay="input" input-id="admission_date" date-format="dd/mm/yy" input-class="mt-2" :invalid="!!fieldErrors['employee.admission_date']" @update:model-value="cleanFieldInvalids('employee.admission_date')"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="resignation_date">Data de desligamento</label>
                <DatePicker v-model="formData.employee.resignation_date" showIcon fluid iconDisplay="input" input-id="resignation_date" date-format="dd/mm/yy" input-class="mt-2"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label class="mb-2"><span class="text-danger me-1">*</span>Status do funcionário</label>
                <Select v-model="formData.employee.status" :options="filteredStatus" optionLabel="name" optionValue="code" class="w-100" :pt="{ root: { id: 'role' } }" :invalid="!!fieldErrors['employee.status']" @change="cleanFieldInvalids('employee.status')"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label class="mb-2"><span class="text-danger me-1">*</span>Status da conta <i class="pi pi-question-circle" style="font-size: 1rem" v-tooltip.top="'Permite acesso a plataforma'"></i></label>
                <Select v-model="formData.account_status" :options="accountStatus" optionLabel="name" optionValue="code" class="w-100" :pt="{ root: { id: 'account_status' } }" :invalid="!!fieldErrors.account_status" @change="cleanFieldInvalids('account_status')"/>
            </div>

            <Divider />
            <h4 class="mb-3">Endereço</h4>

            <div class="mb-3 col-12 col-md-5 d-flex flex-column">
                <label for="address" class="mb-2">Endereço</label>
                <InputText type="text" v-model="formData.address" id="address" fluid/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="complement" class="mb-2">Complemento</label>
                <InputText type="text" v-model="formData.complement" id="complement" fluid/>
            </div>
            <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                <label for="city" class="mb-2">Cidade</label>
                <InputText type="text" v-model="formData.city" id="city" fluid/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="zip_code" class="mb-2">Código postal</label>
                <InputText type="text" v-model="formData.zip_code" id="zip_code" fluid/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="state" class="mb-2">Estado</label>
                <InputText type="text" v-model="formData.state" id="state" fluid/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="country" class="mb-2">País</label>
                <InputText type="text" v-model="formData.country" id="country" fluid/>
            </div>

            <Divider />
            <h4 class="mb-3">Informações de acesso</h4>

            <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                <label for="email"><span class="text-danger me-1">*</span>E-mail</label>
                <InputText type="email" v-model="formData.email" id="email" class="mt-2" fluid :invalid="!!fieldErrors.email" @input="cleanFieldInvalids('email')"/>
            </div>
            <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                <label for="password" class="mb-2"><span v-if="props.mode == 'create'" class="text-danger me-1">*</span>Senha</label>
                <Password id="senha" v-model="formData.password" :toggleMask="true" :feedback="false" inputClass="w-100" input-id="password" fluid :invalid="!!fieldErrors.password" @input="cleanFieldInvalids('password')"/>
            </div>

            <Divider />
            <h4 class="mb-3">Descrição do funcionário</h4>
            <div class="mb-3 col-12">
                <label for="description" class="mb-3">Descrição</label>
                <div class="position-relative mt-2">
                    <Textarea v-model="formData.employee.employee_description" @input="countDescription" autoResize rows="5" cols="30" maxlength="500" class="w-100" id="description" placeholder="Responsável por..."/>
                    <span class="counter text-secondary">{{ formData.employee.employee_description?.length ?? 0}} / 500</span>  
                </div>
            </div>
        </form>
        <template #footer>
            <Button 
                label="Cancelar" 
                icon="pi pi-times" 
                @click="visible = false" 
                class="p-button-text"
                size="small" 
                :disabled="loading"
                severity="danger"
            />
            <Button 
                :label="loading ? 'Aguarde...' : 'Salvar'" 
                icon="pi pi-check" 
                @click="props.mode === 'create'? save() : update()" 
                size="small"  
                :loading="loading"
            />
        </template>
    </Dialog>
</template>
<style scoped>
.counter {
    position: absolute;
    bottom: 6px;
    right: 6px;
    font-size: 0.9rem;
}
</style>