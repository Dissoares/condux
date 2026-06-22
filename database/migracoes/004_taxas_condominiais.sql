CREATE TABLE taxas_condominiais (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unidade_id     INT UNSIGNED  NOT NULL,
    competencia    CHAR(7)       NOT NULL,
    valor          DECIMAL(10,2) NOT NULL,
    vencimento     DATE          NOT NULL,
    status         ENUM('pendente','pago','vencido','isento') NOT NULL DEFAULT 'pendente',
    data_pagamento DATE          NULL,
    comprovante    VARCHAR(255)  NULL,
    observacao     TEXT          NULL,
    criado_em      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_unidade_competencia (unidade_id, competencia),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
