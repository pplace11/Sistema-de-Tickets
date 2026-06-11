<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';

const loading = ref(true);
const stats = ref({
    tickets: 0,
    entities: 0,
    contacts: 0,
    inboxes: 0,
});
const recentTickets = ref([]);

const loadDashboard = async () => {
    loading.value = true;

    try {
        const [ticketsResponse, entitiesResponse, contactsResponse, lookupsResponse] = await Promise.all([
            api.get('/tickets', { params: { page: 1 } }),
            api.get('/entities', { params: { page: 1 } }),
            api.get('/contacts', { params: { page: 1 } }),
            api.get('/lookups'),
        ]);

        stats.value = {
            tickets: ticketsResponse.data.total ?? 0,
            entities: entitiesResponse.data.total ?? 0,
            contacts: contactsResponse.data.total ?? 0,
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
    <section class="panel">
        <header class="header">
            <div>
                <h2>Dashboard</h2>
                <p>Visao geral do sistema de tickets.</p>
            </div>
            <button @click="loadDashboard">Atualizar</button>
        </header>

        <div class="cards">
            <article class="card">
                <h3>Tickets</h3>
                <strong>{{ stats.tickets }}</strong>
            </article>
            <article class="card">
                <h3>Entidades</h3>
                <strong>{{ stats.entities }}</strong>
            </article>
            <article class="card">
                <h3>Contactos</h3>
                <strong>{{ stats.contacts }}</strong>
            </article>
            <article class="card">
                <h3>Inboxes</h3>
                <strong>{{ stats.inboxes }}</strong>
            </article>
        </div>

        <div class="recent">
            <h3>Tickets recentes</h3>
            <p v-if="loading">A carregar...</p>
            <ul v-else class="list">
                <li v-for="ticket in recentTickets" :key="ticket.id">
                    <div>
                        <strong>{{ ticket.ticket_number }}</strong>
                        <p>{{ ticket.subject }}</p>
                    </div>
                    <span>{{ ticket.status?.name || '-' }}</span>
                </li>
                <li v-if="!recentTickets.length">Sem tickets para mostrar.</li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.panel {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.header h2 {
    margin: 0;
}

.header p {
    margin: 6px 0 0;
    color: #52616b;
}

button {
    border: 0;
    border-radius: 10px;
    padding: 10px 14px;
    color: #fff;
    background: #1d3557;
}

.cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
}

.card h3 {
    margin: 0;
    font-size: 0.95rem;
    color: #52616b;
}

.card strong {
    display: block;
    font-size: 1.8rem;
    margin-top: 8px;
    color: #1d3557;
}

.recent h3 {
    margin: 0 0 10px;
}

.list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 8px;
}

.list li {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.list p {
    margin: 2px 0 0;
    color: #52616b;
}

@media (max-width: 1000px) {
    .cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .cards {
        grid-template-columns: 1fr;
    }

    .list li {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
