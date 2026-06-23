<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/TaxaExtraRepository.php';
require_once __DIR__ . '/../repository/ProjetoRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/PrestadoraRepository.php';
require_once __DIR__ . '/../repository/VistoriaRepository.php';
require_once __DIR__ . '/../repository/FuncionarioRepository.php';
require_once __DIR__ . '/../repository/ConfigRepository.php';
require_once __DIR__ . '/../services/TaxaCondominialService.php';

class PainelAdminController
{
    private UnidadeRepository         $unidadeRepo;
    private TaxaCondominialRepository $taxaRepo;
    private TaxaExtraRepository       $extraRepo;
    private ProjetoRepository         $projetoRepo;
    private MoradorRepository         $moradorRepo;
    private PrestadoraRepository      $prestadoraRepo;
    private VistoriaRepository         $vistoriaRepo;
    private FuncionarioRepository      $funcionarioRepo;

    public function __construct()
    {
        $pdo = Conexao::obter();
        $this->unidadeRepo     = new UnidadeRepository($pdo);
        $this->taxaRepo        = new TaxaCondominialRepository($pdo);
        $this->extraRepo       = new TaxaExtraRepository($pdo);
        $this->projetoRepo     = new ProjetoRepository($pdo);
        $this->moradorRepo     = new MoradorRepository($pdo);
        $this->prestadoraRepo  = new PrestadoraRepository($pdo);
        $this->vistoriaRepo    = new VistoriaRepository($pdo);
        $this->funcionarioRepo = new FuncionarioRepository($pdo);
    }

    public function exibirPainel(): void
    {
        $this->pseudoCronGerarTaxas();

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
        $totalUnidades     = count($this->unidadeRepo->listarAtivas());
        $totalMoradores    = $this->moradorRepo->contarAtivos();
        $totalPrestadoras  = count($this->prestadoraRepo->listarAtivas());
        $totalFuncionarios = $this->funcionarioRepo->contarAtivos();

        require_once RAIZ . '/views/admin/painel.php';
    }

    /**
     * Gera automaticamente as taxas do mês atual na primeira visita admin do mês.
     * Usa o valor e dia de vencimento salvos nas configurações.
     * Falha silenciosa — o admin pode gerar manualmente se necessário.
     */
    private function pseudoCronGerarTaxas(): void
    {
        $competencia = date('Y-m');
        $pdo         = Conexao::obter();
        $configRepo  = new ConfigRepository($pdo);

        if ($configRepo->obter('taxa_ultima_geracao_auto') === $competencia) {
            return;
        }

        $dia   = (int)   $configRepo->obter('taxa_dia_vencimento', 10);
        $valor = (float) $configRepo->obter('taxa_valor_mensal',   0);

        if ($valor <= 0) {
            return;
        }

        try {
            $vencimento  = $competencia . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT);
            $taxaService = new TaxaCondominialService(
                new TaxaCondominialRepository($pdo),
                new UnidadeRepository($pdo),
            );
            $taxaService->gerarEmLote($competencia, $valor, $vencimento);
            $configRepo->salvar('taxa_ultima_geracao_auto', $competencia);
        } catch (\Throwable) {
            // Falha silenciosa
        }
    }
}
