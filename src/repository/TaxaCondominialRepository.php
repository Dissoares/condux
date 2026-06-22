<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/TaxaCondominial.php';

class TaxaCondominialRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?TaxaCondominial
    {
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? TaxaCondominial::fromArray($linha) : null;
    }

    /** @return TaxaCondominial[] */
    public function listarPorUnidade(int $unidadeId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.unidade_id = :unidade_id
             ORDER BY tc.competencia DESC'
        );
        $stmt->execute([':unidade_id' => $unidadeId]);
        return array_map(fn($l) => TaxaCondominial::fromArray($l), $stmt->fetchAll());
    }

    /** @return TaxaCondominial[] */
    public function listarPorCompetencia(string $competencia): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.competencia = :competencia
             ORDER BY u.bloco, u.numero'
        );
        $stmt->execute([':competencia' => $competencia]);
        return array_map(fn($l) => TaxaCondominial::fromArray($l), $stmt->fetchAll());
    }

    /** @return TaxaCondominial[] — taxas pendentes/vencidas de uma unidade */
    public function listarPendentesPorunidade(int $unidadeId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.unidade_id = :unidade_id
               AND tc.status IN ("pendente","vencido")
             ORDER BY tc.competencia'
        );
        $stmt->execute([':unidade_id' => $unidadeId]);
        return array_map(fn($l) => TaxaCondominial::fromArray($l), $stmt->fetchAll());
    }

    public function buscarCompetenciaAtualDaUnidade(int $unidadeId): ?TaxaCondominial
    {
        $competencia = date('Y-m');
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.unidade_id = :unidade_id AND tc.competencia = :competencia LIMIT 1'
        );
        $stmt->execute([':unidade_id' => $unidadeId, ':competencia' => $competencia]);
        $linha = $stmt->fetch();

        return $linha ? TaxaCondominial::fromArray($linha) : null;
    }

    public function salvar(TaxaCondominial $taxa): int
    {
        if ($taxa->id === null) {
            return $this->inserir($taxa);
        }
        $this->atualizar($taxa);
        return $taxa->id;
    }

    private function inserir(TaxaCondominial $taxa): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO taxas_condominiais
             (unidade_id, competencia, valor, vencimento, status, data_pagamento, comprovante, observacao)
             VALUES (:unidade_id, :competencia, :valor, :vencimento, :status, :data_pagamento, :comprovante, :observacao)'
        );
        $stmt->execute([
            ':unidade_id'     => $taxa->unidadeId,
            ':competencia'    => $taxa->competencia,
            ':valor'          => $taxa->valor,
            ':vencimento'     => $taxa->vencimento,
            ':status'         => $taxa->status,
            ':data_pagamento' => $taxa->dataPagamento,
            ':comprovante'    => $taxa->comprovante,
            ':observacao'     => $taxa->observacao,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(TaxaCondominial $taxa): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE taxas_condominiais SET
             status = :status, data_pagamento = :data_pagamento,
             comprovante = :comprovante, observacao = :observacao
             WHERE id = :id'
        );
        $stmt->execute([
            ':status'         => $taxa->status,
            ':data_pagamento' => $taxa->dataPagamento,
            ':comprovante'    => $taxa->comprovante,
            ':observacao'     => $taxa->observacao,
            ':id'             => $taxa->id,
        ]);
    }

    public function gerarEmLotePorCompetencia(
        string $competencia,
        float  $valor,
        string $vencimento,
        array  $unidadeIds
    ): int {
        $stmt = $this->conexao->prepare(
            'INSERT INTO taxas_condominiais (unidade_id, competencia, valor, vencimento, status)
             VALUES (:unidade_id, :competencia, :valor, :vencimento, "pendente")
             ON DUPLICATE KEY UPDATE
               valor      = IF(status NOT IN ("pago","isento"), VALUES(valor),      valor),
               vencimento = IF(status NOT IN ("pago","isento"), VALUES(vencimento), vencimento)'
        );
        foreach ($unidadeIds as $unidadeId) {
            $stmt->execute([
                ':unidade_id'  => $unidadeId,
                ':competencia' => $competencia,
                ':valor'       => $valor,
                ':vencimento'  => $vencimento,
            ]);
        }
        return count($unidadeIds);
    }

    /** @return array<int, TaxaCondominial[]> indexado por unidade_id */
    public function listarTodasAgrupadasPorUnidade(): array
    {
        $stmt = $this->conexao->query(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             ORDER BY tc.competencia DESC'
        );
        $agrupadas = [];
        foreach ($stmt->fetchAll() as $linha) {
            $t = TaxaCondominial::fromArray($linha);
            $agrupadas[$t->unidadeId][] = $t;
        }
        return $agrupadas;
    }

    public function resumoMesAtual(): array
    {
        $stmt = $this->conexao->query(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "pago")    AS total_pagas,
                SUM(status IN ("pendente","vencido")) AS total_pendentes,
                SUM(CASE WHEN status = "pago" THEN valor ELSE 0 END) AS valor_arrecadado
             FROM taxas_condominiais
             WHERE competencia = DATE_FORMAT(NOW(), "%Y-%m")'
        );
        return $stmt->fetch() ?: [];
    }
}
