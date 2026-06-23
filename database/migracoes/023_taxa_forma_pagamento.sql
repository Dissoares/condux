-- Adiciona forma de pagamento às taxas condominiais
ALTER TABLE taxas_condominiais
  ADD COLUMN forma_pagamento ENUM('pix','transferencia','dinheiro','boleto','cartao','cheque','outro') NULL
  AFTER data_pagamento;
