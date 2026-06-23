<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/TaxaCondominialService.php';
require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/TaxaExtraRepository.php';
require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/ConfigRepository.php';
require_once __DIR__ . '/../services/EmailService.php';

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

        if (!empty($_GET['aguardando'])) {
            $aguardando = $this->taxaService->listarAguardandoAprovacao();
            require_once RAIZ . '/views/admin/taxas/aguardando.php';
        } elseif (isset($_GET['competencia']) && $_GET['competencia'] !== '') {
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
        $diaVencimento   = (int) $this->configRepo->obter('taxa_dia_vencimento', 10);
        $valorMensal     = $this->configRepo->obter('taxa_valor_mensal');
        $competencia     = $_GET['competencia'] ?? date('Y-m');
        $mensagem        = Sessao::lerFlash('sucesso');
        $erroMensagem    = Sessao::lerFlash('erro');
        $unidadesComTaxa = $this->taxaService->listarUnidadesComTaxaPorCompetencia($competencia);
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

    /**
     * Geração automática do mês atual usando a configuração salva.
     * Chamado pelo pseudo-cron do painel ou pela URL /taxas/gerar-lote-auto.
     * Retorna o número de taxas geradas (0 se já existirem ou não configurado).
     */
    public function gerarLoteAutomatico(): void
    {
        $dia   = (int)   $this->configRepo->obter('taxa_dia_vencimento', 10);
        $valor = (float) $this->configRepo->obter('taxa_valor_mensal',   0);

        if ($valor <= 0) {
            Sessao::flash('erro', 'Configure o valor da taxa mensal em Taxas → Gerar Lote antes de usar a geração automática.');
            Roteador::redirecionar('/taxas/gerar-lote');
            return;
        }

        $competencia = date('Y-m');
        $vencimento  = $competencia . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT);

        try {
            $total = $this->taxaService->gerarEmLote($competencia, $valor, $vencimento);
            $this->configRepo->salvar('taxa_ultima_geracao_auto', $competencia);
            Sessao::flash('sucesso', "{$total} taxas geradas automaticamente para {$competencia}.");

            // E-mail para cada morador responsável
            if ($total > 0) {
                $email = new EmailService();
                $unidades = $this->unidadeRepo->listarAtivas();
                $ids      = array_map(fn($u) => $u->id, $unidades);
                foreach ($this->moradorRepository->emailsResponsaveisPorUnidades($ids) as $m) {
                    $email->taxaCondominialAberta($m['email'], $m['nome'], $competencia, (float)$valor, $vencimento);
                }
            }
        } catch (InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar('/taxas');
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
            // E-mail de pagamento aprovado para o morador responsável
            $morador = $this->moradorRepository->emailResponsavelDaUnidade($unidadeId);
            if ($morador) {
                $taxa = $this->taxaService->buscarTaxa($taxaId);
                (new EmailService())->pagamentoAprovado(
                    $morador['email'], $morador['nome'],
                    $taxa ? $taxa->competencia : $competencia,
                    $taxa ? $taxa->valor : 0
                );
            }
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
            $taxa = $this->taxaService->buscarTaxa($taxaId);
            $this->taxaService->aprovarComprovante($taxaId);
            Sessao::flash('sucesso', 'Pagamento aprovado com sucesso.');
            // E-mail de aprovação para o morador
            if ($taxa) {
                $morador = $this->moradorRepository->emailResponsavelDaUnidade($taxa->unidadeId);
                if ($morador) {
                    (new EmailService())->pagamentoAprovado(
                        $morador['email'], $morador['nome'], $taxa->competencia, $taxa->valor
                    );
                }
            }
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

    public function enviarCobranca(): void
    {
        $taxaId    = (int) ($_GET['id']        ?? 0);
        $unidadeId = (int) ($_GET['unidade_id'] ?? 0);
        $origem    = $_GET['origem'] ?? 'modal';

        $taxa    = $this->taxaService->buscarTaxa($taxaId);
        $morador = $unidadeId ? $this->moradorRepository->emailResponsavelDaUnidade($unidadeId) : null;

        if (!$taxa || !$morador) {
            Sessao::flash('erro', 'Taxa ou morador não encontrado.');
            Roteador::redirecionar($origem === 'modal' ? '/unidades' : "/taxas/unidade/{$unidadeId}");
            return;
        }

        $diasAtraso = max(0, (int) floor((time() - strtotime($taxa->vencimento)) / 86400));

        $enviouEmail = false;
        $enviouPush  = false;

        try {
            require_once RAIZ . '/src/services/EmailService.php';
            $email = new EmailService();
            $enviouEmail = $email->taxaCondominialVencida(
                $morador['email'],
                $morador['nome'],
                $taxa->competencia,
                $taxa->valor,
                $diasAtraso
            );
        } catch (\Throwable) {}

        try {
            require_once RAIZ . '/src/services/PushNotificationService.php';
            require_once RAIZ . '/src/repository/PushSubscriptionRepository.php';
            $push = new PushNotificationService(new PushSubscriptionRepository(Conexao::obter()));
            if ($push->estaConfigurado() && isset($morador['usuario_id'])) {
                $push->enviarParaUsuario((int) $morador['usuario_id'], [
                    'title' => '⚠️ Taxa em atraso',
                    'body'  => "Taxa de {$taxa->competenciaFormatada()} — R$ " . number_format($taxa->valor, 2, ',', '.') . ' está em atraso há ' . $diasAtraso . ' dia' . ($diasAtraso !== 1 ? 's' : '') . '.',
                    'url'   => url('minhas-taxas'),
                    'tag'   => 'cobranca-' . $taxa->id,
                ]);
                $enviouPush = true;
            }
        } catch (\Throwable) {}

        if ($enviouEmail || $enviouPush) {
            $canais = array_filter(['e-mail' => $enviouEmail, 'push' => $enviouPush]);
            Sessao::flash('sucesso', 'Cobrança enviada via ' . implode(' e ', array_keys($canais)) . '.');
        } else {
            Sessao::flash('erro', 'Não foi possível enviar a cobrança. Verifique as configurações de e-mail e push.');
        }

        if ($origem === 'modal') {
            Roteador::redirecionar('/unidades');
        } else {
            Roteador::redirecionar("/taxas/unidade/{$unidadeId}?competencia={$taxa->competencia}");
        }
    }

    public function listarMinhasTaxas(): void
    {
        $unidadeId = $this->obterUnidadeDoMoradorLogado();

        if ($unidadeId === null) {
            $tituloPagina = 'Minhas Taxas';
            $taxas = [];
            $taxaAtual = null;
            $mensagem = null;
            $erroMensagem = 'Você ainda não está vinculado a nenhuma unidade. Entre em contato com o síndico.';
            require_once RAIZ . '/views/morador/minhas-taxas.php';
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
        $taxaId         = (int) ($_POST['taxa_id']         ?? 0);
        $formaPagamento = trim($_POST['forma_pagamento']    ?? '');

        if (empty($_FILES['comprovante']) || $_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
            Sessao::flash('erro', 'Selecione um arquivo de comprovante válido.');
            Roteador::redirecionar('/minhas-taxas');
            return;
        }

        if (!$formaPagamento) {
            Sessao::flash('erro', 'Selecione a forma de pagamento.');
            Roteador::redirecionar('/minhas-taxas');
            return;
        }

        try {
            $this->taxaService->enviarComprovante($taxaId, $formaPagamento, $_FILES['comprovante']);
            Sessao::flash('sucesso', 'Comprovante enviado! Aguarde a aprovação do síndico.');

            // Push notification para todos os admins
            try {
                require_once RAIZ . '/src/services/PushNotificationService.php';
                require_once RAIZ . '/src/repository/PushSubscriptionRepository.php';
                $taxa = $this->taxaService->buscarTaxa($taxaId);
                $push = new PushNotificationService(new PushSubscriptionRepository(Conexao::obter()));
                $push->enviarParaPerfil('admin', [
                    'title' => '📋 Comprovante para aprovar',
                    'body'  => 'Um condômino enviou comprovante de pagamento' . ($taxa ? ' — ' . $taxa->competenciaFormatada() : '') . '. Toque para revisar.',
                    'url'   => url('taxas?aguardando=1'),
                    'tag'   => 'comprovante-' . $taxaId,
                ]);
            } catch (\Throwable) {}

        } catch (InvalidArgumentException|RuntimeException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar('/minhas-taxas');
    }

    /** Pseudo-cron: envia e-mail de cobrança para taxas vencidas sem aviso. */
    public function avisarVencidas(): void
    {
        $taxaRepo = new TaxaCondominialRepository(Conexao::obter());
        $extraRepo = new TaxaExtraRepository(Conexao::obter());
        $email    = new EmailService();
        $total    = 0;

        foreach ($taxaRepo->listarVencidasSemAviso() as $row) {
            if ($email->taxaCondominialVencida(
                $row['email_morador'], $row['nome_morador'],
                $row['competencia'], (float) $row['valor'], (int) $row['dias_atraso']
            )) {
                $taxaRepo->marcarAvisoVencidaEnviado((int) $row['id']);
                $total++;
            }
        }

        foreach ($extraRepo->listarVencidasSemAviso() as $row) {
            if ($email->taxaExtraVencida(
                $row['email_morador'], $row['nome_morador'],
                $row['nome_parcela'], (float) $row['valor'], (int) $row['dias_atraso']
            )) {
                $extraRepo->marcarAvisoVencidaEnviado((int) $row['id']);
                $total++;
            }
        }

        Sessao::flash('sucesso', "{$total} aviso(s) de vencimento enviado(s) por e-mail.");
        Roteador::redirecionar('/taxas');
    }

    private function obterUnidadeDoMoradorLogado(): ?int
    {
        return $this->moradorRepository->buscarUnidadeDoUsuario(Sessao::obter('usuario_id'));
    }
}
