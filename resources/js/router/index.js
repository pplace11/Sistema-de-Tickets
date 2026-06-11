import { createRouter, createWebHistory } from 'vue-router';
import TicketsView from '../views/TicketsView.vue';
import EntitiesView from '../views/EntitiesView.vue';
import ContactsView from '../views/ContactsView.vue';
import DashboardView from '../views/DashboardView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import WelcomeView from '../views/WelcomeView.vue';
import ProfileView from '../views/ProfileView.vue';
import { useAuthStore } from '../stores/auth';

const routes = [
    { path: '/', component: WelcomeView, meta: { landingLayout: true } },
    { path: '/login', component: LoginView, meta: { authLayout: true } },
    { path: '/register', component: RegisterView, meta: { authLayout: true } },
    { path: '/dashboard', component: DashboardView, meta: { requiresAuth: true } },
    { path: '/tickets', component: TicketsView, meta: { requiresAuth: true } },
    { path: '/entities', component: EntitiesView, meta: { requiresAuth: true } },
    { path: '/contacts', component: ContactsView, meta: { requiresAuth: true } },
    { path: '/profile', component: ProfileView, meta: { requiresAuth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    // Only resolve session state when navigating to protected pages.
    if (to.meta.requiresAuth && !auth.loaded) {
        await auth.fetchUser();
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return '/login';
    }

    return true;
});

export default router;
