<template>
    <div class="wrap">
        <div class="card">
            <div class="hero">
                <section class="left">
                    <div class="topbar">
                        <div class="brand-block">
                            <div class="brand">TicketFlow</div>
                            <p class="brand-note">Plataforma de suporte interno e externo</p>
                        </div>
                        <div class="pill">Multidepartamento</div>
                    </div>

                    <div class="headline-block">
                        <p class="eyebrow">Operacao centralizada</p>
                        <h1>Gestao profissional de tickets para equipas e clientes</h1>
                        <p class="lead">
                            Sistema unificado para gerir pedidos, comunicacoes e historico completo por inbox,
                            com operacao dedicada para Comercial, Apoio Tecnico e Recursos Humanos.
                        </p>
                    </div>

                    <div class="actions">
                        <RouterLink class="btn primary" to="/login">Entrar no sistema</RouterLink>
                        <RouterLink class="btn secondary" to="/register">Criar conta</RouterLink>
                    </div>

                    <div class="trust-strip">
                        <span>Fluxos por inbox</span>
                        <span>Tickets internos e externos</span>
                        <span>Notificacoes por email</span>
                    </div>

                    <div class="meta">
                        <div class="stat accent-dark">
                            <small>Inboxes operacionais</small>
                            <strong>3 areas ativas</strong>
                            <span>Comercial, Apoio Tecnico e RH com equipas e regras proprias.</span>
                        </div>
                        <div class="stat accent-teal">
                            <small>Numeracao controlada</small>
                            <strong>Serie TC-000</strong>
                            <span>Identificacao sequencial para rastreio e resposta mais rapida.</span>
                        </div>
                        <div class="stat accent-light">
                            <small>Rastreabilidade total</small>
                            <strong>Historico consolidado</strong>
                            <span>Mensagens, anexos, respostas e logs reunidos numa so vista.</span>
                        </div>
                    </div>
                </section>

                <section class="right">
                    <div class="right-head">
                        <div>
                            <p class="section-title">Funcionalidades</p>
                            <h2 class="side-title">Estrutura pensada para operacao real</h2>
                        </div>
                        <div class="side-badge">Pronto para uso</div>
                    </div>

                    <div class="feature-grid">
                        <article class="feature feature-large">
                            <div class="feature-icon">01</div>
                            <div>
                                <h3>Tickets internos e externos</h3>
                                <p>Clientes e operadores podem abrir, responder e acompanhar tickets ligados as suas entidades.</p>
                            </div>
                        </article>
                        <article class="feature">
                            <div class="feature-icon">02</div>
                            <div>
                                <h3>Filtros inteligentes</h3>
                                <p>Pesquisa por inbox, estado, operador, tipo, entidade, assunto, email e numero do ticket.</p>
                            </div>
                        </article>
                        <article class="feature">
                            <div class="feature-icon">03</div>
                            <div>
                                <h3>Notificacoes por email</h3>
                                <p>Alertas para criador, contactos em conhecimento e operador associado, com base em templates.</p>
                            </div>
                        </article>
                    </div>

                    <div class="capabilities">
                        <div class="capability">
                            <span class="dot"></span>
                            <div>
                                <strong>Permissoes segmentadas</strong>
                                <p>Operadores com permissao por inbox e contexto de trabalho separado.</p>
                            </div>
                        </div>
                        <div class="capability">
                            <span class="dot"></span>
                            <div>
                                <strong>Base relacional de clientes</strong>
                                <p>Entidades, contactos e funcoes associados ao ticket certo.</p>
                            </div>
                        </div>
                        <div class="capability">
                            <span class="dot"></span>
                            <div>
                                <strong>Respostas completas</strong>
                                <p>Texto, imagens, anexos e historico continuo por pedido.</p>
                            </div>
                        </div>
                    </div>
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
    padding: 28px;
    background:
        radial-gradient(circle at top left, rgba(42, 157, 143, 0.08), transparent 26%),
        radial-gradient(circle at bottom right, rgba(29, 53, 87, 0.08), transparent 30%),
        linear-gradient(180deg, #f4f7fa 0%, #eef3f7 100%);
}

.card {
    width: min(100%, 1180px);
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(18px);
    border: 1px solid rgba(217, 227, 235, 0.9);
    border-radius: 32px;
    box-shadow: 0 28px 80px rgba(22, 50, 79, 0.10);
    overflow: hidden;
}

.hero {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(360px, 0.92fr);
}

.left {
    padding: 52px;
    background:
        linear-gradient(160deg, rgba(18, 37, 61, 0.98) 0%, rgba(24, 64, 92, 0.98) 46%, rgba(43, 142, 145, 0.96) 100%);
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
    width: 320px;
    height: 320px;
    background: rgba(255, 255, 255, 0.08);
    top: -96px;
    right: -76px;
}

.left::after {
    width: 240px;
    height: 240px;
    background: rgba(255, 255, 255, 0.06);
    bottom: -84px;
    left: -84px;
}

.topbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 34px;
}

.brand-block {
    display: grid;
    gap: 8px;
}

.brand {
    letter-spacing: 0.28em;
    text-transform: uppercase;
    font-weight: 700;
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.85);
}

.brand-note {
    margin: 0;
    color: rgba(255, 255, 255, 0.68);
    font-size: 0.92rem;
}

.pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: rgba(255, 255, 255, 0.92);
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.headline-block {
    display: grid;
    gap: 12px;
}

.eyebrow {
    margin: 0;
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.86rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    font-weight: 700;
}

h1 {
    margin: 0;
    font-size: clamp(3rem, 5vw, 4.8rem);
    line-height: 0.96;
    letter-spacing: -0.06em;
    max-width: 11ch;
}

.lead {
    margin: 4px 0 0;
    max-width: 52ch;
    color: rgba(255, 255, 255, 0.86);
    font-size: 1.02rem;
    line-height: 1.72;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 32px;
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
    transform: translateY(-2px);
}

.btn.primary {
    background: #fff;
    color: #1d3557;
    box-shadow: 0 16px 30px rgba(0, 0, 0, .12);
}

.btn.secondary {
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}

.trust-strip {
    margin-top: 24px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.trust-strip span {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.84rem;
}

.meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 42px;
}

.stat {
    padding: 18px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.10);
    background: rgba(255, 255, 255, 0.08);
    display: grid;
    gap: 6px;
}

.stat small {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
}

.stat strong {
    display: block;
    font-size: 1.08rem;
}

.stat span {
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.92rem;
    line-height: 1.5;
}

.accent-dark {
    background: rgba(255, 255, 255, 0.09);
}

.accent-teal {
    background: rgba(105, 230, 220, 0.10);
}

.accent-light {
    background: rgba(255, 255, 255, 0.13);
}

.right {
    padding: 48px;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(246, 249, 251, 0.98));
    display: grid;
    align-content: start;
    gap: 18px;
}

.section-title {
    margin: 0;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: #2a9d8f;
    font-weight: 700;
}

.right-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.side-title {
    margin: 10px 0 0;
    color: #102a43;
    font-size: 1.9rem;
    line-height: 1.05;
    letter-spacing: -0.04em;
    max-width: 12ch;
}

.side-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 12px;
    border-radius: 999px;
    background: #edf7f5;
    color: #1b6f69;
    border: 1px solid #d7ebe7;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.feature-grid {
    display: grid;
    gap: 14px;
}

.feature {
    padding: 18px;
    border-radius: 20px;
    border: 1px solid rgba(22, 50, 79, 0.08);
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 10px 24px rgba(22, 50, 79, 0.04);
    display: grid;
    grid-template-columns: 52px 1fr;
    gap: 14px;
    align-items: start;
}

.feature-large {
    background: linear-gradient(180deg, #ffffff 0%, #f7fbfb 100%);
}

.feature-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #16324f, #2a9d8f);
    color: #fff;
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.08em;
}

.feature h3 {
    margin: 0 0 8px;
    font-size: 1rem;
    color: #16324f;
}

.feature p {
    margin: 0;
    color: #5f6b7a;
    line-height: 1.6;
}

.capabilities {
    display: grid;
    gap: 10px;
}

.capability {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    color: #16324f;
    padding: 14px;
    border-radius: 16px;
    background: rgba(29, 53, 87, 0.035);
    border: 1px solid rgba(29, 53, 87, 0.06);
}

.capability strong {
    display: block;
    margin-bottom: 4px;
    color: #16324f;
}

.capability p {
    margin: 0;
    color: #5f6b7a;
    line-height: 1.55;
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

    .right-head {
        flex-direction: column;
    }

    .side-title {
        max-width: none;
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

    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .feature {
        grid-template-columns: 1fr;
    }
}
</style>
