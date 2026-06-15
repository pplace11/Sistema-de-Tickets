<template>
    <div class="shell">
        <aside class="sidebar">
            <!-- Topo: workspace -->
            <div class="workspace">
                <div class="ws-icon">
                    <img :src="logoSrc" alt="TicketFlow" />
                </div>
                <div>
                    <span class="ws-label">SISTEMA</span>
                    <span class="ws-name">Sistema de Tickets</span>
                </div>
            </div>

            <!-- Navegacao principal -->
            <nav>
                <RouterLink to="/dashboard">Dashboard</RouterLink>
                <RouterLink to="/tickets">Tickets</RouterLink>
                <RouterLink v-if="auth.user?.role === 'operator'" to="/entities">Entidades</RouterLink>
                <RouterLink v-if="auth.user?.role === 'operator'" to="/contacts">Contactos</RouterLink>
                <RouterLink v-if="auth.user?.role === 'operator'" to="/users">Utilizadores</RouterLink>
            </nav>

            <!-- Rodape: conta -->
            <div class="account">
                <RouterLink class="account-info" to="/profile">
                    <span class="account-label">CONTA</span>
                    <span class="account-name">{{ auth.user?.name ?? 'Utilizador' }}</span>
                </RouterLink>
                <button class="logout-btn" title="Terminar sessao" @click="logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </div>
        </aside>

        <main class="content">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const logoSrc = '/image/ticketflow-logo.svg';

const logout = async () => {
    try {
        await auth.logout();
    } finally {
        window.location.assign('/login');
    }
};
</script>

<style scoped>
.shell {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 274px 1fr;
    background:
        radial-gradient(circle at top right, rgba(42, 157, 143, 0.08), transparent 20%),
        linear-gradient(180deg, #eef3f6 0%, #f6f8fa 100%);
}

.sidebar {
    background:
        linear-gradient(180deg, rgba(10, 23, 40, 0.98) 0%, rgba(17, 44, 67, 0.98) 52%, rgba(24, 88, 94, 0.96) 100%);
    color: #fff;
    padding: 22px 16px;
    display: flex;
    flex-direction: column;
    gap: 0;
    min-height: 100vh;
    position: sticky;
    top: 0;
    box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.06);
}

.workspace {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px 20px;
    margin-bottom: 10px;
}

.ws-icon {
    width: 58px;
    height: 42px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.04));
    border-radius: 14px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    box-shadow: 0 14px 24px rgba(0, 0, 0, 0.14);
}

.ws-icon img {
    width: 44px;
    height: auto;
    display: block;
}

.ws-label {
    display: block;
    font-size: 0.65rem;
    letter-spacing: 0.18em;
    color: rgba(255,255,255,0.45);
    text-transform: uppercase;
    line-height: 1;
    margin-bottom: 3px;
}

.ws-name {
    display: block;
    font-size: 0.94rem;
    font-weight: 700;
    color: #fff;
    line-height: 1;
}

nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

nav a {
    display: flex;
    align-items: center;
    color: rgba(255, 255, 255, 0.68);
    text-decoration: none;
    padding: 12px 14px;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    transition: background .15s, color .15s, transform .15s;
}

nav a:hover {
    color: rgba(255,255,255,0.92);
    background: rgba(255,255,255,0.08);
    transform: translateX(2px);
}

nav a.router-link-active {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.08));
    color: #fff;
    font-weight: 700;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
}

.account {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.10);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 18px 12px 8px;
}

.account-info {
    display: grid;
    text-decoration: none;
    border-radius: 12px;
    padding: 8px 10px;
    margin: -8px -10px;
    transition: background .15s;
}

.account-info:hover {
    background: rgba(255,255,255,0.08);
}

.account-label {
    display: block;
    font-size: 0.65rem;
    letter-spacing: 0.18em;
    color: rgba(255,255,255,0.40);
    text-transform: uppercase;
    margin-bottom: 3px;
}

.account-name {
    display: block;
    font-size: 0.96rem;
    font-weight: 700;
    color: rgba(255,255,255,0.88);
}

.logout-btn {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.66);
    padding: 8px;
    border-radius: 12px;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: background .15s, color .15s;
    flex: 0 0 auto;
}

.logout-btn:hover {
    background: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.95);
}

.content {
    padding: 28px;
}

@media (max-width: 900px) {
    .shell {
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .sidebar {
        min-height: auto;
        position: static;
        padding: 14px;
    }

    nav {
        flex-direction: row;
        overflow-x: auto;
        flex: none;
        gap: 4px;
    }

    nav a {
        white-space: nowrap;
        transform: none;
    }

    .account {
        padding: 12px 10px 0;
        margin-top: 14px;
    }

    .content {
        padding: 18px;
    }
}
</style>
