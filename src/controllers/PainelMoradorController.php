<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/UnidadeRepository.php';

class PainelMoradorController
{
    private MoradorRepository         $moradorRepository;
    private TaxaCondominialRepository $taxaRepository;
    private UnidadeRepository         $unidadeRepository;

    public function __construct()
    {
        $conexao = Conexao::obter();
        $this->moradorRepository = new MoradorRepository($conexao);
        $this->taxaRepository    = new TaxaCondominialRepository($conexao);
        $this->unidadeRepository = new UnidadeRepository($conexao);
    }

    public function exibirPainel(): void
    {
        $usuarioId = Sessao::obter('usuario_id');
        $unidadeId = $this->moradorRepository->buscarUnidadeDoUsuario($usuarioId);

        $unidade        = $unidadeId ? $this->unidadeRepository->buscarPorId($unidadeId) : null;
        $taxaMesAtual   = $unidadeId ? $this->taxaRepository->buscarCompetenciaAtualDaUnidade($unidadeId) : null;
        $taxasPendentes = $unidadeId ? $this->taxaRepository->listarPendentesPorunidade($unidadeId) : [];

        require_once RAIZ . '/views/morador/painel.php';
    }
}
