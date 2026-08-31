# CondoConnect

Aplicação para gestão de chamados em condomínios, preparada para publicação no Netlify.

## Arquitetura

- As páginas públicas estão em HTML (`index.html` e `pages/*.html`).
- O frontend chama a API em `/.netlify/functions/api` por meio de `/api/*`.
- A função serverless usa **Netlify Blobs** para persistir usuários e chamados.
- Senhas são derivadas com `scrypt` antes de serem salvas.
- O formulário de contato usa Netlify Forms.

O Netlify não executa PHP nem mantém arquivos SQLite entre chamadas. Por isso, os arquivos PHP/SQLite legados não participam do deploy e não devem ser usados na versão hospedada.

## Executar localmente

1. Instale Node.js 20 ou superior.
2. Instale as dependências: `npm install`.
3. Instale e autentique a CLI do Netlify, se necessário: `npm install -g netlify-cli` e `netlify login`.
4. Inicie: `npx netlify dev`.

## Publicar no Netlify

1. Envie este repositório ao GitHub.
2. No Netlify, escolha **Add new site → Import an existing project**.
3. Selecione o repositório e mantenha a configuração encontrada em `netlify.toml`.
4. Clique em **Deploy site**.

## Limitações intencionais

- Recuperação de senha depende da configuração de um serviço de e-mail.
- Anexos de chamados exigem um storage de arquivos e foram removidos para não gravar dados no ambiente efêmero da função.
