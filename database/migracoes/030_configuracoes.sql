CREATE TABLE IF NOT EXISTS configuracoes (
    chave     VARCHAR(100) NOT NULL PRIMARY KEY,
    valor     TEXT         NULL,
    criado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Valores padrão
INSERT IGNORE INTO configuracoes (chave, valor) VALUES
  ('app_nome',        'Condux'),
  ('app_nome_curto',  'Condux'),
  ('app_descricao',   'Gestão de condomínio'),
  ('app_logo',        NULL),
  ('cor_primaria',    '#1a3c5e'),
  ('cor_escura',      '#0f2540'),
  ('cor_acento',      '#f0a500');
