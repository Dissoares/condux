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

    /** Resumo por ano para a listagem inicial do morador */
    public function resumoAnosPorUnidade(int $unidadeId): array
    {
        $stmt = $this->conexao->prepare(
            "SELECT
                LEFT(competencia, 4)                                      AS ano,
                COUNT(*)                                                   AS total,
                SUM(status IN ('pago','isento'))                           AS pagas,
                SUM(status NOT IN ('pago','isento'))                       AS pendentes,
                SUM(status = 'pendente' AND vencimento < CURDATE())        AS vencidas,
                SUM(status = 'aguardando')                                 AS aguardando,
                SUM(valor)                                                 AS valor_total
             FROM taxas_condominiais
             WHERE unidade_id = :unidade_id
             GROUP BY ano
             ORDER BY ano DESC"
        );
        $stmt->execute([':unidade_id' => $unidadeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return TaxaCondominial[] — todas as taxas de um ano específico */
    public function listarPorUnidadeEAno(int $unidadeId, string $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.unidade_id = :unidade_id
               AND LEFT(tc.competencia, 4) = :ano
             ORDER BY tc.competencia DESC'
        );
        $stmt->execute([':unidade_id' => $unidadeId, ':ano' => $ano]);
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
             forma_pagamento = :forma_pagamento,
             comprovante = :comprovante, observacao = :observacao
             WHERE id = :id'
        );
        $stmt->execute([
            ':status'          => $taxa->status,
            ':data_pagamento'  => $taxa->dataPagamento,
            ':forma_pagamento' => $taxa->formaPagamento,
            ':comprovante'     => $taxa->comprovante,
            ':observacao'      => $taxa->observacao,
            ':id'              => $taxa->id,
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

    /** @return TaxaCondominial[] — todas as taxas com determinado status */
    public function listarPorStatus(string $status): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.status = :status
             ORDER BY tc.competencia DESC, u.bloco, u.numero'
        );
        $stmt->execute([':status' => $status]);
        return array_map(fn($l) => TaxaCondominial::fromArray($l), $stmt->fetchAll());
    }

    /**
     * Retorna todas as unidades ativas com o resumo de taxas para uma competência.
     * Unidades sem taxa aparecem com taxa_id = null.
     * @return array<array{unidade_id:int, identificacao:string, taxa_id:int|null, status:string|null, valor:float|null, vencimento:string|null, data_pagamento:string|null}>
     */
    public function listarUnidadesComTaxaPorCompetencia(string $competencia): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                u.id AS unidade_id,
                u.numero,
                u.bloco,
                CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao,
                tc.id         AS taxa_id,
                tc.status,
                tc.valor,
                tc.vencimento,
                tc.data_pagamento,
                tc.forma_pagamento
             FROM unidades u
             LEFT JOIN taxas_condominiais tc
                    ON tc.unidade_id = u.id AND tc.competencia = :competencia
             WHERE u.ativo = 1
             ORDER BY u.bloco, u.numero'
        );
        $stmt->execute([':competencia' => $competencia]);
        return $stmt->fetchAll();
    }

    /** @return array<int, TaxaCondominial[]> indexado por unidade_id — últimos 24 meses */
    public function listarTodasAgrupadasPorUnidade(): array
    {
        $stmt = $this->conexao->query(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.competencia >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 24 MONTH), \'%Y-%m\')
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

    /** Resumo do mês atual com atrasadas e pendentes separadas */
    public function resumoMesDetalhado(): array
    {
        $stmt = $this->conexao->query(
            'SELECT
                SUM(status = "pago") AS total_pagas,
                SUM(status = "pago" OR status = "isento") AS total_quitadas,
                SUM(status = "vencido" OR (status = "pendente" AND vencimento < CURDATE())) AS total_atrasadas,
                SUM(status = "aguardando" OR (status = "pendente" AND vencimento >= CURDATE())) AS total_pendentes,
                SUM(status = "aguardando") AS total_aguardando,
                SUM(CASE WHEN status = "pago" THEN valor ELSE 0 END) AS valor_arrecadado,
                SUM(CASE WHEN status = "vencido" OR (status = "pendente" AND vencimento < CURDATE()) THEN valor ELSE 0 END) AS valor_atrasado,
                SUM(CASE WHEN status = "aguardando" OR (status = "pendente" AND vencimento >= CURDATE()) THEN valor ELSE 0 END) AS valor_pendente
             FROM taxas_condominiais
             WHERE competencia = DATE_FORMAT(NOW(), "%Y-%m")'
        );
        return $stmt->fetch() ?: [];
    }

    /** Conta taxas com comprovante enviado aguardando aprovação do síndico */
    public function contarAguardandoAprovacao(): int
    {
        $stmt = $this->conexao->query(
            'SELECT COUNT(*) FROM taxas_condominiais WHERE status = "aguardando"'
        );
        return (int) $stmt->fetchColumn();
    }

    /** Totais globais de inadimplência (todos os meses) */
    public function totaisGlobais(): array
    {
        $stmt = $this->conexao->query(
            'SELECT
                SUM(CASE WHEN status = "vencido" OR (status = "pendente" AND vencimento < CURDATE()) THEN valor ELSE 0 END) AS valor_total_atrasado,
                SUM(CASE WHEN status = "aguardando" OR (status = "pendente" AND vencimento >= CURDATE()) THEN valor ELSE 0 END) AS valor_total_pendente,
                SUM(CASE WHEN status = "vencido" OR (status = "pendente" AND vencimento < CURDATE()) THEN 1 ELSE 0 END) AS qtd_atrasadas,
                SUM(CASE WHEN status = "aguardando" OR (status = "pendente" AND vencimento >= CURDATE()) THEN 1 ELSE 0 END) AS qtd_pendentes,
                COUNT(DISTINCT CASE WHEN status = "vencido" OR (status = "pendente" AND vencimento < CURDATE()) THEN unidade_id END) AS qtd_unidades_inadimplentes
             FROM taxas_condominiais
             WHERE status NOT IN ("pago", "isento")'
        );
        return $stmt->fetch() ?: [];
    }

    /** Resumo de uma competência específica */
    public function resumoPorCompetencia(string $competencia): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "pago") AS total_pagas,
                SUM(status = "pago" OR status = "isento") AS total_quitadas,
                SUM(status = "vencido" OR (status = "pendente" AND vencimento < CURDATE())) AS total_atrasadas,
                SUM(status = "pendente" AND vencimento >= CURDATE()) AS total_pendentes,
                SUM(CASE WHEN status = "pago" THEN valor ELSE 0 END) AS valor_arrecadado
             FROM taxas_condominiais
             WHERE competencia = :competencia'
        );
        $stmt->execute([':competencia' => $competencia]);
        return $stmt->fetch() ?: [];
    }

    /** Retorna uma linha por competência com totais — para o grid de meses */
    public function resumoAnos(): array
    {
        $stmt = $this->conexao->query(
            'SELECT
                LEFT(competencia, 4)                                                              AS ano,
                COUNT(DISTINCT competencia)                                                        AS total_meses,
                COUNT(*)                                                                           AS total,
                SUM(status = "pago")                                                               AS total_pagas,
                SUM(status = "vencido" OR (status = "pendente" AND vencimento < CURDATE()))        AS total_atrasadas,
                SUM(status = "aguardando" OR (status = "pendente" AND vencimento >= CURDATE()))    AS total_pendentes,
                SUM(CASE WHEN status = "pago" THEN valor ELSE 0 END)                              AS valor_arrecadado
             FROM taxas_condominiais
             GROUP BY ano
             ORDER BY ano DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCompetenciasPorAno(string $ano): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                competencia,
                COUNT(*) AS total,
                SUM(status = "pago") AS total_pagas,
                SUM(status = "vencido" OR (status = "pendente" AND vencimento < CURDATE())) AS total_atrasadas,
                SUM(status = "aguardando" OR (status = "pendente" AND vencimento >= CURDATE())) AS total_pendentes,
                SUM(status = "aguardando") AS total_aguardando,
                SUM(CASE WHEN status = "pago" THEN valor ELSE 0 END) AS valor_arrecadado
             FROM taxas_condominiais
             WHERE LEFT(competencia, 4) = :ano
             GROUP BY competencia
             ORDER BY competencia DESC'
        );
        $stmt->execute([':ano' => $ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCompetencias(): array
    {
        $stmt = $this->conexao->query(
            'SELECT
                competencia,
                COUNT(*) AS total,
                SUM(status = "pago") AS total_pagas,
                SUM(status = "vencido" OR (status = "pendente" AND vencimento < CURDATE())) AS total_atrasadas,
                SUM(status = "aguardando" OR (status = "pendente" AND vencimento >= CURDATE())) AS total_pendentes,
                SUM(status = "aguardando") AS total_aguardando,
                SUM(CASE WHEN status = "pago" THEN valor ELSE 0 END) AS valor_arrecadado
             FROM taxas_condominiais
             GROUP BY competencia
             ORDER BY competencia DESC'
        );
        return $stmt->fetchAll();
    }

    /** Unidades ativas com status da taxa condominial em uma competência (LEFT JOIN) */
    public function listarUnidadesComStatusPorCompetencia(string $competencia): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                u.id AS unidade_id,
                CONCAT("Apto ", u.numero,
                       IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade,
                tc.id        AS taxa_id,
                tc.valor,
                tc.vencimento,
                tc.status,
                tc.comprovante,
                tc.data_pagamento
             FROM unidades u
             LEFT JOIN taxas_condominiais tc
                    ON tc.unidade_id = u.id AND tc.competencia = :competencia
             WHERE u.ativo = 1
             ORDER BY u.bloco, u.numero'
        );
        $stmt->execute([':competencia' => $competencia]);
        return $stmt->fetchAll();
    }

    public function buscarPorUnidadeECompetencia(int $unidadeId, string $competencia): ?TaxaCondominial
    {
        $stmt = $this->conexao->prepare(
            'SELECT tc.*,
                    CONCAT("Apto ", u.numero,
                           IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade
             FROM taxas_condominiais tc
             JOIN unidades u ON u.id = tc.unidade_id
             WHERE tc.unidade_id = :unidade_id AND tc.competencia = :competencia LIMIT 1'
        );
        $stmt->execute([':unidade_id' => $unidadeId, ':competencia' => $competencia]);
        $linha = $stmt->fetch();
        return $linha ? TaxaCondominial::fromArray($linha) : null;
    }

    /** Taxas vencidas (pendentes/vencidas) que ainda não receberam aviso por e-mail. */
    public function listarVencidasSemAviso(): array
    {
        return $this->conexao->query(
            "SELECT tc.id, tc.unidade_id, tc.competencia, tc.valor, tc.vencimento,
                    u.nome AS nome_morador, u.email AS email_morador,
                    DATEDIFF(CURDATE(), tc.vencimento) AS dias_atraso
             FROM taxas_condominiais tc
             JOIN unidades un ON un.id = tc.unidade_id
             JOIN moradores m  ON m.unidade_id = un.id AND m.ativo = 1 AND m.responsavel = 1
             JOIN usuarios  u  ON u.id = m.usuario_id
             WHERE tc.status IN ('pendente','vencido')
               AND tc.vencimento < CURDATE()
               AND (tc.aviso_vencida_em IS NULL)
               AND u.email IS NOT NULL AND u.email <> ''
             ORDER BY tc.vencimento"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarAvisoVencidaEnviado(int $taxaId): void
    {
        $this->conexao->prepare(
            'UPDATE taxas_condominiais SET aviso_vencida_em = NOW() WHERE id = :id'
        )->execute([':id' => $taxaId]);
    }
}
