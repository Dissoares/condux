-- Adiciona tipos antes/depois e campo descricao nos anexos de projeto
ALTER TABLE projeto_anexos
    MODIFY COLUMN tipo ENUM('foto','video','nota_fiscal','documento','antes','depois') NOT NULL,
    ADD COLUMN descricao VARCHAR(255) NULL AFTER nome_original;
