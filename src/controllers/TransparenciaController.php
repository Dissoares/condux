<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/TaxaCondominialRepository.php';
require_once RAIZ . '/src/repository/ContaRepository.php';
require_once RAIZ . '/src/repository/ProjetoRepository.php';
require_once RAIZ . '/src/repository/FuncionarioPagamentoRepository.php';
require_once RAIZ . '/src/models/Conta.php';

class TransparenciaController
{
    private TaxaCondominialRepository   $taxaRepo;
    private ContaRepository             $contaRepo;
    private ProjetoRepository           $projetoRepo;
    private FuncionarioPagamentoRepository $folhaRepo;

    public function __construct()
    {
        $pdo = Conexao::obter();
        $this->taxaRepo    = new TaxaCondominialRepository($pdo);
        $this->contaRepo   = new ContaRepository($pdo);
        $this->projetoRepo = new ProjetoRepository($pdo);
        $this->folhaRepo   = new FuncionarioPagamentoRepository($pdo);
    }

    public function exibir(): void
    {
        $comp = trim($_GET['comp'] ?? '') ?: date('Y-m');

        $resumoTaxas  = $this->taxaRepo->resumoPorCompetencia($comp);
        $totalContas  = $this->contaRepo->totalPorCompetencia($comp);
        $contas       = $this->contaRepo->listarPorCompetencia($comp);
        $projetos     = $this->projetoRepo->listarTodos('em_andamento');
        $folha        = $this->folhaRepo->listarFolhaConsolidada($comp);

        $arrecadado   = (float) ($resumoTaxas['valor_arrecadado'] ?? 0);
        $totalGastos  = (float) ($totalContas['valor_total'] ?? 0);
        $totalFolha   = array_sum(array_column($folha, 'valor'));
        $saldo        = $arrecadado - $totalGastos - $totalFolha;

        $tituloPagina = 'Transparência';
        require_once RAIZ . '/views/layouts/cabecalho.php';
        require_once RAIZ . '/views/morador/transparencia.php';
        require_once RAIZ . '/views/layouts/rodape.php';
    }
}
