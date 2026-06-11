<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';

const tickets = ref([]);
const entities = ref([]);
const lookups = ref({ inboxes: [], ticketTypes: [], ticketStatuses: [] });

const filters = ref({ search: '', inbox_id: '', ticket_status_id: '', ticket_type_id: '', entity_id: '' });
const form = ref({
    subject: '',
    inbox_id: '',
    ticket_type_id: '',
    ticket_status_id: '',
    entity_id: '',
    message: '',
    cc: '',
});

const load = async () => {
    const [ticketsResponse, entitiesResponse, lookupsResponse] = await Promise.all([
        api.get('/tickets', { params: filters.value }),
        api.get('/entities'),
        api.get('/lookups'),
    ]);

    tickets.value = ticketsResponse.data.data;
    entities.value = entitiesResponse.data.data;
    lookups.value = lookupsResponse.data;

    if (!form.value.ticket_status_id && lookups.value.ticketStatuses.length > 0) {
        form.value.ticket_status_id = lookups.value.ticketStatuses[0].id;
    }
};

const createTicket = async () => {
    await api.post('/tickets', {
        ...form.value,
        cc: form.value.cc
            ? form.value.cc.split(',').map((email) => email.trim()).filter(Boolean)
            : [],
    });

    form.value = {
        subject: '',
        inbox_id: '',
        ticket_type_id: '',
        ticket_status_id: lookups.value.ticketStatuses[0]?.id || '',
        entity_id: '',
        message: '',
        cc: '',
    };

    await load();
};

onMounted(load);
</script>

<template>
    <section class="panel">
        <h2>Tickets</h2>

        <div class="filters">
            <input v-model="filters.search" placeholder="Pesquisar por numero, assunto, email ou entidade" />
            <select v-model="filters.inbox_id">
                <option value="">Inbox</option>
                <option v-for="inbox in lookups.inboxes" :key="inbox.id" :value="inbox.id">{{ inbox.name }}</option>
            </select>
            <select v-model="filters.ticket_status_id">
                <option value="">Estado</option>
                <option v-for="status in lookups.ticketStatuses" :key="status.id" :value="status.id">{{ status.name }}</option>
            </select>
            <button @click="load">Filtrar</button>
        </div>

        <div class="grid">
            <input v-model="form.subject" placeholder="Assunto" />
            <select v-model="form.inbox_id">
                <option disabled value="">Inbox</option>
                <option v-for="inbox in lookups.inboxes" :key="inbox.id" :value="inbox.id">{{ inbox.name }}</option>
            </select>
            <select v-model="form.ticket_type_id">
                <option disabled value="">Tipo</option>
                <option v-for="type in lookups.ticketTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
            </select>
            <select v-model="form.ticket_status_id">
                <option disabled value="">Estado</option>
                <option v-for="status in lookups.ticketStatuses" :key="status.id" :value="status.id">{{ status.name }}</option>
            </select>
            <select v-model="form.entity_id">
                <option disabled value="">Entidade</option>
                <option v-for="entity in entities" :key="entity.id" :value="entity.id">{{ entity.name }}</option>
            </select>
            <input v-model="form.cc" placeholder="CC: email1@x.pt, email2@x.pt" />
            <textarea v-model="form.message" placeholder="Mensagem"></textarea>
            <button @click="createTicket">Criar Ticket</button>
        </div>

        <ul class="list">
            <li v-for="ticket in tickets" :key="ticket.id">
                <div>
                    <strong>{{ ticket.ticket_number }}</strong>
                    <p>{{ ticket.subject }}</p>
                </div>
                <div class="meta">
                    <span>{{ ticket.inbox?.name }}</span>
                    <span>{{ ticket.status?.name }}</span>
                    <span>{{ ticket.entity?.name }}</span>
                </div>
            </li>
        </ul>
    </section>
</template>

<style scoped>
.panel { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
.filters { display: grid; grid-template-columns: 2fr repeat(2, 1fr) auto; gap: 8px; margin-bottom: 14px; }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 20px; }
.grid textarea { grid-column: 1 / -1; min-height: 100px; }
.list { list-style: none; padding: 0; margin: 0; display: grid; gap: 8px; }
.list li { display: flex; justify-content: space-between; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
.meta { display: grid; gap: 2px; text-align: right; }
input, textarea, button, select { padding: 10px; border-radius: 10px; border: 1px solid #cbd5e1; }
button { background: #1d3557; color: white; border: 0; }
@media (max-width: 900px) {
    .filters, .grid { grid-template-columns: 1fr; }
    .list li { flex-direction: column; gap: 8px; }
    .meta { text-align: left; }
}
</style>
