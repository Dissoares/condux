-- Configurações SMTP (inseridas sem sobrescrever se já existirem)
INSERT IGNORE INTO configuracoes (chave, valor) VALUES
('email_ativo',            '0'),
('email_smtp_host',        ''),
('email_smtp_porta',       '587'),
('email_smtp_usuario',     ''),
('email_smtp_senha',       ''),
('email_smtp_seguranca',   'tls'),
('email_remetente_nome',   ''),
('email_remetente_email',  '');

-- Controle de aviso de vencimento em taxas_condominiais
SET @db = DATABASE();

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'taxas_condominiais' AND COLUMN_NAME = 'aviso_vencida_em') = 0,
    'ALTER TABLE taxas_condominiais ADD COLUMN aviso_vencida_em DATETIME NULL DEFAULT NULL AFTER data_pagamento',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Controle de aviso de vencimento em taxas_extras_unidades
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'taxas_extras_unidades' AND COLUMN_NAME = 'aviso_vencida_em') = 0,
    'ALTER TABLE taxas_extras_unidades ADD COLUMN aviso_vencida_em DATETIME NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Token para recuperação de senha
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'reset_token') = 0,
    'ALTER TABLE usuarios ADD COLUMN reset_token VARCHAR(64) NULL AFTER foto, ADD COLUMN reset_expira_em DATETIME NULL AFTER reset_token',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
