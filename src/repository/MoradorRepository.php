<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Morador.php';

class MoradorRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?Morador
    {
        $stmt = $this->conexao->prepare(
            'SELECT m.*,
                    u.nome  AS nome_usuario,
                    u.email AS email_usuario,
                    CONCAT("Apto ", un.numero, IF(un.bloco IS NOT NULL, CONCAT(" — Bloco ", un.bloco), "")) AS identificacao_unidade
             FROM moradores m
             JOIN usuarios  u  ON u.id  = m.usuario_id
             JOIN unidades  un ON un.id = m.unidade_id
             WHERE m.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? Morador::fromArray($linha) : null;
    }

    /** @return Morador[] */
    public function listarAtivosPorUnidade(int $unidadeId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT m.*,
                    u.nome  AS nome_usuario,
                    u.email AS email_usuario,
                    CONCAT("Apto ", un.numero, IF(un.bloco IS NOT NULL, CONCAT(" — Bloco ", un.bloco), "")) AS identificacao_unidade
             FROM moradores m
             JOIN usuarios  u  ON u.id  = m.usuario_id
             JOIN unidades  un ON un.id = m.unidade_id
             WHERE m.unidade_id = :unidade_id AND m.ativo = 1
             ORDER BY m.responsavel DESC, u.nome'
        );
        $stmt->execute([':unidade_id' => $unidadeId]);
        return array_map(fn($l) => Morador::fromArray($l), $stmt->fetchAll());
    }

    /** @return Morador[] */
    public function listarTodos(): array
    {
        $stmt = $this->conexao->query(
            'SELECT m.*,
                    u.nome  AS nome_usuario,
                    u.email AS email_usuario,
                    CONCAT("Apto ", un.numero, IF(un.bloco IS NOT NULL, CONCAT(" — Bloco ", un.bloco), "")) AS identificacao_unidade
             FROM moradores m
             JOIN usuarios  u  ON u.id  = m.usuario_id
             JOIN unidades  un ON un.id = m.unidade_id
             WHERE m.ativo = 1
             ORDER BY un.bloco, un.numero, m.responsavel DESC'
        );
        return array_map(fn($l) => Morador::fromArray($l), $stmt->fetchAll());
    }

    /** @return Morador[] Vínculos ativos do condômino com suas unidades */
    public function listarPorUsuario(int $usuarioId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT m.*,
                    u.nome  AS nome_usuario,
                    u.email AS email_usuario,
                    CONCAT("Apto ", un.numero, IF(un.bloco IS NOT NULL, CONCAT(" — Bloco ", un.bloco), "")) AS identificacao_unidade
             FROM moradores m
             JOIN usuarios u  ON u.id  = m.usuario_id
             JOIN unidades un ON un.id = m.unidade_id
             WHERE m.usuario_id = :usuario_id AND m.ativo = 1
             ORDER BY un.bloco, un.numero'
        );
        $stmt->execute([':usuario_id' => $usuarioId]);
        return array_map(fn($l) => Morador::fromArray($l), $stmt->fetchAll());
    }

    /**
     * @return array<int, Morador[]>  Moradores ativos indexados por unidade_id
     */
    public function listarTodosAtivosAgrupados(): array
    {
        $stmt = $this->conexao->query(
            'SELECT m.*, u.nome AS nome_usuario, u.email AS email_usuario
             FROM moradores m
             JOIN usuarios u ON u.id = m.usuario_id
             WHERE m.ativo = 1
             ORDER BY m.unidade_id, m.responsavel DESC, u.nome'
        );
        $agrupados = [];
        foreach ($stmt->fetchAll() as $linha) {
            $m = Morador::fromArray($linha);
            $agrupados[$m->unidadeId][] = $m;
        }
        return $agrupados;
    }

    public function buscarUnidadeDoUsuario(int $usuarioId): ?int
    {
        $stmt = $this->conexao->prepare(
            'SELECT unidade_id FROM moradores
             WHERE usuario_id = :usuario_id AND ativo = 1 LIMIT 1'
        );
        $stmt->execute([':usuario_id' => $usuarioId]);
        $linha = $stmt->fetch();

        return $linha ? (int) $linha['unidade_id'] : null;
    }

    public function salvar(Morador $morador): int
    {
        if ($morador->id === null) {
            return $this->inserir($morador);
        }
        $this->atualizar($morador);
        return $morador->id;
    }

    private function inserir(Morador $morador): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO moradores (usuario_id, unidade_id, responsavel, data_entrada, ativo)
             VALUES (:usuario_id, :unidade_id, :responsavel, :data_entrada, 1)'
        );
        $stmt->execute([
            ':usuario_id'   => $morador->usuarioId,
            ':unidade_id'   => $morador->unidadeId,
            ':responsavel'  => (int) $morador->responsavel,
            ':data_entrada' => $morador->dataEntrada,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(Morador $morador): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE moradores SET responsavel = :responsavel, data_saida = :data_saida,
             ativo = :ativo WHERE id = :id'
        );
        $stmt->execute([
            ':responsavel' => (int) $morador->responsavel,
            ':data_saida'  => $morador->dataSaida,
            ':ativo'       => (int) $morador->ativo,
            ':id'          => $morador->id,
        ]);
    }

    public function desativar(int $id): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE moradores SET ativo = 0, data_saida = CURDATE() WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    public function contarAtivos(): int
    {
        $stmt = $this->conexao->query('SELECT COUNT(*) FROM moradores WHERE ativo = 1');
        return (int) $stmt->fetchColumn();
    }
}
