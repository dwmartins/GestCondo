<script setup>
import { Button, Dialog, InputText, Select, Tag, ToggleSwitch, useToast } from 'primevue';
import { createAlert } from '../../../helpers/alert';
import { computed, reactive, ref, watch } from 'vue';
import Editor from 'primevue/editor';
import commonSpacesService from '../../../services/commonSpaces.service';
import { useCondominiumStore } from '../../../stores/condominiumStore';
import { defaultImage } from '../../../helpers/constants';
import AppSpinner from '../../AppSpinner.vue';

const showAlert = createAlert(useToast());
const condominiumSelectedId = useCondominiumStore().getCondominiumId();

const props = defineProps({
    modelValue: Boolean,
    commonSpaceData: Object,
    mode: {Type: String, default: 'create'} 
});

const emptyImage = new URL('@assets/svg/empty.svg', import.meta.url).href;

const loading = ref(false);
const uploadingPhoto = ref(false);

const lookingCommonSpaces = ref(false);

const emit = defineEmits(['update:modelValue', 'saved']);

const getDefaultFormData = () => ({
    id: null,
    name: null,
    description: null,
    condominium_id: null,
    rules: [],
    manual_approval: true,
    status: true,
    photo: null,
    photo_url: null
});

const formData = reactive(getDefaultFormData());
const previewPhoto = ref(null);
const fileToSave = ref(null);

const requiredFields = [
    {id: 'name', label: 'Nome'}
];

const fieldErrors = reactive({});

const filterStatus = [
    { code: true, name: 'Disponível', severity: 'success' },
    { code: false, name: 'Indisponível', severity: 'warn' }
];

const filterManualAprove = [
    { code: true, name: 'Sim', severity: 'info', icon: 'pi pi-times' },
    { code: false, name: 'Não', severity: 'secondary', icon: 'pi pi-check' }
]

const manual_approval_message = "Defina se as reservas feitas pelos moradores precisarão da aprovação do síndico antes de serem confirmadas";

const setItem = (item) => {
    for(const key in item) {
        if(key === 'rules') {
            formData.rules = Array.isArray(item.rules) ? item.rules : [];
            continue;
        }

        formData[key] = item[key];
    }
}

const validateFields = () => {
    Object.keys(fieldErrors).forEach(key => fieldErrors[key] = null);

    let isValid = true;
    const newErrors = {};

    for(const {id, label} of requiredFields) {
        if (!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
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

const resetForm = () => {
    Object.assign(formData, getDefaultFormData());
    previewPhoto.value = null;
    fileToSave.value = null;
}

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const addRule = () => {
    formData.rules.push('');
}

const removeRule = (index) => {
    formData.rules.splice(index, 1)
}

const save = async () => {
    if(!validateFields()) return;

    let response;
    loading.value = true;
    formData.condominium_id = condominiumSelectedId;

    try {
        if(props.mode === 'create') {
            response = await commonSpacesService.create(formData, fileToSave.value);
        } else {
            response = await commonSpacesService.update(formData, fileToSave.value);
        }

        showAlert('success', 'Sucesso', response.data.message);

        emit('saved', response.data);
        visible.value = false;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const photoSource = computed(() => {
    if(previewPhoto.value) return previewPhoto.value;
    if(formData.photo_url) return formData.photo_url;
    return defaultImage;
});

const onFileSelected = async (event) => {
    const fileInput = event.target;
    const file = fileInput.files?.[0];

    if(!file) return;

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    
    if (!allowedTypes.includes(file.type)) {
        showAlert('error', 'Imagem inválida', 'A imagem deve ser JPEG ou PNG.');
        fileInput.value = '';
        return;
    }

    const fileSizeInMB = file.size / (1024 * 1024);
    if (fileSizeInMB > 2) {
        showAlert('error', 'Imagem inválida', 'A imagem deve ter no máximo 2 MB.');
        fileInput.value = '';
        return;
    }

    uploadingPhoto.value = true;
    fileToSave.value = file;

    try {
        const readerResult = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.onerror = () => resolve(null);
            reader.readAsDataURL(file);
        });

        if (readerResult) {
            previewPhoto.value = readerResult;
        }
    } catch (error) {
        showAlert('error', 'Erro', 'Falha ao processar a imagem.');
    } finally {
        uploadingPhoto.value = false;
    }
}

watch(() => props.modelValue, (visible) => {
    if(visible) {
        loading.value = false;
        Object.keys(fieldErrors).forEach(key => fieldErrors[key] = null);
        resetForm();

        if(props.mode === 'update') {
            setItem(props.commonSpaceData);
        }
    }
}, { immediate: true })
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        :header="props.mode === 'create' ? 'Adicionar Área comum' : 'Editar Área comum'"
        :style="{ width: '45rem' }"
        :draggable="false"
    >

        <form @submit.prevent="save()" method="post" id="commonSpaceForm" class="row">
            <div class="mb-3 col-12 col-md-4 d-flex justify-content-center align-items-center">
                <div class="photo">
                    <img :src="photoSource" alt="foto">
                    <label class="btn-change-photo">
                        <Button aria-label="Alterar foto" class="change-photo" @click.prevent="$refs.fileInput.click()">
                            <template #icon>
                                <i class="fa-solid fa-pencil"></i>
                            </template>
                        </Button>
                    </label>
                    <input type="file" id="photo" name="photo" class="d-none" ref="fileInput" @change="onFileSelected($event)" accept="image/jpeg, image/jpg, image/png">

                    <div v-show="uploadingPhoto" class="spinner">
                        <AppSpinner 
                            width="lg"
                        />
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-8 row">
                <div class="mb-3 col-12">
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
                <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                    <label class="mb-2"><span class="text-danger me-1">*</span>Status</label>
                    <Select 
                        v-model="formData.status" 
                        :options="filterStatus" 
                        optionLabel="name" 
                        optionValue="code" 
                        class="w-100" 
                        :pt="{ root: { id: 'status' } }" 
                        :invalid="!!fieldErrors.status" 
                        @change="cleanFieldInvalids('status')"
                    >
                        <template #item="slotProps">
                            <Tag :value="slotProps.option.name" :severity="slotProps.option.severity" class="px-1"/>
                        </template>

                        <template #option="slotProps">
                            <Tag :value="slotProps.option.name" :severity="slotProps.option.severity" class="px-1"/>
                        </template>
                    </Select>
                </div>
                <div class="mb-3 col-12 col-md-6 d-flex flex-column">
                    <label class="mb-2"><span class="text-danger me-1">*</span>Aprovação manual? <i v-tooltip.top="manual_approval_message" class="pi pi-question-circle text-secondary cursor-pointer"></i></label>
                    <Select 
                        v-model="formData.manual_approval" 
                        :options="filterManualAprove" 
                        optionLabel="name" 
                        optionValue="code" 
                        class="w-100" 
                        :pt="{ root: { id: 'status' } }" 
                        :invalid="!!fieldErrors.manual_approval" 
                        @change="cleanFieldInvalids('manual_approval')"
                    >
                        <template #item="slotProps">
                            <Tag :value="slotProps.option.name" :severity="slotProps.option.severity" class="px-1"/>
                        </template>

                        <template #option="slotProps">
                            <Tag :value="slotProps.option.name" :severity="slotProps.option.severity" :icon="slotProps.option.icon" class="px-1"/>
                        </template>
                    </Select>
                </div>
            </div>
            <div class="mb-3 col-12">
                <label class="d-flex mb-2">Descrição</label>
                <Editor v-model="formData.description" editorStyle="height: 120px">
                    <template #toolbar>
                        <span class="ql-formats">
                            <button class="ql-bold"></button>
                            <button class="ql-italic"></button>
                            <button class="ql-underline"></button>
                            <button class="ql-strike"></button>
                        </span>

                        <span class="ql-formats">
                            <select class="ql-header">
                            <option value="1">Heading 1</option>
                            <option value="2">Heading 2</option>
                            <option selected>Normal</option>
                            </select>
                        </span>

                        <span class="ql-formats">
                            <button class="ql-list" value="ordered"></button>
                            <button class="ql-list" value="bullet"></button>
                            <button class="ql-indent" value="-1"></button>
                            <button class="ql-indent" value="+1"></button>
                        </span>

                        <span class="ql-formats">
                            <select class="ql-color"></select>
                            <select class="ql-background"></select>
                        </span>

                        <span class="ql-formats">
                            <button class="ql-clean"></button>
                        </span>
                    </template>
                </Editor>
            </div>
            <div class="mb-3 col-12">
                <label class="mb-3">Regras</label>

                <div v-if="!formData.rules.length" class="empty-image text-center">
                    <img :src="emptyImage" alt="Empty">
                    <small>Nenhuma regra adicionada.</small>
                </div>

                <div v-else class="d-flex flex-column gap-2 mt-3 mb-3">
                    <div 
                        v-for="(rule, index) in formData.rules" 
                        :key="index" 
                        class="d-flex align-items-center gap-2"
                    >
                        <InputText 
                            v-model="formData.rules[index]" 
                            placeholder="Digite a regra"
                            class="flex-1"
                            fluid=""
                        />
                        <Button 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined 
                            @click="removeRule(index)"
                        />
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <Button 
                        label="Adicionar nova regra"
                        variant="outlined"
                        size="small"
                        class="mt-2"
                        @click="addRule"
                    />
                </div>
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
                form="commonSpaceForm" 
            />
        </template>
    </Dialog>
</template>
<style scoped>
.photo {
    width: 160px;
    height: 120px;
    min-width: 160px;
    min-width: 120px;
    position: relative;
}

.photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 0.3125rem 0.625rem 0 rgba(0, 0, 0, 0.12);
}

.photo .btn-change-photo {
    position: absolute;
    bottom: -8px;
    right: -8px;
    z-index: 9;
    transition: 0.2s ease;
}

.photo .btn-change-photo button {
    box-shadow: 0 0.3125rem 0.625rem 0 rgba(0, 0, 0, 0.12);
}

.photo .change-photo {
    width: 1.7rem;
    height: 1.7rem;
}

.photo .spinner {
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

.empty-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.empty-image img {
    width: 100%;
    max-width: 100px;
    margin-bottom: 20px;
}
</style>