<template>
    <div class="auth-shell">
        <div class="auth-card">
            <p class="eyebrow">TicketFlow</p>
            <slot />
        </div>
    </div>
</template>

<style scoped>
.auth-shell {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 24px;
    background:
        radial-gradient(circle at top left, rgba(42, 157, 143, 0.35), transparent 32%),
        radial-gradient(circle at bottom right, rgba(29, 53, 87, 0.3), transparent 28%),
        linear-gradient(135deg, #f7f9fb 0%, #edf6f4 100%);
}

.auth-card {
    width: min(100%, 460px);
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.7);
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12);
}

.eyebrow {
    margin: 0 0 18px;
    text-transform: uppercase;
    letter-spacing: 0.22em;
    font-size: 0.75rem;
    color: #2a9d8f;
    font-weight: 700;
}
</style>
