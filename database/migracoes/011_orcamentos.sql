CREATE TABLE orcamentos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vistoria_id   INT UNSIGNED  NULL,
    projeto_id    INT UNSIGNED  NULL,
    prestadora_id INT UNSIGNED  NOT NULL,
    valor         DECIMAL(12,2) NOT NULL,
    descricao     TEXT          NULL,
    status        ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
    arquivo       VARCHAR(255)  NULL,
    criado_em     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vistoria_id)   REFERENCES vistorias(id),
    FOREIGN KEY (projeto_id)    REFERENCES projetos(id),
    FOREIGN KEY (prestadora_id) REFERENCES prestadoras(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
