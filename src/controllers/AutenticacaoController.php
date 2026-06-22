<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AutenticacaoService.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';

class AutenticacaoController
{
    private AutenticacaoService $autenticacaoService;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->autenticacaoService = new AutenticacaoService(
            new UsuarioRepository($conexao)
        );
    }

    public function exibir(): void
    {
        if (Sessao::estaAutenticado()) {
            $this->redirecionarParaPainel();
            return;
        }
        $erroLogin = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/autenticacao/login.php';
    }

    public function entrar(): void
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            Sessao::flash('erro', 'Preencha e-mail e senha.');
            $this->redirecionarParaLogin();
            return;
        }

        $usuario = $this->autenticacaoService->entrar($email, $senha);

        if ($usuario === null) {
            Sessao::flash('erro', 'E-mail ou senha incorretos.');
            $this->redirecionarParaLogin();
            return;
        }

        $this->redirecionarParaPainel();
    }

    public function sair(): void
    {
        $this->autenticacaoService->sair();
        $this->redirecionarParaLogin();
    }

    private function redirecionarParaLogin(): void
    {
        header('Location: ' . Roteador::urlLogin());
        exit;
    }

    private function redirecionarParaPainel(): void
    {
        $app = require RAIZ . '/config/app.php';
        header('Location: ' . $app['url_base'] . '/index.php?pagina=painel');
        exit;
    }
}
