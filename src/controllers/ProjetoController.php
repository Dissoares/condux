<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/ProjetoService.php';
require_once __DIR__ . '/../repository/ProjetoRepository.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';

class ProjetoController
{
    private ProjetoService    $projetoService;
    private UsuarioRepository $usuarioRepository;
    private bool              $ehAdmin;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->projetoService    = new ProjetoService(new ProjetoRepository($conexao));
        $this->usuarioRepository = new UsuarioRepository($conexao);
        $this->ehAdmin           = in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true);
    }

    public function listar(): void
    {
        $statusFiltro = $_GET['status'] ?? null;
        $projetos     = $this->projetoService->listarTodos($statusFiltro);
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        $ehAdmin      = $this->ehAdmin;

        $view = $this->ehAdmin
            ? RAIZ . '/views/admin/projetos/lista.php'
            : RAIZ . '/views/transparencia/lista.php';

        require_once $view;
    }

    public function ver(): void
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $projeto = $this->projetoService->buscarProjeto($id);

        if ($projeto === null) {
            http_response_code(404);
            echo 'Projeto não encontrado.';
            return;
        }

        $ehAdmin = $this->ehAdmin;
        $view    = $this->ehAdmin
            ? RAIZ . '/views/admin/projetos/detalhe.php'
            : RAIZ . '/views/transparencia/detalhe.php';

        require_once $view;
    }

    public function formulario(): void
    {
        $this->exigirAdmin();

        $projeto      = null;
        $responsaveis = array_merge(
            $this->usuarioRepository->listarPorPerfil('sindico'),
            $this->usuarioRepository->listarPorPerfil('subsindico'),
        );

        if (!empty($_GET['id'])) {
            $projeto = $this->projetoService->buscarProjeto((int) $_GET['id']);
        }

        require_once RAIZ . '/views/admin/projetos/formulario.php';
    }

    public function salvar(): void
    {
        $this->exigirAdmin();

        try {
            $id = $this->projetoService->salvarProjeto($_POST);
            Sessao::flash('sucesso', 'Projeto salvo com sucesso.');
            Roteador::redirecionar("/projetos/{$id}");
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
            Roteador::redirecionar('/projetos/novo');
        }
    }

    public function atualizarStatus(): void
    {
        $this->exigirAdmin();

        $projetoId  = (int) ($_POST['projeto_id'] ?? 0);
        $novoStatus = $_POST['status'] ?? '';

        try {
            $this->projetoService->atualizarStatus($projetoId, $novoStatus);
            Sessao::flash('sucesso', 'Status atualizado.');
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar("/projetos/{$projetoId}");
    }

    public function adicionarAnexo(): void
    {
        $this->exigirAdmin();

        $projetoId = (int) ($_POST['projeto_id'] ?? 0);
        $tipo      = $_POST['tipo'] ?? '';

        if (empty($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            Sessao::flash('erro', 'Selecione um arquivo válido.');
            Roteador::redirecionar("/projetos/{$projetoId}");
        }

        try {
            $this->projetoService->adicionarAnexo($projetoId, $tipo, $_FILES['arquivo']);
            Sessao::flash('sucesso', 'Anexo adicionado com sucesso.');
        } catch (InvalidArgumentException|RuntimeException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar("/projetos/{$projetoId}");
    }

    public function removerAnexo(): void
    {
        $this->exigirAdmin();

        $anexoId   = (int) ($_GET['anexo_id']  ?? 0);
        $projetoId = (int) ($_GET['projeto_id'] ?? 0);

        $this->projetoService->removerAnexo($anexoId);
        Sessao::flash('sucesso', 'Anexo removido.');
        Roteador::redirecionar("/projetos/{$projetoId}");
    }

    private function exigirAdmin(): void
    {
        if (!$this->ehAdmin) {
            http_response_code(403);
            echo 'Acesso não autorizado.';
            exit;
        }
    }
}
