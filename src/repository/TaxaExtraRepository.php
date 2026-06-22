<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/TaxaExtra.php';

class TaxaExtraRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?TaxaExtra
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM taxas_extras WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? TaxaExtra::fromArray($linha) : null;
    }

    /** @return TaxaExtra[] */
    public function listarTodas(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM taxas_extras ORDER BY vencimento DESC'
        );
        return array_map(fn($l) => TaxaExtra::fromArray($l), $stmt->fetchAll());
    }

    /** Retorna o status de pagamento de uma taxa extra para cada unidade */
    public function listarCobrancasPorTaxa(int $taxaExtraId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT teu.*,
                    CONCAT("Apto ", u.numero, IF(u.bloco IS NOT NULL, CONCAT(" — Bloco ", u.bloco), "")) AS identificacao_unidade,
                    us.nome AS nome_responsavel
             FROM taxas_extras_unidades teu
             JOIN unidades u ON u.id = teu.unidade_id
             LEFT JOIN moradores m ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios  us ON us.id = m.usuario_id
             WHERE teu.taxa_extra_id = :taxa_extra_id
             ORDER BY u.bloco, u.numero'
        );
        $stmt->execute([':taxa_extra_id' => $taxaExtraId]);
        return $stmt->fetchAll();
    }

    /** Retorna todas as taxas extras de uma unidade */
    public function listarPorUnidade(int $unidadeId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT teu.*, te.nome, te.descricao, te.valor AS valor_original, te.vencimento
             FROM taxas_extras_unidades teu
             JOIN taxas_extras te ON te.id = teu.taxa_extra_id
             WHERE teu.unidade_id = :unidade_id
             ORDER BY te.vencimento DESC'
        );
        $stmt->execute([':unidade_id' => $unidadeId]);
        return $stmt->fetchAll();
    }

    public function salvar(TaxaExtra $taxa): int
    {
        if ($taxa->id === null) {
            return $this->inserir($taxa);
        }
        $this->atualizar($taxa);
        return $taxa->id;
    }

    private function inserir(TaxaExtra $taxa): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO taxas_extras (nome, descricao, valor, vencimento)
             VALUES (:nome, :descricao, :valor, :vencimento)'
        );
        $stmt->execute([
            ':nome'       => $taxa->nome,
            ':descricao'  => $taxa->descricao,
            ':valor'      => $taxa->valor,
            ':vencimento' => $taxa->vencimento,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(TaxaExtra $taxa): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE taxas_extras SET nome = :nome, descricao = :descricao,
             valor = :valor, vencimento = :vencimento WHERE id = :id'
        );
        $stmt->execute([
            ':nome'       => $taxa->nome,
            ':descricao'  => $taxa->descricao,
            ':valor'      => $taxa->valor,
            ':vencimento' => $taxa->vencimento,
            ':id'         => $taxa->id,
        ]);
    }

    /** Atribui uma taxa extra a uma lista de unidades */
    public function atribuirParaUnidades(int $taxaExtraId, array $unidadeIds): void
    {
        $stmt = $this->conexao->prepare(
            'INSERT IGNORE INTO taxas_extras_unidades (taxa_extra_id, unidade_id, status)
             VALUES (:taxa_extra_id, :unidade_id, "pendente")'
        );
        foreach ($unidadeIds as $unidadeId) {
            $stmt->execute([
                ':taxa_extra_id' => $taxaExtraId,
                ':unidade_id'    => $unidadeId,
            ]);
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
}
