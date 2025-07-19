<script setup>
import { ref, onMounted } from 'vue';
import { userStore } from '../stores/userStore';
import { website_logo } from '../helpers/constants';
import { Button } from 'primevue';
import { toggleTheme } from '../helpers/theme';

const user = userStore.user;

const toggleSidebar = ref(true);
const isMobile = ref(false);
const isDarkMode = ref(false);

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
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});
</script>

<template>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'collapsed': !toggleSidebar }">
            <div class="sidebar-header">
                <div class="website-logo">
                    <img :src="website_logo" alt="Logo">
                </div>
            </div>

            <div class="sidebar-menu">
                <ul>
                    <li class="active">
                        <router-link to="/app/dashboard" active-class="active">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/app/chamados" active-class="active">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Chamados</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/app/reservas" active-class="active">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>Reservas</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/app/moradores" active-class="active">
                            <i class="fa-solid fa-users"></i>
                            <span>Moradores</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/app/condominios" active-class="active">
                            <i class="fa-solid fa-city"></i>
                            <span>Condomínios</span>
                        </router-link>
                    </li>
                </ul>
            </div>
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
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span>{{ user.name }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="main-content">
                <router-view></router-view>
            </main>
        </div>
    </div>
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
    background-color: #09090b;
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
    z-index: 100;
    transition: transform 0.3s ease;
}

.sidebar.collapsed {
    transform: translateX(-100%);
}

.sidebar-header {
    padding: 25px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: var(--header-height);
}

.sidebar-header .website-logo {
    width: 100%;
    display: flex;
    justify-content: center;
}

.sidebar-header .website-logo img {
    width: 100%;
    max-width: 100px;
}

.sidebar-menu {
    padding: 10px 0;
    overflow-y: auto;
    height: calc(100vh - var(--header-height));
}

.sidebar-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li a {
    padding: 12px 24px;
    cursor: pointer;
    transition: color 0.2s;
    display: flex;
    align-items: center;
    gap: 15px;
    color: #9097a7;
    text-decoration: none;
}

.sidebar-menu li a:hover {
    color: #cfcfcf;
}

.sidebar-menu li a.active {
    color: #fff;
}

.sidebar-menu li i {
    width: 20px;
    text-align: center;
}

/* Main Content Styles */
.main-content-area {
    flex: 1;
    min-height: 100vh;
    margin-left: var(--sidebar-width);
    transition: margin-left 0.3s ease;
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
    padding: 20px;
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