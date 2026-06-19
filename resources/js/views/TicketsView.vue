<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const loading = ref(true);
const submitting = ref(false);
const tickets = ref([]);
const entities = ref([]);
const lookups = ref({ inboxes: [], ticketTypes: [], ticketStatuses: [], operators: [] });
const ticketAttachments = ref([]);
const ticketFilesInputKey = ref(0);
const messageEditor = ref(null);
const auth = useAuthStore();
const isOperator = computed(() => auth.user?.role === 'operator');
const userEntityId = computed(() => auth.user?.entity_id || '');
const filterHint = computed(() => isOperator.value
    ? 'Pesquisa por numero, assunto, email ou entidade'
    : 'Pesquisa por numero, assunto ou email');
const filterPlaceholder = computed(() => isOperator.value
    ? 'Pesquisar por numero, assunto, email ou entidade'
    : 'Pesquisar por numero, assunto ou email');

const filters = ref({
    search: '',
    inbox_id: '',
    ticket_status_id: '',
    ticket_type_id: '',
    assigned_operator_id: '',
    entity_id: '',
});
const form = ref({
    subject: '',
    inbox_id: '',
    ticket_type_id: '',
    ticket_status_id: '',
    entity_id: '',
    message: '',
    cc: '',
});

const editorActions = [
    { label: 'B', command: 'bold', title: 'Negrito' },
    { label: 'I', command: 'italic', title: 'Italico' },
    { label: 'Lista', command: 'insertUnorderedList', title: 'Lista' },
];

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
    const value = (statusName || '').toLowerCase();

    if (value.includes('fechado')) {
        return 'is-closed';
    }

    if (value.includes('tratamento')) {
        return 'is-progress';
    }

    return 'is-open';
};

const onMessageEditorInput = () => {
    form.value.message = messageEditor.value?.innerHTML?.trim() ?? '';
};

const applyEditorCommand = (command) => {
    if (!messageEditor.value) {
        return;
    }

    messageEditor.value.focus();
    document.execCommand(command, false);
    onMessageEditorInput();
};

const insertEditorLink = () => {
    const url = window.prompt('Introduza a URL do link');

    if (!url) {
        return;
    }

    if (!messageEditor.value) {
        return;
    }

    messageEditor.value.focus();
    document.execCommand('createLink', false, url.trim());
    onMessageEditorInput();
};

const onTicketFilesChange = (event) => {
    const files = event.target?.files;
    ticketAttachments.value = files ? Array.from(files) : [];
};

const load = async () => {
    loading.value = true;

    try {
        const requests = [
            api.get('/tickets', { params: filters.value }),
            api.get('/lookups'),
            api.get('/entities'),
        ];

        const [ticketsResponse, lookupsResponse, entitiesResponse] = await Promise.all(requests);

        tickets.value = ticketsResponse.data.data;
        lookups.value = lookupsResponse.data;
        entities.value = entitiesResponse?.data?.data ?? [];

        if (!form.value.ticket_status_id && lookups.value.ticketStatuses.length > 0) {
            form.value.ticket_status_id = lookups.value.ticketStatuses[0].id;
        }

        if (!form.value.ticket_type_id && lookups.value.ticketTypes.length > 0) {
            form.value.ticket_type_id = lookups.value.ticketTypes[0].id;
        }

        if (!form.value.entity_id && entities.value.length > 0) {
            form.value.entity_id = userEntityId.value || entities.value[0].id;
        }
    } finally {
        loading.value = false;
    }
};

const createTicket = async () => {
    submitting.value = true;

    try {
        const payload = new FormData();

        payload.append('subject', form.value.subject);
        payload.append('inbox_id', String(form.value.inbox_id || ''));
        payload.append('ticket_type_id', String(form.value.ticket_type_id || lookups.value.ticketTypes[0]?.id || ''));
        payload.append('ticket_status_id', String(form.value.ticket_status_id || lookups.value.ticketStatuses[0]?.id || ''));
        payload.append('entity_id', String(form.value.entity_id || userEntityId.value || entities.value[0]?.id || ''));
        payload.append('message', form.value.message);

        const ccList = form.value.cc
            ? form.value.cc.split(',').map((email) => email.trim()).filter(Boolean)
            : [];

        for (const email of ccList) {
            payload.append('cc[]', email);
        }

        for (const file of ticketAttachments.value) {
            payload.append('attachments[]', file);
        }

        await api.post('/tickets', payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        form.value = {
            subject: '',
            inbox_id: '',
            ticket_type_id: '',
            ticket_status_id: lookups.value.ticketStatuses[0]?.id || '',
            entity_id: entities.value[0]?.id || '',
            message: '',
            cc: '',
        };
        ticketAttachments.value = [];
        ticketFilesInputKey.value += 1;

        if (messageEditor.value) {
            messageEditor.value.innerHTML = '';
        }

        await load();
    } finally {
        submitting.value = false;
    }
};

onMounted(load);
</script>

<template>
    <section class="panel">
        <header class="hero">
            <div>
                <p class="eyebrow">Operacao</p>
                <h2>Gestao de tickets</h2>
                <p class="subtitle">
                    {{ isOperator
                        ? 'Filtra, acompanha e cria novos pedidos num unico painel.'
                        : 'Abre novos pedidos de forma rapida para a tua equipa.' }}
                </p>
            </div>
            <button class="btn btn-dark" :disabled="loading" @click="load">
                {{ loading ? 'A atualizar...' : 'Atualizar lista' }}
            </button>
        </header>

        <section class="box">
            <div class="box-head">
                <h3>Filtros de pesquisa</h3>
                <span class="hint">{{ filterHint }}</span>
            </div>

            <div class="filters">
                <input v-model="filters.search" :placeholder="filterPlaceholder" />
                <select v-model="filters.inbox_id">
                    <option value="">Inbox</option>
                    <option v-for="inbox in lookups.inboxes" :key="inbox.id" :value="inbox.id">{{ inbox.name }}</option>
                </select>
                <select v-model="filters.ticket_status_id">
                    <option value="">Estado</option>
                    <option v-for="status in lookups.ticketStatuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                </select>
                <select v-model="filters.ticket_type_id">
                    <option value="">Tipo</option>
                    <option v-for="type in lookups.ticketTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                </select>
                <select v-if="isOperator" v-model="filters.assigned_operator_id">
                    <option value="">Operador</option>
                    <option v-for="operator in lookups.operators" :key="operator.id" :value="operator.id">{{ operator.name }}</option>
                </select>
                <select v-if="isOperator" v-model="filters.entity_id">
                    <option value="">Entidade</option>
                    <option v-for="entity in entities" :key="entity.id" :value="entity.id">{{ entity.name }}</option>
                </select>
                <button class="btn btn-dark" @click="load">Filtrar</button>
            </div>
        </section>

        <section class="box">
            <div class="box-head">
                <h3>Novo ticket</h3>
                <span class="hint">Preenche os campos obrigatorios para criar um pedido</span>
            </div>

            <div class="grid">
                <input v-model="form.subject" placeholder="Assunto" />
                <select v-model="form.inbox_id">
                    <option disabled value="">Inbox</option>
                    <option v-for="inbox in lookups.inboxes" :key="inbox.id" :value="inbox.id">{{ inbox.name }}</option>
                </select>
                <select v-if="isOperator" v-model="form.ticket_type_id">
                    <option disabled value="">Tipo</option>
                    <option v-for="type in lookups.ticketTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                </select>
                <select v-if="isOperator" v-model="form.ticket_status_id">
                    <option disabled value="">Estado</option>
                    <option v-for="status in lookups.ticketStatuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                </select>
                <select v-if="isOperator" v-model="form.entity_id">
                    <option disabled value="">Entidade</option>
                    <option v-for="entity in entities" :key="entity.id" :value="entity.id">{{ entity.name }}</option>
                </select>
                <input v-model="form.cc" placeholder="CC: email1@x.pt, email2@x.pt" />
                <div class="editor-wrapper">
                    <div class="editor-toolbar">
                        <button
                            v-for="action in editorActions"
                            :key="action.command"
                            type="button"
                            class="editor-btn"
                            :title="action.title"
                            @click="applyEditorCommand(action.command)"
                        >
                            {{ action.label }}
                        </button>
                        <button type="button" class="editor-btn" title="Inserir link" @click="insertEditorLink">Link</button>
                    </div>
                    <div
                        ref="messageEditor"
                        class="editor-input"
                        contenteditable="true"
                        role="textbox"
                        aria-label="Mensagem do ticket"
                        @input="onMessageEditorInput"
                    ></div>
                </div>
                <div class="upload-row">
                    <label class="upload-label" :for="`ticket-files-${ticketFilesInputKey}`">Anexos iniciais</label>
                    <input
                        :id="`ticket-files-${ticketFilesInputKey}`"
                        :key="ticketFilesInputKey"
                        class="file-input"
                        type="file"
                        multiple
                        @change="onTicketFilesChange"
                    >
                    <small class="upload-hint">Pode selecionar multiplos ficheiros (max 10MB por ficheiro).</small>
                </div>
                <button class="btn btn-primary" :disabled="submitting" @click="createTicket">
                    {{ submitting ? 'A criar ticket...' : 'Criar Ticket' }}
                </button>
            </div>
        </section>

        <section class="box list-section">
            <div class="box-head">
                <h3>Lista de tickets</h3>
                <span class="hint">{{ tickets.length }} registos na vista atual</span>
            </div>

            <p v-if="loading" class="state">A carregar tickets...</p>

            <ul v-else class="list">
                <li v-for="ticket in tickets" :key="ticket.id" class="ticket-row">
                    <div class="left">
                        <strong class="number">{{ ticket.ticket_number }}</strong>
                        <p class="subject">{{ ticket.subject }}</p>
                        <RouterLink class="detail-link" :to="`/tickets/${ticket.id}`">Abrir ticket</RouterLink>
                        <small class="created">Criado em {{ formatDateTime(ticket.created_at) }}</small>
                    </div>

                    <div class="right">
                        <span class="chip">{{ ticket.inbox?.name || 'Sem inbox' }}</span>
                        <span class="status" :class="statusClass(ticket.status?.name)">
                            {{ ticket.status?.name || '-' }}
                        </span>
                        <span v-if="isOperator" class="entity">{{ ticket.entity?.name || 'Sem entidade' }}</span>
                    </div>
                </li>

                <li v-if="!tickets.length" class="state">Sem tickets para mostrar.</li>
            </ul>
        </section>
    </section>
</template>

<style scoped>
.panel {
    background:
        radial-gradient(circle at top right, rgba(29, 53, 87, 0.07), transparent 32%),
        linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
    border: 1px solid #dde5ee;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 20px 45px rgba(16, 24, 40, 0.08);
}

.hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 14px;
}

.eyebrow {
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
}

h2 {
    margin: 6px 0;
    font-size: 1.35rem;
    color: #0f172a;
}

.subtitle {
    margin: 0;
    color: #475569;
}

.box {
    border: 1px solid #dbe4ed;
    border-radius: 14px;
    background: #fff;
    padding: 14px;
    margin-bottom: 14px;
}

.box-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 10px;
}

.box-head h3 {
    margin: 0;
    color: #0f172a;
    font-size: 1rem;
}

.hint {
    color: #64748b;
    font-size: 0.82rem;
}

.filters {
    display: grid;
    grid-template-columns: 2fr repeat(5, 1fr) auto;
    gap: 8px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.editor-wrapper,
.upload-row,
.grid .btn-primary {
    grid-column: 1 / -1;
}

.editor-wrapper {
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    overflow: hidden;
}

.editor-toolbar {
    display: flex;
    gap: 6px;
    padding: 8px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.editor-btn {
    border: 1px solid #cbd5e1;
    background: #fff;
    border-radius: 8px;
    padding: 4px 8px;
    font-size: 0.8rem;
    color: #334155;
}

.editor-input {
    min-height: 130px;
    padding: 10px;
    outline: none;
    color: #0f172a;
}

.upload-row {
    display: grid;
    gap: 4px;
}

.upload-label {
    color: #334155;
    font-size: 0.86rem;
    font-weight: 600;
}

.file-input {
    padding: 8px;
    border: 1px dashed #b6c3d4;
    border-radius: 10px;
    background: #f8fbff;
}

.upload-hint {
    color: #64748b;
    font-size: 0.78rem;
}

.btn {
    border: 0;
    border-radius: 11px;
    padding: 10px 14px;
    font-weight: 600;
    transition: transform .12s ease, opacity .12s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.75;
    cursor: not-allowed;
}

.btn-dark {
    background: #1d3557;
    color: #fff;
}

.btn-primary {
    background: linear-gradient(135deg, #1d3557 0%, #274a77 100%);
    color: #fff;
}

.list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 8px;
}

.ticket-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    gap: 12px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.left {
    min-width: 0;
}

.number {
    font-size: 1.05rem;
    color: #0f172a;
}

.subject {
    margin: 3px 0;
    color: #334155;
}

.detail-link {
    color: #1d3557;
    font-size: 0.86rem;
    font-weight: 600;
    text-decoration: none;
}

.detail-link:hover {
    text-decoration: underline;
}

.created {
    color: #64748b;
    font-size: 0.8rem;
}

.right {
    display: grid;
    justify-items: end;
    gap: 6px;
    text-align: right;
}

.chip,
.entity {
    color: #334155;
    font-size: 0.88rem;
}

.status {
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
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

.state {
    color: #64748b;
    text-align: center;
    padding: 12px;
}

input, textarea, select {
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #fff;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: #7f9bc0;
    box-shadow: 0 0 0 3px rgba(29, 53, 87, 0.12);
}

@media (max-width: 900px) {
    .hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .filters,
    .grid {
        grid-template-columns: 1fr;
    }

    .ticket-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .right {
        justify-items: start;
        text-align: left;
    }

    .box-head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
