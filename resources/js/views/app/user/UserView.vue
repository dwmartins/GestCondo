<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import BaseCard from '../../../components/BaseCard.vue';
import { userStore } from '../../../stores/userStore';
import { Button, DatePicker, InputNumber, InputText, Password, Select, Steps, Textarea } from 'primevue';

const props = defineProps({
    action: {
        type: String,
        required: true,
        validator: (value) => ['create', 'edit'].includes(value)
    },
    id: {
        type: String,
        required: false
    }
})

const auth = userStore.user;

const action = ref('create');

const roles = [
    {code: 'support', name: 'Suporte'}, 
    {code: 'sindic', name: 'Síndico'},
    {code: 'resident', name: 'Morador'},
]

const stepActive = ref(0);
const steps = [
    { label: 'Básico' },
    { label: 'Endereço' },
    { label: 'Foto' }
]

const formData = reactive({
    id: null,
    name: "",
    last_name: null,
    email: "",
    password: "",
    role: "resident",
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
});

onMounted(() => {
    if (props.action === 'edit' && props.id) {
        action.value = 'edit';
    }
});

const filteredRoles = computed(() => {
    if(auth.role === 'support') {
        return roles; 
    }

    return [{code: 'resident', name: 'Morador'}];
})

const nextStep = () => {
    if(stepActive.value++ > 2) stepActive.value = 2;
}

const previousStep = () => {
    if(stepActive.value-- < 2) stepActive.value = 0;
}

const countDescription = () => {
    if(formData.description.length > 500) {
        formData.description = formData.description.substring(0, 500);
    }
}
</script>

<template>
    <section>
        <h1 class="page-title">Adicionar morador</h1>
        <BaseCard class="mb-4">
            <div>
                <Steps :model="steps" :activeStep="stepActive" class="mb-4" />
            </div>
        </BaseCard>

        <BaseCard>
            <form action="" method="post">
                <div class="row" v-show="stepActive == 0">
                    <h2 class="page-title">Informações básicas</h2>
                
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="name" class="mb-2"><span class="text-danger me-1">*</span>Nome</label>
                        <InputText type="text" v-model="formData.name" id="name"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="last_name" class="mb-2"><span class="text-danger me-1">*</span>Sobrenome</label>
                        <InputText type="text" v-model="formData.last_name" id="last_name"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="phone" class="mb-2">Telefone</label>
                        <InputNumber v-model="formData.phone" inputId="phone" :useGrouping="false" fluid />
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="date_of_birth" class="mb-2">Nascimento</label>
                        <DatePicker v-model="formData.date_of_birth" showIcon fluid iconDisplay="input" input-id="date_of_birth"/>
                    </div>
                    <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                        <label for="email" class="mb-2"><span class="text-danger me-1">*</span>E-mail</label>
                        <InputText type="email" v-model="formData.email" id="email"/>
                    </div>
                    <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                        <label for="password" class="mb-2"><span class="text-danger me-1">*</span>Senha</label>
                        <Password id="senha" v-model="formData.password" :toggleMask="true" :feedback="false" inputClass="w-100" input-id="password"/>
                    </div>
                    <div class="mb-3 col-12 col-md-4 ">
                        <label class="mb-2"><span class="text-danger me-1">*</span>Tipo</label>
                        <Select v-model="formData.role" :options="filteredRoles" optionLabel="name" optionValue="code" class="w-100" :pt="{ root: { id: 'role' } }"/>
                    </div>
                    <div class="mb-3 col-12">
                        <label for="description" class="mb-2">Descrição</label>
                        <div class="position-relative">
                            <Textarea v-model="formData.description" @input="countDescription" autoResize rows="5" cols="30" maxlength="500" class="w-100" id="description"/>
                            <span class="counter text-secondary">{{ formData.description.length }} / 500</span>  
                        </div>
                    </div>
                </div>

                <div class="row" v-show="stepActive == 1">
                    <h2 class="page-title">Informações de endereço</h2>

                    <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                        <label for="address" class="mb-2">Endereço</label>
                        <InputText type="text" v-model="formData.address" id="address"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="complement" class="mb-2">Complemento</label>
                        <InputText type="text" v-model="formData.complement" id="complement"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="city" class="mb-2">Cidade</label>
                        <InputText type="text" v-model="formData.city" id="city"/>
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
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <div class="d-flex gap-3">
                        <Button v-show="stepActive > 0" @click="previousStep" label="Voltar" severity="secondary" size="small"/>
                        <Button v-show="stepActive < 2" @click="nextStep" label="Próximo" size="small"/>
                        <Button v-show="stepActive === 2" :label="action == 'edit' ? 'Salvar' : 'Criar usuário'" size="small"/>
                    </div>
                </div>
            </form>
        </BaseCard>
    </section>
</template>

<style scoped>
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

form .icon_show_password {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    cursor: pointer;
}

.counter {
    position: absolute;
    bottom: 6px;
    right: 6px;
    font-size: 0.9rem;
}
</style>