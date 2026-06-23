<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/FuncionarioRepository.php';
require_once RAIZ . '/src/repository/FuncionarioPagamentoRepository.php';
require_once RAIZ . '/src/repository/FuncionarioOcorrenciaRepository.php';

class FuncionarioController
{
    private FuncionarioRepository           $repo;
    private FuncionarioPagamentoRepository  $pagRepo;
    private FuncionarioOcorrenciaRepository $ocorRepo;
    private bool $ehAdmin;

    public function __construct()
    {
        $pdo            = Conexao::obter();
        $this->repo     = new FuncionarioRepository($pdo);
        $this->pagRepo  = new FuncionarioPagamentoRepository($pdo);
        $this->ocorRepo = new FuncionarioOcorrenciaRepository($pdo);
        $this->ehAdmin  = in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true);
    }

    public function listar(): void
    {
        $this->exigirAdmin();
        $funcionarios = $this->repo->listarTodos();
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/admin/funcionarios/lista.php';
    }

    public function ver(): void
    {
        $this->exigirAdmin();
        $id          = (int) ($_GET['id'] ?? 0);
        $funcionario = $this->repo->buscarPorId($id);

        if (!$funcionario) {
            http_response_code(404);
            echo 'Funcionário não encontrado.';
            return;
        }

        $pagamentos  = $this->pagRepo->listarPorFuncionario($id);
        $ocorrencias = $this->ocorRepo->listarPorFuncionario($id);
        $mensagem    = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');

        require RAIZ . '/views/admin/funcionarios/detalhe.php';
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

        $id         = (int) ($_POST['id'] ?? 0);
        $salarioRaw = trim(str_replace(['.', ','], ['', '.'], $_POST['salario'] ?? ''));
        $diaPag     = (int) ($_POST['dia_pagamento'] ?? 0);

        $funcionario = new Funcionario(
            id:           $id > 0 ? $id : null,
            nome:         trim($_POST['nome']         ?? ''),
            cargo:        trim($_POST['cargo']        ?? ''),
            cpf:          trim($_POST['cpf']          ?? '') ?: null,
            departamento: trim($_POST['departamento'] ?? '') ?: null,
            telefone:     trim($_POST['telefone']     ?? '') ?: null,
            email:        trim($_POST['email']        ?? '') ?: null,
            salario:      $salarioRaw !== '' ? (float) $salarioRaw : null,
            diaPagamento: $diaPag > 0 && $diaPag <= 31 ? $diaPag : null,
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
        Roteador::redirecionar("/funcionarios/{$salvoId}");
    }

    public function registrarPagamento(): void
    {
        $this->exigirAdmin();

        $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
        $competencia   = trim($_POST['competencia'] ?? '');
        $valorRaw      = trim(str_replace(['.', ','], ['', '.'], $_POST['valor'] ?? ''));
        $dataPrev      = trim($_POST['data_prevista'] ?? '') ?: null;
        $dataPago      = trim($_POST['data_pagamento'] ?? '') ?: null;
        $status        = $dataPago ? 'pago' : 'pendente';
        $obs           = trim($_POST['observacoes'] ?? '') ?: null;
        $editId        = (int) ($_POST['id'] ?? 0);

        if (!$competencia || $valorRaw === '') {
            Sessao::flash('erro', 'Competência e valor são obrigatórios.');
            Roteador::redirecionar("/funcionarios/{$funcionarioId}");
        }

        $pag = new FuncionarioPagamento(
            id:            $editId > 0 ? $editId : null,
            funcionarioId: $funcionarioId,
            competencia:   $competencia,
            valor:         (float) $valorRaw,
            dataPrevista:  $dataPrev,
            dataPagamento: $dataPago,
            status:        $status,
            observacoes:   $obs,
        );

        $this->pagRepo->salvar($pag);
        Sessao::flash('sucesso', 'Pagamento registrado.');
        Roteador::redirecionar("/funcionarios/{$funcionarioId}");
    }

    public function excluirPagamento(): void
    {
        $this->exigirAdmin();
        $funcionarioId = (int) ($_GET['funcionario_id'] ?? 0);
        $pagId         = (int) ($_GET['id'] ?? 0);
        $this->pagRepo->excluir($pagId);
        Sessao::flash('sucesso', 'Pagamento removido.');
        Roteador::redirecionar("/funcionarios/{$funcionarioId}");
    }

    public function registrarOcorrencia(): void
    {
        $this->exigirAdmin();

        $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
        $tipo          = $_POST['tipo'] ?? '';
        $dataInicio    = trim($_POST['data_inicio'] ?? '');
        $dataFim       = trim($_POST['data_fim']    ?? '') ?: null;
        $valorRaw      = trim(str_replace(['.', ','], ['', '.'], $_POST['valor'] ?? ''));
        $justificativa = trim($_POST['justificativa'] ?? '') ?: null;
        $status        = $_POST['status'] ?? 'aprovado';

        if (!$tipo || !$dataInicio) {
            Sessao::flash('erro', 'Tipo e data de início são obrigatórios.');
            Roteador::redirecionar("/funcionarios/{$funcionarioId}");
        }

        $anexo       = null;
        $nomeOriginal = null;

        if (!empty($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
            $ext   = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));
            $nome  = 'funcionarios/' . $funcionarioId . '/ocorrencias/' . uniqid() . '.' . $ext;
            $dest  = RAIZ . '/public/uploads/' . $nome;
            @mkdir(dirname($dest), 0755, true);
            if (move_uploaded_file($_FILES['anexo']['tmp_name'], $dest)) {
                $anexo        = $nome;
                $nomeOriginal = $_FILES['anexo']['name'];
            }
        }

        $ocorrencia = new FuncionarioOcorrencia(
            id:            null,
            funcionarioId: $funcionarioId,
            tipo:          $tipo,
            dataInicio:    $dataInicio,
            dataFim:       $dataFim,
            valor:         $valorRaw !== '' ? (float) $valorRaw : null,
            justificativa: $justificativa,
            anexo:         $anexo,
            nomeOriginal:  $nomeOriginal,
            status:        $status,
        );

        $this->ocorRepo->salvar($ocorrencia);
        Sessao::flash('sucesso', 'Ocorrência registrada.');
        Roteador::redirecionar("/funcionarios/{$funcionarioId}");
    }

    public function excluirOcorrencia(): void
    {
        $this->exigirAdmin();
        $funcionarioId = (int) ($_GET['funcionario_id'] ?? 0);
        $ocorId        = (int) ($_GET['id'] ?? 0);

        $caminho = $this->ocorRepo->excluir($ocorId);
        if ($caminho) {
            $arquivo = RAIZ . '/public/uploads/' . $caminho;
            if (file_exists($arquivo)) @unlink($arquivo);
        }

        Sessao::flash('sucesso', 'Ocorrência removida.');
        Roteador::redirecionar("/funcionarios/{$funcionarioId}");
    }

    public function excluir(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->repo->excluir($id);
            Sessao::flash('sucesso', 'Funcionário removido.');
        } catch (PDOException) {
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
