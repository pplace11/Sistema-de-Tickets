<template>
    <div class="auth-shell">
        <div class="auth-frame">
            <section class="auth-showcase">
                <div class="showcase-inner">
                    <p class="eyebrow">TicketFlow</p>
                    <div class="showcase-copy">
                        <h1>Uma entrada mais clara para equipas, clientes e operacoes.</h1>
                        <p>
                            Centraliza pedidos, respostas e acompanhamento num ambiente consistente,
                            profissional e pronto para o dia a dia da tua organizacao.
                        </p>
                    </div>

                    <div class="showcase-metrics">
                        <article>
                            <strong>3</strong>
                            <span>inboxes ativas por area</span>
                        </article>
                        <article>
                            <strong>24/7</strong>
                            <span>historico sempre acessivel</span>
                        </article>
                        <article>
                            <strong>1</strong>
                            <span>fluxo unico para tickets internos e externos</span>
                        </article>
                    </div>

                    <div class="showcase-footer">
                        <span>Permissoes por funcao</span>
                        <span>Entidades e contactos ligados ao ticket</span>
                        <span>Notificacoes automaticas</span>
                    </div>
                </div>
            </section>

            <section class="auth-panel">
                <div class="panel-header">
                    <div class="panel-brand">
                        <span class="brand-dot"></span>
                        <div>
                            <p class="brand-name">TicketFlow</p>
                            <p class="brand-subtitle">Workspace de autenticacao</p>
                        </div>
                    </div>
                    <p class="panel-note">Acesso seguro para operadores e clientes.</p>
                </div>

                <div class="auth-card">
                    <slot />
                </div>
            </section>
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
        radial-gradient(circle at top left, rgba(43, 142, 145, 0.22), transparent 28%),
        radial-gradient(circle at 85% 20%, rgba(15, 56, 88, 0.18), transparent 24%),
        linear-gradient(160deg, #f4f8fb 0%, #eaf1f5 52%, #f3f6f8 100%);
}

.auth-frame {
    width: min(1180px, 100%);
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(420px, 0.9fr);
    background: rgba(255, 255, 255, 0.76);
    border: 1px solid rgba(206, 220, 229, 0.72);
    border-radius: 34px;
    overflow: hidden;
    box-shadow: 0 32px 90px rgba(15, 33, 52, 0.12);
    backdrop-filter: blur(20px);
}

.eyebrow {
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.24em;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.78);
    font-weight: 700;
}

.auth-showcase {
    position: relative;
    padding: 54px;
    color: #fff;
    background:
        linear-gradient(150deg, rgba(12, 34, 58, 0.98) 0%, rgba(16, 60, 88, 0.96) 48%, rgba(42, 146, 142, 0.94) 100%);
}

.auth-showcase::before,
.auth-showcase::after {
    content: '';
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    opacity: 0.28;
}

.auth-showcase::before {
    width: 340px;
    height: 340px;
    top: -110px;
    right: -70px;
    background: rgba(255, 255, 255, 0.12);
}

.auth-showcase::after {
    width: 260px;
    height: 260px;
    bottom: -90px;
    left: -70px;
    background: rgba(255, 255, 255, 0.08);
}

.showcase-inner {
    position: relative;
    z-index: 1;
    display: grid;
    gap: 32px;
    min-height: 100%;
}

.showcase-copy {
    display: grid;
    gap: 14px;
    max-width: 46ch;
}

.showcase-copy h1 {
    margin: 0;
    font-size: clamp(2.8rem, 4vw, 4.5rem);
    line-height: 0.95;
    letter-spacing: -0.05em;
    max-width: 10ch;
}

.showcase-copy p {
    margin: 0;
    font-size: 1.04rem;
    line-height: 1.72;
    color: rgba(255, 255, 255, 0.82);
}

.showcase-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.showcase-metrics article {
    display: grid;
    gap: 8px;
    padding: 18px;
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.10);
}

.showcase-metrics strong {
    font-size: 1.45rem;
}

.showcase-metrics span {
    color: rgba(255, 255, 255, 0.76);
    line-height: 1.45;
}

.showcase-footer {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: auto;
}

.showcase-footer span {
    display: inline-flex;
    align-items: center;
    padding: 9px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.10);
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.84rem;
}

.auth-panel {
    padding: 34px;
    display: grid;
    align-content: center;
    gap: 22px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.96));
}

.panel-header {
    display: grid;
    gap: 10px;
}

.panel-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-dot {
    width: 14px;
    height: 14px;
    border-radius: 999px;
    background: linear-gradient(135deg, #2a9d8f, #16324f);
    box-shadow: 0 0 0 8px rgba(42, 157, 143, 0.10);
}

.brand-name,
.brand-subtitle,
.panel-note {
    margin: 0;
}

.brand-name {
    font-size: 0.92rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #18314f;
}

.brand-subtitle {
    color: #64748b;
    font-size: 0.9rem;
}

.panel-note {
    color: #52616b;
    font-size: 0.96rem;
}

.auth-card {
    width: min(100%, 470px);
    background: #fff;
    border: 1px solid #e4ebf1;
    border-radius: 28px;
    padding: 34px;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06);
}

@media (max-width: 980px) {
    .auth-frame {
        grid-template-columns: 1fr;
    }

    .auth-showcase {
        padding: 34px 28px;
    }

    .showcase-metrics {
        grid-template-columns: 1fr;
    }

    .showcase-copy h1 {
        max-width: 12ch;
    }

    .auth-panel {
        padding: 24px;
    }
}

@media (max-width: 640px) {
    .auth-shell {
        padding: 14px;
    }

    .auth-frame,
    .auth-card {
        border-radius: 24px;
    }

    .auth-showcase,
    .auth-panel,
    .auth-card {
        padding: 22px;
    }

    .showcase-copy h1 {
        font-size: 2.5rem;
    }

    .panel-note {
        font-size: 0.92rem;
    }
}
</style>
