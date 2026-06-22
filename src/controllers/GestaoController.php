<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/GestaoRepository.php';
require_once RAIZ . '/src/repository/UsuarioRepository.php';

class GestaoController
{
    private GestaoRepository $repo;
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $pdo               = Conexao::obter();
        $this->repo        = new GestaoRepository($pdo);
        $this->usuarioRepo = new UsuarioRepository($pdo);
    }

    public function listar(): void
    {
        $gestoes      = $this->repo->listarTodas();
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/gestoes/lista.php';
    }

    public function formulario(): void
    {
        $gestao   = null;
        $usuarios = $this->usuarioRepo->listarTodosComPapeis();

        if (!empty($_GET['id'])) {
            $gestao = $this->repo->buscarPorId((int) $_GET['id']);
        }

        require_once RAIZ . '/views/admin/gestoes/formulario.php';
    }

    public function salvar(): void
    {
        $id      = (int) ($_POST['id'] ?? 0);
        $membros = $this->extrairMembros($_POST);

        if ($id > 0) {
            $this->repo->atualizar($id, $_POST);
        } else {
            $id = $this->repo->inserir($_POST);
        }

        $this->repo->sincronizarMembros($id, $membros);

        Sessao::flash('sucesso', 'Gestão salva com sucesso.');
        Roteador::redirecionar("gestoes/{$id}");
    }

    public function ver(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $gestao = $this->repo->buscarPorId($id);
        if (!$gestao) { Roteador::redirecionar('gestoes'); return; }

        $projetos     = $this->projetosDaGestao($gestao);
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/gestoes/detalhe.php';
    }

    public function encerrar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->repo->encerrar($id);
        Sessao::flash('sucesso', 'Gestão encerrada.');
        Roteador::redirecionar("gestoes/{$id}");
    }

    public function excluir(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->repo->excluir($id);
        Sessao::flash('sucesso', 'Gestão removida.');
        Roteador::redirecionar('gestoes');
    }

    private function extrairMembros(array $post): array
    {
        $usuarios = $post['membro_usuario'] ?? [];
        $cargos   = $post['membro_cargo']   ?? [];
        $membros  = [];

        foreach ($usuarios as $i => $uid) {
            if (empty($uid)) continue;
            $membros[] = [
                'usuario_id' => (int) $uid,
                'cargo'      => $cargos[$i] ?? 'conselheiro',
            ];
        }

        return $membros;
    }

    private function projetosDaGestao(Gestao $gestao): array
    {
        try {
            $pdo  = Conexao::obter();
            $fim  = $gestao->fim ?? date('Y-m-d');
            $stmt = $pdo->prepare(
                "SELECT id, nome, status, data_inicio, data_conclusao
                 FROM projetos
                 WHERE data_inicio >= :inicio AND data_inicio <= :fim
                 ORDER BY data_inicio DESC"
            );
            $stmt->execute(['inicio' => $gestao->inicio, 'fim' => $fim]);
            return $stmt->fetchAll();
        } catch (\PDOException) {
            return [];
        }
    }
}
