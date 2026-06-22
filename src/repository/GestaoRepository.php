<?php

declare(strict_types=1);

require_once RAIZ . '/src/models/Gestao.php';

class GestaoRepository
{
    public function __construct(private \PDO $pdo) {}

    public function listarTodas(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM gestoes ORDER BY status ASC, inicio DESC'
        );
        $gestoes = array_map(fn($l) => Gestao::fromArray($l), $stmt->fetchAll());
        foreach ($gestoes as $g) {
            $g->membros = $this->buscarMembros((int) $g->id);
        }
        return $gestoes;
    }

    public function buscarAtiva(): ?Gestao
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM gestoes WHERE status = 'ativa' ORDER BY inicio DESC LIMIT 1"
        );
        $linha = $stmt->fetch();
        if (!$linha) return null;

        $g = Gestao::fromArray($linha);
        $g->membros = $this->buscarMembros((int) $g->id);
        return $g;
    }

    public function buscarPorId(int $id): ?Gestao
    {
        $stmt = $this->pdo->prepare('SELECT * FROM gestoes WHERE id = ?');
        $stmt->execute([$id]);
        $linha = $stmt->fetch();
        if (!$linha) return null;

        $g = Gestao::fromArray($linha);
        $g->membros = $this->buscarMembros($id);
        return $g;
    }

    /** Retorna qual gestão estava ativa em uma determinada data */
    public function buscarAtivaNaData(string $data): ?Gestao
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM gestoes
             WHERE inicio <= :data
               AND (fim IS NULL OR fim >= :data)
             ORDER BY inicio DESC
             LIMIT 1"
        );
        $stmt->execute(['data' => $data]);
        $linha = $stmt->fetch();
        if (!$linha) return null;

        $g = Gestao::fromArray($linha);
        $g->membros = $this->buscarMembros((int) $g->id);
        return $g;
    }

    public function inserir(array $dados): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO gestoes (descricao, inicio, fim, status, observacoes)
             VALUES (:descricao, :inicio, :fim, :status, :observacoes)'
        );
        $stmt->execute([
            'descricao'   => trim($dados['descricao']),
            'inicio'      => $dados['inicio'],
            'fim'         => $dados['fim'] ?: null,
            'status'      => $dados['status'] ?? 'ativa',
            'observacoes' => $dados['observacoes'] ?: null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE gestoes
             SET descricao = :descricao, inicio = :inicio, fim = :fim,
                 status = :status, observacoes = :observacoes
             WHERE id = :id'
        );
        $stmt->execute([
            'id'          => $id,
            'descricao'   => trim($dados['descricao']),
            'inicio'      => $dados['inicio'],
            'fim'         => $dados['fim'] ?: null,
            'status'      => $dados['status'] ?? 'ativa',
            'observacoes' => $dados['observacoes'] ?: null,
        ]);
    }

    public function encerrar(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE gestoes SET status = 'encerrada', fim = COALESCE(fim, CURDATE()) WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    public function excluir(int $id): void
    {
        $this->pdo->prepare('DELETE FROM gestoes WHERE id = ?')->execute([$id]);
    }

    public function sincronizarMembros(int $gestaoId, array $membros): void
    {
        $this->pdo->prepare('DELETE FROM gestao_membros WHERE gestao_id = ?')->execute([$gestaoId]);

        if (empty($membros)) return;

        $stmt = $this->pdo->prepare(
            'INSERT INTO gestao_membros (gestao_id, usuario_id, cargo) VALUES (?, ?, ?)'
        );
        foreach ($membros as $m) {
            if (empty($m['usuario_id']) || empty($m['cargo'])) continue;
            $stmt->execute([$gestaoId, (int) $m['usuario_id'], $m['cargo']]);
        }
    }

    private function buscarMembros(int $gestaoId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT gm.id AS membro_id, gm.cargo, u.id, u.nome, u.email, u.perfil
             FROM gestao_membros gm
             JOIN usuarios u ON u.id = gm.usuario_id
             WHERE gm.gestao_id = ?
             ORDER BY FIELD(gm.cargo, "sindico","subsindico","conselheiro","suplente"), u.nome'
        );
        $stmt->execute([$gestaoId]);
        return $stmt->fetchAll();
    }
}
