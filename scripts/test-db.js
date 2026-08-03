// test-db.js
// Exemplo de "banco de dados" em memória para CRUD de clientes, funcionários, logins, mensagens e níveis de acesso.

const TestDB = (() => {
    const STORAGE_KEY = 'TestDB_data';

    const initialData = {
        clients: [
            { id: 1, name: 'Ana Silva', email: 'ana@exemplo.com', phone: '+55 11 99999-0001', company: 'ImobPlus' },
            { id: 2, name: 'Ricardo Lima', email: 'ricardo@exemplo.com', phone: '+55 21 98888-0002', company: 'Condo Fácil' }
        ],
        employees: [
            { id: 1, name: 'Paula Souza', email: 'paula@exemplo.com', role: 'Atendimento', department: 'Suporte' },
            { id: 2, name: 'Bruno Alves', email: 'bruno@exemplo.com', role: 'Gestor', department: 'Operações' }
        ],
        users: [
            { id: 1, username: 'admin', password: 'admin123', role: 'admin', linkedId: null },
            { id: 2, username: 'funcionario1', password: 'func123', role: 'employee', linkedId: 2 },
            { id: 3, username: 'cliente1', password: 'cli123', role: 'client', linkedId: 1 }
        ],
        conversations: [
            {
                id: 1,
                title: 'Suporte ao cliente',
                participants: [1, 2, 3],
                createdAt: new Date('2026-08-01T08:30:00Z')
            }
        ],
        messages: [
            { id: 1, conversationId: 1, senderId: 3, text: 'Olá, preciso de ajuda com meu cadastro.', createdAt: new Date('2026-08-01T08:32:00Z') },
            { id: 2, conversationId: 1, senderId: 2, text: 'Oi, Ricardo! Em que posso ajudar?', createdAt: new Date('2026-08-01T08:33:00Z') },
            { id: 3, conversationId: 1, senderId: 1, text: 'Por favor, verifiquem o histórico de atendimento.', createdAt: new Date('2026-08-01T08:35:00Z') }
        ]
    };

    const parseDates = (data) => {
        return {
            ...data,
            conversations: (data.conversations || []).map(item => ({
                ...item,
                createdAt: new Date(item.createdAt)
            })),
            messages: (data.messages || []).map(item => ({
                ...item,
                createdAt: new Date(item.createdAt)
            }))
        };
    };

    const loadDB = () => {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            return parseDates({
                clients: parsed.clients || [],
                employees: parsed.employees || [],
                users: parsed.users || [],
                conversations: parsed.conversations || [],
                messages: parsed.messages || []
            });
        } catch (error) {
            console.warn('Falha ao carregar banco local:', error);
            return null;
        }
    };

    const saveDB = () => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(db));
        } catch (error) {
            console.warn('Falha ao salvar banco local:', error);
        }
    };

    const resetDB = () => {
        db.clients = initialData.clients.map(item => ({ ...item }));
        db.employees = initialData.employees.map(item => ({ ...item }));
        db.users = initialData.users.map(item => ({ ...item }));
        db.conversations = initialData.conversations.map(item => ({
            ...item,
            createdAt: new Date(item.createdAt)
        }));
        db.messages = initialData.messages.map(item => ({
            ...item,
            createdAt: new Date(item.createdAt)
        }));
        saveDB();
        return db;
    };

    const db = loadDB() || initialData;

    const nextId = (collection) => {
        if (!db[collection] || db[collection].length === 0) return 1;
        return Math.max(...db[collection].map(item => item.id)) + 1;
    };

    const findById = (collection, id) => db[collection].find(item => item.id === id) || null;

    const createRecord = (collection, record) => {
        const id = nextId(collection);
        const newRecord = { id, ...record };
        db[collection].push(newRecord);
        saveDB();
        return newRecord;
    };

    const updateRecord = (collection, id, changes) => {
        const record = findById(collection, id);
        if (!record) return null;
        Object.assign(record, changes);
        saveDB();
        return record;
    };

    const deleteRecord = (collection, id) => {
        const index = db[collection].findIndex(item => item.id === id);
        if (index === -1) return false;
        db[collection].splice(index, 1);
        saveDB();
        return true;
    };

    const login = (username, password) => {
        const user = db.users.find(u => u.username === username && u.password === password);
        if (!user) return null;
        return {
            id: user.id,
            username: user.username,
            role: user.role,
            isAdmin: user.role === 'admin'
        };
    };

    const isAdmin = (user) => user && user.role === 'admin';

    const createClient = (client) => createRecord('clients', client);
    const updateClient = (id, changes) => updateRecord('clients', id, changes);
    const deleteClient = (id) => deleteRecord('clients', id);
    const getClient = (id) => findById('clients', id);
    const listClients = () => [...db.clients];

    const createEmployee = (employee) => createRecord('employees', employee);
    const updateEmployee = (id, changes) => updateRecord('employees', id, changes);
    const deleteEmployee = (id) => deleteRecord('employees', id);
    const getEmployee = (id) => findById('employees', id);
    const listEmployees = () => [...db.employees];

    const createUser = (user) => createRecord('users', user);
    const updateUser = (id, changes) => updateRecord('users', id, changes);
    const deleteUser = (id) => deleteRecord('users', id);
    const getUser = (id) => findById('users', id);
    const listUsers = () => [...db.users];

    const createConversation = ({ title, participants }) => {
        if (!Array.isArray(participants)) participants = [];
        return createRecord('conversations', {
            title,
            participants,
            createdAt: new Date()
        });
    };

    const sendMessage = ({ conversationId, senderId, text }) => {
        const conversation = findById('conversations', conversationId);
        if (!conversation) return null;
        const message = createRecord('messages', {
            conversationId,
            senderId,
            text,
            createdAt: new Date()
        });
        if (!conversation.participants.includes(senderId)) {
            conversation.participants.push(senderId);
        }
        return message;
    };

    const getConversationMessages = (conversationId) =>
        db.messages.filter(message => message.conversationId === conversationId);

    const listConversations = () => [...db.conversations];
    const getConversation = (id) => findById('conversations', id);
    const updateConversation = (id, changes) => updateRecord('conversations', id, changes);
    const deleteConversation = (id) => {
        const removed = deleteRecord('conversations', id);
        if (!removed) return false;
        db.messages = db.messages.filter(message => message.conversationId !== id);
        saveDB();
        return true;
    };

    const getUserConversations = (userId) =>
        db.conversations.filter(conv => conv.participants.includes(userId));

    return {
        db,
        createClient,
        updateClient,
        deleteClient,
        getClient,
        listClients,
        createEmployee,
        updateEmployee,
        deleteEmployee,
        getEmployee,
        listEmployees,
        createUser,
        updateUser,
        deleteUser,
        getUser,
        listUsers,
        login,
        isAdmin,
        createConversation,
        updateConversation,
        sendMessage,
        getConversationMessages,
        listConversations,
        getConversation,
        deleteConversation,
        getUserConversations,
        resetDB
    };
})();

if (typeof window !== 'undefined') {
    window.TestDB = TestDB;
}

export default TestDB;
