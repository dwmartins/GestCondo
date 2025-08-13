<script setup>
import { Button, DatePicker, Dialog, Divider, InputNumber, InputText, Password, Textarea } from 'primevue';
import { computed, reactive, ref, watch } from 'vue';

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
    try {
        const response = { data: formData };
        emit('saved', response);
        visible.value = false;
    } catch (error) {
        console.error("Erro ao salvar:", error);
    }
};

const countDescription = () => {
    if(formData.employee.employee_description.length > 500) {
        formData.employee.employee_description = formData.employee.employee_description.substring(0, 500);
    }
}

watch(() => props.modelValue, (visible) => {
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
        :style="{ width: '52rem' }"
        :draggable="false"
    >
        <form @submit.prevent="save()" class="row">
            <h4 class="mb-3">Informações básicas</h4>

            <div class="mb-3 col-12 col-md-4">
                <label for="name"><span class="text-danger me-1">*</span>Nome</label>
                <InputText type="text" v-model="formData.name" id="name" class="mt-2"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="las_name"><span class="text-danger me-1">*</span>Sobrenome</label>
                <InputText type="text" v-model="formData.last_name" id="las_name" class="mt-2"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="occupation"><span class="text-danger me-1">*</span>Ocupação</label>
                <InputText type="text" v-model="formData.employee.occupation" id="occupation" placeholder="Porteiro" class="mt-2"/>
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="phone"><span class="text-danger me-1">*</span>Telefone</label>
                <InputNumber v-model="formData.phone" inputId="phone" :useGrouping="false" fluid class="mt-2" />
            </div>
            <div class="mb-3 col-12 col-md-4">
                <label for="date_of_birth">Nascimento</label>
                <DatePicker v-model="formData.date_of_birth" showIcon fluid iconDisplay="input" input-id="date_of_birth" date-format="dd/mm/yy" input-class="mt-2"/>
            </div>

            <Divider />
            <h4 class="mb-3">Endereço</h4>

            <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                <label for="city" class="mb-2">Cidade</label>
                <InputText type="text" v-model="formData.city" id="city"/>
            </div>
            <div class="mb-3 col-12 col-md-5 d-flex flex-column">
                <label for="address" class="mb-2">Endereço</label>
                <InputText type="text" v-model="formData.address" id="address"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="complement" class="mb-2">Complemento</label>
                <InputText type="text" v-model="formData.complement" id="complement"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="zip_code" class="mb-2">Código postal</label>
                <InputText type="text" v-model="formData.zip_code" id="zip_code"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="state" class="mb-2">Estado</label>
                <InputText type="text" v-model="formData.state" id="state"/>
            </div>
            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                <label for="country" class="mb-2">País</label>
                <InputText type="text" v-model="formData.country" id="country"/>
            </div>

            <Divider />
            <h4 class="mb-3">Informações de acesso</h4>

            <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                <label for="email"><span class="text-danger me-1">*</span>E-mail</label>
                <InputText type="email" v-model="formData.email" id="email" class="mt-2"/>
            </div>
            <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                <label for="password" class="mb-2"><span v-if="props.mode == 'create'" class="text-danger me-1">*</span>Senha</label>
                <Password id="senha" v-model="formData.password" :toggleMask="true" :feedback="false" inputClass="w-100" input-id="password"/>
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
                @click="save" 
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