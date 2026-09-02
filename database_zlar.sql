SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS recuperacoes_senha;
DROP TABLE IF EXISTS chamados_suporte;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS pagamentos;
DROP TABLE IF EXISTS solicitacoes;
DROP TABLE IF EXISTS prestadores;
DROP TABLE IF EXISTS moradores;
DROP TABLE IF EXISTS equipe_suporte;
DROP TABLE IF EXISTS admin_acessos;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('morador','prestador') NOT NULL,
  nome VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  telefone VARCHAR(30) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  status ENUM('ativo','inativo','bloqueado') NOT NULL DEFAULT 'ativo',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_usuario_email (email),
  KEY idx_usuario_tipo_status (tipo, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE moradores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  cpf VARCHAR(20) NOT NULL,
  nascimento DATE NOT NULL,
  endereco VARCHAR(255) NOT NULL,
  cidade VARCHAR(120) NULL,
  estado CHAR(2) NULL,
  UNIQUE KEY uniq_morador_usuario (usuario_id),
  UNIQUE KEY uniq_morador_cpf (cpf),
  CONSTRAINT fk_morador_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prestadores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  servico VARCHAR(120) NOT NULL,
  descricao TEXT NULL,
  nascimento DATE NOT NULL,
  status_aprovacao ENUM('em_analise','aprovado','reprovado','bloqueado') NOT NULL DEFAULT 'em_analise',
  nota_media DECIMAL(3,2) NOT NULL DEFAULT 0,
  total_avaliacoes INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_prestador_usuario (usuario_id),
  KEY idx_prestador_servico_aprovacao (servico, status_aprovacao),
  CONSTRAINT fk_prestador_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_acessos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(80) NOT NULL,
  nome VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  codigo_hash VARCHAR(255) NOT NULL,
  status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_admin_usuario (usuario),
  UNIQUE KEY uniq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE equipe_suporte (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  cargo VARCHAR(80) NOT NULL,
  status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_equipe_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE solicitacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  morador_id INT UNSIGNED NOT NULL,
  prestador_id INT UNSIGNED NULL,
  categoria VARCHAR(120) NOT NULL,
  data_desejada DATE NULL,
  endereco VARCHAR(255) NOT NULL,
  descricao TEXT NOT NULL,
  status ENUM('aberta','aceita','em_andamento','concluida','pagamento_solicitado','pago','cancelada') NOT NULL DEFAULT 'aberta',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  aceito_em DATETIME NULL,
  iniciado_em DATETIME NULL,
  concluido_em DATETIME NULL,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_solicitacao_morador (morador_id, criado_em),
  KEY idx_solicitacao_prestador (prestador_id, status),
  KEY idx_solicitacao_aberta (categoria, status),
  CONSTRAINT fk_solicitacao_morador FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE RESTRICT,
  CONSTRAINT fk_solicitacao_prestador FOREIGN KEY (prestador_id) REFERENCES prestadores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pagamentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id INT UNSIGNED NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  status ENUM('pendente','pago','estornado','cancelado') NOT NULL DEFAULT 'pendente',
  metodo VARCHAR(60) NOT NULL DEFAULT 'Pix',
  chave_pix VARCHAR(255) NOT NULL,
  observacao TEXT NULL,
  solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  pago_em DATETIME NULL,
  UNIQUE KEY uniq_pagamento_solicitacao (solicitacao_id),
  CONSTRAINT fk_pagamento_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE avaliacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id INT UNSIGNED NOT NULL,
  morador_id INT UNSIGNED NOT NULL,
  prestador_id INT UNSIGNED NOT NULL,
  nota TINYINT UNSIGNED NOT NULL,
  comentario TEXT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_avaliacao_solicitacao (solicitacao_id),
  KEY idx_avaliacao_prestador (prestador_id, criado_em),
  CONSTRAINT chk_avaliacao_nota CHECK (nota BETWEEN 1 AND 5),
  CONSTRAINT fk_avaliacao_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
  CONSTRAINT fk_avaliacao_morador FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE RESTRICT,
  CONSTRAINT fk_avaliacao_prestador FOREIGN KEY (prestador_id) REFERENCES prestadores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chamados_suporte (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  perfil ENUM('morador','prestador') NOT NULL,
  tipo VARCHAR(160) NOT NULL,
  urgencia ENUM('Baixa','Media','Alta') NOT NULL DEFAULT 'Media',
  descricao TEXT NOT NULL,
  status ENUM('aberto','em_atendimento','resolvido','cancelado') NOT NULL DEFAULT 'aberto',
  resposta TEXT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_chamado_usuario (usuario_id, criado_em),
  KEY idx_chamado_status (status, urgencia),
  CONSTRAINT fk_chamado_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recuperacoes_senha (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identificador VARCHAR(160) NOT NULL,
  tipo ENUM('morador','prestador','admin') NOT NULL,
  codigo_hash CHAR(64) NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  expira_em DATETIME NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_recuperacao_busca (identificador, tipo, usado, expira_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_acessos (usuario, nome, email, codigo_hash, status)
VALUES ('zlar2026', 'Administrador Zlar', 'admin@zlar.local', '$2y$10$dYi/YBt4hP5WMhXs0XKO3O0AGaeT/.uZcJc9QFBW24H4lGyVWRrJy', 'ativo');

INSERT INTO equipe_suporte (nome, email, cargo, status)
VALUES ('Equipe Zlar', 'suporte@zlar.com.br', 'Suporte', 'ativo');
