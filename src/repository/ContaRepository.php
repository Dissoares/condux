<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Conta.php';

class ContaRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** @return Conta[] */
    public function listarPorCompetencia(string $competencia): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM contas WHERE competencia = :comp ORDER BY data_vencimento, descricao'
        );
        $stmt->execute([':comp' => $competencia]);
        return array_map(fn($l) => Conta::fromArray($l), $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?Conta
    {
        $stmt = $this->conexao->prepare('SELECT * FROM contas WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? Conta::fromArray($linha) : null;
    }

    public function salvar(Conta $c): int
    {
        if ($c->id === null) {
            $stmt = $this->conexao->prepare(
                'INSERT INTO contas
                 (descricao, categoria, competencia, fornecedor, valor,
                  data_vencimento, data_pagamento, status, observacoes, anexo, nome_original)
                 VALUES
                 (:desc, :cat, :comp, :forn, :valor,
                  :venc, :pago, :status, :obs, :anexo, :nome)'
            );
        } else {
            $stmt = $this->conexao->prepare(
                'UPDATE contas SET
                 descricao = :desc, categoria = :cat, competencia = :comp,
                 fornecedor = :forn, valor = :valor,
                 data_vencimento = :venc, data_pagamento = :pago,
                 status = :status, observacoes = :obs,
                 anexo = COALESCE(:anexo, anexo),
                 nome_original = COALESCE(:nome, nome_original)
                 WHERE id = :id'
            );
        }

        $params = [
            ':desc'   => $c->descricao,
            ':cat'    => $c->categoria,
            ':comp'   => $c->competencia,
            ':forn'   => $c->fornecedor,
            ':valor'  => $c->valor,
            ':venc'   => $c->dataVencimento,
            ':pago'   => $c->dataPagamento,
            ':status' => $c->status,
            ':obs'    => $c->observacoes,
            ':anexo'  => $c->anexo,
            ':nome'   => $c->nomeOriginal,
        ];

        if ($c->id !== null) {
            $params[':id'] = $c->id;
        }

        $stmt->execute($params);
        return $c->id ?? (int) $this->conexao->lastInsertId();
    }

    public function marcarPago(int $id, string $dataPagamento): void
    {
        $this->conexao->prepare(
            'UPDATE contas SET status = \'pago\', data_pagamento = :data WHERE id = :id'
        )->execute([':data' => $dataPagamento, ':id' => $id]);
    }

    public function excluir(int $id): ?string
    {
        $stmt = $this->conexao->prepare('SELECT anexo FROM contas WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        $this->conexao->prepare('DELETE FROM contas WHERE id = :id')->execute([':id' => $id]);
        return $linha['anexo'] ?? null;
    }

    /** Lista competências disponíveis (YYYY-MM) ordenadas do mais recente */
    public function listarCompetencias(): array
    {
        return $this->conexao->query(
            'SELECT DISTINCT competencia FROM contas ORDER BY competencia DESC'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Resumo por competência para o painel */
    public function resumoPorCompetencia(): array
    {
        $hoje = date('Y-m-d');
        return $this->conexao->query(
            "SELECT competencia,
                    COUNT(*)                                           AS total,
                    SUM(valor)                                         AS valor_total,
                    SUM(status = 'pago')                              AS total_pagas,
                    SUM(status = 'pendente')                          AS total_pendentes,
                    SUM(status = 'pendente' AND data_vencimento < '{$hoje}') AS total_atrasadas
             FROM contas
             GROUP BY competencia
             ORDER BY competencia DESC"
        )->fetchAll();
    }

    public function totalPorCompetencia(string $competencia): array
    {
        $hoje = date('Y-m-d');
        $stmt = $this->conexao->prepare(
            "SELECT
                SUM(valor)                                          AS valor_total,
                SUM(status = 'pago')                               AS total_pagas,
                SUM(valor * (status = 'pago'))                     AS valor_pago,
                SUM(status = 'pendente')                           AS total_pendentes,
                SUM(valor * (status = 'pendente'))                 AS valor_pendente,
                SUM(status = 'pendente' AND data_vencimento < :hoje) AS total_atrasadas,
                SUM(valor * (status = 'pendente' AND data_vencimento < :hoje2)) AS valor_atrasado
             FROM contas WHERE competencia = :comp"
        );
        $stmt->execute([':comp' => $competencia, ':hoje' => $hoje, ':hoje2' => $hoje]);
        return $stmt->fetch() ?: [];
    }
}
