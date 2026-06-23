<?php

declare(strict_types=1);

class PushSubscriptionRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function salvar(int $usuarioId, string $endpoint, string $p256dh, string $auth): void
    {
        $this->conexao->prepare(
            'INSERT INTO push_subscriptions (usuario_id, endpoint, p256dh, auth)
             VALUES (:uid, :ep, :p256, :auth)
             ON DUPLICATE KEY UPDATE usuario_id = :uid2, p256dh = :p256_2, auth = :auth2'
        )->execute([
            ':uid'    => $usuarioId,
            ':ep'     => $endpoint,
            ':p256'   => $p256dh,
            ':auth'   => $auth,
            ':uid2'   => $usuarioId,
            ':p256_2' => $p256dh,
            ':auth2'  => $auth,
        ]);
    }

    public function remover(string $endpoint): void
    {
        $this->conexao->prepare(
            'DELETE FROM push_subscriptions WHERE endpoint = :ep'
        )->execute([':ep' => $endpoint]);
    }

    public function removerPorEndpoint(string $endpoint): void
    {
        $this->remover($endpoint);
    }

    /** @return array[] */
    public function listarTodas(): array
    {
        return $this->conexao->query(
            'SELECT endpoint, p256dh, auth FROM push_subscriptions'
        )->fetchAll();
    }

    /** @return array[] — subscriptions de um usuário específico */
    public function listarPorUsuario(int $usuarioId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT endpoint, p256dh, auth FROM push_subscriptions WHERE usuario_id = :uid'
        );
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /** @return array[] — subscriptions de todos os usuários com um perfil (ex: 'admin') */
    public function listarPorPerfil(string $perfil): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT ps.endpoint, ps.p256dh, ps.auth
             FROM push_subscriptions ps
             JOIN usuarios u ON u.id = ps.usuario_id
             WHERE u.perfil = :perfil AND u.ativo = 1'
        );
        $stmt->execute([':perfil' => $perfil]);
        return $stmt->fetchAll();
    }
}
