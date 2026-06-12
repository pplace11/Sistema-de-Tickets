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
        <div class="header-block">
            <p class="badge">Acesso interno e externo</p>
            <div class="title-row">
                <div>
                    <h1>Entrar no sistema</h1>
                    <p class="subtitle">Acede aos tickets, inboxes e historico centralizado da tua organizacao.</p>
                </div>
                <div class="status-chip">Ligacao segura</div>
            </div>
        </div>

        <div class="quick-points">
            <span>Tickets por area</span>
            <span>Historico consolidado</span>
            <span>Seguimento por entidade</span>
        </div>

        <form class="form" @submit.prevent="submit">
            <label>
                <span>Email</span>
                <input v-model="form.email" type="email" placeholder="nome@empresa.pt" autocomplete="email" />
            </label>

            <label>
                <span>Password</span>
                <input v-model="form.password" type="password" placeholder="A tua password" autocomplete="current-password" />
            </label>

            <div class="options-row">
                <label class="remember">
                    <input v-model="form.remember" type="checkbox" />
                    <span>Manter sessao iniciada</span>
                </label>
                <span class="helper-text">Acesso reservado a utilizadores autorizados.</span>
            </div>

            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

            <button type="submit" :disabled="loading">
                {{ loading ? 'A entrar...' : 'Entrar' }}
            </button>
        </form>

        <div class="security-note">
            <strong>Ambiente protegido</strong>
            <p>As operacoes de tickets, respostas e notificacoes ficam associadas ao teu perfil e ao contexto da tua organizacao.</p>
        </div>

        <p class="footer">
            Ainda nao tens conta?
            <RouterLink to="/register">Criar conta</RouterLink>
        </p>
    </section>
</template>

<style scoped>
.header-block {
    display: grid;
    gap: 16px;
}

h1 {
    margin: 0;
    font-size: clamp(2.35rem, 4vw, 3.4rem);
    line-height: 0.96;
    letter-spacing: -0.05em;
    color: #102a43;
}

.title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
}

.badge {
    display: inline-flex;
    margin: 0;
    padding: 9px 13px;
    border-radius: 999px;
    background: rgba(42, 157, 143, 0.10);
    color: #19776d;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 12px;
    border-radius: 999px;
    background: #edf7f5;
    border: 1px solid #d9ece7;
    color: #1b6f69;
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
}

.subtitle {
    margin: 14px 0 0;
    color: #52616b;
    line-height: 1.6;
    max-width: 44ch;
}

.quick-points {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 22px 0 24px;
}

.quick-points span {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: #f4f7fa;
    border: 1px solid #e2e8f0;
    color: #405365;
    font-size: 0.84rem;
}

.form {
    display: grid;
    gap: 16px;
}

label {
    display: grid;
    gap: 8px;
    color: #1f2937;
    font-weight: 600;
}

input {
    width: 100%;
    border: 1px solid #d3dde6;
    border-radius: 16px;
    padding: 15px 16px;
    font: inherit;
    background: #fbfdff;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

input:focus {
    outline: none;
    border-color: #2a9d8f;
    box-shadow: 0 0 0 4px rgba(42, 157, 143, 0.12);
    background: #fff;
}

.options-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.remember {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    color: #23384d;
}

.remember input {
    width: auto;
    accent-color: #1f8f84;
}

.helper-text {
    color: #708090;
    font-size: 0.84rem;
}

button {
    margin-top: 6px;
    border: 0;
    border-radius: 16px;
    padding: 15px 16px;
    background: linear-gradient(135deg, #17324d, #208f85);
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 16px 28px rgba(29, 53, 87, 0.18);
    transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
}

button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 20px 34px rgba(29, 53, 87, 0.22);
}

button:disabled {
    opacity: 0.72;
    cursor: wait;
}

.error {
    margin: 0;
    color: #b42318;
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.12);
    padding: 12px 14px;
    border-radius: 12px;
}

.security-note {
    margin-top: 20px;
    padding: 16px 18px;
    border-radius: 18px;
    background: linear-gradient(180deg, #f8fbfc 0%, #f3f7fa 100%);
    border: 1px solid #e4ebf1;
}

.security-note strong,
.security-note p {
    margin: 0;
}

.security-note strong {
    display: block;
    color: #16324f;
    font-size: 0.96rem;
}

.security-note p {
    margin-top: 6px;
    color: #5b6b79;
    line-height: 1.55;
    font-size: 0.92rem;
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

@media (max-width: 640px) {
    .title-row {
        display: grid;
    }

    .status-chip {
        width: fit-content;
    }

    .options-row {
        align-items: flex-start;
    }
}
</style>
