<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import BaseCard from '../../../components/BaseCard.vue';
import { useUserStore } from '../../../stores/userStore';
import { Button, Card, DatePicker, InputNumber, InputText, Password, Select, Steps, Textarea } from 'primevue';
import { useToast } from "primevue/usetoast";
import AppSpinner from '../../../components/AppSpinner.vue';
import { default_avatar, path_avatars } from '../../../helpers/constants';
import userService from '../../../services/user.service';
import { useRouter } from 'vue-router';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { createAlert } from '../../../helpers/alert';
import { loadingStore } from '../../../stores/loadingStore';
import { isDateInFuture } from '../../../helpers/dates';
import { ROLE_DEFINITIONS, ROLE_MORADOR, ROLE_SINDICO, ROLE_SUPORTE } from '../../../helpers/auth';

const router = useRouter();
const props = defineProps({
    action: {
        type: String,
        required: true,
        validator: (value) => ['novo', 'atualizar'].includes(value)
    },
    id: {
        type: String,
        required: false
    }
})

const showAlert = createAlert(useToast());

const userStore = useUserStore();
const auth = userStore.user;

const action = ref('novo');

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'Moradores',
        to: '/app/moradores'
    },
    {
        label: action,
    }
];

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
    role: "morador",
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

const previewAvatar = ref(null);
const fileToSave = ref(null);

const loading = ref({
    avatar: false,
    submitForm: false
});

const fieldErrors = reactive({});
const requiredFields = [
    {id: 'name', label: 'Nome'},
    {id: 'last_name', label: 'Sobrenome'},
    {id: 'email', label: 'E-mail'},
    {id: 'phone', label: 'Telefone'},
    {id: 'password', label: 'Senha'},
    {id: 'role', label: 'Tipo'}
];

onMounted(async () => {
    if (props.action === 'atualizar' && props.id) {
        action.value = 'atualizar';

        await getUserById();
    }
});

const avatarSource = computed(() => {
    if (previewAvatar.value) return previewAvatar.value;
    
    if (formData.avatar) {
        return `${path_avatars}/${formData.avatar}?t=${new Date(formData.updated_at).getTime()}`
    }
    
    return default_avatar;
});

const getUserById = async () => {
    loadingStore.show();

    try {
        const response = await userService.getById(props.id);

        Object.keys(response.data).forEach(key => {
            formData[key] = response.data[key];
        });
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loadingStore.hide();
    }
}

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

const onFileSelected = (event) => {
    const fileInput = event.target;
    const file = fileInput.files?.[0];

    if(!file) return; 

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

    if (!allowedTypes.includes(file.type)) {
        showAlert('error', 'Arquivo inválido', 'A imagem deve ser JPEG ou PNG.');
        fileInput.value = '';
        return;
    }

    const fileSizeInMB = file.size / (1024 * 1024);
    if (fileSizeInMB > 2) {
        showAlert('error', 'Arquivo muito grande', 'A imagem deve ter no máximo 2 MB.');
        fileInput.value = '';
        return;
    }

    fileToSave.value = file;
    loading.value.avatar = true;

    const reader = new FileReader()
        reader.onload = (e) => {
        previewAvatar.value = e.target.result;
    }

    reader.readAsDataURL(file)
    loading.value.avatar = false;
}

const cancelFileSelected = () => {
    previewAvatar.value = null;
    document.getElementById('new_avatar').value = "";
    fileToSave.value = null;
}

const submitForm = async () => {
    if(!validateFields()) return;

    try {
        let response;
        loading.value.submitForm = true;

        if(action.value === 'novo') {
            formData.accepts_emails = true;
            formData.account_status = true;

            response = await userService.create(formData, fileToSave.value);
        } else {
            response = await userService.update(formData, fileToSave.value);
        }
        
        showAlert('success', 'Sucesso', response.data.message);
        router.push('/app/moradores');
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value.submitForm = false;
    }
}

const validateFields = () => {
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    let isValid = true;
    const newErrors = {};

    for (const {id, label} of requiredFields) {
        if (id === 'password' && action.value === 'atualizar') continue;

        if (!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
    }

    const passwordLength = formData.password?.length || 0;

    if (
        (action.value === 'novo' && passwordLength < 8) ||
        (action.value === 'atualizar' && passwordLength > 0 && passwordLength < 8)
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

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <Card class="mb-3">
            <template #content>
                <p class="mb-3 fw-semibold">{{ action == 'atualizar' ? 'Atualizar morador' : 'Adicionar morador' }}</p>

                <div>
                    <Steps :model="steps" :activeStep="stepActive" class="mb-4" />
                </div>
            </template>
        </Card>

        <Card class="mb-3">
            <template #content>
                <form @submit.prevent="submitForm()">
                    <div v-show="stepActive == 0">
                        <p class="mb-2 fw-semibold">Informações básicas</p>

                        <div class="row g-3">
                            <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                                <label for="name" class="mb-2"><span class="text-danger me-1">*</span>Nome</label>
                                <InputText type="text" v-model="formData.name" id="name" :invalid="!!fieldErrors.name" @input="cleanFieldInvalids('name')"/>
                            </div>
                            <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                                <label for="last_name" class="mb-2"><span class="text-danger me-1">*</span>Sobrenome</label>
                                <InputText type="text" v-model="formData.last_name" id="last_name" :invalid="!!fieldErrors.last_name" @input="cleanFieldInvalids('last_name')"/>
                            </div>
                            <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                                <label for="phone" class="mb-2"><span class="text-danger me-1">*</span>Telefone</label>
                                <InputNumber v-model="formData.phone" inputId="phone" :invalid="!!fieldErrors.phone" :useGrouping="false" fluid />
                            </div>
                            <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                                <label for="date_of_birth" class="mb-2">Nascimento</label>
                                <DatePicker v-model="formData.date_of_birth" showIcon fluid iconDisplay="input" input-id="date_of_birth" date-format="dd/mm/yy"/>
                            </div>
                            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                                <label for="email" class="mb-2"><span class="text-danger me-1">*</span>E-mail</label>
                                <InputText type="email" v-model="formData.email" id="email" :invalid="!!fieldErrors.email" @input="cleanFieldInvalids('email')"/>
                            </div>
                            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                                <label for="password" class="mb-2"><span v-if="action == 'novo'" class="text-danger me-1">*</span>Senha</label>
                                <Password id="senha" v-model="formData.password" :toggleMask="true" :feedback="false" inputClass="w-100" input-id="password" :invalid="!!fieldErrors.password" @input="cleanFieldInvalids('password')"/>
                            </div>
                            <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                                <label class="mb-2"><span class="text-danger me-1">*</span>Tipo</label>
                                <Select v-model="formData.role" :options="filteredRoles" optionLabel="name" optionValue="code" class="w-100" :pt="{ root: { id: 'role' } }" :invalid="!!fieldErrors.role" @change="cleanFieldInvalids('role')"/>
                            </div>
                            <div class="mb-3 col-12">
                                <label for="description" class="mb-3">Descrição</label>
                                <div class="position-relative mt-2">
                                    <Textarea v-model="formData.description" @input="countDescription" autoResize rows="5" cols="30" maxlength="500" class="w-100" id="description"/>
                                    <span class="counter text-secondary">{{ formData.description?.length ?? 0}} / 500</span>  
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-show="stepActive == 1">
                        <h5 class="mb-2 fw-semibold">Informações de endereço</h5>
                        
                        <div class="row g-3">
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
                    </div>

                    <div v-show="stepActive == 2">
                        <h5 class="mb-2 fw-semibold">Foto</h5>

                        <div class="d-flex justify-content-center">
                            <div class="d-flex flex-column align-items-center gap-4">
                                <div class="avatar">
                                    <img :src="avatarSource" alt="Avatar" :class="{ 'bg-secondary': loading.avatar }"> 
                                    <label for="new_avatar" class="btn_change_avatar shadow"><i class="fa-solid fa-pencil"></i></label>
                                    <input type="file" id="new_avatar" name="new_avatar" class="d-none" @change="onFileSelected($event)" accept="image/jpeg, image/jpg, image/png">

                                    <AppSpinner 
                                        v-show="loading.avatar"
                                        width="lg"
                                        class="spinner"
                                    />
                                </div>

                                <div v-show="previewAvatar" class="">
                                    <div class="d-flex justify-content-center">
                                        <Button
                                            label="Cancelar" 
                                            severity="danger" 
                                            size="small" 
                                            variant="outlined"
                                            @click="cancelFileSelected"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-5 mt-sm-4">
                        <div class="d-flex gap-3">
                            <Button v-show="stepActive > 0" @click="previousStep" label="Voltar" severity="secondary" size="small"/>
                            <Button v-show="stepActive < 2" @click="nextStep" label="Próximo" size="small"/>
                            <Button type="submit" v-show="stepActive === 2" :label="action == 'atualizar' ? 'Salvar' : 'Criar usuário'" :loading="loading.submitForm" size="small"/>
                        </div>
                    </div>
                </form>
            </template>
        </Card>
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

.avatar {
    position: relative;
    width: auto;
    display: inline-block;
    width: 130px;
    height: 130px;
}

.avatar .btn_change_avatar {
    background-color: var(--primary-color);
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    bottom: -8px;
    right: -8px;
    border-radius: 8px;
    cursor: pointer;
}

.avatar .btn_change_avatar:hover {
    background-color: #9e9d9d;
    transition: 0.3s ease;
}

.avatar img {
    width: 130px;
    height: 130px;
    object-fit: cover;
    border-radius: 8px;
    border: 5px solid #eeeeee;
}

.avatar .spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
</style>