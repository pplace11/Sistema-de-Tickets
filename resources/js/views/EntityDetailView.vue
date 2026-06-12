<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const loading = ref(true);
const saving = ref(false);
const entity = ref(null);
const tickets = ref([]);
const error = ref('');
const isEditing = ref(false);
const saveError = ref('');
const auth = useAuthStore();
const isOperator = computed(() => auth.user?.role === 'operator');
const editForm = ref({
    nif: '',
    name: '',
    phone: '',
    mobile: '',
    website: '',
    email: '',
    internal_notes: '',
});

const entityId = computed(() => route.params.id);

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

const loadEntity = async () => {
    loading.value = true;
    error.value = '';

    try {
        const [entityResponse, ticketsResponse] = await Promise.all([
            api.get(`/entities/${entityId.value}`),
            api.get('/tickets', { params: { entity_id: entityId.value } }),
        ]);

        entity.value = entityResponse.data;
        tickets.value = ticketsResponse.data.data || [];
        editForm.value = {
            nif: entity.value.nif || '',
            name: entity.value.name || '',
            phone: entity.value.phone || '',
            mobile: entity.value.mobile || '',
            website: entity.value.website || '',
            email: entity.value.email || '',
            internal_notes: entity.value.internal_notes || '',
        };
        saveError.value = '';
    } catch {
        error.value = 'Nao foi possivel carregar a entidade.';
    } finally {
        loading.value = false;
    }
};

const startEdit = () => {
    if (!entity.value) {
        return;
    }

    editForm.value = {
        nif: entity.value.nif || '',
        name: entity.value.name || '',
        phone: entity.value.phone || '',
        mobile: entity.value.mobile || '',
        website: entity.value.website || '',
        email: entity.value.email || '',
        internal_notes: entity.value.internal_notes || '',
    };

    saveError.value = '';
    isEditing.value = true;
};

const cancelEdit = () => {
    isEditing.value = false;
    saveError.value = '';
};

const saveEntity = async () => {
    if (!entity.value) {
        return;
    }

    saving.value = true;
    saveError.value = '';

    try {
        const payload = {
            ...editForm.value,
            phone: editForm.value.phone || null,
            mobile: editForm.value.mobile || null,
            website: editForm.value.website || null,
            email: editForm.value.email || null,
            internal_notes: editForm.value.internal_notes || null,
        };

        await api.put(`/entities/${entity.value.id}`, payload);
        isEditing.value = false;
        await loadEntity();
    } catch {
        saveError.value = 'Nao foi possivel guardar as alteracoes da entidade.';
    } finally {
        saving.value = false;
    }
};

onMounted(loadEntity);
</script>

<template>
    <section class="panel">
        <header class="hero">
            <div>
                <RouterLink class="back-link" to="/entities">Voltar as entidades</RouterLink>
                <h2>Detalhe da entidade</h2>
                <p class="subtitle">Informacao geral, contactos e tickets relacionados.</p>
            </div>

            <div class="hero-actions">
                <button v-if="isOperator && !isEditing" class="btn btn-teal" @click="startEdit">
                    Editar entidade
                </button>
                <button class="btn btn-dark" :disabled="loading" @click="loadEntity">
                    {{ loading ? 'A atualizar...' : 'Atualizar' }}
                </button>
            </div>
        </header>

        <p v-if="loading" class="state">A carregar entidade...</p>
        <p v-else-if="error" class="state state-error">{{ error }}</p>

        <template v-else-if="entity">
            <section v-if="isOperator && isEditing" class="box">
                <div class="box-head">
                    <h3>Editar entidade</h3>
                    <span class="hint">Atualize os dados principais</span>
                </div>

                <div class="edit-grid">
                    <input v-model="editForm.nif" placeholder="NIF">
                    <input v-model="editForm.name" placeholder="Nome">
                    <input v-model="editForm.phone" placeholder="Telefone">
                    <input v-model="editForm.mobile" placeholder="Telemovel">
                    <input v-model="editForm.website" placeholder="Website">
                    <input v-model="editForm.email" placeholder="Email">
                    <textarea v-model="editForm.internal_notes" placeholder="Notas internas"></textarea>
                </div>

                <p v-if="saveError" class="state state-error">{{ saveError }}</p>

                <div class="edit-actions">
                    <button class="btn btn-dark" :disabled="saving" @click="saveEntity">
                        {{ saving ? 'A guardar...' : 'Guardar alteracoes' }}
                    </button>
                    <button class="btn btn-light" :disabled="saving" @click="cancelEdit">
                        Cancelar
                    </button>
                </div>
            </section>

            <section class="box summary">
                <div class="summary-main">
                    <h3>{{ entity.name }}</h3>
                    <p class="nif">NIF {{ entity.nif }}</p>
                </div>

                <div class="summary-meta">
                    <span>{{ entity.email || 'Sem email' }}</span>
                    <span>{{ entity.phone || 'Sem telefone' }}</span>
                    <span>{{ entity.mobile || 'Sem telemovel' }}</span>
                    <a v-if="entity.website" :href="entity.website" target="_blank" rel="noreferrer">{{ entity.website }}</a>
                </div>
            </section>

            <section class="box" v-if="entity.internal_notes">
                <div class="box-head">
                    <h3>Notas internas</h3>
                </div>
                <p class="notes">{{ entity.internal_notes }}</p>
            </section>

            <section class="box">
                <div class="box-head">
                    <h3>Contactos associados</h3>
                    <span class="hint">{{ entity.contacts?.length || 0 }} contactos</span>
                </div>

                <ul v-if="entity.contacts?.length" class="list">
                    <li v-for="contact in entity.contacts" :key="contact.id" class="row">
                        <div>
                            <strong>{{ contact.name }}</strong>
                            <small>{{ contact.contact_function?.name || 'Sem funcao' }}</small>
                        </div>
                        <div class="right">
                            <span>{{ contact.email }}</span>
                            <small>{{ contact.mobile || contact.phone || 'Sem contacto telefonico' }}</small>
                        </div>
                    </li>
                </ul>

                <p v-else class="state">Sem contactos associados.</p>
            </section>

            <section class="box">
                <div class="box-head">
                    <h3>Tickets desta entidade</h3>
                    <span class="hint">{{ tickets.length }} registos</span>
                </div>

                <ul v-if="tickets.length" class="list">
                    <li v-for="ticket in tickets" :key="ticket.id" class="row">
                        <div>
                            <RouterLink class="ticket-link" :to="`/tickets/${ticket.id}`">{{ ticket.ticket_number }}</RouterLink>
                            <p class="subject">{{ ticket.subject }}</p>
                            <small>{{ formatDateTime(ticket.created_at) }}</small>
                        </div>
                        <div class="right">
                            <span>{{ ticket.inbox?.name || 'Sem inbox' }}</span>
                            <span class="status" :class="statusClass(ticket.status?.name)">{{ ticket.status?.name || '-' }}</span>
                        </div>
                    </li>
                </ul>

                <p v-else class="state">Sem tickets para esta entidade.</p>
            </section>
        </template>
    </section>
</template>

<style scoped>
.panel {
    background:
        radial-gradient(circle at top right, rgba(42, 157, 143, 0.11), transparent 34%),
        linear-gradient(180deg, #ffffff 0%, #f8fcfb 100%);
    border: 1px solid #dbe6ee;
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

.hero-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.back-link {
    color: #2a9d8f;
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

.edit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.edit-grid textarea {
    grid-column: 1 / -1;
    min-height: 90px;
    resize: vertical;
}

.edit-actions {
    margin-top: 10px;
    display: flex;
    gap: 8px;
}

.summary {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 14px;
}

.summary-main h3 {
    margin: 0;
    color: #0f172a;
}

.nif {
    margin: 5px 0 0;
    color: #64748b;
}

.summary-meta {
    display: grid;
    justify-items: end;
    text-align: right;
    gap: 6px;
    color: #334155;
}

.summary-meta a {
    color: #2a9d8f;
    text-decoration: none;
}

.summary-meta a:hover {
    text-decoration: underline;
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

.notes {
    margin: 0;
    color: #334155;
    white-space: pre-wrap;
}

.list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 8px;
}

.row {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    background: linear-gradient(180deg, #ffffff 0%, #f9fcfb 100%);
}

.row strong {
    display: block;
    color: #0f172a;
}

.row small {
    color: #64748b;
}

.right {
    display: grid;
    justify-items: end;
    text-align: right;
    gap: 5px;
    color: #334155;
}

.ticket-link {
    color: #2a9d8f;
    font-weight: 700;
    text-decoration: none;
}

.ticket-link:hover {
    text-decoration: underline;
}

.subject {
    margin: 4px 0;
    color: #334155;
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

.btn-teal {
    background: #2a9d8f;
    color: #fff;
}

.btn-light {
    background: #eef2f7;
    color: #334155;
}

input,
textarea {
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #fff;
}

input:focus,
textarea:focus {
    outline: none;
    border-color: #77a8a1;
    box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.14);
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

    .hero-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .edit-grid {
        grid-template-columns: 1fr;
    }

    .summary {
        grid-template-columns: 1fr;
    }

    .summary-meta,
    .right {
        justify-items: start;
        text-align: left;
    }

    .row {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
