<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/UnidadeService.php';
require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';

class UnidadeController
{
    private UnidadeService $unidadeService;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->unidadeService = new UnidadeService(
            new UnidadeRepository($conexao),
            new MoradorRepository($conexao),
            new UsuarioRepository($conexao),
        );
    }

    public function listar(): void
    {
        $unidades     = $this->unidadeService->listarUnidades();
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/unidades/lista.php';
    }

    public function formulario(): void
    {
        $unidade = null;
        if (!empty($_GET['id'])) {
            $unidade = $this->unidadeService->buscarUnidade((int) $_GET['id']);
        }

        $todosCondominios = $this->unidadeService->listarCondominios();

        $condominosSelecionados = [];
        if ($unidade !== null) {
            $vinculos = $this->unidadeService->listarMoradoresDaUnidade($unidade->id);
            $condominosSelecionados = array_map(fn($m) => $m->usuarioId, $vinculos);
        }

        require_once RAIZ . '/views/admin/unidades/formulario.php';
    }

    public function salvar(): void
    {
        try {
            $id = $this->unidadeService->salvarUnidade($_POST);

            // Sincronizar condôminos selecionados no formulário
            $condominoIds = array_map('intval', $_POST['condominos'] ?? []);
            $this->unidadeService->sincronizarMoradoresDaUnidade($id, $condominoIds);

            Sessao::flash('sucesso', 'Unidade salva com sucesso.');
            Roteador::redirecionar("/unidades/{$id}");
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
            Roteador::redirecionar('/unidades/nova');
        }
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

        $busca           = trim($_GET['buscar'] ?? '');
        $resultadosBusca = $busca !== '' ? $this->unidadeService->pesquisarCondominios($busca) : null;

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

        Roteador::redirecionar("/unidades/{$unidadeId}");
    }

    public function vincularExistente(): void
    {
        $unidadeId   = (int) ($_POST['unidade_id'] ?? 0);
        $usuarioId   = (int) ($_POST['usuario_id'] ?? 0);
        $dataEntrada = $_POST['data_entrada'] ?? date('Y-m-d');
        $responsavel = !empty($_POST['responsavel']);

        try {
            $this->unidadeService->vincularPorUsuarioId($unidadeId, $usuarioId, $dataEntrada, $responsavel);
            Sessao::flash('sucesso', 'Morador vinculado com sucesso.');
        } catch (Exception $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar("/unidades/{$unidadeId}");
    }

    public function desvincularMorador(): void
    {
        $moradorId = (int) ($_GET['morador_id'] ?? 0);
        $unidadeId = (int) ($_GET['unidade_id'] ?? 0);

        $this->unidadeService->desvincularMorador($moradorId);
        Sessao::flash('sucesso', 'Morador desvinculado.');
        Roteador::redirecionar("/unidades/{$unidadeId}");
    }
}
