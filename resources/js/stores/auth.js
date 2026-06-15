import { defineStore } from 'pinia';
import axios from 'axios';

const authHttp = axios.create({
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loaded: false,
    }),

    getters: {
        isAuthenticated: (state) => Boolean(state.user),
    },

    actions: {
        async fetchUser() {
            try {
                const { data } = await authHttp.get('/me');
                this.user = data.user;
            } catch {
                this.user = null;
            } finally {
                this.loaded = true;
            }
        },

        async login(payload) {
            await authHttp.get('/sanctum/csrf-cookie');
            const { data } = await authHttp.post('/login', payload);
            this.user = data.user;
            this.loaded = true;
        },

        async register(payload) {
            await authHttp.get('/sanctum/csrf-cookie');
            const { data } = await authHttp.post('/register', payload);
            this.user = data.user;
            this.loaded = true;
        },

        async logout() {
            try {
                await authHttp.get('/sanctum/csrf-cookie');
                await authHttp.post('/logout');
            } catch {
                // Force local sign-out even when backend session is already invalid.
            } finally {
                this.user = null;
                this.loaded = true;
            }
        },
    },
});
