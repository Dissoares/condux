-- Contas mensais do condomínio (água, luz, telefone, etc.)
CREATE TABLE IF NOT EXISTS contas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descricao       VARCHAR(150)  NOT NULL,
    categoria       VARCHAR(80)   NOT NULL DEFAULT 'outros',
    competencia     VARCHAR(7)    NOT NULL COMMENT 'AAAA-MM',
    fornecedor      VARCHAR(150)  NULL,
    valor           DECIMAL(10,2) NOT NULL,
    data_vencimento DATE          NULL,
    data_pagamento  DATE          NULL,
    status          ENUM('pendente','pago') NOT NULL DEFAULT 'pendente',
    observacoes     TEXT          NULL,
    anexo           VARCHAR(500)  NULL,
    nome_original   VARCHAR(255)  NULL,
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
