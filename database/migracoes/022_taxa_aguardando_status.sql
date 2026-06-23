-- Adiciona status 'aguardando' para taxas condominiais com comprovante enviado aguardando aprovação do síndico
ALTER TABLE taxas_condominiais
  MODIFY COLUMN status ENUM('pendente','aguardando','pago','vencido','isento') NOT NULL DEFAULT 'pendente';
