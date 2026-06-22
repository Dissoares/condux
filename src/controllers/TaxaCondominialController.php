<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/TaxaCondominialService.php';
require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';

class TaxaCondominialController
{
    private TaxaCondominialService $taxaService;
    private MoradorRepository      $moradorRepository;
    private string                 $urlBase;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->taxaService       = new TaxaCondominialService(
            new TaxaCondominialRepository($conexao),
            new UnidadeRepository($conexao),
        );
        $this->moradorRepository = new MoradorRepository($conexao);
        $app                     = require RAIZ . '/config/app.php';
        $this->urlBase           = $app['url_base'];
    }

    /** Listagem para admin — filtra por competência via GET */
    public function listar(): void
    {
        $competencia = $_GET['competencia'] ?? date('Y-m');
        $taxas       = $this->taxaService->listarPorCompetencia($competencia);
        $resumo      = $this->taxaService->resumoMesAtual();
        $mensagem    = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/taxas/lista.php';
    }

    /** Exibe formulário para geração em lote */
    public function formularioGerarLote(): void
    {
        require_once RAIZ . '/views/admin/taxas/gerar-lote.php';
    }

    public function gerarEmLote(): void
    {
        try {
            $total = $this->taxaService->gerarEmLote(
                $_POST['competencia'],
                (float) str_replace(',', '.', $_POST['valor']),
                $_POST['vencimento'],
            );
            Sessao::flash('sucesso', "{$total} taxas geradas com sucesso.");
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }
        header("Location: {$this->urlBase}/index.php?pagina=taxas");
        exit;
    }

    /** Aprovação de comprovante pelo síndico */
    public function aprovarComprovante(): void
    {
        $taxaId = (int) ($_GET['id'] ?? 0);

        try {
            $this->taxaService->aprovarComprovante($taxaId);
            Sessao::flash('sucesso', 'Pagamento aprovado com sucesso.');
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        $competencia = $_GET['competencia'] ?? date('Y-m');
        header("Location: {$this->urlBase}/index.php?pagina=taxas&competencia={$competencia}");
        exit;
    }

    /** Listagem para o morador — apenas sua unidade */
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

    /** Morador envia comprovante de pagamento */
    public function enviarComprovante(): void
    {
        $taxaId = (int) ($_POST['taxa_id'] ?? 0);

        if (empty($_FILES['comprovante']) || $_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
            Sessao::flash('erro', 'Selecione um arquivo válido.');
            header("Location: {$this->urlBase}/index.php?pagina=minhas-taxas");
            exit;
        }

        try {
            $this->taxaService->enviarComprovante($taxaId, $_FILES['comprovante']);
            Sessao::flash('sucesso', 'Comprovante enviado! Aguarde a aprovação do síndico.');
        } catch (InvalidArgumentException|RuntimeException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        header("Location: {$this->urlBase}/index.php?pagina=minhas-taxas");
        exit;
    }

    private function obterUnidadeDoMoradorLogado(): ?int
    {
        $usuarioId = Sessao::obter('usuario_id');
        return $this->moradorRepository->buscarUnidadeDoUsuario($usuarioId);
    }
}
