CREATE TABLE projetos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(180)  NOT NULL,
    descricao       TEXT          NULL,
    idealizador     VARCHAR(120)  NULL,
    responsavel_id  INT UNSIGNED  NULL,
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
