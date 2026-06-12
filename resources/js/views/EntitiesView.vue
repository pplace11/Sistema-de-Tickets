<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const loading = ref(true);
const submitting = ref(false);
const entities = ref([]);
const search = ref('');
const form = ref({ nif: '', name: '', phone: '', mobile: '', website: '', email: '', internal_notes: '' });
const auth = useAuthStore();
const isOperator = computed(() => auth.user?.role === 'operator');

const load = async () => {
    loading.value = true;

    try {
        const { data } = await api.get('/entities', { params: { search: search.value || undefined } });
        entities.value = data.data;
    } finally {
        loading.value = false;
    }
};

const createEntity = async () => {
    submitting.value = true;

    try {
        await api.post('/entities', form.value);
        form.value = { nif: '', name: '', phone: '', mobile: '', website: '', email: '', internal_notes: '' };
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
                <p class="eyebrow">Base de clientes</p>
                <h2>Gestao de entidades</h2>
                <p class="subtitle">Regista, pesquisa e consulta entidades num unico painel.</p>
            </div>
            <button class="btn btn-dark" :disabled="loading" @click="load">
                {{ loading ? 'A atualizar...' : 'Atualizar lista' }}
            </button>
        </header>

        <section class="box">
            <div class="box-head">
                <h3>Pesquisa</h3>
                <span class="hint">Nome, NIF ou email</span>
            </div>

            <div class="toolbar">
                <input v-model="search" placeholder="Pesquisar por nome, NIF ou email" @keyup.enter="load" />
                <button class="btn btn-teal" @click="load">Pesquisar</button>
            </div>
        </section>

        <section v-if="isOperator" class="box">
            <div class="box-head">
                <h3>Nova entidade</h3>
                <span class="hint">Preenche os dados principais antes de criar</span>
            </div>

            <div class="grid">
                <input v-model="form.nif" placeholder="NIF" />
                <input v-model="form.name" placeholder="Nome" />
                <input v-model="form.phone" placeholder="Telefone" />
                <input v-model="form.mobile" placeholder="Telemovel" />
                <input v-model="form.website" placeholder="Website" />
                <input v-model="form.email" placeholder="Email" />
                <textarea v-model="form.internal_notes" placeholder="Notas internas"></textarea>
                <button class="btn btn-primary" :disabled="submitting" @click="createEntity">
                    {{ submitting ? 'A criar entidade...' : 'Criar Entidade' }}
                </button>
            </div>
        </section>

        <section class="box list-section">
            <div class="box-head">
                <h3>Lista de entidades</h3>
                <span class="hint">{{ entities.length }} registos na vista atual</span>
            </div>

            <p v-if="loading" class="state">A carregar entidades...</p>

            <ul v-else class="list">
                <li v-for="entity in entities" :key="entity.id" class="entity-row">
                    <div class="primary">
                        <strong>{{ entity.name }}</strong>
                        <span class="nif">NIF {{ entity.nif }}</span>
                        <RouterLink class="detail-link" :to="`/entities/${entity.id}`">Abrir entidade</RouterLink>
                    </div>
                    <div class="secondary">
                        <span>{{ entity.email || '-' }}</span>
                    </div>
                </li>

                <li v-if="!entities.length" class="state">Sem entidades para mostrar.</li>
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

.grid textarea {
    grid-column: 1 / -1;
    min-height: 90px;
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

.entity-row {
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

.nif {
    color: #64748b;
    font-size: 0.84rem;
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
    color: #334155;
    font-size: 0.9rem;
    text-align: right;
}

.state {
    color: #64748b;
    text-align: center;
    padding: 12px;
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

@media (max-width: 900px) {
    .hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .toolbar,
    .grid {
        grid-template-columns: 1fr;
    }

    .entity-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .secondary {
        text-align: left;
    }

    .box-head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
