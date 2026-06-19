<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const loading = ref(true);
const submitting = ref(false);
const ticket = ref(null);
const error = ref('');
const replyForm = ref({ message: '' });
const replyAttachments = ref([]);
const replyFilesInputKey = ref(0);
const currentRepliesPage = ref(1);
const repliesPerPage = 5;

const ticketId = computed(() => route.params.id);
const allReplies = computed(() => ticket.value?.replies ?? []);
const totalReplies = computed(() => allReplies.value.length);
const totalReplyPages = computed(() => Math.max(1, Math.ceil(totalReplies.value / repliesPerPage)));
const paginatedReplies = computed(() => {
    const page = Math.min(currentRepliesPage.value, totalReplyPages.value);
    const start = (page - 1) * repliesPerPage;
    return allReplies.value.slice(start, start + repliesPerPage);
});

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

const fileUrl = (path) => `/storage/${path}`;

const escapeHtml = (value) => value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

const sanitizeRichText = (html) => html
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
    .replace(/\son[a-z]+="[^"]*"/gi, '')
    .replace(/\son[a-z]+='[^']*'/gi, '')
    .replace(/\sjavascript:/gi, ' ');

const toRichHtml = (value) => {
    if (!value) {
        return '';
    }

    if (value.includes('<')) {
        return sanitizeRichText(value);
    }

    return escapeHtml(value).replaceAll('\n', '<br>');
};

const replyAuthor = (reply) => {
    if (reply.author_user?.name) {
        return reply.author_user.name;
    }

    if (reply.author_contact?.name) {
        return reply.author_contact.name;
    }

    return reply.author_type === 'operator' ? 'Operador' : 'Cliente';
};

const loadTicket = async () => {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await api.get(`/tickets/${ticketId.value}`);
        ticket.value = data;
        currentRepliesPage.value = 1;
    } catch {
        error.value = 'Nao foi possivel carregar o ticket.';
    } finally {
        loading.value = false;
    }
};

const onReplyFilesChange = (event) => {
    const files = event.target?.files;
    replyAttachments.value = files ? Array.from(files) : [];
};

const goToRepliesPage = (page) => {
    const safePage = Math.min(Math.max(1, page), totalReplyPages.value);
    currentRepliesPage.value = safePage;
};

const sendReply = async () => {
    if (!replyForm.value.message.trim()) {
        return;
    }

    submitting.value = true;

    try {
        const formData = new FormData();
        formData.append('message', replyForm.value.message);

        for (const file of replyAttachments.value) {
            formData.append('attachments[]', file);
        }

        await api.post(`/tickets/${ticketId.value}/replies`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        replyForm.value.message = '';
        replyAttachments.value = [];
        replyFilesInputKey.value += 1;
        await loadTicket();
    } finally {
        submitting.value = false;
    }
};

onMounted(loadTicket);
</script>

<template>
    <section class="panel">
        <header class="hero">
            <div>
                <RouterLink class="back-link" to="/tickets">Voltar aos tickets</RouterLink>
                <h2>Detalhe do ticket</h2>
                <p class="subtitle">Historico completo, anexos e respostas.</p>
            </div>
            <button class="btn btn-dark" :disabled="loading" @click="loadTicket">
                {{ loading ? 'A atualizar...' : 'Atualizar' }}
            </button>
        </header>

        <p v-if="loading" class="state">A carregar ticket...</p>
        <p v-else-if="error" class="state state-error">{{ error }}</p>

        <template v-else-if="ticket">
            <section class="box summary">
                <div class="main">
                    <p class="number">{{ ticket.ticket_number }}</p>
                    <h3>{{ ticket.subject }}</h3>
                    <div class="message" v-html="toRichHtml(ticket.message)"></div>
                </div>

                <div class="meta">
                    <span class="chip">{{ ticket.inbox?.name || 'Sem inbox' }}</span>
                    <span class="status" :class="statusClass(ticket.status?.name)">{{ ticket.status?.name || '-' }}</span>
                    <span class="entity">{{ ticket.entity?.name || 'Sem entidade' }}</span>
                    <small>Criado em {{ formatDateTime(ticket.created_at) }}</small>
                </div>
            </section>

            <section v-if="ticket.attachments?.length" class="box">
                <div class="box-head">
                    <h3>Anexos iniciais</h3>
                </div>

                <ul class="attachment-list">
                    <li v-for="attachment in ticket.attachments" :key="attachment.id">
                        <a :href="fileUrl(attachment.path)" target="_blank" rel="noreferrer">
                            {{ attachment.original_name }}
                        </a>
                    </li>
                </ul>
            </section>

            <section class="box">
                <div class="box-head">
                    <h3>Respostas</h3>
                    <span class="hint">{{ ticket.replies?.length || 0 }} mensagens</span>
                </div>

                <ul v-if="ticket.replies?.length" class="timeline">
                    <li v-for="reply in paginatedReplies" :key="reply.id" class="timeline-item">
                        <div class="timeline-head">
                            <strong>{{ replyAuthor(reply) }}</strong>
                            <small>{{ formatDateTime(reply.created_at) }}</small>
                        </div>
                        <div class="timeline-message" v-html="toRichHtml(reply.message)"></div>

                        <ul v-if="reply.attachments?.length" class="attachment-list inline">
                            <li v-for="attachment in reply.attachments" :key="attachment.id">
                                <a :href="fileUrl(attachment.path)" target="_blank" rel="noreferrer">
                                    {{ attachment.original_name }}
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                <div v-if="totalReplyPages > 1" class="pager">
                    <button class="pager-btn" :disabled="currentRepliesPage === 1" @click="goToRepliesPage(currentRepliesPage - 1)">
                        Anterior
                    </button>
                    <span class="pager-label">Pagina {{ currentRepliesPage }} de {{ totalReplyPages }}</span>
                    <button class="pager-btn" :disabled="currentRepliesPage === totalReplyPages" @click="goToRepliesPage(currentRepliesPage + 1)">
                        Seguinte
                    </button>
                </div>

                <p v-else class="state">Sem respostas neste ticket.</p>
            </section>

            <section class="box">
                <div class="box-head">
                    <h3>Nova resposta</h3>
                </div>

                <div class="reply-form">
                    <textarea v-model="replyForm.message" placeholder="Escreva a resposta do ticket"></textarea>

                    <div class="upload-row">
                        <label class="upload-label" :for="`reply-files-${replyFilesInputKey}`">Anexos da resposta</label>
                        <input
                            :id="`reply-files-${replyFilesInputKey}`"
                            :key="replyFilesInputKey"
                            class="file-input"
                            type="file"
                            multiple
                            @change="onReplyFilesChange"
                        >
                        <small class="upload-hint">Pode selecionar multiplos ficheiros (max 10MB por ficheiro).</small>
                    </div>

                    <button class="btn btn-primary" :disabled="submitting" @click="sendReply">
                        {{ submitting ? 'A enviar...' : 'Enviar resposta' }}
                    </button>
                </div>
            </section>

            <section v-if="ticket.activity_logs?.length" class="box">
                <div class="box-head">
                    <h3>Log de atividade</h3>
                </div>

                <ul class="timeline">
                    <li v-for="log in ticket.activity_logs" :key="log.id" class="timeline-item">
                        <div class="timeline-head">
                            <strong>{{ log.description }}</strong>
                            <small>{{ formatDateTime(log.created_at) }}</small>
                        </div>
                        <p class="timeline-meta">Acao: {{ log.action }}</p>
                    </li>
                </ul>
            </section>
        </template>
    </section>
</template>

<style scoped>
.panel {
    background:
        radial-gradient(circle at top right, rgba(29, 53, 87, 0.09), transparent 35%),
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

.back-link {
    color: #1d3557;
    font-size: 0.88rem;
    text-decoration: none;
}

.back-link:hover {
    text-decoration: underline;
}

h2 {
    margin: 8px 0 4px;
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
    margin-top: 12px;
}

.summary {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 14px;
}

.number {
    margin: 0;
    color: #1d3557;
    font-weight: 700;
}

h3 {
    margin: 4px 0;
    color: #0f172a;
}

.message {
    margin: 8px 0 0;
    color: #334155;
    white-space: pre-wrap;
}

.meta {
    display: grid;
    gap: 6px;
    justify-items: end;
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

.box-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 10px;
}

.hint {
    color: #64748b;
    font-size: 0.82rem;
}

.timeline {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 8px;
}

.timeline-item {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px;
    background: #f8fbff;
}

.timeline-head {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

.timeline-message,
.timeline-meta {
    margin: 8px 0 0;
    color: #334155;
    white-space: pre-wrap;
}

.reply-form {
    display: grid;
    gap: 8px;
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

textarea {
    min-height: 90px;
    resize: vertical;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #fff;
}

textarea:focus {
    outline: none;
    border-color: #7f9bc0;
    box-shadow: 0 0 0 3px rgba(29, 53, 87, 0.12);
}

.attachment-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 6px;
}

.attachment-list a {
    color: #1d3557;
    text-decoration: none;
}

.attachment-list a:hover {
    text-decoration: underline;
}

.attachment-list.inline {
    margin-top: 8px;
}

.pager {
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.pager-btn {
    border: 1px solid #cbd5e1;
    background: #fff;
    border-radius: 9px;
    padding: 6px 10px;
    color: #334155;
}

.pager-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.pager-label {
    color: #475569;
    font-size: 0.84rem;
}

.btn {
    border: 0;
    border-radius: 11px;
    padding: 10px 14px;
    font-weight: 600;
}

.btn-dark {
    background: #1d3557;
    color: #fff;
}

.btn-primary {
    background: linear-gradient(135deg, #1d3557 0%, #274a77 100%);
    color: #fff;
}

.state {
    color: #64748b;
    text-align: center;
    padding: 12px;
}

.state-error {
    color: #b91c1c;
}

@media (max-width: 900px) {
    .hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .summary {
        grid-template-columns: 1fr;
    }

    .meta {
        justify-items: start;
        text-align: left;
    }
}
</style>
