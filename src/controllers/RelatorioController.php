<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/RelatorioRepository.php';

class RelatorioController
{
    private RelatorioRepository $repo;

    public function __construct()
    {
        $this->repo = new RelatorioRepository(Conexao::obter());
    }

    public function exibir(): void
    {
        $perfil = Sessao::perfilAtual();

        if ($perfil === 'morador') {
            $this->exibirMorador();
            return;
        }

        $acao = trim($_GET['acao'] ?? '');
        if ($acao === 'exportar') {
            $this->exportarCsv();
            return;
        }

        $this->exibirAdmin();
    }

    // ── Admin ──────────────────────────────────────────────────────────────

    private function exibirAdmin(): void
    {
        $anos     = $this->repo->anosDisponiveis();
        $ano      = (int) ($_GET['ano'] ?? ($anos[0] ?? date('Y')));
        $aba      = $_GET['aba'] ?? 'arrecadacao';
        $unidades = $this->repo->listarUnidades();

        $unidadeId  = isset($_GET['unidade']) ? (int) $_GET['unidade'] : null;
        $competencia = trim($_GET['comp'] ?? '') ?: date('Y-m');

        $dados = match ($aba) {
            'balancete'     => $this->repo->balanceteAnual($ano),
            'inadimplencia' => $this->repo->inadimplentesNaCompetencia($competencia),
            'despesas'      => $this->repo->despesasPorAno($ano),
            'folha'         => $this->repo->folhaPorAno($ano),
            'unidade'       => $unidadeId ? $this->repo->extratoPorUnidade($unidadeId, $ano) : [],
            default         => $this->repo->arrecadacaoMensalPorAno($ano),
        };

        $tituloPagina = 'Relatórios';
        require_once RAIZ . '/views/layouts/cabecalho.php';
        require_once RAIZ . '/views/admin/relatorios/painel.php';
        require_once RAIZ . '/views/layouts/rodape.php';
    }

    // ── Exportação CSV ─────────────────────────────────────────────────────

    private function exportarCsv(): void
    {
        $tipo = $_GET['tipo'] ?? 'arrecadacao';
        $ano  = (int) ($_GET['ano'] ?? date('Y'));
        $comp = trim($_GET['comp'] ?? '') ?: date('Y-m');

        [$cabecalho, $linhas, $nomeArquivo] = match ($tipo) {
            'arrecadacao' => $this->csvArrecadacao($ano),
            'balancete'   => $this->csvBalancete($ano),
            'inadimplencia' => $this->csvInadimplencia($comp),
            'despesas'    => $this->csvDespesas($ano),
            'folha'       => $this->csvFolha($ano),
            'unidade'     => $this->csvUnidade((int) ($_GET['unidade'] ?? 0), $ano),
            default       => $this->csvArrecadacao($ano),
        };

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel
        fputcsv($out, $cabecalho, ';');
        foreach ($linhas as $linha) {
            fputcsv($out, $linha, ';');
        }
        fclose($out);
        exit;
    }

    private function csvArrecadacao(int $ano): array
    {
        $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                  'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        $dados = $this->repo->arrecadacaoMensalPorAno($ano);
        $cab   = ['Competência','Unidades','Pagas','Inadimplentes','Cobrado (R$)','Arrecadado (R$)','Adimplência (%)'];
        $linhas = [];
        foreach ($dados as $r) {
            [$a, $m] = explode('-', $r['competencia']);
            $pct = $r['total_cobrado'] > 0 ? round(($r['total_pago'] / $r['total_cobrado']) * 100, 1) : 0;
            $linhas[] = [
                ($meses[(int)$m] ?? $m) . '/' . $a,
                $r['total_unidades'],
                $r['total_pagas'],
                $r['total_inadimplentes'],
                number_format((float)$r['total_cobrado'], 2, ',', '.'),
                number_format((float)$r['total_pago'], 2, ',', '.'),
                $pct . '%',
            ];
        }
        return [$cab, $linhas, "arrecadacao_{$ano}.csv"];
    }

    private function csvBalancete(int $ano): array
    {
        $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                  'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        $dados  = $this->repo->balanceteAnual($ano);
        $cab    = ['Competência','Arrecadado (R$)','Despesas (R$)','Folha (R$)','Saldo (R$)'];
        $linhas = [];
        foreach ($dados as $r) {
            [$a, $m] = explode('-', $r['competencia']);
            $linhas[] = [
                ($meses[(int)$m] ?? $m) . '/' . $a,
                number_format((float)$r['arrecadado'], 2, ',', '.'),
                number_format((float)$r['despesas'],   2, ',', '.'),
                number_format((float)$r['folha'],       2, ',', '.'),
                number_format((float)$r['saldo'],       2, ',', '.'),
            ];
        }
        return [$cab, $linhas, "balancete_{$ano}.csv"];
    }

    private function csvInadimplencia(string $comp): array
    {
        $dados  = $this->repo->inadimplentesNaCompetencia($comp);
        $cab    = ['Unidade','Responsável','Valor (R$)','Vencimento','Dias de Atraso','Status'];
        $linhas = [];
        foreach ($dados as $r) {
            $linhas[] = [
                $r['unidade'],
                $r['responsavel'] ?? '',
                number_format((float)$r['valor'], 2, ',', '.'),
                $r['vencimento'] ? date('d/m/Y', strtotime($r['vencimento'])) : '',
                $r['dias_atraso'] > 0 ? $r['dias_atraso'] : 0,
                ucfirst($r['status']),
            ];
        }
        return [$cab, $linhas, "inadimplencia_{$comp}.csv"];
    }

    private function csvDespesas(int $ano): array
    {
        $dados  = $this->repo->contasDetalhadasPorAno($ano);
        $cab    = ['Competência','Descrição','Categoria','Fornecedor','Valor (R$)','Vencimento','Pagamento','Status'];
        $linhas = [];
        foreach ($dados as $r) {
            $linhas[] = [
                $r['competencia'],
                $r['descricao'],
                $r['categoria'],
                $r['fornecedor'] ?? '',
                number_format((float)$r['valor'], 2, ',', '.'),
                $r['data_vencimento'] ? date('d/m/Y', strtotime($r['data_vencimento'])) : '',
                $r['data_pagamento']  ? date('d/m/Y', strtotime($r['data_pagamento']))  : '',
                ucfirst($r['status']),
            ];
        }
        return [$cab, $linhas, "despesas_{$ano}.csv"];
    }

    private function csvFolha(int $ano): array
    {
        $dados  = $this->repo->folhaDetalhadaPorAno($ano);
        $cab    = ['Competência','Funcionário','Cargo','Salário (R$)','Status','Data Pagamento'];
        $linhas = [];
        foreach ($dados as $r) {
            $linhas[] = [
                $r['competencia'],
                $r['nome'],
                $r['cargo'],
                number_format((float)$r['valor'], 2, ',', '.'),
                ucfirst($r['status']),
                $r['data_pagamento'] ? date('d/m/Y', strtotime($r['data_pagamento'])) : '',
            ];
        }
        return [$cab, $linhas, "folha_{$ano}.csv"];
    }

    private function csvUnidade(int $unidadeId, int $ano): array
    {
        $dados  = $this->repo->extratoPorUnidade($unidadeId, $ano);
        $cab    = ['Competência','Valor (R$)','Vencimento','Pagamento','Status','Observação'];
        $linhas = [];
        foreach ($dados as $r) {
            $linhas[] = [
                $r['competencia'],
                number_format((float)$r['valor'], 2, ',', '.'),
                $r['vencimento']     ? date('d/m/Y', strtotime($r['vencimento']))     : '',
                $r['data_pagamento'] ? date('d/m/Y', strtotime($r['data_pagamento'])) : '',
                ucfirst($r['status']),
                $r['observacao'] ?? '',
            ];
        }
        return [$cab, $linhas, "extrato_unidade_{$unidadeId}_{$ano}.csv"];
    }

    // ── Morador ────────────────────────────────────────────────────────────

    private function exibirMorador(): void
    {
        $anos      = $this->repo->anosDisponiveis();
        $ano       = (int) ($_GET['ano'] ?? ($anos[0] ?? date('Y')));
        $usuario   = Sessao::usuarioAtual();
        $unidadeId = $usuario['unidade_id'] ?? null;

        if (($_GET['acao'] ?? '') === 'exportar' && $unidadeId) {
            [$cab, $linhas, $arquivo] = $this->csvUnidade((int) $unidadeId, $ano);
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $arquivo . '"');
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $cab, ';');
            foreach ($linhas as $l) { fputcsv($out, $l, ';'); }
            fclose($out);
            exit;
        }

        $extrato = $unidadeId ? $this->repo->extratoPorUnidade((int) $unidadeId, $ano) : [];

        $tituloPagina = 'Relatórios';
        require_once RAIZ . '/views/layouts/cabecalho.php';
        require_once RAIZ . '/views/morador/relatorios.php';
        require_once RAIZ . '/views/layouts/rodape.php';
    }
}
