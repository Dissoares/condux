CREATE TABLE IF NOT EXISTS comunicados (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(200)  NOT NULL,
    conteudo        TEXT          NOT NULL,
    tipo            ENUM('aviso','urgente','informativo','assembleia','manutencao') NOT NULL DEFAULT 'aviso',
    publicado_por   INT UNSIGNED  NULL,
    data_publicacao DATE          NOT NULL,
    data_expiracao  DATE          NULL,
    ativo           TINYINT(1)    NOT NULL DEFAULT 1,
    criado_em       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
