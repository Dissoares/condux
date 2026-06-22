CREATE TABLE taxas_extras (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(120)  NOT NULL,
    descricao  TEXT          NULL,
    valor      DECIMAL(10,2) NOT NULL,
    vencimento DATE          NOT NULL,
    criado_em  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
