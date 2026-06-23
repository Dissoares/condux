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

    /**
     * Retorna todos os pagamentos com nome/cargo do funcionário,
     * agrupados por competência para a folha consolidada.
     * @return array[]  cada item tem fp.* + nome, cargo, dia_pagamento
     */
    public function listarFolhaConsolidada(?string $competencia = null): array
    {
        $sql = 'SELECT fp.*, f.nome, f.cargo, f.dia_pagamento
                FROM funcionario_pagamentos fp
                JOIN funcionarios f ON f.id = fp.funcionario_id
                WHERE 1=1';
        $params = [];
        if ($competencia) {
            $sql .= ' AND fp.competencia = :comp';
            $params[':comp'] = $competencia;
        }
        $sql .= ' ORDER BY fp.competencia DESC, f.nome';

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Lista competências disponíveis (YYYY-MM) ordenadas do mais recente */
    public function listarCompetencias(): array
    {
        return $this->conexao->query(
            'SELECT DISTINCT competencia FROM funcionario_pagamentos ORDER BY competencia DESC'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Resumo por competência: total, valor, pagas, pendentes */
    public function resumoPorCompetencia(): array
    {
        $rows = $this->conexao->query(
            'SELECT competencia,
                    COUNT(*)                              AS total,
                    SUM(valor)                            AS valor_total,
                    SUM(status = \'pago\')               AS total_pagas,
                    SUM(status = \'pendente\')            AS total_pendentes
             FROM funcionario_pagamentos
             GROUP BY competencia
             ORDER BY competencia DESC'
        )->fetchAll();

        // Marca atrasados: pendente + data_prevista < hoje
        $hoje  = date('Y-m-d');
        $extra = $this->conexao->query(
            "SELECT competencia, COUNT(*) AS atrasados
             FROM funcionario_pagamentos
             WHERE status = 'pendente' AND data_prevista < '{$hoje}'
             GROUP BY competencia"
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($rows as &$r) {
            $r['total_atrasados'] = $extra[$r['competencia']] ?? 0;
        }
        return $rows;
    }
}
