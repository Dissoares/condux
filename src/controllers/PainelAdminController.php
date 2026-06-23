<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/TaxaExtraRepository.php';
require_once __DIR__ . '/../repository/ProjetoRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/PrestadoraRepository.php';
require_once __DIR__ . '/../repository/VistoriaRepository.php';

class PainelAdminController
{
    private UnidadeRepository         $unidadeRepo;
    private TaxaCondominialRepository $taxaRepo;
    private TaxaExtraRepository       $extraRepo;
    private ProjetoRepository         $projetoRepo;
    private MoradorRepository         $moradorRepo;
    private PrestadoraRepository      $prestadoraRepo;
    private VistoriaRepository        $vistoriaRepo;

    public function __construct()
    {
        $pdo = Conexao::obter();
        $this->unidadeRepo    = new UnidadeRepository($pdo);
        $this->taxaRepo       = new TaxaCondominialRepository($pdo);
        $this->extraRepo      = new TaxaExtraRepository($pdo);
        $this->projetoRepo    = new ProjetoRepository($pdo);
        $this->moradorRepo    = new MoradorRepository($pdo);
        $this->prestadoraRepo = new PrestadoraRepository($pdo);
        $this->vistoriaRepo   = new VistoriaRepository($pdo);
    }

    public function exibirPainel(): void
    {
        // Financeiro do mês
        $resumoMes     = $this->taxaRepo->resumoMesDetalhado();
        $totaisGlobais = $this->taxaRepo->totaisGlobais();

        // Taxas extras
        $resumoExtras   = $this->extraRepo->resumoGlobal();
        $extrasRecentes = $this->extraRepo->listarGruposComResumo(5);

        // Projetos
        $projetosRecentes    = array_slice($this->projetoRepo->listarTodos(), 0, 5);
        $totalProjetosAtivos = $this->projetoRepo->contarPorStatus('em_andamento');

        // Vistorias agendadas + realizadas com validade próxima
        $vistoriasAVencer = $this->vistoriaRepo->listarParaPainel();

        // Comprovantes aguardando aprovação do síndico
        $qtdAguardando = $this->taxaRepo->contarAguardandoAprovacao();

        // Contadores
        $totalUnidades    = count($this->unidadeRepo->listarAtivas());
        $totalMoradores   = $this->moradorRepo->contarAtivos();
        $totalPrestadoras = count($this->prestadoraRepo->listarAtivas());

        require_once RAIZ . '/views/admin/painel.php';
    }
}
