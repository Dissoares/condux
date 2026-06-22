CREATE TABLE projeto_anexos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projeto_id    INT UNSIGNED NOT NULL,
    tipo          ENUM('foto','video','nota_fiscal','documento') NOT NULL,
    caminho       VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    enviado_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
