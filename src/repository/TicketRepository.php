<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Ticket.php';

class TicketRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** @return Ticket[] */
    public function listarTodos(string $status = '', string $categoria = ''): array
    {
        $where = ['1=1'];
        $params = [];
        if ($status)    { $where[] = 't.status = :status';       $params[':status'] = $status; }
        if ($categoria) { $where[] = 't.categoria = :categoria'; $params[':categoria'] = $categoria; }

        $sql = 'SELECT t.*,
                       u.nome AS nome_usuario, u.foto AS foto_usuario,
                       r.nome AS nome_responsavel,
                       (SELECT COUNT(*) FROM ticket_mensagens WHERE ticket_id = t.id) AS total_mensagens
                FROM tickets t
                JOIN usuarios u ON u.id = t.usuario_id
                LEFT JOIN usuarios r ON r.id = t.responsavel_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY
                  FIELD(t.status,"aberto","em_andamento","resolvido","fechado"),
                  FIELD(t.prioridade,"urgente","alta","normal","baixa"),
                  t.criado_em DESC';
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);
        return array_map(fn($r) => Ticket::fromArray($r), $stmt->fetchAll());
    }

    /** @return Ticket[] Tickets do morador */
    public function listarPorUsuario(int $usuarioId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT t.*,
                    u.nome AS nome_usuario, u.foto AS foto_usuario,
                    r.nome AS nome_responsavel,
                    (SELECT COUNT(*) FROM ticket_mensagens WHERE ticket_id = t.id) AS total_mensagens
             FROM tickets t
             JOIN usuarios u ON u.id = t.usuario_id
             LEFT JOIN usuarios r ON r.id = t.responsavel_id
             WHERE t.usuario_id = :uid
             ORDER BY t.criado_em DESC'
        );
        $stmt->execute([':uid' => $usuarioId]);
        return array_map(fn($r) => Ticket::fromArray($r), $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?Ticket
    {
        $stmt = $this->conexao->prepare(
            'SELECT t.*,
                    u.nome AS nome_usuario, u.foto AS foto_usuario,
                    r.nome AS nome_responsavel
             FROM tickets t
             JOIN usuarios u ON u.id = t.usuario_id
             LEFT JOIN usuarios r ON r.id = t.responsavel_id
             WHERE t.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Ticket::fromArray($row) : null;
    }

    public function criar(int $usuarioId, string $titulo, string $descricao, string $categoria, string $prioridade): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO tickets (titulo, descricao, categoria, prioridade, usuario_id)
             VALUES (:titulo, :descricao, :categoria, :prioridade, :usuario_id)'
        );
        $stmt->execute([
            ':titulo'     => $titulo,
            ':descricao'  => $descricao,
            ':categoria'  => $categoria,
            ':prioridade' => $prioridade,
            ':usuario_id' => $usuarioId,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizarStatus(int $id, string $status, ?int $responsavelId = null): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE tickets SET status = :status, responsavel_id = :resp WHERE id = :id'
        );
        $stmt->execute([':status' => $status, ':resp' => $responsavelId, ':id' => $id]);
    }

    /** @return array[] */
    public function mensagens(int $ticketId, bool $incluirInternas = false): array
    {
        $sql = 'SELECT m.*, u.nome AS nome_usuario, u.foto AS foto_usuario, u.perfil AS perfil_usuario
                FROM ticket_mensagens m
                JOIN usuarios u ON u.id = m.usuario_id
                WHERE m.ticket_id = :tid'
              . ($incluirInternas ? '' : ' AND m.interno = 0')
              . ' ORDER BY m.criado_em ASC';
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function adicionarMensagem(int $ticketId, int $usuarioId, string $mensagem, bool $interno = false): void
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO ticket_mensagens (ticket_id, usuario_id, mensagem, interno)
             VALUES (:tid, :uid, :msg, :interno)'
        );
        $stmt->execute([
            ':tid'     => $ticketId,
            ':uid'     => $usuarioId,
            ':msg'     => $mensagem,
            ':interno' => (int) $interno,
        ]);
        // Reabre ticket se estava fechado/resolvido e morador respondeu
        $this->conexao->prepare(
            'UPDATE tickets SET atualizado_em = NOW()
             WHERE id = :id'
        )->execute([':id' => $ticketId]);
    }

    public function contarAbertos(): int
    {
        return (int) $this->conexao->query(
            "SELECT COUNT(*) FROM tickets WHERE status IN ('aberto','em_andamento')"
        )->fetchColumn();
    }

    public function contarPorUsuario(int $usuarioId): int
    {
        $stmt = $this->conexao->prepare(
            "SELECT COUNT(*) FROM tickets WHERE usuario_id = :uid AND status NOT IN ('resolvido','fechado')"
        );
        $stmt->execute([':uid' => $usuarioId]);
        return (int) $stmt->fetchColumn();
    }
}
