-- Adiciona forma_pagamento e status aguardando às cobranças de taxa extra
ALTER TABLE taxas_extras_unidades
  MODIFY COLUMN status ENUM('pendente','pago','vencido','isento','aguardando') NOT NULL DEFAULT 'pendente',
  ADD COLUMN forma_pagamento ENUM('pix','transferencia','dinheiro','boleto','cartao','cheque','outro') NULL
    AFTER data_pagamento;
