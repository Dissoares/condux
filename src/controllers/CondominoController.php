<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/UsuarioRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../models/Usuario.php';

class CondominoController
{
    private UsuarioRepository $usuarioRepo;
    private MoradorRepository $moradorRepo;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->usuarioRepo = new UsuarioRepository($conexao);
        $this->moradorRepo = new MoradorRepository($conexao);
    }

    public function listar(): void
    {
        $condominios  = $this->usuarioRepo->listarMoradoresComUnidade();
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/condominios/lista.php';
    }

    public function formulario(): void
    {
        $usuario         = !empty($_GET['id']) ? $this->usuarioRepo->buscarPorId((int) $_GET['id']) : null;
        $retornarUnidade = (int) ($_GET['retornar_unidade'] ?? 0);
        $mensagem        = Sessao::lerFlash('sucesso');
        $erroMensagem    = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/condominios/formulario.php';
    }

    public function salvar(): void
    {
        $id              = !empty($_POST['id']) ? (int) $_POST['id'] : null;
        $retornarUnidade = (int) ($_POST['retornar_unidade'] ?? 0);
        $nome            = trim($_POST['nome'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $senha           = $_POST['senha'] ?? '';

        $urlErro = $id ? "/condominios/{$id}/editar" : '/condominios/novo';
        if ($retornarUnidade) {
            $urlErro .= ($id ? '' : '') . '?retornar_unidade=' . $retornarUnidade;
        }

        if (empty($nome)) {
            Sessao::flash('erro', 'Nome é obrigatório.');
            Roteador::redirecionar($urlErro);
            return;
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Sessao::flash('erro', 'E-mail inválido.');
            Roteador::redirecionar($urlErro);
            return;
        }

        if ($id === null) {
            if (empty($senha) || strlen($senha) < 6) {
                Sessao::flash('erro', 'Senha deve ter pelo menos 6 caracteres.');
                Roteador::redirecionar($urlErro);
                return;
            }
            if ($this->usuarioRepo->buscarPorEmail($email) !== null) {
                Sessao::flash('erro', 'E-mail já cadastrado. Use "Buscar" na tela da unidade para vinculá-lo.');
                Roteador::redirecionar($urlErro);
                return;
            }
            $usuario = new Usuario(
                id:     null,
                nome:   $nome,
                email:  $email,
                senha:  password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]),
                perfil: 'morador',
                ativo:  true,
            );
        } else {
            $usuario = $this->usuarioRepo->buscarPorId($id);
            if ($usuario === null) {
                Sessao::flash('erro', 'Condômino não encontrado.');
                Roteador::redirecionar('/condominios');
                return;
            }
            $usuario->nome  = $nome;
            $usuario->email = $email;
            $usuario->ativo = !empty($_POST['ativo']);
            if (!empty($senha) && strlen($senha) >= 6) {
                $usuario->senha = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
            }
        }

        $this->usuarioRepo->salvar($usuario);

        Sessao::flash('sucesso', $id ? 'Condômino atualizado.' : 'Condômino cadastrado.');

        if ($retornarUnidade > 0) {
            Roteador::redirecionar("/unidades/{$retornarUnidade}");
        } else {
            Roteador::redirecionar('/condominios');
        }
    }

    public function excluir(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->usuarioRepo->desativar($id);
            Sessao::flash('sucesso', 'Condômino removido.');
        }
        Roteador::redirecionar('/condominios');
    }
}
