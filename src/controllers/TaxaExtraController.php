<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/TaxaExtraRepository.php';
require_once RAIZ . '/src/repository/ProjetoRepository.php';
require_once RAIZ . '/src/repository/UnidadeRepository.php';
require_once RAIZ . '/src/services/EmailService.php';

class TaxaExtraController
{
    private TaxaExtraRepository $repo;
    private ProjetoRepository   $projetoRepo;
    private UnidadeRepository   $unidadeRepo;

    public function __construct()
    {
        $pdo               = Conexao::obter();
        $this->repo        = new TaxaExtraRepository($pdo);
        $this->projetoRepo = new ProjetoRepository($pdo);
        $this->unidadeRepo = new UnidadeRepository($pdo);
    }

    /** GET /taxas-extra */
    public function listar(): void
    {
        $grupos = $this->repo->listarGrupos();
        require_once RAIZ . '/views/admin/taxas-extra/lista.php';
    }

    /** GET /taxas-extra/nova */
    public function formulario(): void
    {
        $projetos = $this->projetoRepo->listarTodos('em_andamento');
        $mensagem = $_GET['msg'] ?? null;
        require_once RAIZ . '/views/admin/taxas-extra/gerar.php';
    }

    /** POST /taxas-extra/gerar */
    public function gerar(): void
    {
        $projetoId     = (int)   ($_POST['projeto_id']     ?? 0);
        $valorTotal    = (float) str_replace(',', '.', $_POST['valor_total']    ?? '0');
        $valorParcela  = (float) str_replace(',', '.', $_POST['valor_parcela']  ?? '0');
        $totalParcelas = (int)   ($_POST['total_parcelas']  ?? 0);
        $primeiroVenc  = trim($_POST['primeiro_vencimento'] ?? '');
        $descricao     = trim($_POST['descricao'] ?? '');

        if (!$projetoId || $valorParcela <= 0 || $totalParcelas < 1 || !$primeiroVenc) {
            Roteador::redirecionar('taxas-extra/nova?msg=erro');
            return;
        }

        $projeto = $this->projetoRepo->buscarPorId($projetoId);
        if (!$projeto) {
            Roteador::redirecionar('taxas-extra/nova?msg=projeto_invalido');
            return;
        }

        $unidades   = $this->unidadeRepo->listarAtivas();
        $unidadeIds = array_map(fn($u) => $u->id, $unidades);

        if (empty($unidadeIds)) {
            Roteador::redirecionar('taxas-extra/nova?msg=sem_unidades');
            return;
        }

        $vencBase = new DateTimeImmutable($primeiroVenc);

        for ($i = 1; $i <= $totalParcelas; $i++) {
            $venc   = $vencBase->modify('+' . ($i - 1) . ' months')->format('Y-m-d');
            $nome   = $projeto->nome . " — Parcela {$i}/{$totalParcelas}";

            $id = $this->repo->inserir([
                ':nome'           => $nome,
                ':descricao'      => $descricao ?: null,
                ':valor'          => $valorParcela,
                ':vencimento'     => $venc,
                ':projeto_id'     => $projetoId,
                ':parcela'        => $i,
                ':total_parcelas' => $totalParcelas,
                ':valor_total'    => $valorTotal ?: null,
            ]);

            $this->repo->atribuirParaUnidades($id, $unidadeIds);
        }

        // E-mail: avisa todos os moradores responsáveis sobre as parcelas abertas
        $email = new EmailService();
        if ($email->ativo()) {
            require_once RAIZ . '/src/repository/MoradorRepository.php';
            $moradorRepo = new MoradorRepository(Conexao::obter());
            $vencBase2   = new DateTimeImmutable($primeiroVenc);
            foreach ($moradorRepo->emailsResponsaveisPorUnidades($unidadeIds) as $m) {
                for ($i = 1; $i <= $totalParcelas; $i++) {
                    $venc = $vencBase2->modify('+' . ($i - 1) . ' months')->format('Y-m-d');
                    $nome = $projeto->nome . " — Parcela {$i}/{$totalParcelas}";
                    $email->taxaExtraAberta($m['email'], $m['nome'], $nome, $valorParcela, $venc);
                }
            }
        }

        Roteador::redirecionar('taxas-extra?msg=gerado&parcelas=' . $totalParcelas);
    }

    /** GET /taxas-extra/{id} */
    public function ver(): void
    {
        $id        = (int) ($_GET['id'] ?? 0);
        $taxaExtra = $this->repo->buscarPorId($id);

        if (!$taxaExtra) {
            Roteador::redirecionar('taxas-extra');
            return;
        }

        $cobrancas     = $this->repo->listarCobrancasPorTaxa($id);
        $resumo        = $this->repo->resumoCobrancas($id);
        $parcelas      = $taxaExtra->projetoId
            ? $this->repo->listarPorProjeto($taxaExtra->projetoId)
            : [];

        require_once RAIZ . '/views/admin/taxas-extra/detalhe.php';
    }
}
