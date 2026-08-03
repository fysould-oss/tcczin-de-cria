import TestDB from './test-db.js';

const sections = {
    clients: {
        title: 'Clientes',
        description: 'Criar, atualizar e remover clientes do banco de teste.',
        renderList: renderClients,
        renderForm: renderClientForm
    },
    employees: {
        title: 'Funcionários',
        description: 'Gerencie funcionários e perfis da equipe.',
        renderList: renderEmployees,
        renderForm: renderEmployeeForm
    },
    users: {
        title: 'Logins',
        description: 'Cadastre usuários, defina níveis de acesso e teste logins.',
        renderList: renderUsers,
        renderForm: renderUserForm
    },
    conversations: {
        title: 'Conversas',
        description: 'Crie chats entre contas, envie mensagens e visualize histórico.',
        renderList: renderConversations,
        renderForm: renderConversationForm
    },
    login: {
        title: 'Login',
        description: 'Faça login de teste e veja o nível de acesso de cada usuário.',
        renderList: renderLoginInfo,
        renderForm: renderLoginForm
    }
};

const listContainer = document.getElementById('list-container');
const formContainer = document.getElementById('form-container');
const sectionTitle = document.getElementById('section-title');
const sectionDescription = document.getElementById('section-description');
const logOutput = document.getElementById('log-output');
const tabButtons = document.querySelectorAll('.tab-button');
const resetButton = document.getElementById('reset-button');
let currentSection = 'clients';
let selectedId = null;
let selectedConversationId = null;

function log(message) {
    const line = document.createElement('p');
    line.className = 'log-line';
    line.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
    logOutput.prepend(line);
}

function clearSelected() {
    selectedId = null;
    selectedConversationId = null;
}

function updateSection(sectionKey) {
    currentSection = sectionKey;
    clearSelected();
    tabButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.section === sectionKey));
    const section = sections[sectionKey];
    sectionTitle.textContent = section.title;
    sectionDescription.textContent = section.description;
    section.renderList();
    section.renderForm();
}

function createFormGroup({ label, id, type = 'text', value = '', placeholder = '', rows = 3 }) {
    const field = document.createElement('div');
    field.className = 'input-group';

    const labelEl = document.createElement('label');
    labelEl.setAttribute('for', id);
    labelEl.textContent = label;

    let inputEl;
    if (type === 'textarea') {
        inputEl = document.createElement('textarea');
        inputEl.rows = rows;
    } else {
        inputEl = document.createElement('input');
        inputEl.type = type;
    }

    inputEl.id = id;
    inputEl.name = id;
    inputEl.value = value;
    inputEl.placeholder = placeholder;

    field.append(labelEl, inputEl);
    return field;
}

function renderClients() {
    listContainer.innerHTML = '<h4>Clientes</h4>';
    const list = TestDB.listClients();

    if (list.length === 0) {
        listContainer.innerHTML += '<p>Nenhum cliente encontrado.</p>';
        return;
    }

    list.forEach(client => {
        const item = document.createElement('div');
        item.className = 'list-item';
        item.innerHTML = `
            <strong>${client.name}</strong>
            <p>${client.email}</p>
            <p>${client.phone}</p>
            <p>${client.company}</p>
        `;
        const row = document.createElement('div');
        row.className = 'action-row';

        const edit = document.createElement('button');
        edit.textContent = 'Editar';
        edit.className = 'secondary';
        edit.addEventListener('click', () => {
            selectedId = client.id;
            renderClientForm(client);
        });

        const remove = document.createElement('button');
        remove.textContent = 'Excluir';
        remove.addEventListener('click', () => {
            TestDB.deleteClient(client.id);
            log(`Cliente excluído: ${client.name} (id=${client.id})`);
            renderClients();
            renderClientForm();
        });

        row.append(edit, remove);
        item.append(row);
        listContainer.append(item);
    });
}

function renderClientForm(client = {}) {
    formContainer.innerHTML = '<h4>Formulário de cliente</h4>';
    const form = document.createElement('form');
    form.className = 'testdb-form';

    const nameField = createFormGroup({ label: 'Nome', id: 'client-name', value: client.name || '', placeholder: 'Nome completo' });
    const emailField = createFormGroup({ label: 'E-mail', id: 'client-email', type: 'email', value: client.email || '', placeholder: 'email@exemplo.com' });
    const phoneField = createFormGroup({ label: 'Telefone', id: 'client-phone', value: client.phone || '', placeholder: '+55 11 99999-0000' });
    const companyField = createFormGroup({ label: 'Empresa', id: 'client-company', value: client.company || '', placeholder: 'Nome da empresa' });

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = client.id ? 'Salvar alterações' : 'Adicionar cliente';

    form.append(nameField, emailField, phoneField, companyField, submit);

    form.addEventListener('submit', event => {
        event.preventDefault();
        const data = {
            name: form.querySelector('#client-name').value.trim(),
            email: form.querySelector('#client-email').value.trim(),
            phone: form.querySelector('#client-phone').value.trim(),
            company: form.querySelector('#client-company').value.trim()
        };

        if (client.id) {
            const updated = TestDB.updateClient(client.id, data);
            log(`Cliente atualizado: ${updated.name} (id=${updated.id})`);
        } else {
            const created = TestDB.createClient(data);
            log(`Cliente criado: ${created.name} (id=${created.id})`);
        }

        selectedId = null;
        renderClients();
        renderClientForm();
    });

    formContainer.append(form);
}

function renderEmployees() {
    listContainer.innerHTML = '<h4>Funcionários</h4>';
    const list = TestDB.listEmployees();
    if (list.length === 0) {
        listContainer.innerHTML += '<p>Nenhum funcionário encontrado.</p>';
        return;
    }

    list.forEach(employee => {
        const item = document.createElement('div');
        item.className = 'list-item';
        item.innerHTML = `
            <strong>${employee.name}</strong>
            <p>${employee.email}</p>
            <p>${employee.role} — ${employee.department}</p>
        `;
        const row = document.createElement('div');
        row.className = 'action-row';

        const edit = document.createElement('button');
        edit.textContent = 'Editar';
        edit.className = 'secondary';
        edit.addEventListener('click', () => {
            selectedId = employee.id;
            renderEmployeeForm(employee);
        });

        const remove = document.createElement('button');
        remove.textContent = 'Excluir';
        remove.addEventListener('click', () => {
            TestDB.deleteEmployee(employee.id);
            log(`Funcionário excluído: ${employee.name} (id=${employee.id})`);
            renderEmployees();
            renderEmployeeForm();
        });

        row.append(edit, remove);
        item.append(row);
        listContainer.append(item);
    });
}

function renderEmployeeForm(employee = {}) {
    formContainer.innerHTML = '<h4>Formulário de funcionário</h4>';
    const form = document.createElement('form');
    form.className = 'testdb-form';

    const nameField = createFormGroup({ label: 'Nome', id: 'employee-name', value: employee.name || '', placeholder: 'Nome completo' });
    const emailField = createFormGroup({ label: 'E-mail', id: 'employee-email', type: 'email', value: employee.email || '', placeholder: 'email@exemplo.com' });
    const roleField = createFormGroup({ label: 'Função', id: 'employee-role', value: employee.role || '', placeholder: 'Atendimento, Gestor...' });
    const deptField = createFormGroup({ label: 'Departamento', id: 'employee-dept', value: employee.department || '', placeholder: 'Suporte, Operações...' });

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = employee.id ? 'Salvar alterações' : 'Adicionar funcionário';

    form.append(nameField, emailField, roleField, deptField, submit);

    form.addEventListener('submit', event => {
        event.preventDefault();
        const data = {
            name: form.querySelector('#employee-name').value.trim(),
            email: form.querySelector('#employee-email').value.trim(),
            role: form.querySelector('#employee-role').value.trim(),
            department: form.querySelector('#employee-dept').value.trim()
        };

        if (employee.id) {
            const updated = TestDB.updateEmployee(employee.id, data);
            log(`Funcionário atualizado: ${updated.name} (id=${updated.id})`);
        } else {
            const created = TestDB.createEmployee(data);
            log(`Funcionário criado: ${created.name} (id=${created.id})`);
        }

        selectedId = null;
        renderEmployees();
        renderEmployeeForm();
    });

    formContainer.append(form);
}

function renderUsers() {
    listContainer.innerHTML = '<h4>Logins</h4>';
    const list = TestDB.listUsers();
    if (list.length === 0) {
        listContainer.innerHTML += '<p>Nenhum usuário encontrado.</p>';
        return;
    }

    list.forEach(user => {
        const item = document.createElement('div');
        item.className = 'list-item';
        item.innerHTML = `
            <strong>${user.username}</strong>
            <p>Nível: ${user.role}</p>
            <p>ID vinculado: ${user.linkedId ?? 'nenhum'}</p>
        `;
        const row = document.createElement('div');
        row.className = 'action-row';

        const edit = document.createElement('button');
        edit.textContent = 'Editar';
        edit.className = 'secondary';
        edit.addEventListener('click', () => {
            selectedId = user.id;
            renderUserForm(user);
        });

        const remove = document.createElement('button');
        remove.textContent = 'Excluir';
        remove.addEventListener('click', () => {
            TestDB.deleteUser(user.id);
            log(`Usuário excluído: ${user.username} (id=${user.id})`);
            renderUsers();
            renderUserForm();
        });

        row.append(edit, remove);
        item.append(row);
        listContainer.append(item);
    });
}

function renderUserForm(user = {}) {
    formContainer.innerHTML = '<h4>Formulário de login</h4>';
    const form = document.createElement('form');
    form.className = 'testdb-form';

    const userField = createFormGroup({ label: 'Usuário', id: 'user-username', value: user.username || '', placeholder: 'login123' });
    const passField = createFormGroup({ label: 'Senha', id: 'user-password', type: 'password', value: user.password || '', placeholder: 'senha' });
    const roleField = createFormGroup({ label: 'Nível de acesso', id: 'user-role', value: user.role || 'client', placeholder: 'admin, employee, client' });
    const linkedField = createFormGroup({ label: 'ID relacionado', id: 'user-linked', value: user.linkedId || '', placeholder: 'ID do cliente ou funcionário' });

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = user.id ? 'Salvar alterações' : 'Adicionar login';

    form.append(userField, passField, roleField, linkedField, submit);

    form.addEventListener('submit', event => {
        event.preventDefault();
        const data = {
            username: form.querySelector('#user-username').value.trim(),
            password: form.querySelector('#user-password').value.trim(),
            role: form.querySelector('#user-role').value.trim() || 'client',
            linkedId: Number(form.querySelector('#user-linked').value.trim()) || null
        };

        if (user.id) {
            const updated = TestDB.updateUser(user.id, data);
            log(`Login atualizado: ${updated.username} (id=${updated.id})`);
        } else {
            const created = TestDB.createUser(data);
            log(`Login criado: ${created.username} (id=${created.id})`);
        }

        selectedId = null;
        renderUsers();
        renderUserForm();
    });

    formContainer.append(form);
}

function renderConversations() {
    listContainer.innerHTML = '<h4>Conversas</h4>';
    const list = TestDB.listConversations();
    if (list.length === 0) {
        listContainer.innerHTML += '<p>Nenhuma conversa registrada.</p>';
        return;
    }

    list.forEach(conversation => {
        const item = document.createElement('div');
        item.className = 'conversation-item';
        item.innerHTML = `
            <strong>${conversation.title}</strong>
            <p>Participantes: ${conversation.participants.join(', ')}</p>
            <p>Criado em: ${new Date(conversation.createdAt).toLocaleString()}</p>
        `;
        const row = document.createElement('div');
        row.className = 'action-row';

        const open = document.createElement('button');
        open.textContent = 'Abrir mensagens';
        open.className = 'secondary';
        open.addEventListener('click', () => {
            selectedConversationId = conversation.id;
            renderConversationForm(conversation);
        });

        const remove = document.createElement('button');
        remove.textContent = 'Excluir conversa';
        remove.addEventListener('click', () => {
            TestDB.deleteConversation(conversation.id);
            log(`Conversa excluída: ${conversation.title} (id=${conversation.id})`);
            selectedConversationId = null;
            renderConversations();
            renderConversationForm();
        });

        row.append(open, remove);
        item.append(row);
        listContainer.append(item);
    });
}

function renderConversationForm(conversation = {}) {
    formContainer.innerHTML = '<h4>Formulário de conversa</h4>';
    const wrapper = document.createElement('div');
    wrapper.className = 'testdb-form';

    const form = document.createElement('form');

    const titleField = createFormGroup({ label: 'Título da conversa', id: 'conversation-title', value: conversation.title || '', placeholder: 'Assunto da conversa' });
    const participantsField = createFormGroup({ label: 'Participantes (ids)', id: 'conversation-participants', value: conversation.participants ? conversation.participants.join(', ') : '', placeholder: '1,2,3' });
    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = conversation.id ? 'Salvar conversa' : 'Criar conversa';

    form.append(titleField, participantsField, submit);
    form.addEventListener('submit', event => {
        event.preventDefault();
        const data = {
            title: form.querySelector('#conversation-title').value.trim(),
            participants: form.querySelector('#conversation-participants').value
                .split(',')
                .map(id => Number(id.trim()))
                .filter(id => id > 0)
        };

        if (conversation.id) {
            const updated = TestDB.updateConversation(conversation.id, data);
            if (updated) {
                log(`Conversa atualizada: ${updated.title} (id=${updated.id})`);
            }
        } else {
            const created = TestDB.createConversation(data);
            log(`Conversa criada: ${created.title} (id=${created.id})`);
            selectedConversationId = created.id;
            renderConversations();
        }

        renderConversations();
        renderConversationForm(TestDB.getConversation(selectedConversationId) || {});
    });

    wrapper.append(form);

    const messageSection = document.createElement('div');
    messageSection.className = 'message-section';
    const messagesTitle = document.createElement('h4');
    messagesTitle.textContent = 'Mensagens';
    messageSection.append(messagesTitle);

    if (conversation.id || selectedConversationId) {
        const conversationId = conversation.id || selectedConversationId;
        const messages = TestDB.getConversationMessages(conversationId);

        if (messages.length === 0) {
            const empty = document.createElement('p');
            empty.textContent = 'Nenhuma mensagem nesta conversa.';
            messageSection.append(empty);
        } else {
            messages.forEach(msg => {
                const messageItem = document.createElement('div');
                messageItem.className = 'message-item';
                messageItem.innerHTML = `
                    <strong>De: ${msg.senderId} — ${new Date(msg.createdAt).toLocaleString()}</strong>
                    <p>${msg.text}</p>
                `;
                messageSection.append(messageItem);
            });
        }

        const messageForm = document.createElement('form');
        const senderField = createFormGroup({ label: 'ID do remetente', id: 'message-sender', type: 'number', placeholder: 'Digite o id' });
        const textField = createFormGroup({ label: 'Mensagem', id: 'message-text', type: 'textarea', placeholder: 'Escreva sua mensagem' });
        const sendButton = document.createElement('button');
        sendButton.type = 'submit';
        sendButton.textContent = 'Enviar mensagem';

        messageForm.append(senderField, textField, sendButton);
        messageForm.addEventListener('submit', event => {
            event.preventDefault();
            const data = {
                conversationId,
                senderId: Number(messageForm.querySelector('#message-sender').value.trim()),
                text: messageForm.querySelector('#message-text').value.trim()
            };
            const message = TestDB.sendMessage(data);
            if (message) {
                log(`Mensagem enviada na conversa ${conversationId} por ${data.senderId}`);
                renderConversationForm(TestDB.getConversation(conversationId));
            } else {
                log(`Falha ao enviar mensagem: conversa ${conversationId} não encontrada`);
            }
        });

        messageSection.append(messageForm);
    }

    wrapper.append(messageSection);
    formContainer.append(wrapper);
}

function renderLoginInfo() {
    listContainer.innerHTML = '<h4>Login de teste</h4>';
    listContainer.innerHTML += '<p>Use o formulário à direita para fazer login com usuários de exemplo.</p>';
}

function renderLoginForm() {
    formContainer.innerHTML = '<h4>Formulário de login</h4>';
    const form = document.createElement('form');
    form.className = 'login-form';

    const usernameField = createFormGroup({ label: 'Usuário', id: 'login-username', placeholder: 'admin' });
    const passwordField = createFormGroup({ label: 'Senha', id: 'login-password', type: 'password', placeholder: 'admin123' });
    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = 'Fazer login';

    const info = document.createElement('p');
    info.textContent = 'Usuários de exemplo: admin/admin123, funcionario1/func123, cliente1/cli123';
    info.style.color = 'var(--text-muted)';
    info.style.marginBottom = '1rem';

    form.append(usernameField, passwordField, submit, info);
    form.addEventListener('submit', event => {
        event.preventDefault();
        const username = form.querySelector('#login-username').value.trim();
        const password = form.querySelector('#login-password').value.trim();
        const result = TestDB.login(username, password);

        if (result) {
            log(`Login bem-sucedido: ${result.username} (admin=${result.isAdmin})`);
            alert(`Login OK: ${result.username}\nNível: ${result.role}\nAdmin: ${result.isAdmin}`);
        } else {
            log(`Falha no login: ${username}`);
            alert('Login inválido. Use um dos exemplos acima.');
        }
    });

    formContainer.append(form);
}

function resetDatabase() {
    if (!confirm('Tem certeza que deseja resetar o banco de dados de teste? Todos os dados atuais serão perdidos.')) {
        return;
    }
    TestDB.resetDB();
    updateSection(currentSection);
    log('Banco de dados resetado para os valores iniciais.');
}

function init() {
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => updateSection(btn.dataset.section));
    });
    resetButton.addEventListener('click', resetDatabase);
    updateSection(currentSection);
}

init();
