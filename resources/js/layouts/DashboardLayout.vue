<script setup>
import { ref, onMounted, computed } from 'vue';
import { useUserStore } from '../stores/userStore';
import { default_avatar, path_avatars, website_logo } from '../helpers/constants';
import { Avatar, Badge, Button, Divider, Drawer, Menu, useToast } from 'primevue';
import { toggleTheme } from '../helpers/theme';
import { is_sindico, is_support, ROLE_SINDICO, ROLE_SUPORTE } from '../helpers/auth';
import { useRouter } from 'vue-router';
import { createAlert } from '../helpers/alert';
import authService from '../services/auth.service';
import ChangeCondominiumModal from '@components/modals/condominium/ChangeCondominiumModal.vue';
import AppMenu from '../components/layout/AppMenu.vue';
import NotificationBell from '../components/layout/NotificationBell.vue';

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
        { label: 'Perfil', icon: 'pi pi-user', command: () => router.push('/app/morador/perfil') },
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

const avatarSource = computed(() => {
    if (user.avatar) {
        return `${path_avatars}/${user.avatar}?t=${new Date(user.updated_at).getTime()}`
    }
    
    return default_avatar;
});

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
            <div class="filter" v-if="isMobile && toggleSidebar" @click="toggleSidebarFn"></div>
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <Button 
                        @click="toggleSidebarFn()"
                        icon="pi pi-align-justify" 
                        variant="text"
                        severity="secondary" 
                        rounded 
                        aria-label="Filter" 
                        size="large"
                    />
                </div>

                <div class="header-right">
                    <Button 
                        :icon="isDarkMode ? 'pi pi-moon' : 'pi pi-sun'" 
                        variant="text" 
                        aria-label="Filter" 
                        severity="secondary"
                        size="large"
                        rounded
                        @click="changeTheme"
                    />

                    <NotificationBell />

                    <div class="d-flex align-items-center gap-2">
                        <Button @click="toggleMenu" class="p-0" severity="secondary" text>
                            <div class="d-flex align-items-center gap-2">
                                <Avatar
                                    :image="avatarSource"
                                    shape="circle"
                                    class="border"
                                    size="normal"
                                />
                                <span class="user_name">{{ user.name }}</span>
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
    background-color: #fff;
}

html.dark-mode .header {
    background-color: var(--cards-dark);
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
    gap: 10px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Dashboard Content Styles */
.main-content {
    padding-top: 20px;
    width: 100%;
    max-width: 100%;
}

.filter {
    background-color: rgba(0, 0, 0, 0.5);
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: 900;
    top: 0;
    left: 0;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .main-content-area {
        margin-left: 0 !important;
    }

    .sidebar:not(.collapsed) {
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .filter {
        display: block;
    }
}

@media (max-width: 327px) {
    .header .user_name {
        display: none;
    }
}
</style>