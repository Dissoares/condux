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

        $keys = self::tentarGerarChaves();

        if ($keys === null) {
            throw new \RuntimeException('Não foi possível gerar as chaves VAPID. Verifique se o OpenSSL está instalado no servidor.');
        }

        $conteudo = "<?php\nreturn [\n"
            . "    'public_key'  => '" . $keys['publicKey']  . "',\n"
            . "    'private_key' => '" . $keys['privateKey'] . "',\n"
            . "    'subject'     => 'mailto:admin@condux.local',\n"
            . "];\n";

        file_put_contents(RAIZ . '/config/vapid.php', $conteudo);
        return $keys;
    }

    /** Tenta gerar VAPID via biblioteca e, se falhar, via CLI openssl (fallback para Windows/Laragon). */
    private static function tentarGerarChaves(): ?array
    {
        // Tentativa 1: biblioteca (funciona em Linux/macOS/hosting padrão)
        try {
            return VAPID::createVapidKeys();
        } catch (\Throwable $e) {
            // Ignora — tenta via CLI abaixo
        }

        // Tentativa 2: openssl CLI (funciona no Windows/Laragon onde openssl_pkey_new() falha para EC)
        return self::gerarChavesViaCli();
    }

    private static function gerarChavesViaCli(): ?array
    {
        $candidatos = [
            'C:/laragon/bin/git/mingw64/bin/openssl.exe',
            'C:/laragon/bin/git/usr/bin/openssl.exe',
            '/usr/bin/openssl',
            '/usr/local/bin/openssl',
            'openssl',
        ];

        $opensslBin = null;
        foreach ($candidatos as $bin) {
            if ($bin === 'openssl' || (file_exists($bin) && is_executable($bin))) {
                $opensslBin = $bin;
                break;
            }
        }

        if (!$opensslBin) {
            return null;
        }

        $tmpdir  = rtrim(sys_get_temp_dir(), '/\\');
        $privPem = $tmpdir . '/condux_vapid_' . uniqid() . '.pem';

        try {
            $cmd = '"' . $opensslBin . '" ecparam -name prime256v1 -genkey -noout -out "' . $privPem . '" 2>&1';
            exec($cmd, $out, $code);

            if ($code !== 0 || !file_exists($privPem)) {
                return null;
            }

            $privKey = openssl_pkey_get_private(file_get_contents($privPem));
            if (!$privKey) {
                return null;
            }

            $details = openssl_pkey_get_details($privKey);
            if (!$details || !isset($details['ec']['d'], $details['ec']['x'], $details['ec']['y'])) {
                return null;
            }

            $d = str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT);
            $x = str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT);
            $y = str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);

            return [
                'publicKey'  => self::base64urlEncode("\x04" . $x . $y),
                'privateKey' => self::base64urlEncode($d),
            ];
        } finally {
            if (file_exists($privPem)) {
                @unlink($privPem);
            }
        }
    }

    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
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
