<template>
    <div class="shell">
        <aside class="sidebar">
            <!-- Topo: workspace -->
            <div class="workspace">
                <div class="ws-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    </svg>
                </div>
                <div>
                    <span class="ws-label">SISTEMA</span>
                    <span class="ws-name">TicketFlow</span>
                </div>
            </div>

            <!-- Navegacao principal -->
            <nav>
                <RouterLink to="/dashboard">Dashboard</RouterLink>
                <RouterLink to="/tickets">Tickets</RouterLink>
                <RouterLink to="/entities">Entidades</RouterLink>
                <RouterLink to="/contacts">Contactos</RouterLink>
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

const logout = async () => {
    await auth.logout();
    window.location.assign('/');
};
</script>

<style scoped>
/* Layout */
.shell {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 230px 1fr;
    background: #f0f2f5;
}

/* Sidebar */
.sidebar {
    background: #14192e;
    color: #fff;
    padding: 20px 14px;
    display: flex;
    flex-direction: column;
    gap: 0;
    min-height: 100vh;
    position: sticky;
    top: 0;
}

/* Workspace topo */
.workspace {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 10px 18px;
    margin-bottom: 6px;
}

.ws-icon {
    width: 38px;
    height: 38px;
    background: rgba(255,255,255,0.12);
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    color: rgba(255,255,255,0.9);
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
    font-size: 0.98rem;
    font-weight: 700;
    color: #fff;
    line-height: 1;
}

/* Nav links */
nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
}

nav a {
    display: block;
    color: rgba(255, 255, 255, 0.60);
    text-decoration: none;
    padding: 9px 12px;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: background .15s, color .15s;
}

nav a:hover {
    color: rgba(255,255,255,0.92);
    background: rgba(255,255,255,0.06);
}

nav a.router-link-active {
    background: rgba(255, 255, 255, 0.11);
    color: #fff;
    font-weight: 600;
}

/* Account footer */
.account {
    margin-top: auto;
    padding-top: 18px;
    border-top: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 16px 10px 6px;
}

.account-info {
    display: grid;
    text-decoration: none;
    border-radius: 8px;
    padding: 4px 6px;
    margin: -4px -6px;
    transition: background .15s;
}

.account-info:hover {
    background: rgba(255,255,255,0.07);
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
    font-size: 0.92rem;
    font-weight: 600;
    color: rgba(255,255,255,0.88);
}

.logout-btn {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.45);
    padding: 6px;
    border-radius: 8px;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: background .15s, color .15s;
    flex: 0 0 auto;
}

.logout-btn:hover {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.85);
}

/* Content */
.content {
    padding: 28px;
}

/* Responsive */
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
    }

    .account {
        padding: 12px 10px 0;
        margin-top: 14px;
    }
}
</style>
