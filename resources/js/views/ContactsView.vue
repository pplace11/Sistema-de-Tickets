<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';

const contacts = ref([]);
const entities = ref([]);
const functions = ref([]);
const search = ref('');

const form = ref({
    name: '', email: '', phone: '', mobile: '', internal_notes: '', contact_function_id: '', entity_ids: [],
});

const load = async () => {
    const [{ data: c }, { data: e }, { data: f }] = await Promise.all([
        api.get('/contacts', { params: { search: search.value || undefined } }),
        api.get('/entities'),
        api.get('/contact-functions'),
    ]);

    contacts.value = c.data;
    entities.value = e.data;
    functions.value = f;
};

const createContact = async () => {
    await api.post('/contacts', {
        ...form.value,
        contact_function_id: form.value.contact_function_id || null,
    });
    form.value = { name: '', email: '', phone: '', mobile: '', internal_notes: '', contact_function_id: '', entity_ids: [] };
    await load();
};

onMounted(load);
</script>

<template>
    <section class="panel">
        <h2>Contactos</h2>
        <div class="toolbar">
            <input v-model="search" placeholder="Pesquisar por nome ou email" @keyup.enter="load" />
            <button @click="load">Pesquisar</button>
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
            <button @click="createContact">Criar Contacto</button>
        </div>

        <ul class="list">
            <li v-for="contact in contacts" :key="contact.id">
                <strong>{{ contact.name }}</strong>
                <span>{{ contact.email }}</span>
            </li>
        </ul>
    </section>
</template>

<style scoped>
.panel { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
.toolbar { display: flex; gap: 8px; margin-bottom: 12px; }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 20px; }
.grid textarea, .grid select { grid-column: 1 / -1; min-height: 80px; }
.list { list-style: none; padding: 0; margin: 0; display: grid; gap: 8px; }
.list li { padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; display: grid; gap: 2px; }
input, textarea, button, select { padding: 10px; border-radius: 10px; border: 1px solid #cbd5e1; }
button { background: #2a9d8f; color: white; border: 0; }
@media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
</style>
