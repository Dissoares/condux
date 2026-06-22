<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AutenticacaoService.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';

class AutenticacaoController
{
    private AutenticacaoService $autenticacaoService;

    public function __construct()
    {
        $this->autenticacaoService = new AutenticacaoService(
            new UsuarioRepository(Conexao::obter())
        );
    }

    public function exibir(): void
    {
        if (Sessao::estaAutenticado()) {
            Roteador::redirecionar('/painel');
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
            Roteador::redirecionar('/login');
        }

        $usuario = $this->autenticacaoService->entrar($email, $senha);

        if ($usuario === null) {
            Sessao::flash('erro', 'E-mail ou senha incorretos.');
            Roteador::redirecionar('/login');
        }

        Roteador::redirecionar('/painel');
    }

    public function sair(): void
    {
        $this->autenticacaoService->sair();
        Roteador::redirecionar('/login');
    }
}
