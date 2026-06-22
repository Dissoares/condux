<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/UsuarioRepository.php';

class AutenticacaoService
{
    public function __construct(private readonly UsuarioRepository $usuarioRepository) {}

    /**
     * Valida credenciais e inicia a sessão.
     * Retorna null em caso de falha para não expor qual campo está errado.
     */
    public function entrar(string $email, string $senha): ?array
    {
        $usuario = $this->usuarioRepository->buscarPorEmail($email);

        if ($usuario === null || !password_verify($senha, $usuario->senha)) {
            return null;
        }

        $dadosSessao = [
            'usuario_id' => $usuario->id,
            'usuario'    => [
                'id'     => $usuario->id,
                'nome'   => $usuario->nome,
                'email'  => $usuario->email,
                'perfil' => $usuario->perfil,
            ],
        ];

        Sessao::definir('usuario_id', $usuario->id);
        Sessao::definir('usuario', $dadosSessao['usuario']);

        return $dadosSessao['usuario'];
    }

    public function sair(): void
    {
        Sessao::destruir();
    }

    public function hashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function validarSenha(string $senhaAtual, string $hashArmazenado): bool
    {
        return password_verify($senhaAtual, $hashArmazenado);
    }
}
