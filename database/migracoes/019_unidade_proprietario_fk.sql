ALTER TABLE unidades
    ADD COLUMN proprietario_id INT UNSIGNED NULL AFTER tipo_ocupacao,
    ADD COLUMN inquilino_id    INT UNSIGNED NULL AFTER proprietario_id,
    ADD CONSTRAINT fk_unidade_proprietario
        FOREIGN KEY (proprietario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_unidade_inquilino
        FOREIGN KEY (inquilino_id) REFERENCES usuarios(id) ON DELETE SET NULL;
