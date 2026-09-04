/* Regras de tela e interações do Condomínio Connect. */
(function () {
  "use strict";

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const categoryIcons = {
    "Elétrica": "⚡",
    "Hidráulica": "◈",
    "Pintura": "▨",
    "Reformas": "◆",
    "Limpeza": "✦",
    "Manutenção": "●",
    "Gesso": "▤",
    "Marcenaria": "▥",
  };
  const categoryDefaults = {
    "Elétrica": ["Reparo elétrico", "Ex.: tomadas sem energia e disjuntor desarmando."],
    "Hidráulica": ["Reparo de vazamento", "Ex.: vazamento sob a pia mesmo com o registro fechado."],
    "Pintura": ["Pintura de ambiente", "Ex.: pintura da sala com correção de manchas e acabamento."],
    "Reformas": ["Pequena reforma", "Ex.: reforma do banheiro com revestimento e acabamento."],
    "Limpeza": ["Limpeza pós-obra", "Ex.: limpeza de pisos, vidros e retirada de resíduos leves."],
    "Manutenção": ["Manutenção preventiva", "Ex.: revisão de portas, fechaduras e equipamentos."],
    "Gesso": ["Reparo em gesso", "Ex.: correção de trinca no forro e acabamento da área."],
    "Marcenaria": ["Reparo de marcenaria", "Ex.: porta de armário desalinhada e troca de dobradiça."],
  };
  const roleLabels = {
    cliente: "Morador / síndico",
    profissional: "Profissional",
    administrador: "Administrador",
  };
  const state = {
    user: null,
    categories: [],
    professionals: [],
    requests: [],
    admin: null,
    route: "home",
    adminTab: "validations",
    activeCategory: "",
    activeConversation: null,
    validationChecks: {},
  };

  function escapeHtml(value) {
    return String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function initials(name) {
    return String(name || "Usuário")
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part[0])
      .join("")
      .toUpperCase();
  }

  function prettyStatus(status) {
    const labels = {
      publicada: "Publicado",
      visita_agendada: "Visita agendada",
      aguardando_orcamento: "Aguardando orçamento",
      orcamento_recebido: "Orçamento recebido",
      orcamento_aprovado: "Orçamento aprovado",
      em_execucao: "Em execução",
      aguardando_confirmacao: "Aguardando confirmação",
      concluida: "Concluído",
      cancelada: "Cancelado",
      em_analise: "Em análise",
      aprovado: "Aprovado",
      recusado: "Recusado",
      ativo: "Ativo",
      pendente: "Pendente",
      bloqueado: "Bloqueado",
    };
    return labels[status] || String(status || "-").replaceAll("_", " ");
  }

  function statusClass(status) {
    if (["aprovado", "ativo", "concluida", "pago", "liberado"].includes(status)) return "badge badge--success";
    if (["recusado", "cancelada", "bloqueado", "falhou"].includes(status)) return "badge badge--danger";
    if (["pendente", "em_analise", "aguardando_orcamento", "orcamento_recebido"].includes(status)) return "badge badge--warning";
    return "badge";
  }

  function formatDate(value) {
    if (!value) return "-";
    const date = new Date(String(value).replace(" ", "T"));
    if (Number.isNaN(date.getTime())) return String(value);
    return new Intl.DateTimeFormat("pt-BR", { dateStyle: "short", timeStyle: "short" }).format(date);
  }

  function money(value) {
    return Number(value || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }

  function setLoading(active) {
    $("#loading-layer").classList.toggle("is-hidden", !active);
  }

  function toast(message, type = "info") {
    const element = document.createElement("div");
    element.className = `toast toast--${type}`;
    element.textContent = message;
    $("#toast-region").append(element);
    window.setTimeout(() => element.remove(), 4000);
  }

  function setFormMessage(selector, message, success = false) {
    const element = $(selector);
    element.textContent = message;
    element.classList.toggle("is-success", success);
  }

  function openDetails(title, entries) {
    $("#details-title").textContent = title;
    $("#details-content").innerHTML = entries
      .map(([label, value]) => `<div class="details-row"><dt>${escapeHtml(label)}</dt><dd>${escapeHtml(value ?? "-")}</dd></div>`)
      .join("");
    $("#details-dialog").showModal();
  }

  function pageHeader(eyebrow, title, description, action = "") {
    return `<header class="page-header"><div><span class="eyebrow">${escapeHtml(eyebrow)}</span><h1>${escapeHtml(title)}</h1><p>${escapeHtml(description)}</p></div>${action}</header>`;
  }

  function categoryCard(category) {
    return `<button class="card card--clickable" type="button" data-action="category" data-id="${category.id}">
      <div class="card__top"><span class="card__icon">${categoryIcons[category.nome] || "◇"}</span><span class="badge">${category.profissionais} profissionais</span></div>
      <div class="card__body"><h3>${escapeHtml(category.nome)}</h3><p>${escapeHtml(category.descricao)}</p></div>
    </button>`;
  }

  function professionalCard(professional) {
    return `<article class="card professional-card">
      <div class="professional-card__header">
        <span class="avatar avatar--large">${initials(professional.nome)}</span>
        <div><h3>${escapeHtml(professional.nome)}</h3><p>${escapeHtml(professional.categoria)} · ${Number(professional.anos_experiencia)} anos de experiência</p></div>
      </div>
      <div class="card__body"><p>${escapeHtml(professional.descricao)}</p></div>
      <div class="card__footer"><span><strong>${Number(professional.media_avaliacao).toFixed(1).replace(".", ",")}</strong> ★ · ${professional.quantidade_avaliacoes} avaliações</span><button class="button button--secondary" data-action="professional-details" data-id="${professional.id}">Ver perfil</button></div>
    </article>`;
  }

  function requestRow(request, options = {}) {
    return `<article class="list-row">
      <span class="card__icon">${categoryIcons[request.categoria] || "◇"}</span>
      <div class="list-row__content"><h3>${escapeHtml(request.titulo)}</h3><p>${escapeHtml(request.codigo)} · ${escapeHtml(request.categoria)} · ${escapeHtml(request.local_atendimento)}</p><p>${escapeHtml(request.descricao)}</p></div>
      <span class="${statusClass(request.status)}">${escapeHtml(prettyStatus(request.status))}</span>
      ${options.chat && request.conversa_id ? `<button class="button button--secondary" data-action="open-chat" data-request-id="${request.id}">Abrir chat</button>` : ""}
      <button class="button button--ghost" data-action="request-details" data-id="${request.id}">Detalhes</button>
    </article>`;
  }

  const navByRole = {
    cliente: [
      ["home", "Início"], ["professionals", "Profissionais"], ["services", "Serviços"], ["messages", "Mensagens"], ["profile", "Perfil"],
    ],
    profissional: [
      ["home", "Painel"], ["opportunities", "Oportunidades"], ["agenda", "Agenda"], ["messages", "Mensagens"], ["profile", "Perfil"],
    ],
    administrador: [["admin", "Administração"], ["profile", "Perfil"]],
  };

  function renderNavigation() {
    const items = navByRole[state.user.tipo];
    const render = ([route, label]) => `<button class="nav-button ${state.route === route || (route === "admin" && state.route === "home") ? "is-active" : ""}" type="button" data-route="${route}">${escapeHtml(label)}</button>`;
    $("#desktop-nav").innerHTML = items.map(render).join("");
    $("#mobile-nav").innerHTML = items.map(render).join("");
  }

  async function loadApplicationData() {
    const tasks = [CondoApi.categories(), CondoApi.requests()];
    if (state.user.tipo !== "administrador") tasks.push(CondoApi.professionals());
    if (state.user.tipo === "administrador") tasks.push(CondoApi.admin());
    const [categoriesResult, requestsResult, thirdResult] = await Promise.all(tasks);
    state.categories = categoriesResult.categories;
    state.requests = requestsResult.requests;
    if (state.user.tipo === "administrador") state.admin = thirdResult;
    else state.professionals = thirdResult.professionals;
  }

  function showAuth() {
    state.user = null;
    $("#auth-view").classList.remove("is-hidden");
    $("#app-view").classList.add("is-hidden");
  }

  async function showApplication(user) {
    state.user = user;
    state.route = user.tipo === "administrador" ? "admin" : "home";
    $("#auth-view").classList.add("is-hidden");
    $("#app-view").classList.remove("is-hidden");
    $("#user-initials").textContent = initials(user.nome);
    $("#user-role").textContent = roleLabels[user.tipo] || user.tipo;
    setLoading(true);
    try {
      await loadApplicationData();
      render();
    } catch (error) {
      toast(error.message, "error");
      $("#page-content").innerHTML = pageHeader("Configuração necessária", "Banco de dados indisponível", "Importe o arquivo SQL e confira config/database.php.");
    } finally {
      setLoading(false);
    }
  }

  function renderClientHome() {
    const recent = state.requests.slice(0, 3);
    const highlighted = state.professionals.slice(0, 3);
    return `<section class="hero-card">
      <div class="hero-card__copy"><span class="eyebrow eyebrow--light">Olá, ${escapeHtml(state.user.nome.split(" ")[0])}</span><h1>Qual serviço seu condomínio precisa?</h1><p>Abra um chamado, encontre profissionais validados e acompanhe todo o atendimento.</p><div class="hero-card__actions"><button class="button button--primary" data-route="new-request">Abrir chamado</button><button class="button button--secondary" data-route="professionals">Ver profissionais</button></div></div>
      <div class="hero-card__visual"><span class="hero-card__badge">CC</span></div>
    </section>
    <div class="section-heading"><div><span class="eyebrow">Categorias</span><h2>Escolha o tipo de serviço</h2></div></div>
    <section class="grid grid--4">${state.categories.map(categoryCard).join("")}</section>
    <div class="section-heading"><div><span class="eyebrow">Em destaque</span><h2>Profissionais validados</h2></div><button class="button button--ghost" data-route="professionals">Ver todos</button></div>
    <section class="grid grid--3">${highlighted.map(professionalCard).join("")}</section>
    <div class="section-heading"><div><span class="eyebrow">Acompanhamento</span><h2>Seus chamados recentes</h2></div><button class="button button--ghost" data-route="services">Ver histórico</button></div>
    <section class="list">${recent.length ? recent.map((item) => requestRow(item, { chat: true })).join("") : '<div class="empty-state"><strong>Nenhum chamado encontrado</strong>Abra seu primeiro chamado para iniciar.</div>'}</section>`;
  }

  function renderProfessionalHome() {
    const open = state.requests.filter((item) => item.status === "publicada");
    const active = state.requests.filter((item) => item.status !== "publicada" && !["concluida", "cancelada"].includes(item.status));
    return `${pageHeader("Área profissional", `Olá, ${state.user.nome.split(" ")[0]}`, "Acompanhe oportunidades, agenda e serviços em andamento.", '<span class="badge badge--success">Cadastro validado</span>')}
      <section class="grid grid--4"><article class="card metric"><span>Novos chamados</span><strong>${open.length}</strong></article><article class="card metric"><span>Em andamento</span><strong>${active.length}</strong></article><article class="card metric"><span>Conversas</span><strong>${state.requests.filter((item) => item.conversa_id).length}</strong></article><article class="card metric"><span>Avaliação</span><strong>4,9</strong></article></section>
      <div class="section-heading"><div><span class="eyebrow">Oportunidades</span><h2>Novos chamados</h2></div><button class="button button--ghost" data-route="opportunities">Ver todos</button></div>
      <section class="list">${open.slice(0, 4).map((item) => requestRow(item, { chat: true })).join("") || '<div class="empty-state"><strong>Nenhum chamado novo</strong>Novas oportunidades aparecerão aqui.</div>'}</section>`;
  }

  function renderProfessionals() {
    const options = state.categories.map((item) => `<option value="${item.id}" ${String(item.id) === String(state.activeCategory) ? "selected" : ""}>${escapeHtml(item.nome)}</option>`).join("");
    const list = state.activeCategory
      ? state.professionals.filter((item) => String(item.categoria_id) === String(state.activeCategory))
      : state.professionals;
    return `${pageHeader("Rede validada", "Encontre um profissional", "Filtre por categoria e consulte experiência, avaliação e taxa de visita.")}
      <div class="toolbar"><select id="professional-filter" aria-label="Filtrar por categoria"><option value="">Todas as categorias</option>${options}</select><span class="muted">${list.length} resultado(s)</span></div>
      <section class="grid grid--3">${list.length ? list.map(professionalCard).join("") : '<div class="empty-state"><strong>Nenhum profissional disponível</strong>Escolha outra categoria.</div>'}</section>`;
  }

  function renderNewRequest() {
    const options = state.categories.map((item) => `<option value="${item.id}" ${String(item.id) === String(state.activeCategory) ? "selected" : ""}>${escapeHtml(item.nome)}</option>`).join("");
    const category = state.categories.find((item) => String(item.id) === String(state.activeCategory)) || state.categories[0];
    const defaults = categoryDefaults[category?.nome] || ["Novo serviço", "Descreva o serviço necessário."];
    return `${pageHeader("Novo chamado", "Descreva o serviço", "As informações serão exibidas somente para profissionais da categoria escolhida.")}
      <form id="request-form" class="card request-form"><div class="form-grid">
        <label>Categoria<select id="request-category" name="categoria_id" required>${options}</select></label>
        <label>Prioridade<select name="prioridade"><option value="normal">Normal</option><option value="baixa">Baixa</option><option value="alta">Alta</option><option value="urgente">Urgente</option></select></label>
        <label class="form-grid__full">Título<input id="request-title" name="titulo" maxlength="160" value="${escapeHtml(defaults[0])}" required></label>
        <label class="form-grid__full">Descrição<textarea id="request-description" name="descricao" rows="5" maxlength="3000" placeholder="${escapeHtml(defaults[1])}" required></textarea></label>
        <label class="form-grid__full">Local do atendimento<input name="local_atendimento" maxlength="220" value="Edifício Solar · Bloco B · Apartamento 402" required></label>
      </div><footer class="dialog__footer"><button class="button button--secondary" type="button" data-route="home">Cancelar</button><button class="button button--primary" type="submit">Publicar chamado</button></footer></form>`;
  }

  function renderServices() {
    return `${pageHeader("Histórico", "Meus serviços", "Consulte chamados publicados, propostas recebidas e atendimentos concluídos.", '<button class="button button--primary" data-route="new-request">Novo chamado</button>')}
      <section class="list">${state.requests.length ? state.requests.map((item) => requestRow(item, { chat: true })).join("") : '<div class="empty-state"><strong>Nenhum serviço encontrado</strong>Seus chamados aparecerão aqui.</div>'}</section>`;
  }

  function renderMessages() {
    const conversations = state.requests.filter((item) => item.conversa_id);
    return `${pageHeader("Comunicação", "Mensagens", "Converse com segurança dentro do chamado correto.")}
      <section class="list">${conversations.length ? conversations.map((item) => requestRow(item, { chat: true })).join("") : '<div class="empty-state"><strong>Nenhuma conversa disponível</strong>O chat é liberado quando existe um atendimento vinculado.</div>'}</section>`;
  }

  function renderOpportunities() {
    const items = state.requests.filter((item) => item.status === "publicada");
    return `${pageHeader("Novos chamados", "Oportunidades disponíveis", "Cada chamado possui cliente, local e descrição próprios.")}
      <section class="list">${items.length ? items.map((item) => requestRow(item, { chat: true })).join("") : '<div class="empty-state"><strong>Nenhuma oportunidade agora</strong>Atualize novamente mais tarde.</div>'}</section>`;
  }

  function renderAgenda() {
    const items = state.requests.filter((item) => item.status !== "publicada");
    return `${pageHeader("Organização", "Agenda e serviços", "Acompanhe os atendimentos vinculados ao seu perfil.")}
      <section class="list">${items.length ? items.map((item) => requestRow(item, { chat: true })).join("") : '<div class="empty-state"><strong>Nenhum atendimento agendado</strong>Atendimentos aceitos aparecerão aqui.</div>'}</section>`;
  }

  function renderProfile() {
    return `${pageHeader("Conta", "Meu perfil", "Informações vinculadas ao seu acesso no Condomínio Connect.")}
      <article class="card"><div class="professional-card__header"><span class="avatar avatar--large">${initials(state.user.nome)}</span><div><h3>${escapeHtml(state.user.nome)}</h3><p>${escapeHtml(roleLabels[state.user.tipo])}</p></div></div>
      <div class="details-list" style="margin-top:1rem"><div class="details-row"><dt>E-mail</dt><dd>${escapeHtml(state.user.email)}</dd></div><div class="details-row"><dt>Status</dt><dd>Ativo</dd></div><div class="details-row"><dt>Identificador</dt><dd>USR-${String(state.user.id).padStart(3, "0")}</dd></div></div></article>`;
  }

  function renderNotifications() {
    const count = state.requests.filter((item) => ["orcamento_recebido", "em_execucao", "publicada"].includes(item.status)).length;
    return `${pageHeader("Atualizações", "Notificações", "Acompanhe mudanças importantes nos seus chamados.")}
      <article class="card"><strong>${count} atualização(ões) relevante(s)</strong><p class="muted" style="margin-top:.5rem">Abra a área de serviços para consultar os detalhes e responder quando necessário.</p><button class="button button--primary" data-route="${state.user.tipo === "profissional" ? "opportunities" : "services"}">Ver chamados</button></article>`;
  }

  function table(headers, rows) {
    if (!rows.length) return '<div class="empty-state"><strong>Nenhum registro encontrado</strong></div>';
    return `<div class="data-table-wrap"><table class="data-table"><thead><tr>${headers.map((item) => `<th>${escapeHtml(item)}</th>`).join("")}</tr></thead><tbody>${rows.join("")}</tbody></table></div>`;
  }

  function renderAdminPanel() {
    const data = state.admin;
    if (!data) return '<div class="empty-state"><strong>Dados indisponíveis</strong></div>';
    const tabs = [
      ["validations", "Validações"], ["users", "Usuários"], ["professionals", "Profissionais"], ["requests", "Chamados"], ["categories", "Categorias"], ["reviews", "Avaliações"],
    ];
    return `${pageHeader("Operação SGB Tech", "Painel administrativo", "Consulte usuários, profissionais e chamados; valide os cadastros antes da publicação.", '<span class="badge">Acesso restrito</span>')}
      <section class="grid grid--4"><article class="card metric"><span>Usuários ativos</span><strong>${data.summary.usuarios}</strong></article><article class="card metric"><span>Profissionais</span><strong>${data.summary.profissionais}</strong></article><article class="card metric"><span>Chamados em andamento</span><strong>${data.summary.chamados}</strong></article><article class="card metric"><span>Aguardando validação</span><strong>${data.summary.validacoes}</strong></article></section>
      <section class="card" style="margin-top:1rem"><div class="tabs">${tabs.map(([id, label]) => `<button class="tab ${state.adminTab === id ? "is-active" : ""}" data-action="admin-tab" data-tab="${id}">${label}</button>`).join("")}</div><div class="panel">${renderAdminTab()}</div></section>`;
  }

  function renderAdminTab() {
    const data = state.admin;
    if (state.adminTab === "users") {
      return table(["Nome", "E-mail", "Perfil", "Status", "Ação"], data.users.map((item) => `<tr><td><strong>${escapeHtml(item.nome)}</strong></td><td>${escapeHtml(item.email)}</td><td>${escapeHtml(roleLabels[item.tipo] || item.tipo)}</td><td><span class="${statusClass(item.status)}">${prettyStatus(item.status)}</span></td><td><button class="table-action" data-action="admin-details" data-kind="user" data-id="${item.id}">Ver detalhes</button></td></tr>`));
    }
    if (state.adminTab === "professionals") {
      return table(["Profissional", "Categoria", "CNPJ", "Status", "Ação"], data.professionals.map((item) => `<tr><td><strong>${escapeHtml(item.nome)}</strong><br><small>${escapeHtml(item.email)}</small></td><td>${escapeHtml(item.categorias || "-")}</td><td>${escapeHtml(item.cnpj)}</td><td><span class="${statusClass(item.status_validacao)}">${prettyStatus(item.status_validacao)}</span></td><td><button class="table-action" data-action="admin-details" data-kind="professional" data-id="${item.id}">Ver detalhes</button></td></tr>`));
    }
    if (state.adminTab === "requests") {
      return table(["Chamado", "Categoria", "Cliente", "Status", "Ação"], data.requests.map((item) => `<tr><td><strong>#${item.id} · ${escapeHtml(item.titulo)}</strong><br><small>${escapeHtml(item.condominio)}</small></td><td>${escapeHtml(item.categoria)}</td><td>${escapeHtml(item.cliente)}</td><td><span class="${statusClass(item.status)}">${prettyStatus(item.status)}</span></td><td><button class="table-action" data-action="admin-details" data-kind="request" data-id="${item.id}">Ver detalhes</button></td></tr>`));
    }
    if (state.adminTab === "categories") {
      return table(["Categoria", "Descrição", "Status"], data.categories.map((item) => `<tr><td><strong>${escapeHtml(item.nome)}</strong></td><td>${escapeHtml(item.descricao)}</td><td><span class="${item.ativo ? "badge badge--success" : "badge badge--danger"}">${item.ativo ? "Ativa" : "Inativa"}</span></td></tr>`));
    }
    if (state.adminTab === "reviews") {
      return table(["Profissional", "Cliente", "Nota", "Comentário", "Status"], data.reviews.map((item) => `<tr><td><strong>${escapeHtml(item.profissional)}</strong></td><td>${escapeHtml(item.cliente)}</td><td>${item.nota} ★</td><td>${escapeHtml(item.comentario || "Sem comentário")}</td><td><span class="${statusClass(item.status_moderacao)}">${prettyStatus(item.status_moderacao)}</span></td></tr>`));
    }
    const pending = data.professionals.filter((item) => item.status_validacao === "em_analise");
    return `<p class="notice"><strong>Fluxo de validação:</strong> confira a situação cadastral do CNPJ e os documentos recebidos. A aprovação só deve ocorrer após as duas conferências.</p><div class="list">${pending.length ? pending.map((item) => {
      const checks = state.validationChecks[item.id] || { cnpj: false, documents: false };
      return `<article class="list-row"><span class="avatar avatar--large">${initials(item.nome)}</span><div class="list-row__content"><h3>${escapeHtml(item.nome)}</h3><p>${escapeHtml(item.categorias || "Sem categoria")} · CNPJ ${escapeHtml(item.cnpj)}</p><p><span class="${checks.cnpj ? "badge badge--success" : "badge badge--warning"}">CNPJ ${checks.cnpj ? "conferido" : "pendente"}</span> <span class="${checks.documents ? "badge badge--success" : "badge badge--warning"}">Documentos ${checks.documents ? "conferidos" : "pendentes"}</span></p></div><button class="button button--secondary" data-action="check-cnpj" data-id="${item.id}">Consultar CNPJ</button><button class="button button--secondary" data-action="check-documents" data-id="${item.id}">Conferir documentos</button><button class="button button--primary" data-action="approve-professional" data-id="${item.id}" ${checks.cnpj && checks.documents ? "" : "disabled"}>Aprovar</button></article>`;
    }).join("") : '<div class="empty-state"><strong>Nenhuma validação pendente</strong>Todos os cadastros foram analisados.</div>'}</div>`;
  }

  function render() {
    renderNavigation();
    $("#chat-launcher").classList.toggle("is-hidden", state.user.tipo === "administrador" || !state.requests.some((item) => item.conversa_id));
    let content;
    if (state.route === "profile") content = renderProfile();
    else if (state.route === "notifications") content = renderNotifications();
    else if (state.user.tipo === "administrador") content = renderAdminPanel();
    else if (state.user.tipo === "profissional") {
      if (state.route === "opportunities") content = renderOpportunities();
      else if (state.route === "agenda") content = renderAgenda();
      else if (state.route === "messages") content = renderMessages();
      else content = renderProfessionalHome();
    } else {
      if (state.route === "professionals") content = renderProfessionals();
      else if (state.route === "new-request") content = renderNewRequest();
      else if (state.route === "services") content = renderServices();
      else if (state.route === "messages") content = renderMessages();
      else content = renderClientHome();
    }
    $("#page-content").innerHTML = content;
    $("#page-content").focus({ preventScroll: true });
  }

  function navigate(route) {
    state.route = route === "admin" ? "admin" : route;
    window.scrollTo({ top: 0, behavior: "smooth" });
    render();
  }

  async function refreshData() {
    setLoading(true);
    try {
      await loadApplicationData();
      render();
    } catch (error) {
      toast(error.message, "error");
    } finally {
      setLoading(false);
    }
  }

  async function openChat(requestId) {
    const request = state.requests.find((item) => String(item.id) === String(requestId)) || state.requests.find((item) => item.conversa_id);
    if (!request?.conversa_id) {
      toast("Este chamado ainda não possui uma conversa.", "error");
      return;
    }
    setLoading(true);
    try {
      const result = await CondoApi.messages(request.conversa_id);
      state.activeConversation = result.conversation;
      const otherName = state.user.tipo === "profissional" ? result.conversation.cliente : result.conversation.profissional;
      $("#chat-title").textContent = otherName;
      $("#chat-subtitle").textContent = `#${result.conversation.solicitacao_id} · ${result.conversation.titulo}`;
      $("#chat-messages").innerHTML = result.messages.map((message) => `<div class="message ${String(message.remetente_id) === String(state.user.id) ? "message--mine" : ""}">${escapeHtml(message.conteudo)}<small>${escapeHtml(message.remetente)} · ${formatDate(message.criada_em)}</small></div>`).join("");
      $("#chat-panel").classList.remove("is-hidden");
      $("#chat-launcher").classList.add("is-hidden");
      $("#chat-messages").scrollTop = $("#chat-messages").scrollHeight;
    } catch (error) {
      toast(error.message, "error");
    } finally {
      setLoading(false);
    }
  }

  function closeChat() {
    $("#chat-panel").classList.add("is-hidden");
    if (state.user?.tipo !== "administrador" && state.requests.some((item) => item.conversa_id)) $("#chat-launcher").classList.remove("is-hidden");
  }

  document.addEventListener("click", async (event) => {
    const routeButton = event.target.closest("[data-route]");
    if (routeButton) {
      event.preventDefault();
      navigate(routeButton.dataset.route);
      return;
    }
    const closeButton = event.target.closest("[data-close-dialog]");
    if (closeButton) {
      $("#" + closeButton.dataset.closeDialog).close();
      return;
    }
    const actionButton = event.target.closest("[data-action]");
    if (!actionButton) return;
    const action = actionButton.dataset.action;
    if (action === "category") {
      state.activeCategory = actionButton.dataset.id;
      state.route = "new-request";
      render();
    } else if (action === "professional-details") {
      const item = state.professionals.find((professional) => String(professional.id) === actionButton.dataset.id);
      if (item) openDetails(item.nome, [["Categoria", item.categoria], ["Experiência", `${item.anos_experiencia} anos`], ["Avaliação", `${Number(item.media_avaliacao).toFixed(1).replace(".", ",")} (${item.quantidade_avaliacoes} avaliações)`], ["Taxa de visita", money(item.taxa_visita)], ["Sobre", item.descricao], ["Validação", "CNPJ e documentos aprovados pela SGB Tech"]]);
    } else if (action === "request-details") {
      const item = state.requests.find((request) => String(request.id) === actionButton.dataset.id);
      if (item) openDetails(`${item.codigo} · ${item.titulo}`, [["Categoria", item.categoria], ["Cliente", item.cliente], ["Descrição", item.descricao], ["Local", item.local_atendimento], ["Prioridade", prettyStatus(item.prioridade)], ["Status", prettyStatus(item.status)], ["Profissional", item.profissional || "Ainda não escolhido"], ["Criado em", formatDate(item.criada_em)]]);
    } else if (action === "open-chat") {
      await openChat(actionButton.dataset.requestId);
    } else if (action === "admin-tab") {
      state.adminTab = actionButton.dataset.tab;
      render();
    } else if (action === "admin-details") {
      const group = actionButton.dataset.kind === "user" ? state.admin.users : actionButton.dataset.kind === "professional" ? state.admin.professionals : state.admin.requests;
      const item = group.find((record) => String(record.id) === actionButton.dataset.id);
      if (item) openDetails(item.nome || item.titulo, Object.entries(item).filter(([key]) => key !== "id").map(([key, value]) => [prettyStatus(key), prettyStatus(value)]));
    } else if (action === "check-cnpj" || action === "check-documents") {
      const id = actionButton.dataset.id;
      const current = state.validationChecks[id] || { cnpj: false, documents: false };
      state.validationChecks[id] = { ...current, [action === "check-cnpj" ? "cnpj" : "documents"]: true };
      toast(action === "check-cnpj" ? "Situação cadastral conferida." : "Documentos conferidos.", "success");
      render();
    } else if (action === "approve-professional") {
      const id = Number(actionButton.dataset.id);
      const checks = state.validationChecks[id] || state.validationChecks[String(id)];
      if (!checks?.cnpj || !checks?.documents) return;
      setLoading(true);
      try {
        await CondoApi.reviewProfessional({ profissional_id: id, decisao: "aprovar", cnpj_conferido: true, documentos_conferidos: true });
        toast("Profissional aprovado.", "success");
        await loadApplicationData();
        render();
      } catch (error) {
        toast(error.message, "error");
      } finally {
        setLoading(false);
      }
    }
  });

  $("#login-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setFormMessage("#auth-message", "");
    setLoading(true);
    try {
      const result = await CondoApi.login(form.get("email"), form.get("password"));
      await showApplication(result.user);
    } catch (error) {
      setFormMessage("#auth-message", error.message);
    } finally {
      setLoading(false);
    }
  });

  $("#open-register").addEventListener("click", () => $("#register-dialog").showModal());
  $$('input[name="tipo"]', $("#register-form")).forEach((radio) => radio.addEventListener("change", () => {
    const professional = radio.value === "profissional" && radio.checked;
    $("#professional-fields").classList.toggle("is-hidden", !professional);
    $$('input, select, textarea', $("#professional-fields")).forEach((field) => { field.required = professional; });
  }));

  $("#register-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    setFormMessage("#register-message", "");
    setLoading(true);
    try {
      const result = await CondoApi.register(data);
      setFormMessage("#register-message", result.message, true);
      event.currentTarget.reset();
      $("#professional-fields").classList.add("is-hidden");
      $$("input, select, textarea", $("#professional-fields")).forEach((field) => { field.required = false; });
      window.setTimeout(() => $("#register-dialog").close(), 1800);
    } catch (error) {
      setFormMessage("#register-message", error.message);
    } finally {
      setLoading(false);
    }
  });

  $("#logout-button").addEventListener("click", async () => {
    try { await CondoApi.logout(); } catch { /* A sessão local também será encerrada. */ }
    closeChat();
    showAuth();
  });

  $("#chat-launcher").addEventListener("click", () => openChat(state.requests.find((item) => item.conversa_id)?.id));
  $("#close-chat").addEventListener("click", closeChat);
  $("#chat-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const inputElement = event.currentTarget.elements.message;
    const content = inputElement.value.trim();
    if (!content || !state.activeConversation) return;
    try {
      await CondoApi.sendMessage(state.activeConversation.id, content);
      inputElement.value = "";
      await openChat(state.activeConversation.solicitacao_id);
    } catch (error) {
      toast(error.message, "error");
    }
  });

  $("#page-content").addEventListener("change", async (event) => {
    if (event.target.id === "professional-filter") {
      state.activeCategory = event.target.value;
      render();
    }
    if (event.target.id === "request-category") {
      state.activeCategory = event.target.value;
      const category = state.categories.find((item) => String(item.id) === String(event.target.value));
      const defaults = categoryDefaults[category?.nome] || ["Novo serviço", "Descreva o serviço necessário."];
      $("#request-title").value = defaults[0];
      $("#request-description").placeholder = defaults[1];
    }
  });

  $("#page-content").addEventListener("submit", async (event) => {
    if (event.target.id !== "request-form") return;
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.target).entries());
    setLoading(true);
    try {
      await CondoApi.createRequest(data);
      toast("Chamado publicado com sucesso.", "success");
      state.route = "services";
      await refreshData();
    } catch (error) {
      toast(error.message, "error");
    } finally {
      setLoading(false);
    }
  });

  async function start() {
    try {
      const session = await CondoApi.session();
      const categories = await CondoApi.categories();
      state.categories = categories.categories;
      $("#register-category").innerHTML = state.categories.map((item) => `<option value="${item.id}">${escapeHtml(item.nome)}</option>`).join("");
      if (session.authenticated) await showApplication(session.user);
      else showAuth();
    } catch (error) {
      showAuth();
      setFormMessage("#auth-message", `${error.message} Confira o MySQL e o arquivo config/database.php.`);
    }
  }

  start();
})();
