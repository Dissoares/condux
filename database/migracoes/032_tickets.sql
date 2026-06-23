CREATE TABLE tickets (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo        VARCHAR(160)                                                    NOT NULL,
    descricao     TEXT                                                            NOT NULL,
    categoria     ENUM('sugestao','reclamacao','manutencao','informacao','outro') NOT NULL DEFAULT 'outro',
    status        ENUM('aberto','em_andamento','resolvido','fechado')             NOT NULL DEFAULT 'aberto',
    prioridade    ENUM('baixa','normal','alta','urgente')                         NOT NULL DEFAULT 'normal',
    usuario_id    INT UNSIGNED NOT NULL,
    responsavel_id INT UNSIGNED NULL,
    criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id)     REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ticket_mensagens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    mensagem   TEXT NOT NULL,
    interno    TINYINT(1) NOT NULL DEFAULT 0,
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id)  REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
