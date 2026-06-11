<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const errorMessage = ref('');
const form = reactive({ email: '', password: '', remember: false });

const submit = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        await auth.login(form);
        await router.push('/dashboard');
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || 'Nao foi possivel iniciar sessao.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <section>
        <p class="badge">Acesso interno e externo</p>
        <h1>Entrar no sistema</h1>
        <p class="subtitle">Acede aos tickets, inboxes e historico centralizado da tua organizacao.</p>

        <form class="form" @submit.prevent="submit">
            <label>
                Email
                <input v-model="form.email" type="email" placeholder="nome@empresa.pt" autocomplete="email" />
            </label>

            <label>
                Password
                <input v-model="form.password" type="password" placeholder="A tua password" autocomplete="current-password" />
            </label>

            <label class="remember">
                <input v-model="form.remember" type="checkbox" />
                Manter sessao iniciada
            </label>

            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

            <button type="submit" :disabled="loading">
                {{ loading ? 'A entrar...' : 'Entrar' }}
            </button>
        </form>

        <p class="footer">
            Ainda nao tens conta?
            <RouterLink to="/register">Criar conta</RouterLink>
        </p>
    </section>
</template>

<style scoped>
h1 {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3rem);
    color: #102a43;
}

.badge {
    display: inline-flex;
    margin: 0 0 14px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(42, 157, 143, 0.12);
    color: #2a9d8f;
    font-size: 0.82rem;
    font-weight: 700;
}

.subtitle {
    margin: 12px 0 24px;
    color: #52616b;
    line-height: 1.6;
}

.form {
    display: grid;
    gap: 14px;
}

label {
    display: grid;
    gap: 8px;
    color: #1f2937;
    font-weight: 600;
}

input {
    width: 100%;
    border: 1px solid #d0d7de;
    border-radius: 14px;
    padding: 14px 16px;
    font: inherit;
    background: #fff;
}

.remember {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.remember input {
    width: auto;
}

button {
    margin-top: 4px;
    border: 0;
    border-radius: 14px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #1d3557, #2a9d8f);
    color: #fff;
    font-weight: 700;
}

.error {
    margin: 0;
    color: #b42318;
    background: rgba(180, 35, 24, 0.08);
    padding: 12px 14px;
    border-radius: 12px;
}

.footer {
    margin: 22px 0 0;
    color: #52616b;
}

.footer a {
    color: #1d3557;
    font-weight: 700;
    text-decoration: none;
}
</style>
