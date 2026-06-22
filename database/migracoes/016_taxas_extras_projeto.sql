ALTER TABLE taxas_extras
    ADD COLUMN projeto_id        INT UNSIGNED  NULL AFTER nome,
    ADD COLUMN parcela           SMALLINT UNSIGNED NULL AFTER projeto_id,
    ADD COLUMN total_parcelas    SMALLINT UNSIGNED NULL AFTER parcela,
    ADD COLUMN valor_total       DECIMAL(12,2) NULL AFTER total_parcelas,
    ADD CONSTRAINT fk_taxa_extra_projeto
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL;
