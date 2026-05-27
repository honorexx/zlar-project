CREATE DATABASE IF NOT EXISTS zlar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zlar;

DROP TABLE IF EXISTS recuperacoes_senha;
DROP TABLE IF EXISTS admin_acessos;
DROP TABLE IF EXISTS chamados_suporte;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS pagamentos;
DROP TABLE IF EXISTS solicitacoes;
DROP TABLE IF EXISTS prestadores;
DROP TABLE IF EXISTS moradores;
DROP TABLE IF EXISTS equipe_suporte;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('morador','prestador','admin') NOT NULL,
  nome VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  telefone VARCHAR(30),
  senha_hash VARCHAR(255) NOT NULL,
  status ENUM('ativo','inativo','bloqueado') NOT NULL DEFAULT 'ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE moradores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  cpf VARCHAR(20) NOT NULL UNIQUE,
  nascimento DATE NOT NULL,
  endereco VARCHAR(255),
  cidade VARCHAR(120),
  estado VARCHAR(2),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE prestadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  servico VARCHAR(120) NOT NULL,
  descricao TEXT,
  nascimento DATE NOT NULL,
  status_aprovacao ENUM('em_analise','aprovado','reprovado','bloqueado') NOT NULL DEFAULT 'em_analise',
  nota_media DECIMAL(3,2) DEFAULT 0,
  total_avaliacoes INT NOT NULL DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE equipe_suporte (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  cargo VARCHAR(80) NOT NULL,
  status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE solicitacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  morador_id INT NULL,
  prestador_id INT NULL,
  categoria VARCHAR(120) NOT NULL,
  data_desejada DATE NULL,
  endereco VARCHAR(255) NOT NULL,
  descricao TEXT NOT NULL,
  status ENUM('aberta','em_analise','aceita','em_andamento','concluida','cancelada') NOT NULL DEFAULT 'aberta',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE SET NULL,
  FOREIGN KEY (prestador_id) REFERENCES prestadores(id) ON DELETE SET NULL
);

CREATE TABLE pagamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id INT NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  status ENUM('pendente','pago','estornado','cancelado') NOT NULL DEFAULT 'pendente',
  metodo VARCHAR(60),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE
);

CREATE TABLE avaliacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id INT NOT NULL,
  morador_id INT NULL,
  prestador_id INT NULL,
  nota INT NOT NULL,
  comentario TEXT,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_avaliacao_solicitacao (solicitacao_id),
  FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
  FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE SET NULL,
  FOREIGN KEY (prestador_id) REFERENCES prestadores(id) ON DELETE SET NULL
);

CREATE TABLE recuperacoes_senha (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(160) NOT NULL,
  tipo ENUM('morador','prestador','admin') NOT NULL,
  codigo VARCHAR(12) NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  expira_em DATETIME NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_acessos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(80) NOT NULL UNIQUE,
  codigo VARCHAR(20) NOT NULL,
  status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chamados_suporte (
  id INT AUTO_INCREMENT PRIMARY KEY,
  perfil ENUM('morador','prestador') NOT NULL,
  usuario_nome VARCHAR(160) NOT NULL,
  usuario_email VARCHAR(160),
  tipo VARCHAR(160) NOT NULL,
  urgencia ENUM('Baixa','Media','Alta') NOT NULL DEFAULT 'Media',
  descricao TEXT NOT NULL,
  status ENUM('aberto','em_atendimento','resolvido','cancelado') NOT NULL DEFAULT 'aberto',
  resposta TEXT,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NULL
);

INSERT INTO usuarios (tipo, nome, email, telefone, senha_hash, status)
VALUES
('admin', 'Administrador Zlar', 'zlar2026', NULL, '$2y$10$example.placeholder.hash.configure.no.php', 'ativo');

INSERT INTO admin_acessos (usuario, codigo, status)
VALUES ('zlar2026', '747171', 'ativo');

INSERT INTO equipe_suporte (nome, email, cargo, status)
VALUES
('Equipe Zlar', 'suporte@zlar.com.br', 'Suporte', 'ativo');
