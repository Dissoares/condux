<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/UsuarioRepository.php';

class PerfilController
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository(Conexao::obter());
    }

    public function exibir(): void
    {
        $usuario      = $this->repo->buscarPorId((int) Sessao::usuarioAtual()['id']);
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/perfil/index.php';
    }

    public function salvar(): void
    {
        $id      = (int) Sessao::usuarioAtual()['id'];
        $usuario = $this->repo->buscarPorId($id);

        if (!$usuario) {
            Roteador::redirecionar('/perfil');
            return;
        }

        $nome     = trim($_POST['nome']  ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '') ?: null;

        if (!$nome || !$email) {
            Sessao::flash('erro', 'Nome e e-mail são obrigatórios.');
            Roteador::redirecionar('/perfil');
            return;
        }

        $usuario->nome     = $nome;
        $usuario->email    = $email;
        $usuario->telefone = $telefone;

        // Troca de senha (opcional)
        $senhaAtual = $_POST['senha_atual']  ?? '';
        $senhaNova  = $_POST['senha_nova']   ?? '';
        $senhaConf  = $_POST['senha_conf']   ?? '';

        if ($senhaAtual !== '') {
            if (!password_verify($senhaAtual, $usuario->senha)) {
                Sessao::flash('erro', 'Senha atual incorreta.');
                Roteador::redirecionar('/perfil');
                return;
            }
            if (strlen($senhaNova) < 6) {
                Sessao::flash('erro', 'A nova senha deve ter pelo menos 6 caracteres.');
                Roteador::redirecionar('/perfil');
                return;
            }
            if ($senhaNova !== $senhaConf) {
                Sessao::flash('erro', 'A confirmação da nova senha não confere.');
                Roteador::redirecionar('/perfil');
                return;
            }
            $usuario->senha = password_hash($senhaNova, PASSWORD_DEFAULT);
        }

        $this->repo->salvar($usuario);

        // Atualiza sessão com novo nome
        $_SESSION['usuario']['nome'] = $usuario->nome;

        Sessao::flash('sucesso', 'Perfil atualizado com sucesso.');
        Roteador::redirecionar('/perfil');
    }
}
