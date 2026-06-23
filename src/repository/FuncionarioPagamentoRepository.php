<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/FuncionarioPagamento.php';

class FuncionarioPagamentoRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** @return FuncionarioPagamento[] */
    public function listarPorFuncionario(int $funcionarioId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM funcionario_pagamentos
             WHERE funcionario_id = :fid
             ORDER BY competencia DESC'
        );
        $stmt->execute([':fid' => $funcionarioId]);
        return array_map(fn($l) => FuncionarioPagamento::fromArray($l), $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?FuncionarioPagamento
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM funcionario_pagamentos WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? FuncionarioPagamento::fromArray($linha) : null;
    }

    public function salvar(FuncionarioPagamento $p): int
    {
        if ($p->id === null) {
            $stmt = $this->conexao->prepare(
                'INSERT INTO funcionario_pagamentos
                 (funcionario_id, competencia, valor, data_prevista, data_pagamento, status, observacoes)
                 VALUES (:fid, :comp, :valor, :prev, :pago, :status, :obs)
                 ON DUPLICATE KEY UPDATE
                 valor = VALUES(valor), data_prevista = VALUES(data_prevista),
                 data_pagamento = VALUES(data_pagamento), status = VALUES(status),
                 observacoes = VALUES(observacoes)'
            );
            $stmt->execute([
                ':fid'    => $p->funcionarioId,
                ':comp'   => $p->competencia,
                ':valor'  => $p->valor,
                ':prev'   => $p->dataPrevista,
                ':pago'   => $p->dataPagamento,
                ':status' => $p->status,
                ':obs'    => $p->observacoes,
            ]);
            return (int) $this->conexao->lastInsertId();
        }

        $stmt = $this->conexao->prepare(
            'UPDATE funcionario_pagamentos SET
             valor = :valor, data_prevista = :prev, data_pagamento = :pago,
             status = :status, observacoes = :obs
             WHERE id = :id'
        );
        $stmt->execute([
            ':valor'  => $p->valor,
            ':prev'   => $p->dataPrevista,
            ':pago'   => $p->dataPagamento,
            ':status' => $p->status,
            ':obs'    => $p->observacoes,
            ':id'     => $p->id,
        ]);
        return $p->id;
    }

    public function excluir(int $id): void
    {
        $this->conexao->prepare('DELETE FROM funcionario_pagamentos WHERE id = :id')
            ->execute([':id' => $id]);
    }
}
