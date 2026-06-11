<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';

const entities = ref([]);
const search = ref('');
const form = ref({ nif: '', name: '', phone: '', mobile: '', website: '', email: '', internal_notes: '' });

const load = async () => {
    const { data } = await api.get('/entities', { params: { search: search.value || undefined } });
    entities.value = data.data;
};

const createEntity = async () => {
    await api.post('/entities', form.value);
    form.value = { nif: '', name: '', phone: '', mobile: '', website: '', email: '', internal_notes: '' };
    await load();
};

onMounted(load);
</script>

<template>
    <section class="panel">
        <h2>Entidades</h2>
        <div class="toolbar">
            <input v-model="search" placeholder="Pesquisar por nome, NIF ou email" @keyup.enter="load" />
            <button @click="load">Pesquisar</button>
        </div>

        <div class="grid">
            <input v-model="form.nif" placeholder="NIF" />
            <input v-model="form.name" placeholder="Nome" />
            <input v-model="form.phone" placeholder="Telefone" />
            <input v-model="form.mobile" placeholder="Telemovel" />
            <input v-model="form.website" placeholder="Website" />
            <input v-model="form.email" placeholder="Email" />
            <textarea v-model="form.internal_notes" placeholder="Notas internas"></textarea>
            <button @click="createEntity">Criar Entidade</button>
        </div>

        <ul class="list">
            <li v-for="entity in entities" :key="entity.id">
                <strong>{{ entity.name }}</strong>
                <span>{{ entity.nif }}</span>
                <span>{{ entity.email || '-' }}</span>
            </li>
        </ul>
    </section>
</template>

<style scoped>
.panel { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
.toolbar { display: flex; gap: 8px; margin-bottom: 12px; }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 20px; }
.grid textarea { grid-column: 1 / -1; min-height: 80px; }
.list { list-style: none; padding: 0; margin: 0; display: grid; gap: 8px; }
.list li { padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; display: grid; gap: 2px; }
input, textarea, button { padding: 10px; border-radius: 10px; border: 1px solid #cbd5e1; }
button { background: #2a9d8f; color: white; border: 0; }
@media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
</style>
