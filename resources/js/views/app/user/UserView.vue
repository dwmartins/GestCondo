<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import BaseCard from '../../../components/BaseCard.vue';
import { userStore } from '../../../stores/userStore';

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
const stepActive = ref(1);
const showPassword = ref(false);
const roles = [
    {value: 'support', label: 'Suporte'}, 
    {value: 'sindic', label: 'Síndico'},
    {value: 'resident', label: 'Morador'},
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
    
    return roles.filter(role => role.value !== 'support');
})

const nextStep = () => {
    if(stepActive.value++ > 2) stepActive.value = 3;
}

const previousStep = () => {
    if(stepActive.value-- < 2) stepActive.value = 1;
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
            <el-steps style="width: 100%;" :active="stepActive">
                <el-step title="Básico"/>
                <el-step title="Endereço" />
                <el-step title="Foto" />
            </el-steps>
        </BaseCard>

        <BaseCard>
            <form action="" method="post">
                <div class="row" v-show="stepActive == 1">
                    <h2 class="page-title">Informações básicas</h2>
                
                    <div class="mb-3 col-12 col-md-3">
                        <label for="name" class="mb-2"><span class="text-danger me-1">*</span>Nome</label>
                        <input type="text" name="name" id="name" v-model="formData.name" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-3">
                        <label for="last_name" class="mb-2"><span class="text-danger me-1">*</span>Sobrenome</label>
                        <input type="text" name="last_name" id="last_name" v-model="formData.last_name" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-3">
                        <label for="phone" class="mb-2">Telefone</label>
                        <input type="number" name="phone" id="phone" v-model="formData.phone" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-3">
                        <label for="date_of_birth" class="mb-2">Nascimento</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" v-model="formData.date_of_birth" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-4">
                        <label for="email" class="mb-2"><span class="text-danger me-1">*</span>E-mail</label>
                        <input type="email" name="email" id="email" autocomplete="email" v-model="formData.email" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-4">
                        <label for="password" class="mb-2"><span class="text-danger me-1">*</span>Senha</label>
                        <div class="position-relative">
                            <input 
                                :type="showPassword ? 'text' : 'password'"
                                name="password" 
                                v-model="formData.password" 
                                class="form-control custom_focus text-secondary"
                                id="password"
                            >
                            <i :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" class="fa-regular icon_show_password text-secondary fs-5" @click="showPassword = !showPassword"></i>
                        </div>
                    </div>
                    <div class="mb-3 col-12 col-md-4">
                        <label for="role" class="mb-2"><span class="text-danger me-1">*</span>Tipo</label>
                        <select class="form-select custom_focus" id="role" v-model="formData.role">
                            <option 
                                v-for="item in filteredRoles" 
                                :key="item.value" 
                                :value="item.value"
                                :selected="action == 'edit' && item.value == 'resident'">
                                {{ item.label }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-3 col-12">
                        <label for="description" class="mb-2">Descrição</label>
                        <div class="position-relative">
                            <textarea 
                            rows="3"
                            class="form-control custom_focus" 
                            name="description" 
                            id="description"
                            placeholder="Insira uma descrição aqui."
                            v-model="formData.description"
                            @input="countDescription"></textarea>
                            <div class="position-absolute counter fs-8 bottom-0 end-0 px-2 text-secondary">{{ formData.description.length }} / 500</div>
                        </div>
                    </div>
                </div>

                <div class="row" v-show="stepActive == 2">
                    <h2 class="page-title">Informações de endereço</h2>

                    <div class="mb-3 col-12 col-md-6">
                        <label for="address" class="mb-2">Endereço</label>
                        <input type="text" name="address" id="address" v-model="formData.address" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-3">
                        <label for="complement" class="mb-2">Complemento</label>
                        <input type="text" name="complement" id="complement" v-model="formData.complement" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-3">
                        <label for="city" class="mb-2">Cidade</label>
                        <input type="text" name="city" id="city" v-model="formData.city" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-4">
                        <label for="zip_code" class="mb-2">Código postal</label>
                        <input type="number" name="zip_code" id="zip_code" v-model="formData.zip_code" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-4">
                        <label for="state" class="mb-2">Estado</label>
                        <input type="text" name="state" id="state" v-model="formData.state" class="form-control custom_focus text-secondary">
                    </div>
                    <div class="mb-3 col-12 col-md-4">
                        <label for="country" class="mb-2">País</label>
                        <input type="text" name="country" id="country" v-model="formData.country" class="form-control custom_focus text-secondary">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <div class="d-flex gap-3">
                        <button v-show="stepActive > 1" @click="previousStep" type="button" class="btn btn-sm btn-outline-secondary">Voltar</button>
                        <button v-show="stepActive < 3" @click="nextStep" type="button" class="btn btn-sm btn-outline-primary">Próximo</button>
                        <button v-show="stepActive === 3" type="submit" class="btn btn-sm btn-outline-primary">{{ action == 'edit' ? 'Salvar' : 'Criar usuário' }}</button>
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

.counter-danger {
    color: #dc3545;
}
</style>