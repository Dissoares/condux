<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/RelatorioRepository.php';

class RelatorioController
{
    private RelatorioRepository $repositorio;

    public function __construct()
    {
        $this->repositorio = new RelatorioRepository(Conexao::obter());
    }

    public function exibir(): void
    {
        $perfil = Sessao::perfilAtual();
        $ano    = (int) ($_GET['ano'] ?? date('Y'));
        $anos   = $this->repositorio->anosDisponiveis();

        if (!in_array($ano, $anos, true) && !empty($anos)) {
            $ano = (int) $anos[0];
        }

        if ($perfil === 'morador') {
            $this->exibirParaMorador($ano, $anos);
            return;
        }

        $this->exibirParaAdmin($ano, $anos);
    }

    private function exibirParaAdmin(int $ano, array $anos): void
    {
        $mensalidade       = $this->repositorio->arrecadacaoMensalPorAno($ano);
        $competenciaAtual  = date('Y-m');
        $inadimplentes     = $this->repositorio->inadimplentesNaCompetencia($competenciaAtual);

        require_once RAIZ . '/views/admin/relatorios/painel.php';
    }

    private function exibirParaMorador(int $ano, array $anos): void
    {
        $usuario   = Sessao::usuarioAtual();
        $unidadeId = $usuario['unidade_id'] ?? null;

        $extrato = $unidadeId
            ? $this->repositorio->extratoPorUnidade((int) $unidadeId, $ano)
            : [];

        require_once RAIZ . '/views/morador/relatorios.php';
    }
}
