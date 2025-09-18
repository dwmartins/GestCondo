<script setup>
import { Button, DatePicker, Dialog, InputMask, InputText, Password, Select, Textarea, ToggleSwitch, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import { computed, reactive, ref, watch } from 'vue';
import { isDateInFuture } from '../../../helpers/dates';
import userService from '../../../services/user.service';
import { ROLE_DEFINITIONS } from '../../../helpers/auth';

const showAlert = createAlert(useToast());

const props = defineProps({
    modelValue: Boolean,
    residentData: Object,
    mode: { type: String, default: 'create'} 
});

const loading = ref(false);
const uploadingAvatar = ref(false);

const fieldErrors = reactive({});

const previewAvatar = ref(null);
const avatarFile = ref(null);

const requiredFields = [
    {id: 'name', label: 'Nome'},
    {id: 'last_name', label: 'Sobrenome'},
    {id: 'email', label: 'E-mail'},
    {id: 'phone', label: 'Telefone'},
    {id: 'password', label: 'Senha'},
    {id: 'role', label: 'Tipo'}
];

const accountStatus = [
    {code: true, name: 'Ativa'},
    {code: false, name: 'Inativa'}
];

const filteredRoles = computed(() => {
    const allRoles = Object.entries(ROLE_DEFINITIONS).map(([code, name]) => ({
        code,
        name
    }));
    
    return allRoles;
})

const defaultFormData = reactive({
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

const formData = reactive({ ...defaultFormData });

const emit = defineEmits(['update:modelValue', 'saved']);

const setUser = (user) => {
    const itens_boolean = ['account_status', 'accepts_emails'];

    for (const key in user) {
        if (itens_boolean.includes(key)) {
            if(props.mode == 'create') {
                formData[key] = true;
                console.log(formData[key])
            } else {
                formData[key] = !!user[key];
            }

            continue;
        }

        formData[key] = user[key];
    }
}

const resetForm = () => {
    Object.assign(formData, defaultFormData);
    previewAvatar.value = null;
    avatarFile.value = null;
}

const validateFields = () => {
    Object.keys(fieldErrors).forEach(key => fieldErrors[key] = null);

    let isValid = true;
    const newErrors = {};

    for (const {id, label} of requiredFields) {
        if (id === 'password' && props.mode === 'update') continue;

        if (!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
    }

    const passwordLength = formData.password?.length || 0;

    if (
        (props.mode === 'create' && passwordLength < 6) ||
        (props.mode === 'update' && passwordLength > 0 && passwordLength < 6)
    ) {
        isValid = false;
        newErrors['password'] = ['A senha deve conter no mínimo 6 caracteres.'];
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

const onFileSelected = async (event) => {
    const fileInput = event.target;
    const file = fileInput.files?.[0];

    if(!file) return; 

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const fileSizeInMB = file.size / (1024 * 1024); // 2MB

    if (!allowedTypes.includes(file.type)) {
        showAlert('error', 'Arquivo inválido', 'A imagem deve ser JPEG ou PNG.');
        fileInput.value = '';
        return;
    }

    if (fileSizeInMB > 2) {
        showAlert('error', 'Arquivo muito grande', 'A imagem deve ter no máximo 2 MB.');
        fileInput.value = '';
        return;
    }

    uploadingAvatar.value = true;
    avatarFile.value = file;
    
    try {
        const readerResult = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.onerror = () => resolve(null);
            reader.readAsDataURL(file);
        });

        if (readerResult) {
            previewAvatar.value = readerResult;
        }
    } catch (error) {
        showAlert('error', 'Erro', 'Falha ao processar a imagem.');
    } finally {
        uploadingAvatar.value = false;
    }
}

const countDescription = () => {
    if(formData.description.length > 500) {
        formData.description = formData.description.substring(0, 500);
    }
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
    let response;
    loading.value = true;

    try {
        if(props.mode === 'create') {
            response = await userService.create(formData, avatarFile.value);
        } else {
            response = await userService.update(formData, avatarFile.value);
        }

        showAlert('success', 'Sucesso', response.data.message);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = true;
    }
}

watch(() => props.modelValue, (visible) => {
    if(visible) {
        Object.keys(fieldErrors).forEach(key => fieldErrors[key] = null);

        if(props.mode === 'create') {
            resetForm();
        } else {
            setUser(props.residentData);
        }
    }
}, { immediate: true });

</script>
<template>
    <Dialog
        v-model:visible="visible"
        modal
        :header="props.mode === 'create' ? 'Adicionar morador' : 'Editar morador'" 
        :style="{ width: '55rem' }"
        :draggable="false"
    >
    
    <form @submit.prevent="save()" id="residentForm" method="post" class="row">
        <div class="d-flex justify-content-between mb-3">
            <p class="fw-semibold">Informações básicas</p>
            <div class="d-flex gap-2 align-items-center">
                <ToggleSwitch 
                    v-model="formData.account_status"
                    itemid="account_status"
                    input-id="account_status"
                />
                <template v-if="formData.account_status">
                    <span class="text-success">Ativo</span>
                </template>
                <template v-else>
                    <span class="text-danger">Inativo</span>
                </template>
            </div>
        </div>

        <div class="mb-3 col-12 col-md-3">
            <label for="name"><span class="text-danger me-1">*</span>Nome</label>
            <InputText 
                type="text" 
                v-model="formData.name" 
                id="name" 
                class="mt-2" 
                fluid 
                :invalid="!!fieldErrors.name" 
                @input="cleanFieldInvalids('name')"
            />
        </div>
        <div class="mb-3 col-12 col-md-3">
            <label for="las_name"><span class="text-danger me-1">*</span>Sobrenome</label>
            <InputText 
                type="text" 
                v-model="formData.last_name" 
                id="las_name" 
                class="mt-2" 
                fluid 
                :invalid="!!fieldErrors.last_name" 
                @input="cleanFieldInvalids('last_name')"
            />
        </div>
        <div class="mb-3 col-12 col-md-3 d-flex flex-column">
            <label for="phone" class="mb-2"><span class="text-danger me-1">*</span>Telefone</label>
            <InputMask 
                v-model="formData.phone" 
                mask="(99) 99999-9999" 
                placeholder="(99) 99999-9999" 
                id="phone"
            />
        </div>
        <div class="mb-3 col-12 col-md-3 d-flex flex-column">
            <label for="date_of_birth" class="mb-2">Nascimento</label>
            <DatePicker 
                v-model="formData.date_of_birth" 
                showIcon 
                fluid 
                iconDisplay="input" 
                input-id="date_of_birth" 
                date-format="dd/mm/yy"
            />
        </div>
        <div class="mb-3 col-12 col-md-4 d-flex flex-column">
            <label for="email" class="mb-2"><span class="text-danger me-1">*</span>E-mail</label>
            <InputText 
                type="email" 
                v-model="formData.email" 
                id="email" 
                :invalid="!!fieldErrors.email" 
                @input="cleanFieldInvalids('email')"
            />
        </div>
        <div class="mb-3 col-12 col-md-4 d-flex flex-column">
            <label for="password" class="mb-2"><span v-if="mode == 'novo'" class="text-danger me-1">*</span>Senha</label>
            <Password
                id="senha" 
                v-model="formData.password" 
                :toggleMask="true" 
                :feedback="true" 
                inputClass="w-100" 
                input-id="password" 
                :invalid="!!fieldErrors.password" 
                @input="cleanFieldInvalids('password')"
            >
                <template #footer>
                    <ul class="mt-2 text-sm text-gray-500">
                        <li>• Pelo menos 6 caracteres</li>
                    </ul>
                </template>
            </Password> 
        </div>
        <div class="mb-3 col-12 col-md-4 d-flex flex-column">
            <label class="mb-2"><span class="text-danger me-1">*</span>Tipo</label>
            <Select 
                v-model="formData.role" 
                :options="filteredRoles" 
                optionLabel="name" 
                optionValue="code" 
                class="w-100" 
                :pt="{ root: { id: 'role' } }" 
                :invalid="!!fieldErrors.role" 
                @change="cleanFieldInvalids('role')"
            />
        </div>
        <div class="mb-3 col-12">
            <label for="description" class="mb-3">Descrição</label>
            <div class="position-relative mt-2">
                <Textarea 
                    v-model="formData.description" 
                    @input="countDescription" 
                    autoResize 
                    rows="5" 
                    cols="30" 
                    maxlength="500" 
                    class="w-100" 
                    id="description" 
                    placeholder="Responsável por..."
                />
                <span class="counter text-secondary">{{ formData.description?.length ?? 0}} / 500</span>  
            </div>
        </div>

        <p class="mb-3 fw-semibold">Informações de endereço</p>

        <div class="mb-3 col-12 col-md-6 d-flex flex-column">
            <label for="address" class="mb-2">Endereço</label>
            <InputText 
                type="text" 
                v-model="formData.address" 
                id="address"
            />
        </div>
        <div class="mb-3 col-12 col-md-3 d-flex flex-column">
            <label for="complement" class="mb-2">Complemento</label>
            <InputText 
                type="text" 
                v-model="formData.complement" 
                id="complement"
            />
        </div>
        <div class="mb-3 col-12 col-md-3 d-flex flex-column">
            <label for="city" class="mb-2">Cidade</label>
            <InputText 
                type="text" 
                v-model="formData.city" 
                id="city"
            />
        </div>
        <div class="mb-3 col-12 col-md-4 d-flex flex-column">
            <label for="zip_code" class="mb-2">Código postal</label>
            <InputText 
                type="text" 
                v-model="formData.zip_code" 
                id="zip_code"
            />
        </div>
        <div class="mb-3 col-12 col-md-4 d-flex flex-column">
            <label for="state" class="mb-2">Estado</label>
            <InputText 
                type="text" 
                v-model="formData.state" 
                id="state"
            />
        </div>
        <div class="mb-3 col-12 col-md-4 d-flex flex-column">
            <label for="country" class="mb-2">País</label>
            <InputText 
                type="text" 
                v-model="formData.country" 
                id="country"
            />
        </div>
    </form>
        <template #footer>
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
                :loading="loading"
                type="submit"
                form="residentForm" 
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