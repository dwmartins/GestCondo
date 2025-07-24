<script setup>
import { Button, Column, DataTable, Dialog, IconField, InputIcon, InputText, Tag, useToast } from 'primevue';
import { ref, defineExpose } from 'vue';
import condominiumService from '../../../services/condominium.service';
import { createAlert } from '../../../helpers/alert';
import AppLoadingData from '../../AppLoadingData.vue';
import AppEmpty from '../../AppEmpty.vue';
import { setSelectedCondominiumId } from '../../../helpers/condominium';

const showAlert = createAlert(useToast());
const visible = ref(false);
const condominiums = ref([]);
const loading = ref(true);

const filters = ref({
    global: { value: '', matchMode: 'contains' }
});

const searchFields = ref([
    'name',
    'phone',
    'city',
    'cnpj',
    'company_type',
    'postal_code',
    'street',
    'number',
    'neighborhood',
    'state',
    'email',
]);

const open = () => {
    condominiums.value = [];
    visible.value = true;
    loadCondominiums();
};

const close = () => {
    visible.value = false;
};

const loadCondominiums = async () => {
    try {
        loading.value = true;
        const response = await condominiumService.getAll();
        condominiums.value = response.data.data;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data)
    } finally {
        loading.value = false;
    }
};

const selectCondominium = (condominium) => {
    setSelectedCondominiumId(condominium.id)
    console.log('Selecionado:', condominium.id);
    close();
};

defineExpose({ open });
</script>

<template>
    <Dialog v-model:visible="visible" header="Alterar Condomínio" modal style="width: 40rem">
        <Transition name="fade">
            <AppLoadingData v-if="loading">
                Buscando condomínios...
            </AppLoadingData>
        </Transition>

        <Transition name="fade">
            <DataTable v-if="condominiums.length" :value="condominiums" v-model:filters="filters" filterDisplay="menu" :globalFilterFields="searchFields" paginator :rows="7" scrollable v-show="!loading">
                <template #header>
                    <div class="row">
                        <div class="col d-flex justify-content-end">
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText v-model="filters.global.value" placeholder="Buscar..." size="small"/>
                            </IconField>
                        </div>
                    </div>
                </template>
                
                <Column field="name" header="Nome" sortable></Column>
                <Column field="city" header="Cidade" sortable></Column>
                <Column field="is_active" header="Status" sortable style="width: 10px">
                    <template #body="{ data }">
                        <div class="text-center">
                            <Tag v-if="data.is_active" severity="success" value="Ativo" style="font-size: 12px; padding: 2px 6px;"></Tag>
                            <Tag v-if="!data.is_active" severity="danger" value="Inativo" style="font-size: 12px; padding: 2px 6px;"></Tag>
                        </div>
                    </template>
                </Column>
                <Column field="" header="Ações" header-class="d-flex justify-content-center">
                    <template #body="{ data }">
                        <div class="d-flex justify-content-center gap-2">
                            <Button 
                                icon="pi pi-check" 
                                label="Selecionar" 
                                variant="outlined"
                                size="small" 
                                rounded 
                                @click="selectCondominium(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </Transition>

        <AppEmpty v-if="!loading && !condominiums.length"/>
    </Dialog>
</template>
<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>