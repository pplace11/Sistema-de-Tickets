<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const loading = ref(true);
const auth = useAuthStore();
const isOperator = computed(() => auth.user?.role === 'operator');
const stats = ref({
    tickets: 0,
    entities: 0,
    contacts: 0,
    inboxes: 0,
});
const recentTickets = ref([]);
const quickLinks = computed(() => {
    if (!isOperator.value) {
        return [
            {
                title: 'Abrir ticket',
                description: 'Regista um novo pedido e acompanha o estado da solicitacao.',
                to: '/tickets',
            },
            {
                title: 'Consultar tickets',
                description: 'Acede aos teus pedidos recentes e responde quando necessario.',
                to: '/tickets',
            },
        ];
    }

    return [
        {
            title: 'Abrir ticket',
            description: 'Regista um novo pedido e acompanha o fluxo completo.',
            to: '/tickets',
        },
        {
            title: 'Gerir entidades',
            description: 'Consulta clientes, parceiros e contexto associado.',
            to: '/entities',
        },
        {
            title: 'Atualizar contactos',
            description: 'Mantem a rede de contactos ligada a cada entidade.',
            to: '/contacts',
        },
    ];
});

const formatNumber = (value) => new Intl.NumberFormat('pt-PT').format(value ?? 0);

const formatDateTime = (value) => {
    if (!value) {
        return 'Sem data';
    }

    return new Intl.DateTimeFormat('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const statusClass = (statusName) => {
    const name = (statusName || '').toLowerCase();

    if (name.includes('fechado')) {
        return 'is-closed';
    }

    if (name.includes('tratamento')) {
        return 'is-progress';
    }

    return 'is-open';
};

const loadDashboard = async () => {
    loading.value = true;

    try {
        const requests = [
            api.get('/tickets', { params: { page: 1 } }),
            api.get('/lookups'),
        ];

        if (isOperator.value) {
            requests.push(api.get('/entities', { params: { page: 1 } }));
            requests.push(api.get('/contacts', { params: { page: 1 } }));
        }

        const [ticketsResponse, lookupsResponse, entitiesResponse, contactsResponse] = await Promise.all(requests);

        stats.value = {
            tickets: ticketsResponse.data.total ?? 0,
            entities: entitiesResponse?.data?.total ?? 0,
            contacts: contactsResponse?.data?.total ?? 0,
            inboxes: lookupsResponse.data.inboxes?.length ?? 0,
        };

        recentTickets.value = ticketsResponse.data.data?.slice(0, 5) ?? [];
    } finally {
        loading.value = false;
    }
};

onMounted(loadDashboard);
</script>

<template>
    <section class="dashboard-shell">
        <header class="hero-panel">
            <div class="hero-copy">
                <p class="eyebrow">Painel de controlo</p>
                <h2>{{ isOperator ? 'Dashboard operacional' : 'Dashboard do cliente' }}</h2>
                <p class="subtitle">
                    {{ isOperator
                        ? 'Vista executiva para acompanhar tickets, entidades e capacidade de atendimento numa unica superficie.'
                        : 'Vista simplificada para abrir pedidos e acompanhar tickets da tua entidade.' }}
                </p>

                <div class="hero-tags">
                    <span>Seguimento por inbox</span>
                    <span>{{ isOperator ? 'Historico consolidado' : 'Acompanhamento de pedidos' }}</span>
                    <span>{{ isOperator ? 'Operacao interna e externa' : 'Comunicacao centralizada' }}</span>
                </div>
            </div>

            <div class="hero-actions">
                <div class="pulse-card">
                    <small>Estado do workspace</small>
                    <strong>{{ loading ? 'A sincronizar dados' : 'Dados atualizados' }}</strong>
                    <span>Resumo rapido para decisao e acompanhamento da operacao.</span>
                </div>

                <button class="refresh" :disabled="loading" @click="loadDashboard">
                    {{ loading ? 'A atualizar...' : 'Atualizar dashboard' }}
                </button>
            </div>
        </header>

        <div class="stats-grid">
            <article class="stat-card stat-tickets">
                <div class="stat-top">
                    <span class="icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8 7h8" />
                            <path d="M8 11h5" />
                            <path d="M7 3h10a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2V5a2 2 0 0 1 2-2Z" />
                        </svg>
                    </span>
                    <span class="label">Tickets</span>
                </div>
                <strong>{{ formatNumber(stats.tickets) }}</strong>
                <small>Total geral registado</small>
            </article>

            <article v-if="isOperator" class="stat-card stat-entities">
                <div class="stat-top">
                    <span class="icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 21h18" />
                            <path d="M5 21V7l7-4 7 4v14" />
                            <path d="M9 10h6" />
                            <path d="M9 14h6" />
                        </svg>
                    </span>
                    <span class="label">Entidades</span>
                </div>
                <strong>{{ formatNumber(stats.entities) }}</strong>
                <small>Clientes e parceiros</small>
            </article>

            <article v-if="isOperator" class="stat-card stat-contacts">
                <div class="stat-top">
                    <span class="icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 21a6 6 0 0 0-12 0" />
                            <circle cx="12" cy="8" r="4" />
                        </svg>
                    </span>
                    <span class="label">Contactos</span>
                </div>
                <strong>{{ formatNumber(stats.contacts) }}</strong>
                <small>Pontos de contacto ativos</small>
            </article>

            <article class="stat-card stat-inboxes">
                <div class="stat-top">
                    <span class="icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 12h4l2 3h4l2-3h4" />
                            <path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                        </svg>
                    </span>
                    <span class="label">Inboxes</span>
                </div>
                <strong>{{ formatNumber(stats.inboxes) }}</strong>
                <small>Canais de atendimento</small>
            </article>
        </div>

        <div class="content-grid">
            <section class="quick-panel">
                <div class="section-head">
                    <div>
                        <p class="section-eyebrow">Atalhos de trabalho</p>
                        <h3>{{ isOperator ? 'Entradas rapidas para a operacao' : 'Acoes rapidas do cliente' }}</h3>
                    </div>
                    <span class="section-note">{{ isOperator ? 'Acoes frequentes' : 'Area do cliente' }}</span>
                </div>

                <div class="quick-links">
                    <RouterLink v-for="link in quickLinks" :key="link.to" :to="link.to" class="quick-link">
                        <div>
                            <strong>{{ link.title }}</strong>
                            <p>{{ link.description }}</p>
                        </div>
                        <span class="arrow">→</span>
                    </RouterLink>
                </div>
            </section>

            <section class="recent-panel">
                <div class="section-head recent-head">
                    <div>
                        <p class="section-eyebrow">Ultima atividade</p>
                        <h3>Tickets recentes</h3>
                    </div>
                    <span class="section-note">Ultimos 5 registos</span>
                </div>

                <p v-if="loading" class="loading">A carregar dados do dashboard...</p>
                <ul v-else class="list">
                    <li v-for="ticket in recentTickets" :key="ticket.id">
                        <div class="ticket-main">
                            <div class="ticket-topline">
                                <RouterLink class="ticket-number" :to="`/tickets/${ticket.id}`">{{ ticket.ticket_number }}</RouterLink>
                                <span class="ticket-date">{{ formatDateTime(ticket.created_at) }}</span>
                            </div>
                            <p class="ticket-subject">{{ ticket.subject }}</p>
                            <small class="ticket-meta">
                                <span v-if="isOperator" class="meta-pill">{{ ticket.entity?.name || 'Sem entidade' }}</span>
                                <span class="meta-pill">{{ ticket.inbox?.name || 'Sem inbox' }}</span>
                            </small>
                        </div>
                        <span class="status" :class="statusClass(ticket.status?.name)">
                            {{ ticket.status?.name || '-' }}
                        </span>
                    </li>
                    <li v-if="!recentTickets.length" class="empty">Sem tickets para mostrar.</li>
                </ul>
            </section>
        </div>
    </section>
</template>

<style scoped>
.dashboard-shell {
    display: grid;
    gap: 24px;
}

.hero-panel {
    background:
        radial-gradient(circle at top right, rgba(111, 223, 213, 0.16), transparent 30%),
        linear-gradient(145deg, #0f2742 0%, #173b5d 45%, #1f8f84 100%);
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 28px 60px rgba(15, 33, 52, 0.18);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    color: #fff;
}

.hero-copy {
    display: grid;
    gap: 10px;
    max-width: 54ch;
}

.eyebrow {
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    font-size: 0.76rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.72);
}

.hero-panel h2 {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3rem);
    line-height: 0.96;
    letter-spacing: -0.05em;
}

.subtitle {
    margin: 0;
    color: rgba(255, 255, 255, 0.82);
    line-height: 1.65;
}

.hero-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.hero-tags span {
    display: inline-flex;
    align-items: center;
    padding: 9px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.09);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.84);
    font-size: 0.84rem;
}

.hero-actions {
    min-width: 280px;
    display: grid;
    gap: 14px;
    justify-items: stretch;
}

.pulse-card {
    padding: 18px;
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.12);
    display: grid;
    gap: 6px;
}

.pulse-card small,
.pulse-card strong,
.pulse-card span {
    margin: 0;
}

.pulse-card small {
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: rgba(255, 255, 255, 0.68);
    font-size: 0.72rem;
    font-weight: 700;
}

.pulse-card strong {
    font-size: 1.05rem;
}

.pulse-card span {
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.92rem;
    line-height: 1.55;
}

.refresh {
    border: 0;
    border-radius: 16px;
    padding: 14px 18px;
    color: #16324f;
    background: #ffffff;
    font-weight: 700;
    font-size: 0.96rem;
    box-shadow: 0 18px 36px rgba(7, 16, 28, 0.16);
    transition: transform .16s ease, opacity .16s ease, box-shadow .16s ease;
}

.refresh:hover {
    transform: translateY(-1px);
    box-shadow: 0 22px 40px rgba(7, 16, 28, 0.2);
}

.refresh:disabled {
    opacity: 0.75;
    cursor: not-allowed;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.stat-card {
    border: 1px solid #dce6ef;
    border-radius: 22px;
    padding: 18px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 250, 253, 0.98));
    display: grid;
    gap: 8px;
    box-shadow: 0 18px 34px rgba(15, 23, 42, 0.05);
}

.stat-top {
    display: flex;
    align-items: center;
    gap: 10px;
}

.icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
}

.icon-wrap svg {
    width: 20px;
    height: 20px;
}

.stat-tickets .icon-wrap {
    color: #173b5d;
    background: rgba(29, 53, 87, 0.09);
}

.stat-entities .icon-wrap {
    color: #0f766e;
    background: rgba(42, 157, 143, 0.11);
}

.stat-contacts .icon-wrap {
    color: #8a4b14;
    background: rgba(251, 191, 36, 0.14);
}

.stat-inboxes .icon-wrap {
    color: #7c2d12;
    background: rgba(249, 115, 22, 0.12);
}

.label {
    margin: 0;
    font-size: 0.82rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 800;
}

.stat-card strong {
    display: block;
    font-size: 2.2rem;
    line-height: 0.95;
    color: #1d3557;
}

.stat-card small {
    color: #64748b;
    font-size: 0.82rem;
}

.content-grid {
    display: grid;
    grid-template-columns: minmax(280px, 0.9fr) minmax(0, 1.4fr);
    gap: 18px;
}

.quick-panel,
.recent-panel {
    border: 1px solid #dde5ee;
    border-radius: 24px;
    padding: 22px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    box-shadow: 0 20px 45px rgba(16, 24, 40, 0.06);
}

.section-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 16px;
}

.section-eyebrow {
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 700;
}

.section-head h3 {
    margin: 6px 0 0;
    color: #102a43;
    font-size: 1.25rem;
    letter-spacing: -0.03em;
}

.section-note {
    color: #64748b;
    font-size: 0.82rem;
    white-space: nowrap;
}

.quick-links {
    display: grid;
    gap: 12px;
}

.quick-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid #e1e9f0;
    text-decoration: none;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    color: inherit;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}

.quick-link:hover {
    transform: translateY(-2px);
    border-color: #cddae6;
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
}

.quick-link strong {
    display: block;
    color: #17324d;
    font-size: 1rem;
}

.quick-link p {
    margin: 6px 0 0;
    color: #5b6b79;
    line-height: 1.55;
    font-size: 0.92rem;
}

.arrow {
    color: #1f8f84;
    font-size: 1.2rem;
    font-weight: 700;
}

.loading {
    margin: 0;
    color: #64748b;
}

.list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 8px;
}

.list li {
    border: 1px solid #dde6ee;
    border-radius: 18px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    background: #fff;
}

.ticket-main {
    min-width: 0;
}

.ticket-topline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.ticket-number {
    display: inline-block;
    font-size: 1rem;
    color: #0f172a;
    text-decoration: none;
    font-weight: 700;
}

.ticket-number:hover {
    text-decoration: underline;
}

.ticket-date {
    color: #7b8a99;
    font-size: 0.8rem;
    white-space: nowrap;
}

.ticket-subject {
    margin: 6px 0 0;
    color: #334155;
    line-height: 1.45;
}

.ticket-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
    color: #64748b;
    font-size: 0.8rem;
}

.meta-pill {
    display: inline-flex;
    align-items: center;
    padding: 7px 10px;
    border-radius: 999px;
    background: #f4f7fa;
    border: 1px solid #e2e8f0;
}

.status {
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    white-space: nowrap;
}

.status.is-open {
    background: #ecfdf3;
    color: #166534;
}

.status.is-progress {
    background: #fff7ed;
    color: #9a3412;
}

.status.is-closed {
    background: #e2e8f0;
    color: #334155;
}

.empty {
    justify-content: center;
    color: #64748b;
}

@media (max-width: 1000px) {
    .hero-panel,
    .section-head,
    .ticket-topline {
        display: grid;
    }

    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 700px) {
    .hero-panel,
    .quick-panel,
    .recent-panel {
        padding: 18px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .list li {
        flex-direction: column;
        align-items: flex-start;
    }

    .status {
        align-self: flex-start;
    }
}
</style>
