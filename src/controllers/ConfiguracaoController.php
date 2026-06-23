<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/ConfiguracaoRepository.php';

class ConfiguracaoController
{
    private ConfiguracaoRepository $repo;

    public function __construct()
    {
        if (!in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true)) {
            http_response_code(403); exit;
        }
        $this->repo = new ConfiguracaoRepository(Conexao::obter());
    }

    public function exibir(): void
    {
        $config       = $this->repo->todas();
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/admin/configuracoes/index.php';
    }

    public function salvar(): void
    {
        $pares = [
            'app_nome'       => trim($_POST['app_nome']       ?? '') ?: 'Condux',
            'app_nome_curto' => trim($_POST['app_nome_curto'] ?? '') ?: 'Condux',
            'app_descricao'  => trim($_POST['app_descricao']  ?? ''),
            'cor_primaria'   => $this->validarCor($_POST['cor_primaria'] ?? '', '#1a3c5e'),
            'cor_escura'     => $this->validarCor($_POST['cor_escura']   ?? '', '#0f2540'),
            'cor_acento'     => $this->validarCor($_POST['cor_acento']   ?? '', '#f0a500'),
            // E-mail / SMTP
            'email_ativo'           => isset($_POST['email_ativo']) ? '1' : '0',
            'email_smtp_host'       => trim($_POST['email_smtp_host']       ?? ''),
            'email_smtp_porta'      => trim($_POST['email_smtp_porta']      ?? '587'),
            'email_smtp_usuario'    => trim($_POST['email_smtp_usuario']    ?? ''),
            'email_smtp_seguranca'  => trim($_POST['email_smtp_seguranca']  ?? 'tls'),
            'email_remetente_nome'  => trim($_POST['email_remetente_nome']  ?? ''),
            'email_remetente_email' => trim($_POST['email_remetente_email'] ?? ''),
        ];
        // Senha SMTP: só atualiza se preenchida
        $senhaSMTP = trim($_POST['email_smtp_senha'] ?? '');
        if ($senhaSMTP !== '') {
            $pares['email_smtp_senha'] = $senhaSMTP;
        }

        // Upload de logo
        if (!empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext   = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $bytes = $_FILES['logo']['size'];
            if ($bytes > 2 * 1024 * 1024) {
                Sessao::flash('erro_configuracao', 'O logo deve ter no máximo 2 MB.');
                Roteador::redirecionar('/configuracoes');
                return;
            }
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp'], true)) {
                $dir  = RAIZ . '/public/uploads/configuracoes';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                // Remove logo anterior
                $logoAtual = $this->repo->obter('app_logo');
                if ($logoAtual && file_exists(RAIZ . '/public/uploads/' . $logoAtual)) {
                    @unlink(RAIZ . '/public/uploads/' . $logoAtual);
                }
                $nome = 'configuracoes/logo.' . $ext;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], RAIZ . '/public/uploads/' . $nome)) {
                    $pares['app_logo'] = $nome;
                }
            }
        }

        // Remover logo
        if (isset($_POST['remover_logo'])) {
            $logoAtual = $this->repo->obter('app_logo');
            if ($logoAtual && file_exists(RAIZ . '/public/uploads/' . $logoAtual)) {
                @unlink(RAIZ . '/public/uploads/' . $logoAtual);
            }
            $pares['app_logo'] = null;
        }

        // Upload do ícone PWA (quadrado → gera icon-192.png e icon-512.png)
        if (!empty($_FILES['icone_pwa']) && $_FILES['icone_pwa']['error'] === UPLOAD_ERR_OK) {
            $ext   = strtolower(pathinfo($_FILES['icone_pwa']['name'], PATHINFO_EXTENSION));
            $bytes = $_FILES['icone_pwa']['size'];
            if ($bytes > 2 * 1024 * 1024) {
                Sessao::flash('erro', 'O ícone do app deve ter no máximo 2 MB.');
                Roteador::redirecionar('/configuracoes');
                return;
            }
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $this->gerarIconesPwa($_FILES['icone_pwa']['tmp_name'], $ext);
            }
        }

        $this->repo->salvarVarios($pares);

        // Regenera manifest.json com novo nome
        $this->regenerarManifest($pares);

        Sessao::flash('sucesso', 'Configurações salvas.');
        Roteador::redirecionar('/configuracoes');
    }

    private function gerarIconesPwa(string $tmpPath, string $ext): void
    {
        if (!function_exists('imagecreatefromstring')) return;

        $conteudo = file_get_contents($tmpPath);
        $origem   = imagecreatefromstring($conteudo);
        if (!$origem) return;

        $dir = RAIZ . '/public/icons';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        foreach ([192, 512] as $tamanho) {
            $dest = imagecreatetruecolor($tamanho, $tamanho);
            // Fundo branco (para ícones sem transparência)
            $branco = imagecolorallocate($dest, 255, 255, 255);
            imagefill($dest, 0, 0, $branco);
            // Preserva transparência se PNG
            if ($ext === 'png') {
                imagealphablending($dest, false);
                imagesavealpha($dest, true);
                $transparente = imagecolorallocatealpha($dest, 0, 0, 0, 127);
                imagefill($dest, 0, 0, $transparente);
                imagealphablending($dest, true);
            }
            $larg = imagesx($origem);
            $alt  = imagesy($origem);
            imagecopyresampled($dest, $origem, 0, 0, 0, 0, $tamanho, $tamanho, $larg, $alt);
            imagepng($dest, $dir . '/icon-' . $tamanho . '.png');
            imagedestroy($dest);
        }
        imagedestroy($origem);
    }

    private function validarCor(string $valor, string $padrao): string
    {
        $v = trim($valor);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? $v : $padrao;
    }

    private function regenerarManifest(array $cfg): void
    {
        $manifest = [
            'name'             => $cfg['app_nome']       ?? 'Condux',
            'short_name'       => $cfg['app_nome_curto'] ?? 'Condux',
            'description'      => $cfg['app_descricao']  ?? '',
            'start_url'        => '/',
            'display'          => 'standalone',
            'background_color' => $cfg['cor_primaria']   ?? '#1a3c5e',
            'theme_color'      => $cfg['cor_primaria']   ?? '#1a3c5e',
            'orientation'      => 'portrait-primary',
            'icons'            => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ];
        file_put_contents(
            RAIZ . '/public/manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
