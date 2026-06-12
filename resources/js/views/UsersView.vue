<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const isOperator = computed(() => auth.user?.role === 'operator');

const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const users = ref([]);
const entities = ref([]);
const inboxes = ref([]);
const search = ref('');

const form = ref({
    name: '',
    email: '',
    password: '',
    entity_id: '',
    inbox_ids: [],
    is_active: true,
});

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        password: '',
        entity_id: '',
        inbox_ids: [],
        is_active: true,
    };
};

const roleBadgeClass = (role) => (role === 'operator' ? 'is-operator' : 'is-client');

const formatInboxes = (user) => {
    const names = (user?.inboxes || []).map((inbox) => inbox.name).filter(Boolean);
    return names.length ? names.join(', ') : 'Sem inboxes';
};

const load = async () => {
    loading.value = true;
    error.value = '';

    try {
        const [usersResponse, entitiesResponse, lookupsResponse] = await Promise.all([
            api.get('/users', { params: { search: search.value || undefined, role: 'operator' } }),
            api.get('/entities'),
            api.get('/lookups'),
        ]);

        users.value = usersResponse.data.data || [];
        entities.value = entitiesResponse.data.data || [];
        inboxes.value = lookupsResponse.data.inboxes || [];
    } catch {
        error.value = 'Nao foi possivel carregar os utilizadores.';
    } finally {
        loading.value = false;
    }
};

const createUser = async () => {
    submitting.value = true;
    error.value = '';

    try {
        await api.post('/users', {
            ...form.value,
            entity_id: form.value.entity_id || null,
            inbox_ids: form.value.inbox_ids,
            is_active: Boolean(form.value.is_active),
        });

        resetForm();
        await load();
    } catch {
        error.value = 'Nao foi possivel criar o utilizador. Verifique os dados do formulario.';
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
                <p class="eyebrow">Administracao</p>
                <h2>Gestao de utilizadores</h2>
                <p class="subtitle">Crie operadores por entidade com regras de acesso por inbox.</p>
            </div>
            <button class="btn btn-dark" :disabled="loading" @click="load">
                {{ loading ? 'A atualizar...' : 'Atualizar lista' }}
            </button>
        </header>

        <p v-if="!isOperator" class="state state-error">Apenas operadores podem gerir utilizadores.</p>

        <template v-else>
            <section class="box">
                <div class="box-head">
                    <h3>Pesquisa</h3>
                    <span class="hint">Nome ou email</span>
                </div>

                <div class="toolbar">
                    <input v-model="search" placeholder="Pesquisar por nome ou email" @keyup.enter="load">
                    <button class="btn btn-teal" @click="load">Pesquisar</button>
                </div>
            </section>

            <section class="box">
                <div class="box-head">
                    <h3>Novo utilizador</h3>
                    <span class="hint">Crie um operador e associe entidade e inboxes</span>
                </div>

                <div class="grid">
                    <input v-model="form.name" placeholder="Nome completo">
                    <input v-model="form.email" placeholder="Email">
                    <input v-model="form.password" type="password" placeholder="Password inicial (min. 8)">

                    <select v-model="form.entity_id">
                        <option value="">Entidade do operador</option>
                        <option v-for="entity in entities" :key="entity.id" :value="entity.id">{{ entity.name }}</option>
                    </select>

                    <select v-model="form.inbox_ids" multiple>
                        <option v-for="inbox in inboxes" :key="inbox.id" :value="inbox.id">{{ inbox.name }}</option>
                    </select>

                    <label class="switch">
                        <input v-model="form.is_active" type="checkbox">
                        <span>Utilizador ativo</span>
                    </label>

                    <button class="btn btn-primary" :disabled="submitting" @click="createUser">
                        {{ submitting ? 'A criar...' : 'Criar utilizador' }}
                    </button>
                </div>

                <p v-if="error" class="state state-error">{{ error }}</p>
            </section>

            <section class="box list-section">
                <div class="box-head">
                    <h3>Lista de utilizadores</h3>
                    <span class="hint">{{ users.length }} registos na vista atual</span>
                </div>

                <p v-if="loading" class="state">A carregar utilizadores...</p>

                <ul v-else class="list">
                    <li v-for="user in users" :key="user.id" class="user-row">
                        <div class="primary">
                            <strong>{{ user.name }}</strong>
                            <span>{{ user.email }}</span>
                            <small>Entidade: {{ user.entity?.name || 'Sem entidade' }}</small>
                            <small>Inboxes: {{ formatInboxes(user) }}</small>
                        </div>

                        <div class="secondary">
                            <span class="role-badge" :class="roleBadgeClass(user.role)">OPERADOR</span>
                            <span class="active" :class="{ off: !user.is_active }">{{ user.is_active ? 'Ativo' : 'Inativo' }}</span>
                        </div>
                    </li>

                    <li v-if="!users.length" class="state">Sem utilizadores para mostrar.</li>
                </ul>
            </section>
        </template>
    </section>
</template>

<style scoped>
.panel {
    background:
        radial-gradient(circle at top right, rgba(22, 101, 52, 0.10), transparent 34%),
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

.grid select[multiple],
.switch,
.grid button,
.state {
    grid-column: 1 / -1;
}

.switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #334155;
    font-weight: 600;
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
    background: linear-gradient(135deg, #166534 0%, #15803d 100%);
    color: #fff;
}

.list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 8px;
}

.user-row {
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

.primary small {
    color: #64748b;
}

.secondary {
    display: grid;
    justify-items: end;
    gap: 6px;
    text-align: right;
}

.role-badge {
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.role-badge.is-operator {
    background: #e0f2fe;
    color: #075985;
}

.role-badge.is-client {
    background: #fef3c7;
    color: #92400e;
}

.active {
    color: #166534;
    font-size: 0.82rem;
    font-weight: 600;
}

.active.off {
    color: #b91c1c;
}

.state {
    color: #64748b;
    text-align: center;
    padding: 12px;
}

.state-error {
    color: #b91c1c;
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

    .user-row {
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
