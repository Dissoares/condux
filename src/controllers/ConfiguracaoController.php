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
        ];

        // Upload de logo
        if (!empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext  = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
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

        $this->repo->salvarVarios($pares);

        // Regenera manifest.json com novo nome
        $this->regenerarManifest($pares);

        Sessao::flash('sucesso', 'Configurações salvas.');
        Roteador::redirecionar('/configuracoes');
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
