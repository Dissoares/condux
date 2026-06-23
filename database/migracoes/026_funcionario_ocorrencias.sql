-- Ocorrências do funcionário: folga, férias, falta, atestado, adiantamento
CREATE TABLE IF NOT EXISTS funcionario_ocorrencias (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    funcionario_id  INT UNSIGNED  NOT NULL,
    tipo            ENUM('folga','ferias','falta','atestado','adiantamento') NOT NULL,
    data_inicio     DATE          NOT NULL,
    data_fim        DATE          NULL,
    valor           DECIMAL(10,2) NULL COMMENT 'Usado no tipo adiantamento',
    justificativa   TEXT          NULL,
    anexo           VARCHAR(500)  NULL COMMENT 'Caminho relativo do arquivo',
    nome_original   VARCHAR(255)  NULL,
    status          ENUM('pendente','aprovado','reprovado') NOT NULL DEFAULT 'aprovado',
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
