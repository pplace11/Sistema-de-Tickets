<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainLayout from './components/MainLayout.vue';
import AuthLayout from './layouts/AuthLayout.vue';

const route = useRoute();
const isAuthPage = computed(() => route.meta.authLayout === true);
const isPublicLanding = computed(() => route.meta.landingLayout === true);
</script>

<template>
    <router-view v-if="isPublicLanding" />

    <AuthLayout v-else-if="isAuthPage">
        <router-view />
    </AuthLayout>

    <MainLayout v-else>
        <router-view />
    </MainLayout>
</template>
