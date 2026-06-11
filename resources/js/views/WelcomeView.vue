<template>
    <div class="wrap">
        <div class="card">
            <div class="hero">
                <section class="left">
                    <div class="brand">TicketFlow</div>
                    <h1>Gestao centralizada de tickets</h1>
                    <p class="lead">
                        Sistema interno e externo para gerir pedidos, comunicacoes e historico completo por inbox,
                        com foco em Comercial, Apoio Tecnico e Recursos Humanos.
                    </p>

                    <div class="actions">
                        <RouterLink class="btn primary" to="/login">Entrar no sistema</RouterLink>
                        <RouterLink class="btn secondary" to="/register">Criar conta</RouterLink>
                    </div>

                    <div class="meta">
                        <div class="stat">
                            <strong>3 inboxes</strong>
                            <span>Comercial, Apoio Tecnico e RH com operadores dedicados.</span>
                        </div>
                        <div class="stat">
                            <strong>TC-000</strong>
                            <span>Numeracao sequencial para rastreio rapido de cada pedido.</span>
                        </div>
                        <div class="stat">
                            <strong>Historico completo</strong>
                            <span>Mensagens, anexos, respostas e log de atividade num unico lugar.</span>
                        </div>
                    </div>
                </section>

                <section class="right">
                    <p class="section-title">Funcionalidades</p>
                    <div class="feature-grid">
                        <article class="feature">
                            <h3>Tickets internos e externos</h3>
                            <p>Clientes e operadores podem abrir, responder e acompanhar tickets ligados as suas entidades.</p>
                        </article>
                        <article class="feature">
                            <h3>Filtros inteligentes</h3>
                            <p>Pesquisa por inbox, estado, operador, tipo, entidade, assunto, email e numero do ticket.</p>
                        </article>
                        <article class="feature">
                            <h3>Notificacoes por email</h3>
                            <p>Alertas para criador, contactos em conhecimento e operador associado, com base em templates.</p>
                        </article>
                    </div>

                    <ul class="feature-list">
                        <li><span class="dot"></span> Operadores com permissao por inbox</li>
                        <li><span class="dot"></span> Entidades, contactos e funcoes associados</li>
                        <li><span class="dot"></span> Respostas com texto, imagens e anexos</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.wrap {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 24px;
}

.card {
    width: min(100%, 1180px);
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(18px);
    border: 1px solid rgba(255, 255, 255, 0.75);
    border-radius: 32px;
    box-shadow: 0 30px 80px rgba(22, 50, 79, 0.12);
    overflow: hidden;
}

.hero {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
}

.left {
    padding: 56px;
    background: linear-gradient(135deg, rgba(29, 53, 87, 0.98), rgba(42, 157, 143, 0.96));
    color: #fff;
    position: relative;
    isolation: isolate;
}

.left::before,
.left::after {
    content: '';
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    opacity: 0.35;
}

.left::before {
    width: 280px;
    height: 280px;
    background: rgba(255, 255, 255, 0.1);
    top: -80px;
    right: -60px;
}

.left::after {
    width: 220px;
    height: 220px;
    background: rgba(255, 255, 255, 0.08);
    bottom: -70px;
    left: -70px;
}

.brand {
    letter-spacing: 0.28em;
    text-transform: uppercase;
    font-weight: 700;
    font-size: 0.78rem;
    margin-bottom: 28px;
    color: rgba(255, 255, 255, 0.85);
}

h1 {
    margin: 0;
    font-size: clamp(2.7rem, 6vw, 5.2rem);
    line-height: 0.95;
    letter-spacing: -0.05em;
    max-width: 10ch;
}

.lead {
    margin: 22px 0 0;
    max-width: 58ch;
    color: rgba(255, 255, 255, 0.86);
    font-size: 1.08rem;
    line-height: 1.7;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 34px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 20px;
    border-radius: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn.primary {
    background: #fff;
    color: #1d3557;
    box-shadow: 0 12px 30px rgba(0, 0, 0, .14);
}

.btn.secondary {
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}

.meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 42px;
}

.stat {
    padding: 16px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat strong {
    display: block;
    font-size: 1.1rem;
    margin-bottom: 6px;
}

.stat span {
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.95rem;
    line-height: 1.5;
}

.right {
    padding: 56px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(244, 247, 248, 0.95));
}

.section-title {
    margin: 0 0 18px;
    font-size: 1.02rem;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: #2a9d8f;
    font-weight: 700;
}

.feature-grid {
    display: grid;
    gap: 14px;
}

.feature {
    padding: 18px 18px 18px 20px;
    border-radius: 18px;
    border: 1px solid rgba(22, 50, 79, 0.1);
    background: rgba(255, 255, 255, 0.78);
    box-shadow: 0 8px 20px rgba(22, 50, 79, 0.04);
}

.feature h3 {
    margin: 0 0 8px;
    font-size: 1.02rem;
    color: #1d3557;
}

.feature p {
    margin: 0;
    color: #5f6b7a;
    line-height: 1.6;
}

.feature-list {
    margin: 18px 0 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 10px;
}

.feature-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #16324f;
    padding: 12px 14px;
    border-radius: 14px;
    background: rgba(29, 53, 87, 0.04);
    border: 1px solid rgba(29, 53, 87, 0.06);
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: linear-gradient(135deg, #2a9d8f, #1d3557);
    flex: 0 0 auto;
}

@media (max-width: 960px) {
    .hero {
        grid-template-columns: 1fr;
    }

    .left,
    .right {
        padding-left: 24px;
        padding-right: 24px;
    }

    .left {
        padding-top: 28px;
    }

    .meta {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .wrap {
        padding: 12px;
    }

    .card {
        border-radius: 24px;
    }

    h1 {
        max-width: 100%;
    }

    .actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>
