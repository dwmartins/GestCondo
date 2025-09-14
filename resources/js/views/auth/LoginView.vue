<script setup>
import { computed, reactive, ref } from 'vue';
import authService from '../../services/auth.service';
import { useRouter } from 'vue-router';
import { createAlert } from '../../helpers/alert';
import { useToast } from 'primevue/usetoast'
import { Button, Card, Checkbox, FloatLabel, InputText, Password, usePrimeVue } from 'primevue';

const showAlert = createAlert(useToast());

const router = useRouter();
const loading = ref(false);

const formData = reactive({
    email: "",
    password: "",
    rememberMe: false,
});

const errors = reactive({
    email: false,
    password: false
});

const submit = async () => {
    try {
        if(!validFields()) return;
        loading.value = true;
        const response = await authService.login(formData.email, formData.password, formData.rememberMe);
        const redirect = router.currentRoute.value.query.redirect || '/app/dashboard';
        router.push(redirect);
    } catch (error) {
        console.log(error);
        showAlert('error', '', error.response?.data);
    } finally {
        loading.value = false;
    }
}

const validFields = () => {
    let isValid = true;
    errors.email = false;
    errors.password = false;

    if (!formData.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.email = true;
        isValid = false;
    }

    if (!formData.password) {
        errors.password = true;
        isValid = false;
    }

    if (!isValid) {
        showAlert('error', 'Campos inválidos', {
            errors: {
                email: errors.email ? ['E-mail inválido ou faltando'] : [],
                password: errors.password ? ['A senha é obrigatória'] : []
            }
        });
    }

    return isValid;
}

const cleanFieldInvalids = (field) => {
    if (field === 'email') {
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            errors[field] = false;
        }
        return;
    }

    if(field) {
        errors[field] = false;
    }
}

</script>

<template>
    <section>
        <div class="cover">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 800" class="fixed left-0 top-0 min-h-screen min-w-[100vw]" preserveAspectRatio="none"><rect fill="var(--p-primary-500)" width="1600" height="800"></rect><path fill="var(--p-primary-400)" d="M478.4 581c3.2 0.8 6.4 1.7 9.5 2.5c196.2 52.5 388.7 133.5 593.5 176.6c174.2 36.6 349.5 29.2 518.6-10.2V0H0v574.9c52.3-17.6 106.5-27.7 161.1-30.9C268.4 537.4 375.7 554.2
            478.4 581z"></path><path fill="var(--p-primary-300)" d="M181.8 259.4c98.2 6 191.9 35.2 281.3 72.1c2.8 1.1 5.5 2.3 8.3 3.4c171 71.6 342.7 158.5 531.3 207.7c198.8 51.8 403.4 40.8 597.3-14.8V0H0v283.2C59 263.6 120.6 255.7 181.8 259.4z"></path><path fill="var(--p-primary-200)" d="M454.9 86.3C600.7 177 751.6 269.3 924.1 325c208.6 67.4 431.3 60.8 637.9-5.3c12.8-4.1 25.4-8.4 38.1-12.9V0H288.1c56 21.3 108.7 50.6 159.7 82C450.2 83.4 452.5 84.9 454.9 86.3z"></path><path fill="var(--p-primary-100)" d="M1397.5 154.8c47.2-10.6 93.6-25.3 138.6-43.8c21.7-8.9 43-18.8 63.9-29.5V0H643.4c62.9 41.7 129.7 78.2 202.1 107.4C1020.4 178.1 1214.2 196.1 1397.5 154.8z"></path></svg>
        </div>
        <div class="container vh-100 w-100 item-center">
            <form @submit.prevent="submit()" class="z-1">
                <Card class="p-3">
                    <template #content>
                        <h2 class="mb-1">Conecte-se</h2>
                        <p class="mb-4">Por favor insira seus dados</p>

                        <div class="input-icon mb-3">
                            <i class="pi pi-envelope"></i>
                            <InputText 
                                v-model="formData.email" 
                                placeholder="E-mail" 
                                id="E-mail"
                                :invalid="errors.email"
                                @input="cleanFieldInvalids('email')"
                            />
                        </div>

                        <div class="input-icon mb-4">
                            <i class="pi pi-lock"></i>
                            <Password 
                                v-model="formData.password" 
                                placeholder="Senha" 
                                :toggleMask="true" 
                                :feedback="false" 
                                inputClass="w-100"
                                class="w-100"
                                input-id="password"
                                :inputStyle="{ paddingLeft: '2rem' }"
                                :invalid="errors.password"
                                @input="cleanFieldInvalids('password')"
                            />
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <Checkbox 
                                    v-model="formData.rememberMe" 
                                    inputId="rememberMe" 
                                    name="rememberMe"
                                    size="small" 
                                    binary 
                                />
                                <label for="rememberMe" class="cursor-pointer"> Lembre de mim </label>
                            </div>

                            <RouterLink to="" class="fs-6">
                                Redefinir senha
                            </RouterLink>
                        </div>

                        <Button
                            label="conecte-se"
                            class="w-100"
                            type="submit"
                            :loading="loading"
                        />
                    </template>
                </Card>
            </form>
        </div>
    </section>
</template>

<style scoped>
.cover {
    position: absolute;
    top: 0;
    height: 100%;
    width: 100%;
}

.cover svg {
    position: fixed;
    width: 100vw;
    height: 100vh;
    left: 0;
    top: 0;
}

form {
    width: 100%;
    max-width: 450px;
}

.input-icon {
    position: relative;
}

.input-icon i {
    position: absolute;
    top: 50%;
    left: 0.6rem; 
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 0.875rem;
    z-index: 1;
}

.input-icon .p-inputtext {
    padding-left: 2rem !important;
    width: 100%;
}

a {
    text-decoration: none;
    color: var(--primary);
}

a:hover {
    color: var(--p-primary-600);
}
</style>
