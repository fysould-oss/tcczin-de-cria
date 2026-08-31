import { getStore } from '@netlify/blobs';
import { randomBytes, scryptSync, timingSafeEqual } from 'node:crypto';

const users = () => getStore({ name: 'users', consistency: 'strong' });
const tickets = () => getStore({ name: 'tickets', consistency: 'strong' });
const json = (status, body) => ({ statusCode: status, headers: { 'Content-Type': 'application/json; charset=utf-8' }, body: JSON.stringify(body) });
const id = () => randomBytes(16).toString('hex');
const passwordHash = password => { const salt = randomBytes(16).toString('hex'); return `${salt}:${scryptSync(password, salt, 64).toString('hex')}`; };
const passwordValid = (password, stored) => { const [salt, expected] = stored.split(':'); const actual = scryptSync(password, salt, 64).toString('hex'); return timingSafeEqual(Buffer.from(expected, 'hex'), Buffer.from(actual, 'hex')); };
const publicUser = ({ password, ...user }) => user;
const readBody = async event => { try { return JSON.parse(event.body || '{}'); } catch { return null; } };

export default async (event) => {
  if (event.httpMethod === 'OPTIONS') return { statusCode: 204, headers: { Allow: 'GET, POST, OPTIONS' }, body: '' };
  const action = event.path.split('/').pop();
  const body = await readBody(event);
  if (!body) return json(400, { error: 'JSON inválido.' });

  if (action === 'register' && event.httpMethod === 'POST') {
    const { nome, email, senha, telefone = '', cpf = '', tipoUsuario = 'morador', condominio = '' } = body;
    if (!nome || !email || !senha || !condominio) return json(400, { error: 'Preencha os campos obrigatórios.' });
    if (senha.length < 8) return json(400, { error: 'A senha deve ter pelo menos 8 caracteres.' });
    const key = email.trim().toLowerCase();
    if (await users().get(key, { type: 'json' })) return json(409, { error: 'Este e-mail já está cadastrado.' });
    const user = { id: id(), nome: nome.trim(), email: key, telefone: telefone.trim(), cpf: cpf.trim(), tipoUsuario, condominio: condominio.trim(), password: passwordHash(senha), criadoEm: new Date().toISOString() };
    await users().set(key, JSON.stringify(user));
    return json(201, { user: publicUser(user), token: user.id });
  }

  if (action === 'login' && event.httpMethod === 'POST') {
    const user = await users().get(String(body.email || '').trim().toLowerCase(), { type: 'json' });
    if (!user || !passwordValid(String(body.senha || ''), user.password)) return json(401, { error: 'E-mail ou senha inválidos.' });
    return json(200, { user: publicUser(user), token: user.id });
  }

  const token = event.headers.authorization?.replace('Bearer ', '');
  const allUsers = await users().list();
  let user = null;
  for (const item of allUsers.blobs) { const candidate = await users().get(item.key, { type: 'json' }); if (candidate?.id === token) { user = candidate; break; } }
  if (!user) return json(401, { error: 'Sessão inválida. Entre novamente.' });
  if (action === 'me') return json(200, { user: publicUser(user) });

  if (action === 'tickets' && event.httpMethod === 'GET') {
    const result = await tickets().list(); const data = [];
    for (const item of result.blobs) { const ticket = await tickets().get(item.key, { type: 'json' }); if (ticket?.usuarioId === user.id) data.push(ticket); }
    return json(200, { tickets: data.sort((a, b) => b.criadoEm.localeCompare(a.criadoEm)) });
  }
  if (action === 'tickets' && event.httpMethod === 'POST') {
    const { categoria, titulo, descricao, localProblema, prioridade, dataServico } = body;
    if (![categoria, titulo, descricao, localProblema, prioridade, dataServico].every(Boolean)) return json(400, { error: 'Preencha todos os campos.' });
    const ticket = { id: id(), protocolo: `CP-${Date.now().toString().slice(-8)}`, usuarioId: user.id, categoria, titulo, descricao, localProblema, prioridade, dataServico, status: 'aberto', criadoEm: new Date().toISOString() };
    await tickets().set(ticket.id, JSON.stringify(ticket));
    return json(201, { ticket });
  }
  return json(404, { error: 'Rota não encontrada.' });
};
