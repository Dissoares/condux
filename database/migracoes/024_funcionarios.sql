-- Tabela de funcionários do condomínio
CREATE TABLE IF NOT EXISTS funcionarios (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(150)  NOT NULL,
    cpf          VARCHAR(14)   NULL,
    cargo        VARCHAR(100)  NOT NULL,
    departamento VARCHAR(100)  NULL,
    telefone     VARCHAR(20)   NULL,
    email        VARCHAR(150)  NULL,
    salario      DECIMAL(10,2) NULL,
    data_admissao DATE         NULL,
    data_demissao DATE         NULL,
    observacoes  TEXT          NULL,
    ativo        TINYINT(1)    NOT NULL DEFAULT 1,
    criado_em    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
