<?php

declare(strict_types=1);

require_once RAIZ . '/vendor/autoload.php';
require_once RAIZ . '/src/repository/PushSubscriptionRepository.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

class PushNotificationService
{
    private ?array $vapid;
    private PushSubscriptionRepository $subRepo;

    public function __construct(PushSubscriptionRepository $subRepo)
    {
        $this->subRepo = $subRepo;
        $cfgPath = RAIZ . '/config/vapid.php';
        $this->vapid = file_exists($cfgPath) ? require $cfgPath : null;
    }

    /** Gera e persiste as chaves VAPID em config/vapid.php */
    public static function gerarChaves(): array
    {
        require_once RAIZ . '/vendor/autoload.php';

        // No Windows o OpenSSL precisa que OPENSSL_CONF aponte para o .cnf
        if (DIRECTORY_SEPARATOR === '\\' && empty(getenv('OPENSSL_CONF'))) {
            $candidatos = [
                'C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/extras/ssl/openssl.cnf',
                'C:/laragon/bin/apache/httpd-2.4.66-260107-Win64-VS18/conf/openssl.cnf',
                'C:/Program Files/OpenSSL-Win64/bin/openssl.cfg',
            ];
            foreach ($candidatos as $c) {
                if (file_exists($c)) { putenv("OPENSSL_CONF={$c}"); break; }
            }
        }

        $keys = VAPID::createVapidKeys();

        $conteudo = "<?php\nreturn [\n"
            . "    'public_key'  => '" . $keys['publicKey']  . "',\n"
            . "    'private_key' => '" . $keys['privateKey'] . "',\n"
            . "    'subject'     => 'mailto:admin@condux.local',\n"
            . "];\n";

        file_put_contents(RAIZ . '/config/vapid.php', $conteudo);
        return $keys;
    }

    public function getPublicKey(): ?string
    {
        return $this->vapid['public_key'] ?? null;
    }

    public function estaConfigurado(): bool
    {
        return $this->vapid !== null
            && !empty($this->vapid['public_key'])
            && !empty($this->vapid['private_key']);
    }

    /**
     * Envia uma notificação push para todos os assinantes.
     * @param array{title:string, body:string, url?:string, tag?:string} $payload
     */
    public function enviarParaTodos(array $payload): void
    {
        if (!$this->estaConfigurado()) return;

        $subscriptions = $this->subRepo->listarTodas();
        if (empty($subscriptions)) return;

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => $this->vapid['subject'],
                'publicKey'  => $this->vapid['public_key'],
                'privateKey' => $this->vapid['private_key'],
            ],
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub['endpoint'],
                'contentEncoding' => 'aesgcm',
                'keys' => [
                    'p256dh' => $sub['p256dh'],
                    'auth'   => $sub['auth'],
                ],
            ]);
            $webPush->queueNotification($subscription, json_encode($payload));
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $this->subRepo->remover(
                    (string) $report->getRequest()->getUri()
                );
            }
        }
    }
}
