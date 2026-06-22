ALTER TABLE usuarios
    ADD COLUMN telefone        VARCHAR(20)  NULL AFTER email,
    ADD COLUMN cpf             VARCHAR(14)  NULL AFTER telefone,
    ADD COLUMN data_nascimento DATE         NULL AFTER cpf,
    ADD COLUMN observacoes     TEXT         NULL AFTER data_nascimento;
