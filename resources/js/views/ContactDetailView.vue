<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const loading = ref(true);
const saving = ref(false);
const error = ref('');
const contact = ref(null);
const entities = ref([]);
const contactFunctions = ref([]);
const isEditing = ref(false);
const saveError = ref('');
const auth = useAuthStore();
const isOperator = computed(() => auth.user?.role === 'operator');
const editForm = ref({
    name: '',
    email: '',
    phone: '',
    mobile: '',
    internal_notes: '',
    contact_function_id: '',
    entity_ids: [],
});

const contactId = computed(() => route.params.id);

const loadContact = async () => {
    loading.value = true;
    error.value = '';

    try {
        const [contactResponse, entitiesResponse, functionsResponse] = await Promise.all([
            api.get(`/contacts/${contactId.value}`),
            api.get('/entities'),
            api.get('/contact-functions'),
        ]);

        contact.value = contactResponse.data;
        entities.value = entitiesResponse.data.data || [];
        contactFunctions.value = functionsResponse.data || [];

        editForm.value = {
            name: contact.value.name || '',
            email: contact.value.email || '',
            phone: contact.value.phone || '',
            mobile: contact.value.mobile || '',
            internal_notes: contact.value.internal_notes || '',
            contact_function_id: contact.value.contact_function_id || '',
            entity_ids: (contact.value.entities || []).map((entity) => entity.id),
        };

        saveError.value = '';
    } catch {
        error.value = 'Nao foi possivel carregar o contacto.';
    } finally {
        loading.value = false;
    }
};

const startEdit = () => {
    if (!contact.value) {
        return;
    }

    editForm.value = {
        name: contact.value.name || '',
        email: contact.value.email || '',
        phone: contact.value.phone || '',
        mobile: contact.value.mobile || '',
        internal_notes: contact.value.internal_notes || '',
        contact_function_id: contact.value.contact_function_id || '',
        entity_ids: (contact.value.entities || []).map((entity) => entity.id),
    };

    saveError.value = '';
    isEditing.value = true;
};

const cancelEdit = () => {
    isEditing.value = false;
    saveError.value = '';
};

const saveContact = async () => {
    if (!contact.value) {
        return;
    }

    saving.value = true;
    saveError.value = '';

    try {
        const payload = {
            ...editForm.value,
            contact_function_id: editForm.value.contact_function_id || null,
            phone: editForm.value.phone || null,
            mobile: editForm.value.mobile || null,
            internal_notes: editForm.value.internal_notes || null,
        };

        await api.put(`/contacts/${contact.value.id}`, payload);
        isEditing.value = false;
        await loadContact();
    } catch {
        saveError.value = 'Nao foi possivel guardar as alteracoes do contacto.';
    } finally {
        saving.value = false;
    }
};

onMounted(loadContact);
</script>

<template>
    <section class="panel">
        <header class="hero">
            <div>
                <RouterLink class="back-link" to="/contacts">Voltar aos contactos</RouterLink>
                <h2>Detalhe do contacto</h2>
                <p class="subtitle">Informacao geral e entidades associadas.</p>
            </div>

            <div class="hero-actions">
                <button v-if="isOperator && !isEditing" class="btn btn-teal" @click="startEdit">
                    Editar contacto
                </button>
                <button class="btn btn-dark" :disabled="loading" @click="loadContact">
                    {{ loading ? 'A atualizar...' : 'Atualizar' }}
                </button>
            </div>
        </header>

        <p v-if="loading" class="state">A carregar contacto...</p>
        <p v-else-if="error" class="state state-error">{{ error }}</p>

        <template v-else-if="contact">
            <section v-if="isOperator && isEditing" class="box">
                <div class="box-head">
                    <h3>Editar contacto</h3>
                    <span class="hint">Atualize os dados do contacto</span>
                </div>

                <div class="edit-grid">
                    <input v-model="editForm.name" placeholder="Nome">
                    <input v-model="editForm.email" placeholder="Email">
                    <input v-model="editForm.phone" placeholder="Telefone">
                    <input v-model="editForm.mobile" placeholder="Telemovel">
                    <select v-model="editForm.contact_function_id">
                        <option value="">Funcao</option>
                        <option v-for="item in contactFunctions" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                    <select v-model="editForm.entity_ids" multiple>
                        <option v-for="entity in entities" :key="entity.id" :value="entity.id">{{ entity.name }}</option>
                    </select>
                    <textarea v-model="editForm.internal_notes" placeholder="Notas internas"></textarea>
                </div>

                <p v-if="saveError" class="state state-error">{{ saveError }}</p>

                <div class="edit-actions">
                    <button class="btn btn-dark" :disabled="saving" @click="saveContact">
                        {{ saving ? 'A guardar...' : 'Guardar alteracoes' }}
                    </button>
                    <button class="btn btn-light" :disabled="saving" @click="cancelEdit">
                        Cancelar
                    </button>
                </div>
            </section>

            <section class="box summary">
                <div class="summary-main">
                    <h3>{{ contact.name }}</h3>
                    <span class="function-badge">{{ contact.contact_function?.name || 'Sem funcao' }}</span>
                </div>

                <div class="summary-meta">
                    <span>{{ contact.email || 'Sem email' }}</span>
                    <span>{{ contact.phone || 'Sem telefone' }}</span>
                    <span>{{ contact.mobile || 'Sem telemovel' }}</span>
                </div>
            </section>

            <section v-if="contact.internal_notes" class="box">
                <div class="box-head">
                    <h3>Notas internas</h3>
                </div>
                <p class="notes">{{ contact.internal_notes }}</p>
            </section>

            <section class="box">
                <div class="box-head">
                    <h3>Entidades associadas</h3>
                    <span class="hint">{{ contact.entities?.length || 0 }} entidades</span>
                </div>

                <ul v-if="contact.entities?.length" class="list">
                    <li v-for="entity in contact.entities" :key="entity.id" class="row">
                        <div>
                            <strong>{{ entity.name }}</strong>
                            <small>NIF {{ entity.nif }}</small>
                        </div>

                        <div class="right">
                            <span>{{ entity.email || 'Sem email' }}</span>
                            <RouterLink class="entity-link" :to="`/entities/${entity.id}`">Abrir entidade</RouterLink>
                        </div>
                    </li>
                </ul>

                <p v-else class="state">Sem entidades associadas.</p>
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

.edit-grid select,
.edit-grid textarea {
    grid-column: 1 / -1;
}

.edit-grid textarea {
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

.function-badge {
    margin-top: 6px;
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    background: #e7faf5;
    color: #0f766e;
}

.summary-meta {
    display: grid;
    justify-items: end;
    text-align: right;
    gap: 6px;
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

.entity-link {
    color: #2a9d8f;
    font-size: 0.85rem;
    text-decoration: none;
    font-weight: 600;
}

.entity-link:hover {
    text-decoration: underline;
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
