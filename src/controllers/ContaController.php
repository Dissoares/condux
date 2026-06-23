<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/ContaRepository.php';

class ContaController
{
    private ContaRepository $repo;

    public function __construct()
    {
        $this->repo = new ContaRepository(Conexao::obter());
        if (!in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true)) {
            http_response_code(403);
            echo 'Acesso não autorizado.';
            exit;
        }
    }

    public function listar(): void
    {
        $compFiltro  = trim($_GET['comp'] ?? '') ?: date('Y-m');
        $contas      = $this->repo->listarPorCompetencia($compFiltro);
        $resumos     = $this->repo->resumoPorCompetencia();
        $totais      = $this->repo->totalPorCompetencia($compFiltro);
        $mensagem    = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/admin/contas/lista.php';
    }

    public function salvar(): void
    {
        $id         = (int) ($_POST['id'] ?? 0);
        $valorRaw   = trim(str_replace(['.', ','], ['', '.'], $_POST['valor'] ?? ''));
        $dataPago   = trim($_POST['data_pagamento'] ?? '') ?: null;

        $conta = new Conta(
            id:              $id > 0 ? $id : null,
            descricao:       trim($_POST['descricao']       ?? ''),
            categoria:       trim($_POST['categoria']       ?? 'outros'),
            competencia:     trim($_POST['competencia']     ?? date('Y-m')),
            fornecedor:      trim($_POST['fornecedor']      ?? '') ?: null,
            valor:           $valorRaw !== '' ? (float) $valorRaw : 0.0,
            dataVencimento:  trim($_POST['data_vencimento'] ?? '') ?: null,
            dataPagamento:   $dataPago,
            status:          $dataPago ? 'pago' : 'pendente',
            observacoes:     trim($_POST['observacoes']     ?? '') ?: null,
        );

        if ($conta->descricao === '') {
            Sessao::flash('erro', 'Descrição é obrigatória.');
            Roteador::redirecionar('/contas?comp=' . $conta->competencia);
        }

        // Upload de anexo (apenas em criação)
        if ($id === 0 && !empty($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
            $ext  = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));
            $nome = 'contas/' . date('Y-m') . '/' . uniqid() . '.' . $ext;
            $dest = RAIZ . '/public/uploads/' . $nome;
            @mkdir(dirname($dest), 0755, true);
            if (move_uploaded_file($_FILES['anexo']['tmp_name'], $dest)) {
                $conta = new Conta(
                    id: $conta->id, descricao: $conta->descricao, categoria: $conta->categoria,
                    competencia: $conta->competencia, fornecedor: $conta->fornecedor,
                    valor: $conta->valor, dataVencimento: $conta->dataVencimento,
                    dataPagamento: $conta->dataPagamento, status: $conta->status,
                    observacoes: $conta->observacoes,
                    anexo: $nome, nomeOriginal: $_FILES['anexo']['name'],
                );
            }
        }

        $this->repo->salvar($conta);
        Sessao::flash('sucesso', $id > 0 ? 'Conta atualizada.' : 'Conta adicionada.');
        Roteador::redirecionar('/contas?comp=' . $conta->competencia);
    }

    public function marcarPago(): void
    {
        $id   = (int) ($_POST['id']   ?? 0);
        $comp = trim($_POST['comp']   ?? date('Y-m'));
        $data = trim($_POST['data_pagamento'] ?? date('Y-m-d'));
        $this->repo->marcarPago($id, $data);
        Sessao::flash('sucesso', 'Conta marcada como paga.');
        Roteador::redirecionar('/contas?comp=' . $comp);
    }

    public function excluir(): void
    {
        $id   = (int) ($_GET['id']   ?? 0);
        $comp = trim($_GET['comp']   ?? date('Y-m'));
        $caminho = $this->repo->excluir($id);
        if ($caminho) {
            $arquivo = RAIZ . '/public/uploads/' . $caminho;
            if (file_exists($arquivo)) @unlink($arquivo);
        }
        Sessao::flash('sucesso', 'Conta removida.');
        Roteador::redirecionar('/contas?comp=' . $comp);
    }
}
