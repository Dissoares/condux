<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/ProjetoRepository.php';

class PainelAdminController
{
    private UnidadeRepository          $unidadeRepository;
    private TaxaCondominialRepository  $taxaRepository;
    private ProjetoRepository          $projetoRepository;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->unidadeRepository = new UnidadeRepository($conexao);
        $this->taxaRepository    = new TaxaCondominialRepository($conexao);
        $this->projetoRepository = new ProjetoRepository($conexao);
    }

    public function exibirPainel(): void
    {
        $resumoFinanceiro  = $this->taxaRepository->resumoMesAtual();
        $totalInadimplentes = $this->unidadeRepository->contarInadimplentes();
        $projetosRecentes   = $this->projetoRepository->listarTodos();
        $projetosRecentes   = array_slice($projetosRecentes, 0, 5);

        require_once RAIZ . '/views/admin/painel.php';
    }
}
