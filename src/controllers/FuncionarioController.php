<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/FuncionarioRepository.php';

class FuncionarioController
{
    private FuncionarioRepository $repo;
    private bool $ehAdmin;

    public function __construct()
    {
        $this->repo    = new FuncionarioRepository(Conexao::obter());
        $this->ehAdmin = in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true);
    }

    public function listar(): void
    {
        $this->exigirAdmin();
        $funcionarios  = $this->repo->listarTodos();
        $mensagem      = Sessao::lerFlash('sucesso');
        $erroMensagem  = Sessao::lerFlash('erro');
        require RAIZ . '/views/admin/funcionarios/lista.php';
    }

    public function formulario(): void
    {
        $this->exigirAdmin();
        $id          = (int) ($_GET['id'] ?? 0);
        $funcionario = $id > 0 ? $this->repo->buscarPorId($id) : null;
        require RAIZ . '/views/admin/funcionarios/formulario.php';
    }

    public function salvar(): void
    {
        $this->exigirAdmin();

        $id = (int) ($_POST['id'] ?? 0);

        $salarioRaw = trim(str_replace(['.', ','], ['', '.'], $_POST['salario'] ?? ''));

        $funcionario = new Funcionario(
            id:           $id > 0 ? $id : null,
            nome:         trim($_POST['nome']         ?? ''),
            cargo:        trim($_POST['cargo']        ?? ''),
            cpf:          trim($_POST['cpf']          ?? '') ?: null,
            departamento: trim($_POST['departamento'] ?? '') ?: null,
            telefone:     trim($_POST['telefone']     ?? '') ?: null,
            email:        trim($_POST['email']        ?? '') ?: null,
            salario:      $salarioRaw !== '' ? (float) $salarioRaw : null,
            dataAdmissao: trim($_POST['data_admissao'] ?? '') ?: null,
            dataDemissao: trim($_POST['data_demissao'] ?? '') ?: null,
            observacoes:  trim($_POST['observacoes']   ?? '') ?: null,
            ativo:        ($_POST['ativo'] ?? '0') === '1',
        );

        if ($funcionario->nome === '' || $funcionario->cargo === '') {
            Sessao::flash('erro', 'Nome e cargo são obrigatórios.');
            $redir = $id > 0 ? "/funcionarios/{$id}/editar" : '/funcionarios/novo';
            Roteador::redirecionar($redir);
        }

        $salvoId = $this->repo->salvar($funcionario);
        Sessao::flash('sucesso', 'Funcionário salvo com sucesso.');
        Roteador::redirecionar("/funcionarios/{$salvoId}/editar");
    }

    public function excluir(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->repo->excluir($id);
            Sessao::flash('sucesso', 'Funcionário removido.');
        } catch (PDOException $e) {
            Sessao::flash('erro', 'Não foi possível remover o funcionário.');
        }

        Roteador::redirecionar('/funcionarios');
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
