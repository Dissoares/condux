<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Unidade.php';

class UnidadeRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?Unidade
    {
        $stmt = $this->conexao->prepare(
            'SELECT u.*,
                    m_usr.nome  AS nome_responsavel,
                    CASE
                      WHEN tc.status IS NULL                                          THEN NULL
                      WHEN tc.status = \'pago\'                                      THEN \'pago\'
                      WHEN tc.status = \'isento\'                                    THEN \'isento\'
                      WHEN tc.status = \'pendente\' AND tc.vencimento < CURDATE()    THEN \'vencido\'
                      ELSE tc.status
                    END         AS status_taxa_atual,
                    COALESCE(res.qtd_atrasadas, 0) AS qtd_atrasadas,
                    COALESCE(res.qtd_pendentes, 0) AS qtd_pendentes,
                    prop.nome   AS nome_prop_vinc,
                    prop.email  AS email_prop_vinc,
                    inq.nome    AS nome_inq_vinc,
                    inq.email   AS email_inq_vinc
             FROM unidades u
             LEFT JOIN moradores m
                    ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios m_usr ON m_usr.id = m.usuario_id
             LEFT JOIN taxas_condominiais tc
                    ON tc.unidade_id = u.id
                   AND tc.competencia = DATE_FORMAT(NOW(), "%Y-%m")
             LEFT JOIN (
                    SELECT unidade_id,
                           SUM(CASE WHEN (status = \'pendente\' AND vencimento < CURDATE()) OR status = \'vencido\' THEN 1 ELSE 0 END) AS qtd_atrasadas,
                           SUM(CASE WHEN status = \'pendente\' AND vencimento >= CURDATE() THEN 1 ELSE 0 END) AS qtd_pendentes
                    FROM taxas_condominiais
                    GROUP BY unidade_id
             ) res ON res.unidade_id = u.id
             LEFT JOIN usuarios prop ON prop.id = u.proprietario_id
             LEFT JOIN usuarios inq  ON inq.id  = u.inquilino_id
             WHERE u.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? Unidade::fromArray($linha) : null;
    }

    /** @return Unidade[] */
    public function listarAtivas(): array
    {
        $stmt = $this->conexao->query(
            'SELECT u.*,
                    m_usr.nome  AS nome_responsavel,
                    CASE
                      WHEN tc.status IS NULL                                          THEN NULL
                      WHEN tc.status = \'pago\'                                      THEN \'pago\'
                      WHEN tc.status = \'isento\'                                    THEN \'isento\'
                      WHEN tc.status = \'pendente\' AND tc.vencimento < CURDATE()    THEN \'vencido\'
                      ELSE tc.status
                    END         AS status_taxa_atual,
                    COALESCE(res.qtd_atrasadas, 0) AS qtd_atrasadas,
                    COALESCE(res.qtd_pendentes, 0) AS qtd_pendentes,
                    prop.nome   AS nome_prop_vinc,
                    prop.email  AS email_prop_vinc,
                    inq.nome    AS nome_inq_vinc,
                    inq.email   AS email_inq_vinc
             FROM unidades u
             LEFT JOIN moradores m
                    ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios m_usr ON m_usr.id = m.usuario_id
             LEFT JOIN taxas_condominiais tc
                    ON tc.unidade_id = u.id
                   AND tc.competencia = DATE_FORMAT(NOW(), "%Y-%m")
             LEFT JOIN (
                    SELECT unidade_id,
                           SUM(CASE WHEN (status = \'pendente\' AND vencimento < CURDATE()) OR status = \'vencido\' THEN 1 ELSE 0 END) AS qtd_atrasadas,
                           SUM(CASE WHEN status = \'pendente\' AND vencimento >= CURDATE() THEN 1 ELSE 0 END) AS qtd_pendentes
                    FROM taxas_condominiais
                    GROUP BY unidade_id
             ) res ON res.unidade_id = u.id
             LEFT JOIN usuarios prop ON prop.id = u.proprietario_id
             LEFT JOIN usuarios inq  ON inq.id  = u.inquilino_id
             WHERE u.ativo = 1
             ORDER BY u.bloco, u.numero'
        );
        return array_map(fn($l) => Unidade::fromArray($l), $stmt->fetchAll());
    }

    public function contarInadimplentes(): int
    {
        $stmt = $this->conexao->query(
            'SELECT COUNT(DISTINCT tc.unidade_id)
             FROM taxas_condominiais tc
             WHERE tc.competencia = DATE_FORMAT(NOW(), "%Y-%m")
               AND tc.status IN ("pendente","vencido")'
        );
        return (int) $stmt->fetchColumn();
    }

    public function salvar(Unidade $unidade): int
    {
        if ($unidade->id === null) {
            return $this->inserir($unidade);
        }
        $this->atualizar($unidade);
        return $unidade->id;
    }

    private function inserir(Unidade $unidade): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO unidades
                (numero, bloco, andar, descricao,
                 tipo_ocupacao, proprietario_id, inquilino_id,
                 nome_proprietario, telefone_proprietario, email_proprietario,
                 nome_inquilino, telefone_inquilino, email_inquilino, ativo)
             VALUES
                (:numero, :bloco, :andar, :descricao,
                 :tipo_ocupacao, :proprietario_id, :inquilino_id,
                 :nome_proprietario, :telefone_proprietario, :email_proprietario,
                 :nome_inquilino, :telefone_inquilino, :email_inquilino, :ativo)'
        );
        $stmt->execute($this->parametros($unidade));
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(Unidade $unidade): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE unidades
             SET numero = :numero, bloco = :bloco, andar = :andar, descricao = :descricao,
                 tipo_ocupacao = :tipo_ocupacao,
                 proprietario_id = :proprietario_id,
                 inquilino_id = :inquilino_id,
                 nome_proprietario = :nome_proprietario,
                 telefone_proprietario = :telefone_proprietario,
                 email_proprietario = :email_proprietario,
                 nome_inquilino = :nome_inquilino,
                 telefone_inquilino = :telefone_inquilino,
                 email_inquilino = :email_inquilino,
                 ativo = :ativo
             WHERE id = :id'
        );
        $stmt->execute([...$this->parametros($unidade), ':id' => $unidade->id]);
    }

    private function parametros(Unidade $unidade): array
    {
        return [
            ':numero'                => $unidade->numero,
            ':bloco'                 => $unidade->bloco,
            ':andar'                 => $unidade->andar,
            ':descricao'             => $unidade->descricao,
            ':tipo_ocupacao'         => $unidade->tipoOcupacao,
            ':proprietario_id'       => $unidade->proprietarioId ?: null,
            ':inquilino_id'          => $unidade->inquilinoId    ?: null,
            ':nome_proprietario'     => $unidade->nomeProprietario     ?: null,
            ':telefone_proprietario' => $unidade->telefoneProprietario ?: null,
            ':email_proprietario'    => $unidade->emailProprietario    ?: null,
            ':nome_inquilino'        => $unidade->nomeInquilino        ?: null,
            ':telefone_inquilino'    => $unidade->telefoneInquilino    ?: null,
            ':email_inquilino'       => $unidade->emailInquilino       ?: null,
            ':ativo'                 => (int) $unidade->ativo,
        ];
    }
}
