<script setup>
import { reactive, ref } from 'vue';
import authService from '../../services/auth.service';
import { useRouter } from 'vue-router';
import { showAlert } from '../../helpers/alert';

const router = useRouter();
const logoUrl = new URL('@assets/images/default_logo.webp', import.meta.url).href;
const showPassword = ref(false);
const formData = reactive({
    email: "",
    password: "",
    rememberMe: false,
});

const submit = async () => {
    try {
        const response = await authService.login(formData.email, formData.password, formData.rememberMe);
        const redirect = router.currentRoute.value.query.redirect || '/app/dashboard';
        showAlert('success', '', response.message);
        router.push(redirect);
    } catch (error) {
        showAlert('error', '', error.response.data);
    }
}

</script>

<template>
    <section class="container vh-100 item_center">
        <form @submit.prevent="submit()" action="" class="w-100 rounded-4 p-4 pb-5">
            <div class="logo mb-2">
                <img :src="logoUrl" alt="Logo">
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input 
                    type="email" 
                    name="email" 
                    v-model="formData.email" 
                    class="form-control custom_focus text-secondary" 
                    id="email"
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
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

            <div class="mb-3 d-flex justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" v-model="formData.rememberMe" type="checkbox" id="rememberMe">
                    <label class="form-check-label cursor_pointer text-secondary" for="rememberMe">
                        Lembra de mim
                    </label>
                </div>
                <a href="" class="outline_none fs-7 fw-medium">Esqueci minha senha</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </section>
</template>

<style scoped>
form {
    max-width: 450px;
    box-shadow: 0 0 2rem 0 rgba(136, 152, 170, .15);
}

form .logo {
    width: 200px;
    height: 100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
}

form .logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

form .icon_show_password {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    cursor: pointer;
}
</style>
