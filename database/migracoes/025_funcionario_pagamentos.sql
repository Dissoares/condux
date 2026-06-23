-- Dia de pagamento do salário no cadastro do funcionário
ALTER TABLE funcionarios
    ADD COLUMN dia_pagamento TINYINT UNSIGNED NULL COMMENT 'Dia do mês em que o salário é pago' AFTER salario;

-- Registro de pagamentos de salário por competência
CREATE TABLE IF NOT EXISTS funcionario_pagamentos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    funcionario_id  INT UNSIGNED     NOT NULL,
    competencia     VARCHAR(7)       NOT NULL COMMENT 'AAAA-MM',
    valor           DECIMAL(10,2)    NOT NULL,
    data_prevista   DATE             NULL,
    data_pagamento  DATE             NULL,
    status          ENUM('pendente','pago') NOT NULL DEFAULT 'pendente',
    observacoes     TEXT             NULL,
    criado_em       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_func_comp (funcionario_id, competencia),
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
