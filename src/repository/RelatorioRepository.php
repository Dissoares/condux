<?php

declare(strict_types=1);

class RelatorioRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** Arrecadação mensal de um ano — retorna 12 linhas */
    public function arrecadacaoMensalPorAno(int $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                competencia                              AS competencia,
                SUM(valor)                               AS total_cobrado,
                SUM(CASE WHEN status = "pago" THEN valor ELSE 0 END) AS total_pago,
                COUNT(*)                                 AS total_unidades,
                SUM(status = "pago")                     AS total_pagas,
                SUM(status IN ("pendente","vencido"))    AS total_inadimplentes
             FROM taxas_condominiais
             WHERE competencia LIKE :ano
             GROUP BY competencia
             ORDER BY competencia'
        );
        $stmt->execute([':ano' => $ano . '-%']);
        return $stmt->fetchAll();
    }

    /** Extrato completo de uma unidade, opcionalmente filtrado por ano */
    public function extratoPorUnidade(int $unidadeId, ?int $ano = null): array
    {
        $sql = 'SELECT
                    tc.competencia,
                    tc.valor,
                    tc.vencimento,
                    tc.status,
                    tc.data_pagamento,
                    tc.observacao
                FROM taxas_condominiais tc
                WHERE tc.unidade_id = :unidade_id';

        $params = [':unidade_id' => $unidadeId];

        if ($ano !== null) {
            $sql .= ' AND tc.competencia LIKE :ano';
            $params[':ano'] = $ano . '-%';
        }

        $sql .= ' ORDER BY tc.competencia DESC';

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Resumo de inadimplência por unidade em uma competência */
    public function inadimplentesNaCompetencia(string $competencia): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS unidade,
                usr.nome  AS responsavel,
                tc.valor,
                tc.vencimento,
                tc.status,
                DATEDIFF(CURDATE(), tc.vencimento) AS dias_atraso
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             LEFT JOIN moradores m ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios usr ON usr.id = m.usuario_id
             WHERE tc.competencia = :competencia
               AND tc.status IN ("pendente","vencido")
             ORDER BY dias_atraso DESC, u.bloco, u.numero'
        );
        $stmt->execute([':competencia' => $competencia]);
        return $stmt->fetchAll();
    }

    /** Anos disponíveis com taxas cadastradas */
    public function anosDisponiveis(): array
    {
        $stmt = $this->conexao->query(
            'SELECT DISTINCT YEAR(STR_TO_DATE(CONCAT(competencia, "-01"), "%Y-%m-%d")) AS ano
             FROM taxas_condominiais
             ORDER BY ano DESC'
        );
        return array_column($stmt->fetchAll(), 'ano');
    }
}
