<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/UnidadeService.php';
require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';

class UnidadeController
{
    private UnidadeService $unidadeService;
    private string         $urlBase;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->unidadeService = new UnidadeService(
            new UnidadeRepository($conexao),
            new MoradorRepository($conexao),
            new UsuarioRepository($conexao),
        );
        $app           = require RAIZ . '/config/app.php';
        $this->urlBase = $app['url_base'];
    }

    public function listar(): void
    {
        $unidades  = $this->unidadeService->listarUnidades();
        $mensagem  = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/unidades/lista.php';
    }

    public function formulario(): void
    {
        $unidade = null;
        if (!empty($_GET['id'])) {
            $unidade = $this->unidadeService->buscarUnidade((int) $_GET['id']);
        }
        require_once RAIZ . '/views/admin/unidades/formulario.php';
    }

    public function salvar(): void
    {
        try {
            $id = $this->unidadeService->salvarUnidade($_POST);
            Sessao::flash('sucesso', 'Unidade salva com sucesso.');
            header("Location: {$this->urlBase}/index.php?pagina=unidades&acao=ver&id={$id}");
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
            header("Location: {$this->urlBase}/index.php?pagina=unidades&acao=formulario");
        }
        exit;
    }

    public function ver(): void
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $unidade = $this->unidadeService->buscarUnidade($id);

        if ($unidade === null) {
            http_response_code(404);
            echo 'Unidade não encontrada.';
            return;
        }

        $moradores    = $this->unidadeService->listarMoradoresDaUnidade($id);
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/unidades/detalhe.php';
    }

    public function vincularMorador(): void
    {
        $unidadeId = (int) ($_POST['unidade_id'] ?? 0);

        try {
            $this->unidadeService->vincularMorador($unidadeId, $_POST);
            Sessao::flash('sucesso', 'Morador vinculado com sucesso.');
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        header("Location: {$this->urlBase}/index.php?pagina=unidades&acao=ver&id={$unidadeId}");
        exit;
    }

    public function desvincularMorador(): void
    {
        $moradorId = (int) ($_GET['morador_id'] ?? 0);
        $unidadeId = (int) ($_GET['unidade_id'] ?? 0);

        $this->unidadeService->desvincularMorador($moradorId);
        Sessao::flash('sucesso', 'Morador desvinculado.');
        header("Location: {$this->urlBase}/index.php?pagina=unidades&acao=ver&id={$unidadeId}");
        exit;
    }
}
