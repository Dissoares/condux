<?php

declare(strict_types=1);

class RelatorioRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** Arrecadação mensal de um ano */
    public function arrecadacaoMensalPorAno(int $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                competencia,
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

    /** Extrato completo de uma unidade */
    public function extratoPorUnidade(int $unidadeId, ?int $ano = null): array
    {
        $sql = 'SELECT tc.competencia, tc.valor, tc.vencimento,
                       tc.status, tc.data_pagamento, tc.observacao
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

    /** Inadimplência em uma competência */
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

    /** Despesas (contas) por ano, agrupadas por mês */
    public function despesasPorAno(int $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT competencia,
                    SUM(valor)                              AS total,
                    SUM(valor * (status = "pago"))          AS total_pago,
                    SUM(valor * (status = "pendente"))      AS total_pendente,
                    COUNT(*)                                AS qtd
             FROM contas
             WHERE competencia LIKE :ano
             GROUP BY competencia
             ORDER BY competencia'
        );
        $stmt->execute([':ano' => $ano . '-%']);
        return $stmt->fetchAll();
    }

    /** Detalhes das contas de um ano (para exportação linha a linha) */
    public function contasDetalhadasPorAno(int $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT competencia, descricao, categoria, fornecedor,
                    valor, data_vencimento, data_pagamento, status
             FROM contas
             WHERE competencia LIKE :ano
             ORDER BY competencia, data_vencimento'
        );
        $stmt->execute([':ano' => $ano . '-%']);
        return $stmt->fetchAll();
    }

    /** Folha de pessoal por ano, agrupada por mês */
    public function folhaPorAno(int $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT fp.competencia,
                    SUM(fp.valor)              AS total,
                    SUM(fp.valor * (fp.status = "pago"))    AS total_pago,
                    COUNT(*)                               AS qtd_funcionarios
             FROM funcionario_pagamentos fp
             WHERE fp.competencia LIKE :ano
             GROUP BY fp.competencia
             ORDER BY fp.competencia'
        );
        $stmt->execute([':ano' => $ano . '-%']);
        return $stmt->fetchAll();
    }

    /** Detalhes da folha de um ano (para exportação linha a linha) */
    public function folhaDetalhadaPorAno(int $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT fp.competencia, f.nome, f.cargo,
                    fp.valor, fp.status, fp.data_pagamento
             FROM funcionario_pagamentos fp
             JOIN funcionarios f ON f.id = fp.funcionario_id
             WHERE fp.competencia LIKE :ano
             ORDER BY fp.competencia, f.nome'
        );
        $stmt->execute([':ano' => $ano . '-%']);
        return $stmt->fetchAll();
    }

    /** Balancete anual: arrecadação vs despesas vs folha por mês */
    public function balanceteAnual(int $ano): array
    {
        $arrecadacao = $this->arrecadacaoMensalPorAno($ano);
        $despesas    = $this->despesasPorAno($ano);
        $folha       = $this->folhaPorAno($ano);

        $despPorComp  = array_column($despesas, null, 'competencia');
        $folhaPorComp = array_column($folha,    null, 'competencia');

        $meses = [];
        foreach ($arrecadacao as $r) {
            $c = $r['competencia'];
            $arrecadado  = (float) $r['total_pago'];
            $gastoContas = (float) ($despPorComp[$c]['total_pago'] ?? $despPorComp[$c]['total'] ?? 0);
            $gastoFolha  = (float) ($folhaPorComp[$c]['total_pago'] ?? $folhaPorComp[$c]['total'] ?? 0);
            $meses[] = [
                'competencia' => $c,
                'arrecadado'  => $arrecadado,
                'despesas'    => $gastoContas,
                'folha'       => $gastoFolha,
                'saldo'       => $arrecadado - $gastoContas - $gastoFolha,
            ];
        }
        return $meses;
    }

    /** Lista todas as unidades com nome do responsável */
    public function listarUnidades(): array
    {
        $stmt = $this->conexao->query(
            'SELECT u.id,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao,
                    usr.nome AS responsavel
             FROM unidades u
             LEFT JOIN moradores m ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios usr ON usr.id = m.usuario_id
             ORDER BY u.bloco, u.numero'
        );
        return $stmt->fetchAll();
    }

    /** Anos disponíveis com taxas cadastradas */
    public function anosDisponiveis(): array
    {
        $rows = $this->conexao->query(
            'SELECT DISTINCT YEAR(STR_TO_DATE(CONCAT(competencia, "-01"), "%Y-%m-%d")) AS ano
             FROM taxas_condominiais
             ORDER BY ano DESC'
        )->fetchAll();

        $anos = array_column($rows, 'ano');

        // Garante que o ano corrente sempre aparece
        $anoAtual = (int) date('Y');
        if (!in_array($anoAtual, $anos, true)) {
            array_unshift($anos, $anoAtual);
        }

        return $anos;
    }
}
