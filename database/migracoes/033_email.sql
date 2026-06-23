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

-- Controle de aviso de vencimento (evita spam)
ALTER TABLE taxas_condominiais
  ADD COLUMN aviso_vencida_em DATETIME NULL DEFAULT NULL AFTER data_pagamento;

ALTER TABLE taxa_extra_unidade
  ADD COLUMN aviso_vencida_em DATETIME NULL DEFAULT NULL;

-- Token para recuperação de senha
ALTER TABLE usuarios
  ADD COLUMN reset_token VARCHAR(64) NULL AFTER foto,
  ADD COLUMN reset_expira_em DATETIME NULL AFTER reset_token;
