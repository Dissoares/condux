-- Adiciona forma de pagamento às taxas condominiais
-- Usa procedure dinâmica para ser idempotente no MySQL
SET @existe = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name   = 'taxas_condominiais'
      AND column_name  = 'forma_pagamento'
);
SET @sql = IF(@existe = 0,
    "ALTER TABLE taxas_condominiais ADD COLUMN forma_pagamento ENUM('pix','transferencia','dinheiro','boleto','cartao','cheque','outro') NULL AFTER data_pagamento",
    "SELECT 'coluna forma_pagamento ja existe'"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
