import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@views/auth/LoginView.vue';
import DashboardLayout from '@layouts/DashboardLayout.vue';
import DashboardView from '@views/DashboardView.vue';
import authService from '../services/auth.service';
import NotFoundView from '../views/NotFoundView.vue';

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
                name: 'Dashboard',
                component: DashboardView
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
    document.title = to.meta.title || "Gest condo";
 
    if(to.path.startsWith('/app')) {
        if(from.path === '/' || !from.matched.length) {
            const isValid = await authService.isFullyAuthenticated();
            isValid ? next() : next({ name: 'login' });
        } else {
            authService.isLocallyAuthenticated() ? next() : next({ name: 'login' });
        }
    } else {
        next();
    }
});              

export default router;