import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@views/auth/LoginView.vue';
import DashboardLayout from '@layouts/DashboardLayout.vue';
import DashboardView from '@views/DashboardView.vue';

const routes = [
    {
        path: '/',
        component: DashboardLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'Dashboard',
                component: DashboardView
            }
        ]
    },
    {
        path: '/entrar',
        name: 'login',
        component: LoginView,
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

router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('token');

    if(to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'login' });
    } else {
        next();
    }
});              

export default router;