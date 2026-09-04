-- Condomínio Connect
-- Banco de dados MySQL 8.0+
-- As imagens e documentos são armazenados fora do banco; aqui ficam somente os caminhos.

CREATE DATABASE IF NOT EXISTS condominio_connect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE condominio_connect;

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS condominios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cnpj CHAR(14) NULL,
  cep CHAR(8) NOT NULL,
  logradouro VARCHAR(180) NOT NULL,
  numero VARCHAR(20) NOT NULL,
  complemento VARCHAR(100) NULL,
  bairro VARCHAR(100) NOT NULL,
  cidade VARCHAR(100) NOT NULL,
  uf CHAR(2) NOT NULL,
  ativo BOOLEAN NOT NULL DEFAULT TRUE,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_condominios_cnpj (cnpj),
  KEY idx_condominios_cidade_uf (cidade, uf)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  condominio_id BIGINT UNSIGNED NULL,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(180) NOT NULL,
  telefone VARCHAR(20) NULL,
  senha_hash VARCHAR(255) NOT NULL,
  tipo ENUM('cliente', 'profissional', 'administrador') NOT NULL,
  status ENUM('pendente', 'ativo', 'bloqueado', 'inativo') NOT NULL DEFAULT 'pendente',
  bloco VARCHAR(30) NULL,
  unidade VARCHAR(30) NULL,
  foto_caminho VARCHAR(255) NULL,
  email_verificado_em DATETIME NULL,
  ultimo_acesso_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_usuarios_email (email),
  KEY idx_usuarios_tipo_status (tipo, status),
  CONSTRAINT fk_usuarios_condominio
    FOREIGN KEY (condominio_id) REFERENCES condominios(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profissionais (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NOT NULL,
  cnpj CHAR(14) NOT NULL,
  razao_social VARCHAR(180) NOT NULL,
  nome_fantasia VARCHAR(180) NULL,
  descricao TEXT NOT NULL,
  anos_experiencia SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  taxa_visita DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  raio_atendimento_km SMALLINT UNSIGNED NOT NULL DEFAULT 20,
  media_avaliacao DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  quantidade_avaliacoes INT UNSIGNED NOT NULL DEFAULT 0,
  status_validacao ENUM('rascunho', 'em_analise', 'aprovado', 'recusado') NOT NULL DEFAULT 'rascunho',
  cnpj_situacao ENUM('nao_consultado', 'ativo', 'inapto', 'baixado', 'suspenso') NOT NULL DEFAULT 'nao_consultado',
  cnpj_consultado_em DATETIME NULL,
  cnpj_comprovante VARCHAR(255) NULL,
  motivo_recusa VARCHAR(500) NULL,
  validado_por BIGINT UNSIGNED NULL,
  validado_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_profissionais_usuario (usuario_id),
  UNIQUE KEY uk_profissionais_cnpj (cnpj),
  KEY idx_profissionais_validacao (status_validacao),
  CONSTRAINT fk_profissionais_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_profissionais_validador
    FOREIGN KEY (validado_por) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_profissionais_avaliacao CHECK (media_avaliacao BETWEEN 0 AND 5)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS verificacoes_profissional (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  profissional_id BIGINT UNSIGNED NOT NULL,
  tipo ENUM('cnpj', 'identidade', 'certificado', 'portfolio') NOT NULL,
  status ENUM('pendente', 'aprovado', 'recusado') NOT NULL DEFAULT 'pendente',
  fonte VARCHAR(120) NULL,
  protocolo VARCHAR(160) NULL,
  resultado JSON NULL,
  verificado_por BIGINT UNSIGNED NULL,
  verificado_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_verificacao_profissional_tipo (profissional_id, tipo),
  KEY idx_verificacoes_status (status, criado_em),
  CONSTRAINT fk_verificacoes_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_verificacoes_administrador
    FOREIGN KEY (verificado_por) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias_servico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao VARCHAR(300) NULL,
  icone VARCHAR(60) NULL,
  ativo BOOLEAN NOT NULL DEFAULT TRUE,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profissional_categoria (
  profissional_id BIGINT UNSIGNED NOT NULL,
  categoria_id BIGINT UNSIGNED NOT NULL,
  principal BOOLEAN NOT NULL DEFAULT FALSE,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (profissional_id, categoria_id),
  CONSTRAINT fk_profissional_categoria_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_profissional_categoria_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias_servico(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documentos_profissional (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  profissional_id BIGINT UNSIGNED NOT NULL,
  tipo ENUM('cnpj', 'ccmei', 'identificacao', 'certificado', 'portfolio', 'outro') NOT NULL,
  nome_arquivo VARCHAR(180) NOT NULL,
  caminho_arquivo VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NULL,
  tamanho_bytes BIGINT UNSIGNED NULL,
  status ENUM('pendente', 'aprovado', 'recusado') NOT NULL DEFAULT 'pendente',
  observacao VARCHAR(500) NULL,
  analisado_por BIGINT UNSIGNED NULL,
  analisado_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_documentos_profissional_status (profissional_id, status),
  CONSTRAINT fk_documentos_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_documentos_analisador
    FOREIGN KEY (analisado_por) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS solicitacoes_servico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id BIGINT UNSIGNED NOT NULL,
  condominio_id BIGINT UNSIGNED NOT NULL,
  categoria_id BIGINT UNSIGNED NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  descricao TEXT NOT NULL,
  local_atendimento VARCHAR(220) NOT NULL,
  prioridade ENUM('baixa', 'normal', 'alta', 'urgente') NOT NULL DEFAULT 'normal',
  status ENUM(
    'publicada',
    'visita_agendada',
    'aguardando_orcamento',
    'orcamento_recebido',
    'orcamento_aprovado',
    'em_execucao',
    'aguardando_confirmacao',
    'concluida',
    'cancelada'
  ) NOT NULL DEFAULT 'publicada',
  previsao_conclusao DATETIME NULL,
  concluida_em DATETIME NULL,
  criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_solicitacoes_cliente (cliente_id, status),
  KEY idx_solicitacoes_categoria_status (categoria_id, status),
  KEY idx_solicitacoes_condominio (condominio_id),
  CONSTRAINT fk_solicitacoes_cliente
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_solicitacoes_condominio
    FOREIGN KEY (condominio_id) REFERENCES condominios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_solicitacoes_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias_servico(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS imagens_solicitacao (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id BIGINT UNSIGNED NOT NULL,
  enviado_por BIGINT UNSIGNED NOT NULL,
  etapa ENUM('antes', 'durante', 'depois') NOT NULL DEFAULT 'antes',
  caminho_arquivo VARCHAR(255) NOT NULL,
  legenda VARCHAR(250) NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_imagens_solicitacao (solicitacao_id, etapa),
  CONSTRAINT fk_imagens_solicitacao
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_servico(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_imagens_usuario
    FOREIGN KEY (enviado_por) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS agendamentos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id BIGINT UNSIGNED NOT NULL,
  profissional_id BIGINT UNSIGNED NOT NULL,
  proposto_por BIGINT UNSIGNED NOT NULL,
  data_hora_inicio DATETIME NOT NULL,
  data_hora_fim DATETIME NULL,
  taxa_visita DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('proposto', 'confirmado', 'realizado', 'cancelado', 'nao_compareceu') NOT NULL DEFAULT 'proposto',
  observacao VARCHAR(500) NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_agendamentos_profissional_data (profissional_id, data_hora_inicio),
  KEY idx_agendamentos_solicitacao (solicitacao_id),
  CONSTRAINT fk_agendamentos_solicitacao
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_servico(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_agendamentos_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_agendamentos_proponente
    FOREIGN KEY (proposto_por) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS conversas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id BIGINT UNSIGNED NOT NULL,
  profissional_id BIGINT UNSIGNED NOT NULL,
  ativa BOOLEAN NOT NULL DEFAULT TRUE,
  criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_conversas_solicitacao_profissional (solicitacao_id, profissional_id),
  CONSTRAINT fk_conversas_solicitacao
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_servico(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_conversas_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mensagens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversa_id BIGINT UNSIGNED NOT NULL,
  remetente_id BIGINT UNSIGNED NOT NULL,
  conteudo TEXT NULL,
  tipo ENUM('texto', 'imagem', 'arquivo', 'sistema') NOT NULL DEFAULT 'texto',
  lida_em DATETIME NULL,
  criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_mensagens_conversa_data (conversa_id, criada_em),
  KEY idx_mensagens_remetente (remetente_id),
  CONSTRAINT fk_mensagens_conversa
    FOREIGN KEY (conversa_id) REFERENCES conversas(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_mensagens_remetente
    FOREIGN KEY (remetente_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS anexos_mensagem (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mensagem_id BIGINT UNSIGNED NOT NULL,
  nome_arquivo VARCHAR(180) NOT NULL,
  caminho_arquivo VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NULL,
  tamanho_bytes BIGINT UNSIGNED NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_anexos_mensagem
    FOREIGN KEY (mensagem_id) REFERENCES mensagens(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orcamentos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id BIGINT UNSIGNED NOT NULL,
  profissional_id BIGINT UNSIGNED NOT NULL,
  agendamento_id BIGINT UNSIGNED NULL,
  descricao TEXT NOT NULL,
  valor_mao_obra DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  valor_materiais DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  valor_visita DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  valor_total DECIMAL(10,2) GENERATED ALWAYS AS (valor_mao_obra + valor_materiais + valor_visita) STORED,
  prazo_dias SMALLINT UNSIGNED NOT NULL,
  garantia_dias SMALLINT UNSIGNED NOT NULL DEFAULT 90,
  validade_ate DATE NOT NULL,
  status ENUM('rascunho', 'enviado', 'aprovado', 'recusado', 'expirado', 'cancelado') NOT NULL DEFAULT 'rascunho',
  respondido_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_orcamentos_solicitacao_status (solicitacao_id, status),
  KEY idx_orcamentos_profissional (profissional_id, status),
  CONSTRAINT fk_orcamentos_solicitacao
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_servico(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_orcamentos_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_orcamentos_agendamento
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS itens_orcamento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orcamento_id BIGINT UNSIGNED NOT NULL,
  descricao VARCHAR(250) NOT NULL,
  quantidade DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  valor_unitario DECIMAL(10,2) NOT NULL,
  valor_total DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * valor_unitario) STORED,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_itens_orcamento (orcamento_id),
  CONSTRAINT fk_itens_orcamento
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pagamentos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orcamento_id BIGINT UNSIGNED NOT NULL,
  cliente_id BIGINT UNSIGNED NOT NULL,
  profissional_id BIGINT UNSIGNED NOT NULL,
  gateway VARCHAR(60) NULL,
  transacao_gateway VARCHAR(150) NULL,
  forma_pagamento ENUM('pix', 'cartao', 'boleto') NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  status ENUM('pendente', 'autorizado', 'retido', 'pago', 'liberado', 'estornado', 'falhou') NOT NULL DEFAULT 'pendente',
  pago_em DATETIME NULL,
  liberado_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_pagamentos_orcamento (orcamento_id),
  KEY idx_pagamentos_cliente (cliente_id, status),
  KEY idx_pagamentos_profissional (profissional_id, status),
  CONSTRAINT fk_pagamentos_orcamento
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pagamentos_cliente
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pagamentos_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historico_status_servico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id BIGINT UNSIGNED NOT NULL,
  alterado_por BIGINT UNSIGNED NOT NULL,
  status_anterior VARCHAR(40) NULL,
  status_novo VARCHAR(40) NOT NULL,
  observacao VARCHAR(500) NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_historico_solicitacao_data (solicitacao_id, criado_em),
  CONSTRAINT fk_historico_solicitacao
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_servico(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_historico_usuario
    FOREIGN KEY (alterado_por) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS avaliacoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id BIGINT UNSIGNED NOT NULL,
  cliente_id BIGINT UNSIGNED NOT NULL,
  profissional_id BIGINT UNSIGNED NOT NULL,
  nota TINYINT UNSIGNED NOT NULL,
  comentario VARCHAR(1000) NULL,
  marcadores JSON NULL,
  status_moderacao ENUM('publicada', 'oculta', 'em_analise') NOT NULL DEFAULT 'publicada',
  criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_avaliacoes_solicitacao (solicitacao_id),
  KEY idx_avaliacoes_profissional (profissional_id, status_moderacao),
  CONSTRAINT fk_avaliacoes_solicitacao
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_servico(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_avaliacoes_cliente
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_avaliacoes_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_avaliacoes_nota CHECK (nota BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS indicacoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  indicador_id BIGINT UNSIGNED NOT NULL,
  indicado_usuario_id BIGINT UNSIGNED NULL,
  profissional_id BIGINT UNSIGNED NULL,
  codigo VARCHAR(40) NOT NULL,
  email_indicado VARCHAR(180) NULL,
  status ENUM('enviada', 'cadastro_realizado', 'servico_contratado', 'servico_concluido', 'credito_liberado', 'cancelada') NOT NULL DEFAULT 'enviada',
  valor_credito DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  credito_liberado_em DATETIME NULL,
  criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_indicacoes_indicador_status (indicador_id, status),
  KEY idx_indicacoes_codigo (codigo),
  CONSTRAINT fk_indicacoes_indicador
    FOREIGN KEY (indicador_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_indicacoes_indicado
    FOREIGN KEY (indicado_usuario_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_indicacoes_profissional
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notificacoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  mensagem VARCHAR(500) NOT NULL,
  tipo VARCHAR(50) NOT NULL,
  destino VARCHAR(180) NULL,
  lida_em DATETIME NULL,
  criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_notificacoes_usuario_lida (usuario_id, lida_em, criada_em),
  CONSTRAINT fk_notificacoes_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tokens_recuperacao_senha (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_tokens_recuperacao_hash (token_hash),
  KEY idx_tokens_recuperacao_usuario (usuario_id, expira_em),
  CONSTRAINT fk_tokens_recuperacao_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS logs_administrativos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  administrador_id BIGINT UNSIGNED NOT NULL,
  acao VARCHAR(100) NOT NULL,
  entidade VARCHAR(80) NOT NULL,
  entidade_id BIGINT UNSIGNED NULL,
  detalhes JSON NULL,
  ip VARCHAR(45) NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_logs_admin_data (administrador_id, criado_em),
  CONSTRAINT fk_logs_administrador
    FOREIGN KEY (administrador_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Dados iniciais para desenvolvimento e homologação dos fluxos.
INSERT IGNORE INTO condominios
  (id, nome, cnpj, cep, logradouro, numero, bairro, cidade, uf)
VALUES
  (1, 'Edifício Solar', NULL, '11730000', 'Avenida Principal', '402', 'Centro', 'Mongaguá', 'SP'),
  (2, 'Condomínio Atlântico', NULL, '11701000', 'Avenida da Praia', '1200', 'Guilhermina', 'Praia Grande', 'SP'),
  (3, 'Residencial Mar Azul', NULL, '11705000', 'Rua das Ondas', '315', 'Aviação', 'Praia Grande', 'SP'),
  (4, 'Edifício Costa Azul', NULL, '11730000', 'Rua do Sol', '703', 'Centro', 'Mongaguá', 'SP');

INSERT IGNORE INTO usuarios
  (id, condominio_id, nome, email, telefone, senha_hash, tipo, status, bloco, unidade, email_verificado_em)
VALUES
  (1, 1, 'Maria Silva', 'maria@condominio.com', '13999990000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'cliente', 'ativo', 'B', '402', NOW()),
  (2, NULL, 'Roberto Carlos', 'roberto@servicos.com', '13999990001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (3, NULL, 'André Lima', 'andre@servicos.com', '13999990002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (4, NULL, 'Renata Souza', 'renata@servicos.com', '13999990003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (5, NULL, 'Administrador SGB', 'admin@sgbtech.com', '13999990004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'administrador', 'ativo', NULL, NULL, NOW()),
  (6, NULL, 'Marcelo Oliveira', 'marcelo@servicos.com', '13999990005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (7, NULL, 'Camila Fernandes', 'camila@servicos.com', '13999990006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (8, NULL, 'Diego Martins', 'diego@servicos.com', '13999990007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (9, NULL, 'Beatriz Gomes', 'beatriz@servicos.com', '13999990008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (10, NULL, 'Lucas Carvalho', 'lucas@servicos.com', '13999990009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'ativo', NULL, NULL, NOW()),
  (11, 2, 'Paulo Mendes', 'paulo@condominio.com', '13988884102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'cliente', 'ativo', NULL, '305', NOW()),
  (12, 3, 'Ana Pereira', 'ana@condominio.com', '13988884103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'cliente', 'ativo', NULL, '204', NOW()),
  (13, 4, 'Juliana Rocha', 'juliana@condominio.com', '13988884104', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'cliente', 'ativo', NULL, '703', NOW()),
  (14, NULL, 'Marcos Ferreira', 'marcos@servicos.com', '13991247732', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'pendente', NULL, NULL, NOW()),
  (15, NULL, 'Eliana Costa', 'eliana@servicos.com', '13997451180', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'profissional', 'pendente', NULL, NULL, NOW());

INSERT IGNORE INTO categorias_servico (id, nome, descricao, icone) VALUES
  (1, 'Elétrica', 'Instalações e reparos elétricos', 'zap'),
  (2, 'Hidráulica', 'Vazamentos e manutenção hidráulica', 'droplets'),
  (3, 'Pintura', 'Pintura interna, externa e acabamento', 'paintbrush'),
  (4, 'Reformas', 'Reformas residenciais e condominiais', 'hammer'),
  (5, 'Limpeza', 'Limpeza técnica e pós-obra', 'sparkles'),
  (6, 'Manutenção', 'Manutenção preventiva e corretiva', 'wrench'),
  (7, 'Gesso', 'Forros, sancas, reparos e acabamento em gesso', 'building-2'),
  (8, 'Marcenaria', 'Montagem, ajustes e reparos de móveis', 'briefcase-business');

INSERT IGNORE INTO profissionais
  (id, usuario_id, cnpj, razao_social, nome_fantasia, descricao, anos_experiencia, taxa_visita, raio_atendimento_km, media_avaliacao, quantidade_avaliacoes, status_validacao, cnpj_situacao, cnpj_consultado_em, validado_por, validado_em)
VALUES
  (1, 2, '12345678000190', 'Roberto Carlos Serviços Elétricos MEI', 'RC Elétrica', 'Instalações, reparos e manutenção elétrica em condomínios.', 8, 80.00, 25, 4.90, 86, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (2, 3, '23456789000101', 'André Lima Serviços Hidráulicos MEI', 'AL Hidráulica', 'Detecção de vazamentos e reparos hidráulicos.', 6, 70.00, 20, 4.80, 64, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (3, 4, '34567890000112', 'Renata Souza Pinturas MEI', 'RS Pinturas', 'Pintura residencial, predial e acabamento.', 7, 65.00, 30, 4.70, 51, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (4, 6, '45678901000123', 'Marcelo Oliveira Reformas MEI', 'MO Reformas', 'Pequenas reformas, revestimentos e reparos civis.', 10, 90.00, 25, 4.80, 43, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (5, 7, '56789012000134', 'Camila Fernandes Limpeza MEI', 'CF Limpeza', 'Limpeza técnica, pós-obra e de áreas comuns.', 5, 45.00, 30, 4.90, 72, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (6, 8, '67890123000145', 'Diego Martins Manutenção MEI', 'DM Manutenção', 'Manutenção preventiva e inspeções em condomínios.', 9, 60.00, 35, 4.70, 39, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (7, 9, '78901234000156', 'Beatriz Gomes Gesso MEI', 'BG Gesso', 'Forros, sancas e reparos de gesso.', 6, 55.00, 30, 4.80, 34, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (8, 10, '89012345000167', 'Lucas Carvalho Marcenaria MEI', 'LC Marcenaria', 'Ajustes, montagem e reparos de móveis.', 11, 75.00, 30, 4.90, 47, 'aprovado', 'ativo', NOW(), 5, NOW()),
  (9, 14, '48159267000134', 'Marcos Ferreira Serviços Elétricos MEI', 'MF Elétrica', 'Instalações e manutenção elétrica residencial e predial.', 7, 75.00, 25, 0.00, 0, 'em_analise', 'nao_consultado', NULL, NULL, NULL),
  (10, 15, '53804912000108', 'Eliana Costa Pinturas MEI', 'EC Pinturas', 'Pintura interna, fachadas e acabamento de áreas comuns.', 5, 60.00, 30, 0.00, 0, 'em_analise', 'nao_consultado', NULL, NULL, NULL);

INSERT IGNORE INTO profissional_categoria (profissional_id, categoria_id, principal) VALUES
  (1, 1, TRUE), (1, 6, FALSE),
  (2, 2, TRUE), (2, 6, FALSE),
  (3, 3, TRUE), (3, 4, FALSE),
  (4, 4, TRUE),
  (5, 5, TRUE),
  (6, 6, TRUE),
  (7, 7, TRUE),
  (8, 8, TRUE),
  (9, 1, TRUE),
  (10, 3, TRUE);

INSERT IGNORE INTO documentos_profissional
  (id, profissional_id, tipo, nome_arquivo, caminho_arquivo, mime_type, status, analisado_por, analisado_em)
VALUES
  (1, 1, 'ccmei', 'ccmei-roberto.pdf', '/uploads/profissionais/1/ccmei-roberto.pdf', 'application/pdf', 'aprovado', 5, NOW()),
  (2, 1, 'certificado', 'nr10-roberto.pdf', '/uploads/profissionais/1/nr10-roberto.pdf', 'application/pdf', 'aprovado', 5, NOW()),
  (3, 1, 'portfolio', 'portfolio-roberto.pdf', '/uploads/profissionais/1/portfolio-roberto.pdf', 'application/pdf', 'aprovado', 5, NOW()),
  (4, 9, 'ccmei', 'ccmei-marcos.pdf', '/uploads/profissionais/9/ccmei-marcos.pdf', 'application/pdf', 'pendente', NULL, NULL),
  (5, 9, 'identificacao', 'identidade-marcos.pdf', '/uploads/profissionais/9/identidade-marcos.pdf', 'application/pdf', 'pendente', NULL, NULL),
  (6, 9, 'certificado', 'nr10-marcos.pdf', '/uploads/profissionais/9/nr10-marcos.pdf', 'application/pdf', 'pendente', NULL, NULL),
  (7, 9, 'portfolio', 'portfolio-marcos.pdf', '/uploads/profissionais/9/portfolio-marcos.pdf', 'application/pdf', 'pendente', NULL, NULL),
  (8, 10, 'ccmei', 'ccmei-eliana.pdf', '/uploads/profissionais/10/ccmei-eliana.pdf', 'application/pdf', 'pendente', NULL, NULL),
  (9, 10, 'identificacao', 'identidade-eliana.pdf', '/uploads/profissionais/10/identidade-eliana.pdf', 'application/pdf', 'pendente', NULL, NULL),
  (10, 10, 'certificado', 'curso-pintura-eliana.pdf', '/uploads/profissionais/10/curso-pintura-eliana.pdf', 'application/pdf', 'pendente', NULL, NULL),
  (11, 10, 'portfolio', 'portfolio-eliana.pdf', '/uploads/profissionais/10/portfolio-eliana.pdf', 'application/pdf', 'pendente', NULL, NULL);

INSERT IGNORE INTO verificacoes_profissional
  (id, profissional_id, tipo, status, fonte, protocolo, resultado, verificado_por, verificado_em)
VALUES
  (1, 1, 'cnpj', 'aprovado', 'Receita Federal', 'CNPJ-ROB-2026-001', JSON_OBJECT('situacao', 'ATIVA'), 5, NOW()),
  (2, 1, 'certificado', 'aprovado', 'Análise administrativa', 'DOC-ROB-2026-001', JSON_OBJECT('documento', 'NR-10'), 5, NOW()),
  (3, 9, 'cnpj', 'pendente', 'Receita Federal', NULL, NULL, NULL, NULL),
  (4, 9, 'identidade', 'pendente', 'Análise administrativa', NULL, NULL, NULL, NULL),
  (5, 9, 'certificado', 'pendente', 'Análise administrativa', NULL, NULL, NULL, NULL),
  (6, 9, 'portfolio', 'pendente', 'Análise administrativa', NULL, NULL, NULL, NULL),
  (7, 10, 'cnpj', 'pendente', 'Receita Federal', NULL, NULL, NULL, NULL),
  (8, 10, 'identidade', 'pendente', 'Análise administrativa', NULL, NULL, NULL, NULL),
  (9, 10, 'certificado', 'pendente', 'Análise administrativa', NULL, NULL, NULL, NULL),
  (10, 10, 'portfolio', 'pendente', 'Análise administrativa', NULL, NULL, NULL, NULL);

INSERT IGNORE INTO solicitacoes_servico
  (id, cliente_id, condominio_id, categoria_id, titulo, descricao, local_atendimento, prioridade, status, previsao_conclusao)
VALUES
  (1198, 1, 1, 3, 'Pintura da sala', 'Pintura completa da sala com correção de pequenas manchas na parede.', 'Edifício Solar · Bloco B · Apartamento 402', 'normal', 'concluida', '2026-08-12 17:00:00'),
  (1247, 1, 1, 2, 'Vazamento na cozinha', 'Vazamento contínuo sob a pia, próximo ao registro da cozinha.', 'Edifício Solar · Bloco B · Apartamento 402', 'normal', 'orcamento_recebido', '2026-09-05 12:00:00'),
  (1248, 1, 1, 1, 'Reparo elétrico', 'Tomadas da cozinha sem energia e disjuntor desarmando.', 'Edifício Solar · Bloco B · Apartamento 402', 'normal', 'em_execucao', '2026-09-03 17:00:00'),
  (1258, 1, 1, 1, 'Tomadas sem energia', 'As tomadas da cozinha pararam de funcionar e o disjuntor desarma ao ligar alguns equipamentos.', 'Edifício Solar · Bloco B · Apartamento 402', 'normal', 'publicada', NULL),
  (1259, 11, 2, 1, 'Troca de luminárias', 'Substituição de quatro luminárias da garagem por modelos de LED já adquiridos pelo condomínio.', 'Condomínio Atlântico · Garagem', 'normal', 'publicada', NULL),
  (1260, 12, 3, 1, 'Revisão do quadro elétrico', 'O quadro da área comum precisa de inspeção preventiva e identificação dos circuitos.', 'Residencial Mar Azul · Área comum', 'normal', 'publicada', NULL),
  (1261, 13, 4, 1, 'Instalação de ventilador', 'Instalação de ventilador de teto em um apartamento, com ponto elétrico já disponível.', 'Edifício Costa Azul · Apartamento 703', 'normal', 'publicada', NULL);

INSERT IGNORE INTO imagens_solicitacao
  (id, solicitacao_id, enviado_por, etapa, caminho_arquivo, legenda)
VALUES
  (1, 1248, 1, 'antes', '/uploads/solicitacoes/1248/quadro-disjuntores.jpg', 'Quadro de disjuntores'),
  (2, 1248, 1, 'antes', '/uploads/solicitacoes/1248/tomadas-cozinha.jpg', 'Tomadas da cozinha');

INSERT IGNORE INTO agendamentos
  (id, solicitacao_id, profissional_id, proposto_por, data_hora_inicio, data_hora_fim, taxa_visita, status)
VALUES
  (1, 1248, 1, 1, '2026-09-04 14:00:00', '2026-09-04 15:00:00', 80.00, 'confirmado'),
  (2, 1247, 2, 1, '2026-08-29 10:30:00', '2026-08-29 11:30:00', 70.00, 'realizado'),
  (3, 1198, 3, 1, '2026-08-08 09:00:00', '2026-08-08 10:00:00', 65.00, 'realizado');

INSERT IGNORE INTO conversas (id, solicitacao_id, profissional_id, ativa) VALUES
  (1, 1248, 1, TRUE),
  (2, 1259, 1, TRUE);

INSERT IGNORE INTO mensagens (id, conversa_id, remetente_id, conteudo, tipo, lida_em, criada_em) VALUES
  (1, 1, 2, 'Olá, Maria! Vi seu chamado. Posso realizar a visita amanhã?', 'texto', NOW(), '2026-09-03 09:40:00'),
  (2, 1, 1, 'Pode ser às 14h?', 'texto', NOW(), '2026-09-03 09:42:00'),
  (3, 1, 2, 'Combinado. A taxa da visita é de R$ 80,00 e já ficou registrada.', 'texto', NULL, '2026-09-03 09:43:00'),
  (4, 2, 2, 'Olá, Paulo! Vi o chamado sobre a troca das luminárias da garagem.', 'texto', NOW(), '2026-09-03 10:05:00'),
  (5, 2, 11, 'Olá, Roberto. As quatro luminárias de LED já foram compradas.', 'texto', NOW(), '2026-09-03 10:08:00'),
  (6, 2, 2, 'Perfeito. Posso fazer a visita na sexta-feira às 10h30.', 'texto', NULL, '2026-09-03 10:10:00');

INSERT IGNORE INTO orcamentos
  (id, solicitacao_id, profissional_id, agendamento_id, descricao, valor_mao_obra, valor_materiais, valor_visita, prazo_dias, garantia_dias, validade_ate, status, respondido_em)
VALUES
  (1, 1248, 1, 1, 'Substituição do disjuntor, revisão do circuito e teste das tomadas afetadas.', 580.00, 95.00, 80.00, 1, 90, '2026-09-10', 'aprovado', '2026-09-02 16:42:00'),
  (2, 1247, 2, 2, 'Identificação da origem do vazamento, troca da vedação e teste de estanqueidade.', 390.00, 85.00, 70.00, 1, 90, '2026-09-08', 'enviado', NULL),
  (3, 1198, 3, 3, 'Preparação das paredes, correção das manchas e aplicação de duas demãos de tinta.', 920.00, 420.00, 65.00, 3, 120, '2026-08-15', 'aprovado', '2026-08-08 14:20:00');

INSERT IGNORE INTO itens_orcamento (id, orcamento_id, descricao, quantidade, valor_unitario) VALUES
  (1, 1, 'Mão de obra elétrica', 1.00, 580.00),
  (2, 1, 'Materiais estimados', 1.00, 95.00),
  (3, 1, 'Visita técnica', 1.00, 80.00),
  (4, 2, 'Mão de obra hidráulica', 1.00, 390.00),
  (5, 2, 'Materiais estimados', 1.00, 85.00),
  (6, 2, 'Visita técnica', 1.00, 70.00),
  (7, 3, 'Mão de obra de pintura', 1.00, 920.00),
  (8, 3, 'Materiais estimados', 1.00, 420.00),
  (9, 3, 'Visita técnica', 1.00, 65.00);

INSERT IGNORE INTO pagamentos
  (id, orcamento_id, cliente_id, profissional_id, gateway, transacao_gateway, forma_pagamento, valor, status, pago_em, liberado_em)
VALUES
  (1, 3, 1, 3, 'gateway_configurado', 'txn_1198_20260812', 'pix', 1405.00, 'liberado', '2026-08-12 17:10:00', '2026-08-12 17:11:00');

INSERT IGNORE INTO avaliacoes
  (id, solicitacao_id, cliente_id, profissional_id, nota, comentario, marcadores, status_moderacao)
VALUES
  (1, 1198, 1, 3, 5, 'Serviço pontual, organizado e com ótimo acabamento.', JSON_ARRAY('Pontual', 'Cuidadoso', 'Recomendaria'), 'publicada');

INSERT IGNORE INTO historico_status_servico
  (id, solicitacao_id, alterado_por, status_anterior, status_novo, observacao, criado_em)
VALUES
  (1, 1248, 1, NULL, 'publicada', 'Solicitação criada pela cliente.', '2026-09-02 08:15:00'),
  (2, 1248, 2, 'publicada', 'visita_agendada', 'Visita confirmada com Roberto Carlos.', '2026-09-02 10:00:00'),
  (3, 1248, 2, 'visita_agendada', 'orcamento_recebido', 'Orçamento enviado pelo profissional.', '2026-09-02 15:30:00'),
  (4, 1248, 1, 'orcamento_recebido', 'orcamento_aprovado', 'Orçamento aprovado pela cliente.', '2026-09-02 16:42:00'),
  (5, 1248, 2, 'orcamento_aprovado', 'em_execucao', 'Serviço iniciado.', '2026-09-03 13:10:00');

INSERT IGNORE INTO notificacoes (id, usuario_id, titulo, mensagem, tipo, destino, lida_em) VALUES
  (1, 1, 'Mensagem de Roberto Carlos', 'A taxa da visita já ficou registrada.', 'mensagem', '/mensagens/1', NULL),
  (2, 1, 'Orçamento recebido', 'Roberto enviou uma proposta para o chamado #1248.', 'orcamento', '/solicitacoes/1248/orcamento', NULL),
  (3, 2, 'Visita confirmada', 'Maria confirmou a visita de amanhã às 14:00.', 'agendamento', '/agenda/1', NULL);

INSERT IGNORE INTO indicacoes
  (id, indicador_id, indicado_usuario_id, profissional_id, codigo, email_indicado, status, valor_credito, credito_liberado_em)
VALUES
  (1, 1, NULL, 1, 'MARIA-CONNECT', 'fernanda@example.com', 'credito_liberado', 20.00, NOW());

-- Consulta útil para recalcular a nota dos profissionais após novas avaliações.
-- UPDATE profissionais p
-- JOIN (
--   SELECT profissional_id, AVG(nota) AS media, COUNT(*) AS quantidade
--   FROM avaliacoes
--   WHERE status_moderacao = 'publicada'
--   GROUP BY profissional_id
-- ) a ON a.profissional_id = p.id
-- SET p.media_avaliacao = a.media,
--     p.quantidade_avaliacoes = a.quantidade;
