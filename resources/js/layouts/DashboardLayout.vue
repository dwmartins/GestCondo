<script setup>
import { ref, onMounted } from 'vue';
import { useUserStore } from '../stores/userStore';
import { website_logo } from '../helpers/constants';
import { Avatar, Button, Menu, useToast } from 'primevue';
import { toggleTheme } from '../helpers/theme';
import { is_sindico, is_support, ROLE_SINDICO, ROLE_SUPORTE } from '../helpers/auth';
import { useRouter } from 'vue-router';
import { createAlert } from '../helpers/alert';
import authService from '../services/auth.service';
import ChangeCondominiumModal from '@components/modals/condominium/ChangeCondominiumModal.vue';
import AppMenu from '../components/layout/AppMenu.vue';

const router = useRouter();
const showAlert = createAlert(useToast());

const userStore = useUserStore();
const user = userStore.user;

const menu = ref();
const menuItems = ref([]);

const toggleSidebar = ref(true);
const isMobile = ref(false);
const isDarkMode = ref(false);

const modalRef = ref();

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    setMenuItens();
});

const setMenuItens = () => {
    const items = [
        { label: 'Perfil', icon: 'pi pi-user', command: () => router.push('/perfil') },
    ];

    if ([ROLE_SUPORTE, ROLE_SINDICO].includes(user.role)) {
        items.push({
            label: 'Alterar condomínio',
            icon: 'pi pi-refresh',
            command: () => {
                modalRef.value?.open();
            }
        });
    }

    items.push({ separator: true });
    items.push({
        label: 'Sair',
        icon: 'pi pi-sign-out',
        command: async () => {
            await logout();
        }
    });

    menuItems.value = items;
}

const toggleMenu = (event) => {
    menu.value.toggle(event);
};

const changeTheme = () => {
    toggleTheme();
    isDarkMode.value = !isDarkMode.value;
}

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
    if (isMobile.value) {
        toggleSidebar.value = false;
    }
};

const toggleSidebarFn = () => {
    toggleSidebar.value = !toggleSidebar.value;
}

const logout = async () => {
    try {
        const response = await authService.logout();
        showAlert('success', 'Sucesso', response.data.message);
        router.push('/entrar');
    } catch (error) {
        showAlert('error', 'Erro', error.response.data);
    } 
}
</script>

<template>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'collapsed': !toggleSidebar }">
            <AppMenu/>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content-area" :class="{ 'expanded': !toggleSidebar }">
            <!-- Header -->
            <header class="header bg-white ">
                <div class="header-left">
                    <button class="menu-toggle" @click="toggleSidebarFn()">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <div class="header-right">
                    <Button 
                        :icon="isDarkMode ? 'pi pi-moon' : 'pi pi-sun'" 
                        variant="text" 
                        aria-label="Filter" 
                        severity="secondary"
                        size="small"
                        rounded
                        @click="changeTheme"
                    />

                    <div class="notification-icon">
                        <i class="far fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <Button @click="toggleMenu" class="p-0" severity="secondary" text>
                            <div class="d-flex align-items-center gap-2">
                                <Avatar
                                    :image="user.avatar"
                                    icon="pi pi-user"
                                    shape="circle"
                                    class="border"
                                    size="normal"
                                />
                                <span class="">{{ user.name }}</span>
                                <i class="pi pi-angle-down"></i>
                            </div>
                        </Button>
                        <Menu ref="menu" :model="menuItems" popup />
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="main-content">
                <router-view></router-view>
            </main>
        </div>
    </div>

    <ChangeCondominiumModal ref="modalRef"/>
</template>

<style>
:root {
    --sidebar-width: 240px;
    --sidebar-bg: #1f2937;
    --header-bg: #ffffff;
    --primary: #3b82f6;
    --text-light: #f9fafb;
    --text-dark: var(--text-title);
    --border-color: #e5e7eb;
    --header-height: 60px;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
    background-color: #f3f4f6;
    position: relative;
}

html.dark-mode .dashboard-container {
    background-color: var(--body-dark);
}

/* Sidebar Styles */
.sidebar {
    width: var(--sidebar-width);
    background-color: var(--sidebar-bg);
    color: var(--text-light);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    transition: transform 0.3s ease;
    will-change: transform;
}

html.dark-mode .sidebar {
    background-color: var(--cards-dark);
}

.sidebar.collapsed {
    transform: translateX(-100%);
}

.sidebar-header .website-logo img {
    width: 100%;
    max-width: 100px;
}

/* Main Content Styles */
.main-content-area {
    flex: 1;
    min-height: 100vh;
    margin-left: var(--sidebar-width);
    transition: margin-left 0.3s ease;
    position: relative;
    width: calc(100% - var(--sidebar-width));
}

.sidebar.collapsed+.main-content-area {
    margin-left: 0;
}

/* Header Styles */
.header {
    height: var(--header-height);
    position: sticky;
    top: 0;
    z-index: 90;
    display: flex;
    align-items: center;
    padding: 0 20px;
    justify-content: space-between;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.menu-toggle {
    background: none;
    border: none;
    color: #4b5563;
    font-size: 1.2rem;
    cursor: pointer;
    z-index: 100;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.notification-icon,
.user-info {
    position: relative;
    cursor: pointer;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ef4444;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Dashboard Content Styles */
.main-content {
    padding-top: 20px;
    width: 100%;
    max-width: 100%;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .main-content-area {
        margin-left: 0 !important;
    }

    .sidebar:not(.collapsed) {
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
}
</style>