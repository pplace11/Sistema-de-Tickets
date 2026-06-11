<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const profileForm = reactive({ name: '', email: '' });
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' });

const profileMsg = ref('');
const profileError = ref('');
const passwordMsg = ref('');
const passwordError = ref('');
const loadingProfile = ref(false);
const loadingPassword = ref(false);

onMounted(() => {
    if (auth.user) {
        profileForm.name = auth.user.name ?? '';
        profileForm.email = auth.user.email ?? '';
    }
});

const saveProfile = async () => {
    profileMsg.value = '';
    profileError.value = '';
    loadingProfile.value = true;

    try {
        const { data } = await api.put('/profile', profileForm);
        auth.user = data.user;
        profileMsg.value = 'Perfil guardado com sucesso.';
    } catch (error) {
        profileError.value = error?.response?.data?.message || 'Erro ao guardar perfil.';
    } finally {
        loadingProfile.value = false;
    }
};

const changePassword = async () => {
    passwordMsg.value = '';
    passwordError.value = '';
    loadingPassword.value = true;

    try {
        const { data } = await api.put('/profile/password', passwordForm);
        passwordMsg.value = data.message;
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
    } catch (error) {
        passwordError.value = error?.response?.data?.message || 'Erro ao alterar password.';
    } finally {
        loadingPassword.value = false;
    }
};

const initials = (name) => {
    if (!name) return '?';
    return name.trim().split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase();
};
</script>

<template>
    <div class="profile-wrap">
        <!-- Header -->
        <header class="user-header">
            <div class="user-left">
                <div class="avatar">{{ initials(auth.user?.name) }}</div>
                <div class="user-info">
                    <span class="user-role">CONTA PESSOAL</span>
                    <h2>{{ auth.user?.name }}</h2>
                    <span class="user-email">{{ auth.user?.email }}</span>
                </div>
            </div>
            <div class="two-fa-badge">
                <span class="badge">2FA desativada</span>
                <p>Aqui pode alterar o nome, email, password e ativar a autenticacao em dois fatores.</p>
            </div>
        </header>

        <!-- Main panels -->
        <div class="panels">
            <!-- Dados do perfil -->
            <div class="panel">
                <h3>Dados do perfil</h3>
                <div class="field">
                    <label>Nome</label>
                    <input v-model="profileForm.name" type="text" />
                </div>
                <div class="field">
                    <label>Email</label>
                    <input v-model="profileForm.email" type="email" />
                </div>
                <p v-if="profileMsg" class="msg-ok">{{ profileMsg }}</p>
                <p v-if="profileError" class="msg-err">{{ profileError }}</p>
                <div class="panel-footer">
                    <button :disabled="loadingProfile" @click="saveProfile">
                        {{ loadingProfile ? 'A guardar...' : 'Guardar perfil' }}
                    </button>
                </div>
            </div>

            <!-- Alterar password -->
            <div class="panel">
                <h3>Alterar password</h3>
                <div class="field">
                    <label>Password atual</label>
                    <input v-model="passwordForm.current_password" type="password" />
                </div>
                <div class="field">
                    <label>Nova password</label>
                    <input v-model="passwordForm.password" type="password" />
                </div>
                <div class="field">
                    <label>Confirmar password</label>
                    <input v-model="passwordForm.password_confirmation" type="password" />
                </div>
                <p v-if="passwordMsg" class="msg-ok">{{ passwordMsg }}</p>
                <p v-if="passwordError" class="msg-err">{{ passwordError }}</p>
                <div class="panel-footer">
                    <button :disabled="loadingPassword" @click="changePassword">
                        {{ loadingPassword ? 'A alterar...' : 'Alterar password' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- 2FA -->
        <div class="panel panel-full">
            <h3>Seguranca e 2FA</h3>
            <div class="twofa-inner">
                <div class="twofa-left">
                    <span class="twofa-label">ESTADO ATUAL</span>
                    <p class="twofa-state">2FA desativada</p>
                    <p class="twofa-desc">A 2FA usa codigos de 6 digitos gerados por uma app autenticadora (Google Authenticator, 1Password, Authy).</p>
                    <div class="field" style="max-width:380px">
                        <label>Password atual</label>
                        <input type="password" placeholder="Confirme para ativar a 2FA" disabled />
                    </div>
                    <button disabled class="btn-secondary">Em breve</button>
                </div>
                <div class="twofa-right">
                    <span class="twofa-label">QR CODE</span>
                    <div class="qr-placeholder">
                        <svg viewBox="0 0 100 100" width="140" height="140" xmlns="http://www.w3.org/2000/svg">
                            <rect x="5" y="5" width="35" height="35" fill="none" stroke="currentColor" stroke-width="5"/>
                            <rect x="15" y="15" width="15" height="15" fill="currentColor"/>
                            <rect x="60" y="5" width="35" height="35" fill="none" stroke="currentColor" stroke-width="5"/>
                            <rect x="70" y="15" width="15" height="15" fill="currentColor"/>
                            <rect x="5" y="60" width="35" height="35" fill="none" stroke="currentColor" stroke-width="5"/>
                            <rect x="15" y="70" width="15" height="15" fill="currentColor"/>
                            <rect x="60" y="55" width="10" height="10" fill="currentColor"/>
                            <rect x="75" y="55" width="10" height="10" fill="currentColor"/>
                            <rect x="60" y="70" width="10" height="10" fill="currentColor"/>
                            <rect x="75" y="70" width="20" height="10" fill="currentColor"/>
                            <rect x="60" y="85" width="10" height="10" fill="currentColor"/>
                        </svg>
                        <span>Disponivel em breve</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-wrap {
    display: grid;
    gap: 18px;
}

/* Header */
.user-header {
    background: linear-gradient(135deg, #1d3557, #2a9d8f);
    border-radius: 18px;
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    color: #fff;
    flex-wrap: wrap;
}

.user-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.avatar {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: rgba(255,255,255,0.18);
    font-size: 1.3rem;
    font-weight: 700;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
}

.user-role {
    display: block;
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    color: rgba(255,255,255,0.7);
    text-transform: uppercase;
    margin-bottom: 2px;
}

.user-info h2 {
    margin: 0 0 2px;
    font-size: 1.4rem;
}

.user-email {
    color: rgba(255,255,255,0.78);
    font-size: 0.95rem;
}

.two-fa-badge {
    text-align: right;
}

.badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.15);
    font-size: 0.82rem;
    font-weight: 700;
    margin-bottom: 6px;
}

.two-fa-badge p {
    margin: 0;
    color: rgba(255,255,255,0.78);
    font-size: 0.88rem;
    max-width: 34ch;
    line-height: 1.5;
}

/* Panels */
.panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.panel {
    background: rgba(22, 50, 79, 0.04);
    border: 1px solid rgba(22, 50, 79, 0.10);
    border-radius: 18px;
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.panel-full {
    grid-column: 1 / -1;
}

.panel h3 {
    margin: 0;
    font-size: 1.06rem;
    color: #16324f;
}

.field {
    display: grid;
    gap: 6px;
}

.field label {
    font-size: 0.88rem;
    color: #52616b;
    font-weight: 600;
}

input {
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #d0d7de;
    background: #fff;
    font: inherit;
    color: #16324f;
    width: 100%;
    box-sizing: border-box;
}

input:disabled {
    background: rgba(0,0,0,0.04);
    color: #8a9aa8;
}

.panel-footer {
    margin-top: auto;
    display: flex;
    justify-content: flex-end;
}

button {
    padding: 10px 18px;
    border-radius: 10px;
    border: 0;
    background: #1d3557;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
}

button:disabled {
    opacity: 0.5;
    cursor: default;
}

.btn-secondary {
    background: transparent;
    border: 1px solid rgba(22,50,79,0.25);
    color: #52616b;
    margin-top: 10px;
}

.msg-ok {
    margin: 0;
    color: #1a7f5a;
    background: rgba(26, 127, 90, 0.08);
    padding: 10px 14px;
    border-radius: 10px;
}

.msg-err {
    margin: 0;
    color: #b42318;
    background: rgba(180, 35, 24, 0.08);
    padding: 10px 14px;
    border-radius: 10px;
}

/* 2FA */
.twofa-inner {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 40px;
    align-items: start;
}

.twofa-label {
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    color: #52616b;
    text-transform: uppercase;
    display: block;
    margin-bottom: 6px;
}

.twofa-state {
    margin: 0 0 8px;
    font-size: 1.24rem;
    font-weight: 700;
    color: #16324f;
}

.twofa-desc {
    margin: 0 0 16px;
    color: #52616b;
    line-height: 1.6;
    max-width: 56ch;
}

.twofa-right {
    display: grid;
    gap: 10px;
    justify-items: center;
    min-width: 160px;
}

.qr-placeholder {
    background: #fff;
    border-radius: 14px;
    padding: 18px;
    border: 1px solid #e2e8f0;
    display: grid;
    gap: 8px;
    justify-items: center;
    color: #1d3557;
}

.qr-placeholder span {
    font-size: 0.78rem;
    color: #52616b;
}

@media (max-width: 960px) {
    .panels {
        grid-template-columns: 1fr;
    }
    .user-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .two-fa-badge {
        text-align: left;
    }
    .twofa-inner {
        grid-template-columns: 1fr;
    }
}
</style>
