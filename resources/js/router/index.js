import { createRouter, createWebHistory } from 'vue-router'
import DashboardLayout from '@layouts/DashboardLayout.vue';
import DashboardView from '@views/DashboardView.vue';
import authService from '../services/auth.service';
import { loadingStore } from '../stores/loadingStore';
import UsersView from '../views/app/user/UsersView.vue';
import { checkPermission, is_sindico, is_support } from '../helpers/auth';

const routes = [
    {
        path: '/',
        redirect: '/app/dashboard'
    },
    {
        path: '/app',
        redirect: '/app/dashboard',
        component: DashboardLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: 'dashboard',
                name: 'dashboard',
                component: DashboardView
            },
            {
                path: 'moradores',
                name: 'users',
                component: UsersView,
                meta: { 
                    permission: { module: 'moradores', action: 'visualizar' }
                }
            },
            {
                path: 'morador/perfil',
                name: 'profile',
                component: () => import('../views/app/user/ProfileView.vue'),
            },
            {
                path: 'condominios',
                name: 'condominiums',
                component: () => import('../views/app/condominium/condominiumsView.vue'),
                meta: { requiresSupport: true }
            },
            {
                path: 'funcionarios',
                name: 'employees',
                component: () => import('../views/app/employees/EmployeesView.vue'),
                meta: {
                    permission: { module: 'funcionarios', action: 'visualizar' }
                }
            },
            {
                path: 'portaria/entregas',
                name: 'deliveries',
                component: () => import('../views/app/gatehouse/DeliveriesView.vue'),
                meta: {
                    permission: { module: 'entregas', action: 'visualizar' }
                }
            },
            {
                path: 'areas-comuns',
                name: 'common-spaces',
                component: () => import('../views/app/common-spaces/commonSpacesView.vue')
            }
        ]
    },
    {
        path: '/entrar',
        name: 'login',
        component: () => import('../views/auth/LoginView.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('../views/NotFoundView.vue'),
        meta: { title: "Página não encontrada" }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

router.beforeEach(async (to, from, next) => {
    document.title = to.meta.title || "GestCondo";

    if(to.path === '/entrar' && authService.isLocallyAuthenticated()) {
        next({ name: 'dashboard' })
    }

    if (to.path.startsWith('/app')) {
        const fromOutsideApp = !from.path.startsWith('/app') || !from.matched.length;

        if (fromOutsideApp) {
            loadingStore.show();
            const result = await authService.isFullyAuthenticated();
            loadingStore.hide();

            if (!result.is_valid) {
                return next({ name: 'login' });
            }

            return validateRoute(to, next);
        } else {
            return authService.isLocallyAuthenticated()
                ? validateRoute(to, next)
                : next({ name: 'login' });
        }
    }

    return next();
}); 

function validateRoute(to, next) {

    if(is_support()) {
        return next();
    }

    if(to.meta.requiresSupport && !is_support()) {
        return next({name: 'dashboard'});
    }

    if(to.meta.permission) {
        const { module, action } = to.meta.permission;

        if(!checkPermission(module, action)) {
            return next({name: 'dashboard'});
        }
    }

    return next();
}

export default router;