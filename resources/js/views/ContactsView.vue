<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const loading = ref(true);
const submitting = ref(false);
const contacts = ref([]);
const entities = ref([]);
const functions = ref([]);
const search = ref('');
const auth = useAuthStore();
const isOperator = computed(() => auth.user?.role === 'operator');

const form = ref({
    name: '', email: '', phone: '', mobile: '', internal_notes: '', contact_function_id: '', entity_ids: [],
});

const formatEntityNames = (contact) => {
    const names = (contact?.entities || []).map((entity) => entity.name).filter(Boolean);
    return names.length ? names.join(', ') : 'Sem entidades associadas';
};

const load = async () => {
    loading.value = true;

    try {
        const [{ data: c }, { data: e }, { data: f }] = await Promise.all([
            api.get('/contacts', { params: { search: search.value || undefined } }),
            api.get('/entities'),
            api.get('/contact-functions'),
        ]);

        contacts.value = c.data;
        entities.value = e.data;
        functions.value = f;
    } finally {
        loading.value = false;
    }
};

const createContact = async () => {
    submitting.value = true;

    try {
        await api.post('/contacts', {
            ...form.value,
            contact_function_id: form.value.contact_function_id || null,
        });

        form.value = { name: '', email: '', phone: '', mobile: '', internal_notes: '', contact_function_id: '', entity_ids: [] };
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
                <p class="eyebrow">Relacionamento</p>
                <h2>Gestao de contactos</h2>
                <p class="subtitle">Mantem os contactos organizados por funcao e por entidade.</p>
            </div>
            <button class="btn btn-dark" :disabled="loading" @click="load">
                {{ loading ? 'A atualizar...' : 'Atualizar lista' }}
            </button>
        </header>

        <section class="box">
            <div class="box-head">
                <h3>Pesquisa</h3>
                <span class="hint">Nome ou email</span>
            </div>

            <div class="toolbar">
                <input v-model="search" placeholder="Pesquisar por nome ou email" @keyup.enter="load" />
                <button class="btn btn-teal" @click="load">Pesquisar</button>
            </div>
        </section>

        <section v-if="isOperator" class="box">
            <div class="box-head">
                <h3>Novo contacto</h3>
                <span class="hint">Associa funcao e entidades para melhor contexto</span>
            </div>

            <div class="grid">
                <input v-model="form.name" placeholder="Nome" />
                <input v-model="form.email" placeholder="Email" />
                <input v-model="form.phone" placeholder="Telefone" />
                <input v-model="form.mobile" placeholder="Telemovel" />
                <select v-model="form.contact_function_id">
                    <option value="">Funcao</option>
                    <option v-for="item in functions" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
                <select v-model="form.entity_ids" multiple>
                    <option v-for="entity in entities" :key="entity.id" :value="entity.id">{{ entity.name }}</option>
                </select>
                <textarea v-model="form.internal_notes" placeholder="Notas internas"></textarea>
                <button class="btn btn-primary" :disabled="submitting" @click="createContact">
                    {{ submitting ? 'A criar contacto...' : 'Criar Contacto' }}
                </button>
            </div>
        </section>

        <section class="box list-section">
            <div class="box-head">
                <h3>Lista de contactos</h3>
                <span class="hint">{{ contacts.length }} registos na vista atual</span>
            </div>

            <p v-if="loading" class="state">A carregar contactos...</p>

            <ul v-else class="list">
                <li v-for="contact in contacts" :key="contact.id" class="contact-row">
                    <div class="primary">
                        <strong>{{ contact.name }}</strong>
                        <span>{{ contact.email }}</span>
                        <RouterLink class="detail-link" :to="`/contacts/${contact.id}`">Abrir contacto</RouterLink>
                    </div>

                    <div class="secondary">
                        <span class="function-badge">{{ contact.contact_function?.name || 'Sem funcao' }}</span>
                        <small>{{ formatEntityNames(contact) }}</small>
                    </div>
                </li>

                <li v-if="!contacts.length" class="state">Sem contactos para mostrar.</li>
            </ul>
        </section>
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

.eyebrow {
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.72rem;
    font-weight: 700;
    color: #5a7080;
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

.toolbar {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.grid textarea,
.grid select {
    grid-column: 1 / -1;
    min-height: 80px;
}

.grid textarea {
    resize: vertical;
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

.btn-teal {
    background: #2a9d8f;
    color: #fff;
}

.btn-primary {
    background: linear-gradient(135deg, #2a9d8f 0%, #2f8f84 100%);
    color: #fff;
}

.list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 8px;
}

.contact-row {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    background: linear-gradient(180deg, #ffffff 0%, #f9fcfb 100%);
}

.primary {
    display: grid;
    gap: 4px;
}

.primary strong {
    color: #0f172a;
    font-size: 1.02rem;
}

.primary span {
    color: #334155;
}

.detail-link {
    color: #2a9d8f;
    font-size: 0.85rem;
    text-decoration: none;
    font-weight: 600;
}

.detail-link:hover {
    text-decoration: underline;
}

.secondary {
    display: grid;
    justify-items: end;
    gap: 6px;
    text-align: right;
}

.function-badge {
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    background: #e7faf5;
    color: #0f766e;
}

.secondary small {
    color: #64748b;
    font-size: 0.8rem;
    max-width: 420px;
}

.state {
    color: #64748b;
    text-align: center;
    padding: 12px;
}

input,
textarea,
select {
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #fff;
}

input:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #77a8a1;
    box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.14);
}

@media (max-width: 900px) {
    .hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .toolbar,
    .grid {
        grid-template-columns: 1fr;
    }

    .contact-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .secondary {
        justify-items: start;
        text-align: left;
    }

    .box-head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
