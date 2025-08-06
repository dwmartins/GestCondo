<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useUserStore } from '../../stores/userStore';
import { website_logo } from '../../helpers/constants';
import { checkPermission, ROLE_SINDICO, ROLE_SUPORTE } from '../../helpers/auth';

const route = useRoute();
const openSubmenus = ref([]);
const userStore = useUserStore();
const auth = userStore.user;

const menuItems = [
    {
        label: 'Dashboard',
        icon: 'fas fa-tachometer-alt',
        to: '/app/dashboard',
    },
    {
        label: 'Chamados',
        icon: 'fa-solid fa-list-check',
        to: '/app/chamados',
    },
    {
        label: 'Reservas',
        icon: 'fa-regular fa-calendar-check',
        to: '/app/reservas',
    },
    {
        label: 'Usuários',
        icon: 'fa-solid fa-users',
        items: [
            {
                label: 'Moradores',
                icon: 'fa-solid fa-users',
                to: '/app/moradores',
                permission: { module: 'moradores', action: 'visualizar' },
            },
            {
                label: 'Funcionários',
                icon: 'fa-solid fa-briefcase',
                to: '/app/funcionarios',
                permission: { module: 'funcionarios', action: 'visualizar' },
            }
        ]
    },
    {
        label: 'Condomínios',
        icon: 'fa-solid fa-city',
        to: '/app/condominios',
        isSupport: false
    }
];

const filteredMenu = computed(() => {
    return menuItems
        .map(item => {
            if (item.items) {
                const visibleSubitems = item.items.filter(subItem => {
                    const mergedSubItem = {
                        ...item,
                        ...subItem,
                        permission: subItem.permission ?? item.permission,
                        isSupport: subItem.isSupport ?? item.isSupport,
                    };
                    return isVisible(mergedSubItem);
                });

                if (isVisible(item) && visibleSubitems.length > 0) {
                    return { ...item, items: visibleSubitems };
                }

                return null;
            }

            return isVisible(item) ? item : null;
        })
        .filter(Boolean);
});


const filteredSubitems = (item) => item.items || [];

function isVisible(item) {
    if (item.isSupport) {
        return auth.role === ROLE_SUPORTE;
    }

    if (auth.role === ROLE_SUPORTE || auth.role === ROLE_SINDICO) {
        return true;
    }

    if (item.permission) {
        return checkPermission(item.permission.module, item.permission.action);
    }

    return true;
}


const hasItems = (item) => {
    return item.items && item.items.length > 0;
};

const isActive = (item) => {
    if (item.to) return route.path === item.to;
    if (item.items) {
        return item.items.some(subItem => route.path === subItem.to);
    }
    return false;
};

const isSubmenuOpen = (item) => {
    return openSubmenus.value.includes(item.label);
};

const toggleSubmenu = (item) => {
    if (isSubmenuOpen(item)) {
        openSubmenus.value = openSubmenus.value.filter(label => label !== item.label);
    } else {
        openSubmenus.value.push(item.label);
    }
};

if (route.path) {
    const activeItemWithSubmenu = menuItems.find(item =>
        item.items && item.items.some(subItem => subItem.to === route.path)
    );

    if (activeItemWithSubmenu) {
        openSubmenus.value.push(activeItemWithSubmenu.label);
    }
}
</script>

<template>
    <div class="sidebar-header">
        <div class="website-logo">
            <img :src="website_logo" alt="Logo">
        </div>
    </div>
    <div class="sidebar-menu">
        <ul>
            <li v-for="item in filteredMenu" :key="item.label" :class="{
                'active': isActive(item),
                'has-submenu': hasItems(item),
                'submenu-open': isSubmenuOpen(item)
            }">

                <!-- Item sem submenu -->
                <router-link v-if="!hasItems(item)" :to="item.to" active-class="active" tabindex="0">
                    <i :class="item.icon"></i>
                    <span>{{ item.label }}</span>
                </router-link>

                <!-- Item com submenu -->
                <div v-else tabindex="0" @click="toggleSubmenu(item)" @keydown.enter="toggleSubmenu(item)"
                    @keydown.space.prevent="toggleSubmenu(item)">
                    <i :class="item.icon"></i>
                    <span>{{ item.label }}</span>
                    <i class="submenu-icon"
                        :class="isSubmenuOpen(item) ? 'fa fa-chevron-down' : 'fa fa-chevron-right'"></i>
                </div>

                <ul v-if="hasItems(item) && isSubmenuOpen(item)" class="submenu">
                    <li v-for="subItem in filteredSubitems(item)" :key="subItem.label"
                        :class="{ 'active': $route.path === subItem.to }">
                        <router-link :to="subItem.to" active-class="active" tabindex="0">
                            <i :class="subItem.icon"></i>
                            <span>{{ subItem.label }}</span>
                        </router-link>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.sidebar-header {
    padding: 25px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
}

.sidebar-header .website-logo {
    width: 100%;
    display: flex;
    justify-content: center;
}

.sidebar-menu {
    width: 100%;
    color: #fff;
    height: 100vh;
}


.sidebar-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    position: relative;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #dddddd;
    font-size: 0.9em;
    text-decoration: none;
    transition: all 0.3s;
}

.sidebar-menu li a:hover,  .sidebar-menu li div:hover{
    background: #34495e21;
}

.sidebar-menu li.active>a, .sidebar-menu li.active>div {
    color: var(--primary-color);
}

.sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.submenu-icon {
    margin-left: auto;
    font-size: 0.7em;
}

.submenu {
    overflow: hidden;
}

.submenu li a {
    padding-left: 40px;
}

.has-submenu > div {
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #dddddd;
    text-decoration: none;
    font-size: 0.9em;
}
</style>