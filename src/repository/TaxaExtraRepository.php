<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/TaxaExtra.php';

class TaxaExtraRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?TaxaExtra
    {
        $stmt = $this->conexao->prepare(
            'SELECT te.*, p.nome AS nome_projeto
             FROM taxas_extras te
             LEFT JOIN projetos p ON p.id = te.projeto_id
             WHERE te.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? TaxaExtra::fromArray($linha) : null;
    }

    /** @return TaxaExtra[] — primeira parcela de cada grupo (ou taxa avulsa) */
    public function listarGrupos(): array
    {
        $stmt = $this->conexao->query(
            'SELECT te.*, p.nome AS nome_projeto
             FROM taxas_extras te
             LEFT JOIN projetos p ON p.id = te.projeto_id
             WHERE te.parcela IS NULL OR te.parcela = 1
             ORDER BY te.vencimento DESC'
        );
        return array_map(fn($l) => TaxaExtra::fromArray($l), $stmt->fetchAll());
    }

    /** @return TaxaExtra[] — todas as parcelas de um projeto */
    public function listarPorProjeto(int $projetoId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT te.*, p.nome AS nome_projeto
             FROM taxas_extras te
             LEFT JOIN projetos p ON p.id = te.projeto_id
             WHERE te.projeto_id = :pid
             ORDER BY te.parcela'
        );
        $stmt->execute([':pid' => $projetoId]);
        return array_map(fn($l) => TaxaExtra::fromArray($l), $stmt->fetchAll());
    }

    /** Status de pagamento por unidade para uma taxa extra */
    public function listarCobrancasPorTaxa(int $taxaExtraId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT teu.*,
                    CONCAT("Bloco ", u.bloco, " — Apto ", u.numero) AS identificacao_unidade,
                    us.nome AS nome_responsavel
             FROM taxas_extras_unidades teu
             JOIN unidades u ON u.id = teu.unidade_id
             LEFT JOIN moradores m  ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios  us ON us.id = m.usuario_id
             WHERE teu.taxa_extra_id = :taxa_extra_id
             ORDER BY u.bloco, u.numero'
        );
        $stmt->execute([':taxa_extra_id' => $taxaExtraId]);
        return $stmt->fetchAll();
    }

    public function listarPorUnidade(int $unidadeId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT teu.*, te.nome, te.descricao, te.valor AS valor_original,
                    te.vencimento, te.parcela, te.total_parcelas, te.projeto_id,
                    p.nome AS nome_projeto
             FROM taxas_extras_unidades teu
             JOIN taxas_extras te ON te.id = teu.taxa_extra_id
             LEFT JOIN projetos p ON p.id = te.projeto_id
             WHERE teu.unidade_id = :unidade_id
             ORDER BY te.vencimento DESC'
        );
        $stmt->execute([':unidade_id' => $unidadeId]);
        return $stmt->fetchAll();
    }

    /** @return array<int, array[]> indexado por unidade_id */
    public function listarTodasPorUnidadesAgrupadas(): array
    {
        $stmt = $this->conexao->query(
            'SELECT teu.*, te.nome, te.descricao, te.parcela, te.total_parcelas, te.projeto_id,
                    te.vencimento, te.valor AS valor_original,
                    p.nome AS nome_projeto
             FROM taxas_extras_unidades teu
             JOIN taxas_extras te ON te.id = teu.taxa_extra_id
             LEFT JOIN projetos p ON p.id = te.projeto_id
             ORDER BY te.vencimento DESC'
        );
        $agrupadas = [];
        foreach ($stmt->fetchAll() as $linha) {
            $agrupadas[(int)$linha['unidade_id']][] = $linha;
        }
        return $agrupadas;
    }

    public function inserir(array $dados): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO taxas_extras
                (nome, descricao, valor, vencimento, projeto_id, parcela, total_parcelas, valor_total)
             VALUES
                (:nome, :descricao, :valor, :vencimento, :projeto_id, :parcela, :total_parcelas, :valor_total)'
        );
        $stmt->execute($dados);
        return (int) $this->conexao->lastInsertId();
    }

    /** Atribui uma taxa extra a uma lista de unidades */
    public function atribuirParaUnidades(int $taxaExtraId, array $unidadeIds): void
    {
        $stmt = $this->conexao->prepare(
            'INSERT IGNORE INTO taxas_extras_unidades (taxa_extra_id, unidade_id, status)
             VALUES (:taxa_extra_id, :unidade_id, "pendente")'
        );
        foreach ($unidadeIds as $id) {
            $stmt->execute([':taxa_extra_id' => $taxaExtraId, ':unidade_id' => $id]);
        }
    }

    public function registrarPagamento(int $cobrancaId, string $dataPagamento, ?string $comprovante): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE taxas_extras_unidades
             SET status = "pago", data_pagamento = :data_pagamento, comprovante = :comprovante
             WHERE id = :id'
        );
        $stmt->execute([
            ':data_pagamento' => $dataPagamento,
            ':comprovante'    => $comprovante,
            ':id'             => $cobrancaId,
        ]);
    }

    /** Quantidade de unidades pagas/pendentes numa taxa extra */
    public function resumoCobrancas(int $taxaExtraId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT
                SUM(status = "pago")     AS pagas,
                SUM(status != "pago")    AS pendentes,
                COUNT(*)                  AS total
             FROM taxas_extras_unidades
             WHERE taxa_extra_id = :id'
        );
        $stmt->execute([':id' => $taxaExtraId]);
        return $stmt->fetch() ?: ['pagas' => 0, 'pendentes' => 0, 'total' => 0];
    }
}
