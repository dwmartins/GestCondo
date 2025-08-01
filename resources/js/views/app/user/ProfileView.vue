<script setup>
import { Button, Card, Checkbox, DatePicker, InputNumber, InputText, Password, ToggleSwitch, useToast } from 'primevue';
import { computed, onMounted, reactive, ref } from 'vue';
import { useUserStore } from '../../../stores/userStore';
import { default_avatar, path_avatars } from '../../../helpers/constants';
import AppSpinner from '../../../components/AppSpinner.vue';
import { ROLE_DEFINITIONS } from '../../../helpers/auth';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { createAlert } from '../../../helpers/alert';
import userService from '../../../services/user.service';
import { isDateInFuture } from '../../../helpers/dates';
import ProfileCompletion from '../../../components/profileCompletion.vue';
import authService from '../../../services/auth.service';
import { useRouter } from 'vue-router';
import BaseCard from '../../../components/BaseCard.vue';

const showAlert = createAlert(useToast());

const router = useRouter();

const userStore = useUserStore();
const user = userStore.user;

const formData = reactive({});
const previewAvatar = ref(null);
const fileToSave = ref(null);

const newPassword = ref(null);
const confirmPassword = ref(null);

const confirmDeleteAccount = ref(false);

const breadcrumbItens = [
    {
        icon: 'pi pi-home',
        to: '/app'
    },
    {
        label: 'Perfil',
    }
];
const fieldErrors = reactive({});

const requiredFields = [
    {id: 'name', label: 'Nome'},
    {id: 'last_name', label: 'Sobrenome'},
    {id: 'email', label: 'E-mail'},
    {id: 'phone', label: 'Telefone'}
];

const loadingStates = ref({
    uploadAvatar: false,
    basicInfo: false,
    changePassword: false,
    submitAvatar: false,
    deleteAccount: false,
    changeSettings: false
});

onMounted(async () => {
    setUser(user);
});

const setUser = (user) => {
    const itens_boolean = ['account_status', 'accepts_emails'];

    for (const key in user) {
        if (itens_boolean.includes(key)) {
            formData[key] = !!user[key];
            continue;
        }

        formData[key] = user[key];
    }
}

const avatarSource = computed(() => {
    if (previewAvatar.value) return previewAvatar.value;
    
    if (user.avatar) {
        return `${path_avatars}/${user.avatar}?t=${new Date(user.updated_at).getTime()}`
    }
    
    return default_avatar;
});

const onFileSelected = async (event) => {
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

    setLoading('uploadAvatar', true);
    fileToSave.value = file;

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
        setLoading('uploadAvatar', false);
    }
};

const cancelFileSelected = () => {
    previewAvatar.value = null;
    fileToSave.value = null;
    document.getElementById('new_avatar').value = "";
}

const setLoading = (item, value) => {
    loadingStates.value[item] = value;
}

const cleanFieldInvalids = (field) => {
    if(field) {
        fieldErrors[field] = null;
    }
}

const cleanAllFieldsInvalids = () => {
    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });
}

const changeAvatar = async () => {
    try {
        setLoading('submitAvatar', true);
        const response = await userService.changeAvatar(fileToSave.value, user.id);
        showAlert('success', 'Sucesso', response.data.message);

        userStore.update(response.data.user);
        previewAvatar.value = null;
        document.getElementById('new_avatar').value = "";
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        setLoading('submitAvatar', false);
    }
}

const changeBasicInfo = async () => {
    if(!validateFields()) return;
    setLoading('basicInfo', true);

    try {
        const response = await userService.update(formData);
        userStore.update(response.data.data);

        showAlert('success', 'Sucesso', response.data.message);
    } catch (error) {
        console.log(error)
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        setLoading('basicInfo', false);
    }
}

const changePassword = async () => {
    cleanAllFieldsInvalids();
    const errors = {};
    let isValid = true;

    if(!newPassword.value) {
        errors['newPassword'] = ['O campo "Nova senha" é obrigatório'];
        isValid = false;
    }

    if(!confirmPassword.value) {
        errors['confirmPassword'] = ['O campo "Confirmar senha" é obrigatório'];
        isValid = false;
    }

    if(newPassword.value !== confirmPassword.value) {
        errors['NoMatch'] = ['As senhas não coincidem']
        isValid = false;
    }

    if(newPassword.value && newPassword.value.length < 8) {
        errors['length'] = ['A senha deve ter no mínimo 8 caracteres']
        isValid = false;
    }

    if(!isValid) {
        Object.assign(fieldErrors, errors);

        const filteredErrors = Object.entries(fieldErrors).reduce((acc, [key, value]) => {
            if (value !== null) {
                acc[key] = value;
            }
            return acc;
        }, {});

        showAlert('error', 'Campos inválidos', {
            errors: filteredErrors
        }, 6000);

        return;
    }

    try {
        setLoading('changePassword', true);
        const response = await authService.changePassword({
            newPassword: newPassword.value,
            confirmPassword: confirmPassword.value
        }, user.id);

        newPassword.value = null;
        confirmPassword.value = null;
        
        showAlert('success', 'Sucesso', response.data.message);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        setLoading('changePassword', false);
    }
}

const deleteAccount = async () => {
    try {
        setLoading('deleteAccount', true);
        const response = await userService.delete(user.id);
        showAlert('success', 'Sucesso', response.data.message);

        authService.clearAuth();
        router.push('/entrar');
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data)
    } finally {
        setLoading('deleteAccount', false);
    }
}

const changeSettings = async () => {
    try {
        setLoading('changeSettings', true);
        const response = await userService.changeSettings({
            accepts_emails: formData.accepts_emails
        }, user.id);

        userStore.update(response.data.data);
        showAlert('success', 'Sucesso', response.data.message);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data)
    } finally {
        setLoading('changeSettings', false);
    }
}

const validateFields = () => {
    cleanAllFieldsInvalids();

    let isValid = true;
    const newErrors = {};

    for (const {id, label} of requiredFields) {
        if (!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
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

</script>

<template>
    <section class="container">
        <Breadcrumb :items="breadcrumbItens" />

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-9">
                <Card class="h-100 d-flex justify-content-center">
                    <template #content>
                        <div class="d-flex gap-4">
                            <div class="avatar">
                                <img :src="avatarSource" alt="Avatar">
                                <label for="new_avatar" class="btn_change_avatar">
                                    <Button aria-label="Alterar avatar" size="small" class="change_avatar" @click.prevent="$refs.fileInput.click()">
                                        <template #icon>
                                            <i class="fa-solid fa-pencil"></i>
                                        </template>
                                    </Button>
                                </label>
                                <input type="file" id="new_avatar" name="new_avatar" class="d-none" ref="fileInput" @change="onFileSelected($event)" accept="image/jpeg, image/jpg, image/png">

                                <div v-show="loadingStates.uploadAvatar" class="spinner">
                                    <AppSpinner 
                                        width="lg"
                                    />
                                </div>
                            </div>
                            <div class="d-flex flex-column justify-content-between">
                                <div class="w-100">
                                    <h2 class="fs-6 fs-md-3">{{ user.name }} {{ user.last_name }}</h2>
                                    <p>{{ ROLE_DEFINITIONS[user.role] }}</p>
                                </div>
                                <div v-if="previewAvatar" class="submit_avatar d-flex gap-2">
                                    <Button 
                                        label="Cancelar" 
                                        severity="danger" 
                                        size="small"
                                        @click="cancelFileSelected()"
                                        :disabled="loadingStates.submitAvatar"
                                    />
                                    <Button  
                                        label="Alterar foto" 
                                        :loading="loadingStates.submitAvatar" 
                                        size="small"
                                        @click="changeAvatar()"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
            <div class="col-12 col-md-3">
                <Card class="h-100">
                    <template #content>
                        <ProfileCompletion />
                    </template>
                </Card>
            </div>
        </div>

        <Card class="mb-3">
            <template #content>
                <h4 class="mb-3">Informações básicas</h4>

                <form class="row" @submit.prevent="changeBasicInfo()">
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
                        <label for="address" class="mb-2">Endereço</label>
                        <InputText type="text" v-model="formData.address" id="address"/>
                    </div>
                    <div class="mb-3 col-12 col-md-4 d-flex flex-column">
                        <label for="complement" class="mb-2">Complemento</label>
                        <InputText type="text" v-model="formData.complement" id="complement"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="city" class="mb-2">Cidade</label>
                        <InputText type="text" v-model="formData.city" id="city"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="zip_code" class="mb-2">Código postal</label>
                        <InputText type="text" v-model="formData.zip_code" id="zip_code"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="state" class="mb-2">Estado</label>
                        <InputText type="text" v-model="formData.state" id="state"/>
                    </div>
                    <div class="mb-3 col-12 col-md-3 d-flex flex-column">
                        <label for="country" class="mb-2">País</label>
                        <InputText type="text" v-model="formData.country" id="country"/>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <Button 
                            type="submit" 
                            label="Salvar Alterações" 
                            :loading="loadingStates.basicInfo" 
                            size="small"
                        />
                    </div>
                </form>
            </template>
        </Card>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <BaseCard>
                    <h4 class="mb-3">Alterar senha</h4>
                    <form class="row g-3" @submit.prevent="changePassword()">
                        <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                            <label for="newPassword" class="mb-2">Nova senha</label>
                            <Password 
                                id="senha" 
                                v-model="newPassword" 
                                :toggleMask="true" 
                                :feedback="false" 
                                inputClass="w-100" 
                                input-id="newPassword"
                                :invalid="!!fieldErrors.newPassword"
                                @input="cleanFieldInvalids('newPassword')"
                            />
                        </div>
                        <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                            <label for="confirmPassword" class="mb-2">Confirmar senha</label>
                            <Password 
                                id="senha" 
                                v-model="confirmPassword" 
                                :toggleMask="true" 
                                :feedback="false" 
                                inputClass="w-100" 
                                input-id="confirmPassword"
                                :invalid="!!fieldErrors.confirmPassword"
                                @input="cleanFieldInvalids('confirmPassword')"
                            />
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <Button 
                                type="submit" 
                                label="Alterar senha" 
                                :loading="loadingStates.changePassword" 
                                size="small"
                            />
                        </div>
                    </form>
                </BaseCard>
            </div>
            <div class="col-12 col-md-6">
                <BaseCard class="h-100">
                    <div class="h-100 d-flex flex-column justify-content-end">
                        <h4>Configurações</h4>
                        <div class="d-flex flex-column">
                            <div class="mt-3">
                                <div class="d-flex align-items-center gap-2">
                                    <ToggleSwitch v-model="formData.accepts_emails" inputId="accepts_emails" class="custom-toggle "/>
                                    <label for="accepts_emails">Quero receber e-mails com informações e novidades</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto d-flex justify-content-end">
                            <Button
                                label="Salvar alterações"
                                :loading="loadingStates.changeSettings"
                                size="small"
                                @click="changeSettings()"
                            />
                        </div>
                    </div>
                </BaseCard>
            </div>
        </div>

        <Card class="mb-3">
            <template #content>
                <h4>Excluir conta</h4>

                <div class="cad-delete-account d-flex gap-3 mt-4 mb-4">
                    <div>
                        <i class="fa-solid fa-triangle-exclamation fs-1"></i>
                    </div>
                    <div>
                        <h4>Tem certeza que deseja excluir sua conta?</h4>
                        <small>Essa ação não poderá ser desfeita.</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <Checkbox v-model="confirmDeleteAccount" inputId="confirmDeleteAccount" name="confirmDeleteAccount" binary />
                    <label for="confirmDeleteAccount">Confirmo que gostaria de excluir minha conta</label>
                </div>

                <div class="d-flex justify-content-end">
                    <Button 
                        label="Excluir conta" 
                        severity="danger" 
                        size="small"
                        :disabled="!confirmDeleteAccount"
                        :loading="loadingStates.deleteAccount"
                        @click="deleteAccount()"
                    />
                </div>
            </template>
        </Card>
    </section>
</template>

<style scoped>
.avatar {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-width: 100px;
    position: relative;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 0.3125rem 0.625rem 0 rgba(0, 0, 0, 0.12);
}

.avatar .btn_change_avatar {
    position: absolute;
    bottom: -8px;
    right: -8px;
    z-index: 9;
    box-shadow: 0 0.3125rem 0.625rem 0 rgba(0, 0, 0, 0.12);
    transition: 0.2s ease;
}

.avatar .change_avatar {
    width: 1.7rem;
    height: 1.7rem;
}

.avatar .spinner {
    width: 100%;
    height: 100%;
    border-radius: 10px;
    position: absolute;
    top: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: rgba(0, 0, 0, 0.515);
}

.submit_avatar button {
    height: 28px;
}

.cad-delete-account {
    background-color: #fae4ea;
    color: #d82c5b;
    padding: 20px;
    border-radius: 6px;
}

html.dark-mode .cad-delete-account{
    background-color: #ffffff;
}

.custom-toggle {
    transform: scale(1);
    flex-shrink: 0;
}
</style>