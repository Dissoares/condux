<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AutenticacaoService.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';
require_once __DIR__ . '/../services/EmailService.php';

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

    public function esqueciSenha(): void
    {
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/autenticacao/esqueci-senha.php';
    }

    public function enviarRecuperacao(): void
    {
        $email   = trim($_POST['email'] ?? '');
        $userRepo = new UsuarioRepository(Conexao::obter());
        $usuario  = $userRepo->buscarPorEmail($email);

        // Sempre exibe a mesma mensagem (não revela se e-mail existe)
        Sessao::flash('sucesso', 'Se o e-mail existir no sistema, você receberá as instruções em breve.');

        if ($usuario) {
            $token   = bin2hex(random_bytes(32));
            $expira  = date('Y-m-d H:i:s', strtotime('+2 hours'));
            $userRepo->salvarTokenReset((int) $usuario->id, $token, $expira);
            (new EmailService())->recuperarSenha($usuario->email, $usuario->nome, $token);
        }

        Roteador::redirecionar('/esqueci-senha');
    }

    public function redefinirSenha(): void
    {
        $token        = trim($_GET['token'] ?? '');
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/autenticacao/redefinir-senha.php';
    }

    public function salvarNovaSenha(): void
    {
        $token  = trim($_POST['token']  ?? '');
        $nova   = $_POST['senha_nova']  ?? '';
        $conf   = $_POST['senha_conf']  ?? '';

        if (!$token || !$nova || $nova !== $conf || strlen($nova) < 6) {
            Sessao::flash('erro', 'Dados inválidos. A senha deve ter pelo menos 6 caracteres e as duas confirmações devem ser iguais.');
            Roteador::redirecionar('/redefinir-senha?token=' . urlencode($token));
            return;
        }

        $userRepo = new UsuarioRepository(Conexao::obter());
        $usuario  = $userRepo->buscarPorToken($token);

        if (!$usuario) {
            Sessao::flash('erro', 'Link inválido ou expirado. Solicite um novo link.');
            Roteador::redirecionar('/esqueci-senha');
            return;
        }

        $userRepo->atualizarSenha((int) $usuario->id, password_hash($nova, PASSWORD_DEFAULT));
        $userRepo->limparTokenReset((int) $usuario->id);

        Sessao::flash('sucesso', 'Senha redefinida com sucesso! Faça login.');
        Roteador::redirecionar('/login');
    }
}
