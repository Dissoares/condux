<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/PrestadoraRepository.php';
require_once RAIZ . '/src/repository/ProjetoRepository.php';
require_once RAIZ . '/src/repository/VistoriaRepository.php';
require_once RAIZ . '/src/Sessao.php';

class PrestadoraController
{
    private PrestadoraRepository $repo;
    private ProjetoRepository    $projetoRepo;
    private VistoriaRepository   $vistoriaRepo;
    private bool $ehAdmin;

    public function __construct()
    {
        $db = Conexao::obter();
        $this->repo         = new PrestadoraRepository($db);
        $this->projetoRepo  = new ProjetoRepository($db);
        $this->vistoriaRepo = new VistoriaRepository($db);
        $this->ehAdmin      = in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true);
    }

    public function listar(): void
    {
        $prestadoras = $this->repo->listarTodas();
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/admin/prestadoras/lista.php';
    }

    public function ver(): void
    {
        $id         = (int) ($_GET['id'] ?? 0);
        $prestadora = $this->repo->buscarPorId($id);

        if ($prestadora === null) {
            http_response_code(404);
            echo 'Prestadora não encontrada.';
            return;
        }

        $projetos     = $this->projetoRepo->listarPorPrestadora($id);
        $vistorias    = $this->vistoriaRepo->listarPorPrestadora($id);
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');

        require RAIZ . '/views/admin/prestadoras/detalhe.php';
    }

    public function formulario(): void
    {
        $this->exigirAdmin();

        $id         = (int) ($_GET['id'] ?? 0);
        $prestadora = $id > 0 ? $this->repo->buscarPorId($id) : null;

        require RAIZ . '/views/admin/prestadoras/formulario.php';
    }

    public function salvar(): void
    {
        $this->exigirAdmin();

        $id = (int) ($_POST['id'] ?? 0);

        $prestadora = new Prestadora(
            id:       $id > 0 ? $id : null,
            nome:     trim($_POST['nome'] ?? ''),
            cnpj:     trim($_POST['cnpj'] ?? '') ?: null,
            contato:  trim($_POST['contato'] ?? '') ?: null,
            telefone: trim($_POST['telefone'] ?? '') ?: null,
            email:    trim($_POST['email'] ?? '') ?: null,
            ativo:    ($_POST['ativo'] ?? '0') === '1',
        );

        if ($prestadora->nome === '') {
            Sessao::flash('erro', 'O nome da empresa é obrigatório.');
            Roteador::redirecionar($id > 0 ? "/prestadoras/{$id}/editar" : '/prestadoras/nova');
        }

        try {
            $salvoId = $this->repo->salvar($prestadora);
            Sessao::flash('sucesso', 'Empresa salva com sucesso.');
            Roteador::redirecionar("/prestadoras/{$salvoId}");
        } catch (PDOException $e) {
            Sessao::flash('erro', 'Erro ao salvar: ' . $e->getMessage());
            Roteador::redirecionar('/prestadoras');
        }
    }

    public function excluir(): void
    {
        $this->exigirAdmin();

        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->repo->excluir($id);
            Sessao::flash('sucesso', 'Empresa removida.');
        } catch (PDOException $e) {
            Sessao::flash('erro', 'Não foi possível remover: empresa está vinculada a projetos.');
        }

        Roteador::redirecionar('/prestadoras');
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
