-- Torna unidade_id opcional (inspeções prediais não são por unidade)
ALTER TABLE vistorias
    MODIFY COLUMN unidade_id     INT UNSIGNED NULL,
    ADD COLUMN tipo              ENUM('predial','bombeiros','elevador','sanitaria','orcamento','unidade','outro')
                                 NOT NULL DEFAULT 'predial' AFTER status,
    ADD COLUMN categoria         VARCHAR(120) NULL AFTER tipo,
    ADD COLUMN prestadora_id     INT UNSIGNED NULL AFTER categoria,
    ADD COLUMN numero_documento  VARCHAR(80)  NULL AFTER prestadora_id,
    ADD COLUMN validade          DATE         NULL AFTER numero_documento,
    ADD COLUMN resultado         ENUM('aprovado','reprovado','condicional') NULL AFTER validade,
    ADD CONSTRAINT fk_vistoria_prestadora
        FOREIGN KEY (prestadora_id) REFERENCES prestadoras(id) ON DELETE SET NULL;

CREATE TABLE vistoria_anexos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vistoria_id   INT UNSIGNED NOT NULL,
    tipo          ENUM('laudo','foto','relatorio','orcamento','documento') NOT NULL DEFAULT 'documento',
    caminho       VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    enviado_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vistoria_id) REFERENCES vistorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
