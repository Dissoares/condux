CREATE TABLE vistorias (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unidade_id     INT UNSIGNED NOT NULL,
    responsavel_id INT UNSIGNED NULL,
    data_vistoria  DATE         NOT NULL,
    descricao      TEXT         NULL,
    status         ENUM('agendada','realizada','cancelada') NOT NULL DEFAULT 'agendada',
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unidade_id)     REFERENCES unidades(id),
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
