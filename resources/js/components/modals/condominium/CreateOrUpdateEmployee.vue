<script setup>
import { Button, Dialog, InputText } from 'primevue';
import { computed, reactive, watch } from 'vue';

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
        :style="{ width: '28rem' }"
        :draggable="false"
    >
        <div class="p-fluid">
            <div class="field">
                <label for="name">Nome</label>
                <InputText id="name" v-model="formData.name" />
            </div>
            
            <div class="field">
                <label for="email">E-mail</label>
                <InputText id="email" v-model="formData.email" />
            </div>
        </div>

        <template #footer>
            <Button 
                label="Cancelar" 
                icon="pi pi-times" 
                @click="visible = false" 
                class="p-button-text" 
            />
            <Button 
                label="Salvar" 
                icon="pi pi-check" 
                @click="save" 
                autofocus 
            />
        </template>
    </Dialog>
</template>