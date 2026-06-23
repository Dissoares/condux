<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/TaxaCondominialService.php';
require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/TaxaExtraRepository.php';
require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/ConfigRepository.php';

class TaxaCondominialController
{
    private TaxaCondominialService $taxaService;
    private TaxaExtraRepository    $extraRepo;
    private UnidadeRepository      $unidadeRepo;
    private MoradorRepository      $moradorRepository;
    private ConfigRepository       $configRepo;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->taxaService       = new TaxaCondominialService(
            new TaxaCondominialRepository($conexao),
            new UnidadeRepository($conexao),
        );
        $this->extraRepo         = new TaxaExtraRepository($conexao);
        $this->unidadeRepo       = new UnidadeRepository($conexao);
        $this->moradorRepository = new MoradorRepository($conexao);
        $this->configRepo        = new ConfigRepository($conexao);
    }

    public function listar(): void
    {
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');

        if (isset($_GET['competencia']) && $_GET['competencia'] !== '') {
            $competencia = $_GET['competencia'];
            $unidades    = $this->taxaService->listarUnidadesComStatusPorCompetencia($competencia);
            $resumo      = $this->taxaService->resumoPorCompetencia($competencia);
            require_once RAIZ . '/views/admin/taxas/por-competencia.php';
        } else {
            $competencias = $this->taxaService->listarCompetencias();
            require_once RAIZ . '/views/admin/taxas/lista.php';
        }
    }

    public function detalheUnidade(): void
    {
        $unidadeId    = (int) ($_GET['id'] ?? 0);
        $competencia  = $_GET['competencia'] ?? date('Y-m');
        $unidade      = $this->unidadeRepo->buscarPorId($unidadeId);
        $taxaCond     = $this->taxaService->buscarPorUnidadeECompetencia($unidadeId, $competencia);
        $extrasDoMes  = $this->extraRepo->listarPorUnidadeECompetencia($unidadeId, $competencia);
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/taxas/detalhe-unidade.php';
    }

    public function formularioGerarLote(): void
    {
        $diaVencimento = (int) $this->configRepo->obter('taxa_dia_vencimento', 10);
        $valorMensal   = $this->configRepo->obter('taxa_valor_mensal');
        $mensagem      = Sessao::lerFlash('sucesso');
        $erroMensagem  = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/taxas/gerar-lote.php';
    }

    public function gerarEmLote(): void
    {
        try {
            $competencia = $_POST['competencia'] ?? '';
            $dia         = max(1, min(31, (int) ($_POST['dia_vencimento'] ?? 10)));
            $valor       = parseDinheiro($_POST['valor'] ?? null) ?? 0.0;
            $vencimento  = $competencia . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT);

            $this->configRepo->salvar('taxa_dia_vencimento', $dia);
            $this->configRepo->salvar('taxa_valor_mensal',   $valor);

            $total = $this->taxaService->gerarEmLote($competencia, $valor, $vencimento);
            Sessao::flash('sucesso', "{$total} taxas geradas/atualizadas com sucesso.");
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }
        Roteador::redirecionar('/taxas/gerar-lote');
    }

    public function marcarPago(): void
    {
        $taxaId        = (int) ($_POST['taxa_id']        ?? 0);
        $unidadeId     = (int) ($_POST['unidade_id']     ?? 0);
        $competencia   = $_POST['competencia']   ?? date('Y-m');
        $formaPagamento = $_POST['forma_pagamento'] ?? '';
        $dataPagamento  = $_POST['data_pagamento']  ?? date('Y-m-d');

        $arquivo = (!empty($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK)
                   ? $_FILES['comprovante']
                   : null;

        try {
            $this->taxaService->marcarPago($taxaId, $formaPagamento, $dataPagamento, $arquivo);
            Sessao::flash('sucesso', 'Pagamento registrado com sucesso.');
        } catch (InvalidArgumentException|RuntimeException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar("/taxas/unidade/{$unidadeId}?competencia={$competencia}");
    }

    public function aprovarComprovante(): void
    {
        $taxaId    = (int) ($_GET['id'] ?? 0);
        $unidadeId = (int) ($_GET['unidade_id'] ?? 0);

        try {
            $this->taxaService->aprovarComprovante($taxaId);
            Sessao::flash('sucesso', 'Pagamento aprovado com sucesso.');
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        $competencia = $_GET['competencia'] ?? date('Y-m');
        if ($unidadeId) {
            Roteador::redirecionar("/taxas/unidade/{$unidadeId}?competencia={$competencia}");
        } else {
            Roteador::redirecionar('/taxas?competencia=' . $competencia);
        }
    }

    public function listarMinhasTaxas(): void
    {
        $unidadeId = $this->obterUnidadeDoMoradorLogado();

        if ($unidadeId === null) {
            echo 'Você não está vinculado a nenhuma unidade.';
            return;
        }

        $taxas        = $this->taxaService->listarPorUnidade($unidadeId);
        $taxaAtual    = $this->taxaService->buscarTaxaMesAtualDaUnidade($unidadeId);
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/morador/minhas-taxas.php';
    }

    public function enviarComprovante(): void
    {
        $taxaId = (int) ($_POST['taxa_id'] ?? 0);

        if (empty($_FILES['comprovante']) || $_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
            Sessao::flash('erro', 'Selecione um arquivo válido.');
            Roteador::redirecionar('/minhas-taxas');
        }

        try {
            $this->taxaService->enviarComprovante($taxaId, $_FILES['comprovante']);
            Sessao::flash('sucesso', 'Comprovante enviado! Aguarde a aprovação do síndico.');
        } catch (InvalidArgumentException|RuntimeException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar('/minhas-taxas');
    }

    private function obterUnidadeDoMoradorLogado(): ?int
    {
        return $this->moradorRepository->buscarUnidadeDoUsuario(Sessao::obter('usuario_id'));
    }
}
