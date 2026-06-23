<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/PushSubscriptionRepository.php';

class PushController
{
    private PushSubscriptionRepository $repo;

    public function __construct()
    {
        $this->repo = new PushSubscriptionRepository(Conexao::obter());
    }

    /** POST /push/subscribe — salva subscription do browser */
    public function subscribe(): void
    {
        if (!Sessao::estaAutenticado()) {
            http_response_code(401);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['endpoint']) || empty($body['keys']['p256dh']) || empty($body['keys']['auth'])) {
            http_response_code(400);
            exit;
        }

        $this->repo->salvar(
            (int) Sessao::usuarioAtual()['id'],
            $body['endpoint'],
            $body['keys']['p256dh'],
            $body['keys']['auth'],
        );

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    /** POST /push/unsubscribe */
    public function unsubscribe(): void
    {
        if (!Sessao::estaAutenticado()) {
            http_response_code(401);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true);
        if (!empty($body['endpoint'])) {
            $this->repo->remover($body['endpoint']);
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    /** GET /push/vapid-public-key — retorna a chave pública VAPID */
    public function vapidKey(): void
    {
        $cfgPath = RAIZ . '/config/vapid.php';
        if (!file_exists($cfgPath)) {
            http_response_code(503);
            echo json_encode(['error' => 'VAPID não configurado']);
            return;
        }
        $cfg = require $cfgPath;
        header('Content-Type: application/json');
        echo json_encode(['publicKey' => $cfg['public_key']]);
    }
}
