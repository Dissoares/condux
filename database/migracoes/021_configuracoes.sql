CREATE TABLE IF NOT EXISTS configuracoes (
    chave        VARCHAR(80)  NOT NULL PRIMARY KEY,
    valor        TEXT         NULL,
    atualizado_em DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO configuracoes (chave, valor) VALUES
    ('taxa_dia_vencimento', '10'),
    ('taxa_valor_mensal',   NULL);
