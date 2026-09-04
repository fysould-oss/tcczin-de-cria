/* Comunicação do front-end com os arquivos PHP. */
(function () {
  "use strict";

  async function request(path, options = {}) {
    const response = await fetch(`api/${path}`, {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
        ...(options.body && !(options.body instanceof FormData)
          ? { "Content-Type": "application/json" }
          : {}),
        ...(options.headers || {}),
      },
      ...options,
      body:
        options.body && !(options.body instanceof FormData) && typeof options.body !== "string"
          ? JSON.stringify(options.body)
          : options.body,
    });

    let payload;
    try {
      payload = await response.json();
    } catch {
      payload = { ok: false, message: "O servidor retornou uma resposta inválida." };
    }

    if (!response.ok || payload.ok === false) {
      const error = new Error(payload.message || "Não foi possível concluir a solicitação.");
      error.status = response.status;
      error.details = payload.details || {};
      throw error;
    }
    return payload;
  }

  window.CondoApi = {
    session: () => request("session.php"),
    login: (email, password) => request("login.php", { method: "POST", body: { email, password } }),
    logout: () => request("logout.php", { method: "POST" }),
    register: (data) => request("register.php", { method: "POST", body: data }),
    categories: () => request("categories.php"),
    professionals: (categoryId) =>
      request(`professionals.php${categoryId ? `?categoria_id=${encodeURIComponent(categoryId)}` : ""}`),
    requests: () => request("requests.php"),
    createRequest: (data) => request("requests.php", { method: "POST", body: data }),
    messages: (conversationId) =>
      request(`messages.php?conversa_id=${encodeURIComponent(conversationId)}`),
    sendMessage: (conversationId, content) =>
      request("messages.php", {
        method: "POST",
        body: { conversa_id: conversationId, conteudo: content },
      }),
    admin: () => request("admin.php"),
    reviewProfessional: (data) => request("admin.php", { method: "PATCH", body: data }),
  };
})();
