CREATE TABLE usuarios (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(120)                             NOT NULL,
    email         VARCHAR(180)                             NOT NULL UNIQUE,
    senha         VARCHAR(255)                             NOT NULL,
    perfil        ENUM('sindico','subsindico','morador')   NOT NULL DEFAULT 'morador',
    ativo         TINYINT(1)                               NOT NULL DEFAULT 1,
    criado_em     DATETIME                                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME                                 NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
