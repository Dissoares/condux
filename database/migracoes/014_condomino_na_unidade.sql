ALTER TABLE unidades
    ADD COLUMN tipo_ocupacao         ENUM('proprio','alugado') NOT NULL DEFAULT 'proprio' AFTER descricao,
    ADD COLUMN nome_proprietario     VARCHAR(120) NULL AFTER tipo_ocupacao,
    ADD COLUMN telefone_proprietario VARCHAR(20)  NULL AFTER nome_proprietario,
    ADD COLUMN email_proprietario    VARCHAR(120) NULL AFTER telefone_proprietario,
    ADD COLUMN nome_inquilino        VARCHAR(120) NULL AFTER email_proprietario,
    ADD COLUMN telefone_inquilino    VARCHAR(20)  NULL AFTER nome_inquilino,
    ADD COLUMN email_inquilino       VARCHAR(120) NULL AFTER telefone_inquilino;
