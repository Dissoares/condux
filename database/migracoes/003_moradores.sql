CREATE TABLE moradores (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT UNSIGNED NOT NULL,
    unidade_id   INT UNSIGNED NOT NULL,
    responsavel  TINYINT(1)   NOT NULL DEFAULT 0,
    data_entrada DATE         NOT NULL,
    data_saida   DATE         NULL,
    ativo        TINYINT(1)   NOT NULL DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
