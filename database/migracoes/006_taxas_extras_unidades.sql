CREATE TABLE taxas_extras_unidades (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    taxa_extra_id  INT UNSIGNED  NOT NULL,
    unidade_id     INT UNSIGNED  NOT NULL,
    status         ENUM('pendente','pago','vencido','isento') NOT NULL DEFAULT 'pendente',
    data_pagamento DATE          NULL,
    comprovante    VARCHAR(255)  NULL,
    FOREIGN KEY (taxa_extra_id) REFERENCES taxas_extras(id),
    FOREIGN KEY (unidade_id)    REFERENCES unidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
