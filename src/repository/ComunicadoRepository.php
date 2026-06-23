<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Comunicado.php';

class ComunicadoRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** @return Comunicado[] — todos, para o admin */
    public function listarTodos(): array
    {
        return array_map(
            fn($l) => Comunicado::fromArray($l),
            $this->conexao->query(
                'SELECT c.*, u.nome AS nome_autor
                 FROM comunicados c
                 LEFT JOIN usuarios u ON u.id = c.publicado_por
                 ORDER BY c.data_publicacao DESC, c.id DESC'
            )->fetchAll()
        );
    }

    /** @return Comunicado[] — apenas ativos e dentro do prazo, para moradores */
    public function listarAtivos(): array
    {
        $hoje = date('Y-m-d');
        $stmt = $this->conexao->prepare(
            "SELECT c.*, u.nome AS nome_autor
             FROM comunicados c
             LEFT JOIN usuarios u ON u.id = c.publicado_por
             WHERE c.ativo = 1
               AND c.data_publicacao <= :hoje
               AND (c.data_expiracao IS NULL OR c.data_expiracao >= :hoje2)
             ORDER BY c.tipo = 'urgente' DESC, c.data_publicacao DESC, c.id DESC"
        );
        $stmt->execute([':hoje' => $hoje, ':hoje2' => $hoje]);
        return array_map(fn($l) => Comunicado::fromArray($l), $stmt->fetchAll());
    }

    /** Últimos N ativos, para widget no painel */
    public function listarUltimosAtivos(int $limite = 5): array
    {
        $hoje = date('Y-m-d');
        $stmt = $this->conexao->prepare(
            "SELECT c.*, u.nome AS nome_autor
             FROM comunicados c
             LEFT JOIN usuarios u ON u.id = c.publicado_por
             WHERE c.ativo = 1
               AND c.data_publicacao <= :hoje
               AND (c.data_expiracao IS NULL OR c.data_expiracao >= :hoje2)
             ORDER BY c.tipo = 'urgente' DESC, c.data_publicacao DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':hoje',  $hoje, PDO::PARAM_STR);
        $stmt->bindValue(':hoje2', $hoje, PDO::PARAM_STR);
        $stmt->bindValue(':lim',   $limite, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($l) => Comunicado::fromArray($l), $stmt->fetchAll());
    }

    public function contarAtivos(): int
    {
        $hoje = date('Y-m-d');
        $stmt = $this->conexao->prepare(
            "SELECT COUNT(*) FROM comunicados
             WHERE ativo = 1
               AND data_publicacao <= :hoje
               AND (data_expiracao IS NULL OR data_expiracao >= :hoje2)"
        );
        $stmt->execute([':hoje' => $hoje, ':hoje2' => $hoje]);
        return (int) $stmt->fetchColumn();
    }

    public function buscarPorId(int $id): ?Comunicado
    {
        $stmt = $this->conexao->prepare(
            'SELECT c.*, u.nome AS nome_autor FROM comunicados c
             LEFT JOIN usuarios u ON u.id = c.publicado_por
             WHERE c.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? Comunicado::fromArray($linha) : null;
    }

    public function salvar(Comunicado $c): int
    {
        if ($c->id === null) {
            $stmt = $this->conexao->prepare(
                'INSERT INTO comunicados
                 (titulo, conteudo, tipo, publicado_por, data_publicacao, data_expiracao, ativo)
                 VALUES (:tit, :cont, :tipo, :pub, :dp, :de, :ativo)'
            );
        } else {
            $stmt = $this->conexao->prepare(
                'UPDATE comunicados SET
                 titulo = :tit, conteudo = :cont, tipo = :tipo,
                 data_publicacao = :dp, data_expiracao = :de, ativo = :ativo
                 WHERE id = :id'
            );
        }

        $params = [
            ':tit'   => $c->titulo,
            ':cont'  => $c->conteudo,
            ':tipo'  => $c->tipo,
            ':dp'    => $c->dataPublicacao,
            ':de'    => $c->dataExpiracao,
            ':ativo' => (int) $c->ativo,
        ];

        if ($c->id === null) {
            $params[':pub'] = $c->publicadoPor;
        } else {
            $params[':id'] = $c->id;
        }

        $stmt->execute($params);
        return $c->id ?? (int) $this->conexao->lastInsertId();
    }

    public function excluir(int $id): void
    {
        $this->conexao->prepare('DELETE FROM comunicados WHERE id = :id')->execute([':id' => $id]);
    }
}
