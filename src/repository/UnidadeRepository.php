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
                    m_usr.nome AS nome_responsavel,
                    tc.status  AS status_taxa_atual
             FROM unidades u
             LEFT JOIN moradores m
                    ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios m_usr
                    ON m_usr.id = m.usuario_id
             LEFT JOIN taxas_condominiais tc
                    ON tc.unidade_id = u.id
                   AND tc.competencia = DATE_FORMAT(NOW(), "%Y-%m")
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
                    m_usr.nome AS nome_responsavel,
                    tc.status  AS status_taxa_atual
             FROM unidades u
             LEFT JOIN moradores m
                    ON m.unidade_id = u.id AND m.responsavel = 1 AND m.ativo = 1
             LEFT JOIN usuarios m_usr
                    ON m_usr.id = m.usuario_id
             LEFT JOIN taxas_condominiais tc
                    ON tc.unidade_id = u.id
                   AND tc.competencia = DATE_FORMAT(NOW(), "%Y-%m")
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
                 tipo_ocupacao, nome_proprietario, telefone_proprietario, email_proprietario,
                 nome_inquilino, telefone_inquilino, email_inquilino, ativo)
             VALUES
                (:numero, :bloco, :andar, :descricao,
                 :tipo_ocupacao, :nome_proprietario, :telefone_proprietario, :email_proprietario,
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
