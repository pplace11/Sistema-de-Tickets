<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const errorMessage = ref('');
const form = reactive({ name: '', email: '', password: '', password_confirmation: '' });

const submit = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        await auth.register(form);
        await router.push('/dashboard');
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || 'Nao foi possivel criar a conta.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <section>
        <p class="badge">Criar acesso</p>
        <h1>Registar conta</h1>
        <p class="subtitle">Cria um utilizador cliente para submeter e acompanhar tickets com a tua entidade.</p>

        <form class="form" @submit.prevent="submit">
            <label>
                Nome
                <input v-model="form.name" type="text" placeholder="O teu nome" autocomplete="name" />
            </label>

            <label>
                Email
                <input v-model="form.email" type="email" placeholder="nome@empresa.pt" autocomplete="email" />
            </label>

            <label>
                Password
                <input v-model="form.password" type="password" placeholder="Minimo 8 caracteres" autocomplete="new-password" />
            </label>

            <label>
                Confirmar password
                <input v-model="form.password_confirmation" type="password" placeholder="Repete a password" autocomplete="new-password" />
            </label>

            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

            <button type="submit" :disabled="loading">
                {{ loading ? 'A criar...' : 'Criar conta' }}
            </button>
        </form>

        <p class="footer">
            Ja tens conta?
            <RouterLink to="/login">Entrar</RouterLink>
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
    background: rgba(29, 53, 87, 0.12);
    color: #1d3557;
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

button {
    margin-top: 4px;
    border: 0;
    border-radius: 14px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #2a9d8f, #1d3557);
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
