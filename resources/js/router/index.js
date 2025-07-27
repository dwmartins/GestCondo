import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@views/auth/LoginView.vue';
import DashboardLayout from '@layouts/DashboardLayout.vue';
import DashboardView from '@views/DashboardView.vue';
import authService from '../services/auth.service';
import NotFoundView from '../views/NotFoundView.vue';
import { loadingStore } from '../stores/loadingStore';
import UsersView from '../views/app/user/UsersView.vue';
import UserView from '../views/app/user/UserView.vue';
import { is_sindico, is_support } from '../helpers/auth';

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
                meta: { requiresSindico: true }
            },
            {
                path: 'moradores/morador/:action',
                name: 'user',
                component: UserView,
                props: (route) => ({
                    action: route.params.action,
                    id: route.params.action === 'atualizar' ? route.query.id : null
                })
            },
            {
                path: 'condominios',
                name: 'condominiums',
                component: () => import('../views/app/condominium/condominiumsView.vue'),
                meta: { requiresSupport: true }
            }
        ]
    },
    {
        path: '/entrar',
        name: 'login',
        component: LoginView,
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: NotFoundView,
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

            if(to.meta.requiresSupport && !is_support()) {
                return next({ name: 'dashboard' });
            }

            if(to.meta.requiresSindico && !is_sindico()) {
                return next({ name: 'dashboard' });
            }

            return next();
        } else {
            return authService.isLocallyAuthenticated()
                ? next()
                : next({ name: 'login' });
        }
    }

    return next();
});              

export default router;