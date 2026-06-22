-- ============================================================
-- Condux — Sistema de Gestão de Condomínio
-- Schema do banco de dados
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';

-- ------------------------------------------------------------
-- Usuários do sistema (síndico, subsíndico, morador)
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(120)                              NOT NULL,
    email           VARCHAR(180)                             NOT NULL UNIQUE,
    senha           VARCHAR(255)                             NOT NULL,
    perfil          ENUM('sindico','subsindico','morador')   NOT NULL DEFAULT 'morador',
    ativo           TINYINT(1)                               NOT NULL DEFAULT 1,
    criado_em       DATETIME                                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME                                 NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Unidades (apartamentos / salas)
-- ------------------------------------------------------------
CREATE TABLE unidades (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bloco           VARCHAR(10)  NULL,
    numero          VARCHAR(20)  NOT NULL,
    andar           SMALLINT     NULL,
    descricao       VARCHAR(255) NULL,
    ativo           TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Vínculo morador ↔ unidade (histórico de ocupação)
-- ------------------------------------------------------------
CREATE TABLE moradores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT UNSIGNED NOT NULL,
    unidade_id      INT UNSIGNED NOT NULL,
    responsavel     TINYINT(1)   NOT NULL DEFAULT 0,  -- responsável financeiro
    data_entrada    DATE         NOT NULL,
    data_saida      DATE         NULL,
    ativo           TINYINT(1)   NOT NULL DEFAULT 1,
    FOREIGN KEY (usuario_id)  REFERENCES usuarios(id),
    FOREIGN KEY (unidade_id)  REFERENCES unidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Taxas condominiais mensais por unidade
-- ------------------------------------------------------------
CREATE TABLE taxas_condominiais (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unidade_id      INT UNSIGNED NOT NULL,
    competencia     CHAR(7)      NOT NULL,  -- formato: 2025-01
    valor           DECIMAL(10,2) NOT NULL,
    vencimento      DATE          NOT NULL,
    status          ENUM('pendente','pago','vencido','isento') NOT NULL DEFAULT 'pendente',
    data_pagamento  DATE          NULL,
    comprovante     VARCHAR(255)  NULL,      -- caminho do arquivo enviado
    observacao      TEXT          NULL,
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_unidade_competencia (unidade_id, competencia),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Taxas extras (obras, eventos, fundo de reserva etc.)
-- ------------------------------------------------------------
CREATE TABLE taxas_extras (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(120)  NOT NULL,
    descricao       TEXT          NULL,
    valor           DECIMAL(10,2) NOT NULL,
    vencimento      DATE          NOT NULL,
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Cobrança de taxa extra por unidade
-- ------------------------------------------------------------
CREATE TABLE taxas_extras_unidades (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    taxa_extra_id   INT UNSIGNED  NOT NULL,
    unidade_id      INT UNSIGNED  NOT NULL,
    status          ENUM('pendente','pago','vencido','isento') NOT NULL DEFAULT 'pendente',
    data_pagamento  DATE          NULL,
    comprovante     VARCHAR(255)  NULL,
    FOREIGN KEY (taxa_extra_id) REFERENCES taxas_extras(id),
    FOREIGN KEY (unidade_id)    REFERENCES unidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Prestadoras de serviço
-- ------------------------------------------------------------
CREATE TABLE prestadoras (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150)  NOT NULL,
    cnpj            VARCHAR(18)   NULL,
    contato         VARCHAR(100)  NULL,
    telefone        VARCHAR(20)   NULL,
    email           VARCHAR(180)  NULL,
    ativo           TINYINT(1)    NOT NULL DEFAULT 1,
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Projetos (portal da transparência)
-- ------------------------------------------------------------
CREATE TABLE projetos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(180)  NOT NULL,
    descricao       TEXT          NULL,
    idealizador     VARCHAR(120)  NULL,
    responsavel_id  INT UNSIGNED  NULL,      -- usuário responsável
    prestadora_id   INT UNSIGNED  NULL,
    status          ENUM('pendente','aprovado','em_andamento','concluido','cancelado') NOT NULL DEFAULT 'pendente',
    valor_estimado  DECIMAL(12,2) NULL,
    valor_realizado DECIMAL(12,2) NULL,
    data_inicio     DATE          NULL,
    data_conclusao  DATE          NULL,
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
    FOREIGN KEY (prestadora_id)  REFERENCES prestadoras(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Anexos de projeto (fotos, vídeos, notas fiscais, documentos)
-- ------------------------------------------------------------
CREATE TABLE projeto_anexos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projeto_id      INT UNSIGNED NOT NULL,
    tipo            ENUM('foto','video','nota_fiscal','documento') NOT NULL,
    caminho         VARCHAR(255) NOT NULL,
    nome_original   VARCHAR(255) NOT NULL,
    enviado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Vistorias de unidades
-- ------------------------------------------------------------
CREATE TABLE vistorias (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unidade_id      INT UNSIGNED NOT NULL,
    responsavel_id  INT UNSIGNED NULL,
    data_vistoria   DATE         NOT NULL,
    descricao       TEXT         NULL,
    status          ENUM('agendada','realizada','cancelada') NOT NULL DEFAULT 'agendada',
    criado_em       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unidade_id)     REFERENCES unidades(id),
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Orçamentos vinculados a vistorias ou projetos
-- ------------------------------------------------------------
CREATE TABLE orcamentos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vistoria_id     INT UNSIGNED  NULL,
    projeto_id      INT UNSIGNED  NULL,
    prestadora_id   INT UNSIGNED  NOT NULL,
    valor           DECIMAL(12,2) NOT NULL,
    descricao       TEXT          NULL,
    status          ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
    arquivo         VARCHAR(255)  NULL,
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vistoria_id)   REFERENCES vistorias(id),
    FOREIGN KEY (projeto_id)    REFERENCES projetos(id),
    FOREIGN KEY (prestadora_id) REFERENCES prestadoras(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Dados iniciais — síndico padrão (senha: condux@2025)
-- ------------------------------------------------------------
INSERT INTO usuarios (nome, email, senha, perfil) VALUES
('Administrador', 'admin@condux.com', '$2y$12$eImiTXuWVxfM37uY4JANjOzTbS3qMtQF7yM2DOq/W3XP1RwvRDxqm', 'sindico');
