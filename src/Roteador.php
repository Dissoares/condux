<?php

declare(strict_types=1);

/**
 * Roteador baseado em segmentos de URI.
 * URLs limpas: /login, /unidades, /unidades/5, /projetos/3/editar
 */
class Roteador
{
    public static function despachar(): void
    {
        Sessao::iniciar();

        $metodo = $_SERVER['REQUEST_METHOD'];
        $seg    = self::segmentosUri();   // array indexado de 0 a 4

        // ── Setup (sem autenticação, sem acesso ao banco pelo roteador) ──
        if ($seg[0] === 'setup') {
            require_once RAIZ . '/src/controllers/SetupController.php';
            $ctrl = new SetupController();
            match ($seg[1]) {
                'criar-banco'  => $ctrl->criarBanco(),
                'executar'     => $ctrl->executarMigracoes(),
                'gerar-vapid'  => $ctrl->gerarVapid(),
                default        => $ctrl->exibir(),
            };
            return;
        }

        // ── Rotas públicas ──────────────────────────────────────────────
        if ($seg[0] === 'login') {
            require_once RAIZ . '/src/controllers/AutenticacaoController.php';
            $ctrl = new AutenticacaoController();
            $metodo === 'POST' ? $ctrl->entrar() : $ctrl->exibir();
            return;
        }

        if ($seg[0] === 'sair') {
            require_once RAIZ . '/src/controllers/AutenticacaoController.php';
            (new AutenticacaoController())->sair();
            return;
        }

        // ── Proteção de autenticação ─────────────────────────────────────
        if (!Sessao::estaAutenticado()) {
            self::redirecionar('/login');
            return;
        }

        $ehAdmin = in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true);

        // Raiz → painel
        if ($seg[0] === null) {
            self::carregarPainel($ehAdmin);
            return;
        }

        // ── Roteamento ────────────────────────────────────────────────────
        match ($seg[0]) {
            'painel'       => self::carregarPainel($ehAdmin),
            'unidades'     => self::rotasUnidades($seg, $metodo, $ehAdmin),
            'condominios'  => self::rotasCondominios($seg, $metodo, $ehAdmin),
            'taxas'        => self::rotasTaxas($seg, $metodo, $ehAdmin),
            'taxas-extra'  => self::rotasTaxasExtra($seg, $metodo, $ehAdmin),
            'vistorias'    => self::rotasVistorias($seg, $metodo, $ehAdmin),
            'gestoes'      => self::rotasGestoes($seg, $metodo, $ehAdmin),
            'projetos'     => self::rotasProjetos($seg, $metodo, $ehAdmin),
            'prestadoras'      => self::rotasPrestadoras($seg, $metodo, $ehAdmin),
            'funcionarios'     => self::rotasFuncionarios($seg, $metodo, $ehAdmin),
            'folha-pagamento'  => self::rotasFolhaPagamento($ehAdmin),
            'contas'           => self::rotasContas($seg, $metodo, $ehAdmin),
            'comunicados'      => self::rotasComunicados($seg, $metodo, $ehAdmin),
            'push'             => self::rotasPush($seg, $metodo),
            'configuracoes'    => self::rotasConfiguracoes($seg, $metodo, $ehAdmin),
            'minhas-taxas'     => self::rotasMinhasTaxas($seg, $metodo),
            'transparencia' => self::rotasTransparencia($seg),
            'relatorios'    => self::carregarRelatorio(),
            'perfil'        => self::rotasPerfil($seg, $metodo),
            'tickets'       => self::rotasTickets($seg, $metodo, $ehAdmin),
            default         => self::naoEncontrado(),
        };
    }

    // ── Painel ─────────────────────────────────────────────────────────

    private static function carregarPainel(bool $ehAdmin): void
    {
        if ($ehAdmin) {
            require_once RAIZ . '/src/controllers/PainelAdminController.php';
            (new PainelAdminController())->exibirPainel();
        } else {
            require_once RAIZ . '/src/controllers/PainelMoradorController.php';
            (new PainelMoradorController())->exibirPainel();
        }
    }

    // ── Unidades ────────────────────────────────────────────────────────

    private static function rotasUnidades(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/UnidadeController.php';
        $ctrl = new UnidadeController();

        // POST /unidades/salvar
        if ($seg[1] === 'salvar' && $metodo === 'POST') { $ctrl->salvar(); return; }
        // GET  /unidades/nova
        if ($seg[1] === 'nova')   { $ctrl->formulario(); return; }
        // GET  /unidades
        if ($seg[1] === null)     { $ctrl->listar(); return; }

        $id = (int) $seg[1];

        // POST /unidades/{id}/vincular-morador
        if ($seg[2] === 'vincular-morador' && $metodo === 'POST') {
            $_POST['unidade_id'] = $id;
            $ctrl->vincularMorador();
            return;
        }
        // POST /unidades/{id}/vincular-existente
        if ($seg[2] === 'vincular-existente' && $metodo === 'POST') {
            $_POST['unidade_id'] = $id;
            $ctrl->vincularExistente();
            return;
        }
        // GET  /unidades/{id}/desvincular-morador/{morador_id}
        if ($seg[2] === 'desvincular-morador') {
            $_GET['unidade_id'] = $id;
            $_GET['morador_id'] = (int) ($seg[3] ?? 0);
            $ctrl->desvincularMorador();
            return;
        }
        // GET  /unidades/{id}/editar
        if ($seg[2] === 'editar') { $_GET['id'] = $id; $ctrl->formulario(); return; }
        // GET  /unidades/{id}
        if ($id > 0)              { $_GET['id'] = $id; $ctrl->ver(); return; }

        self::naoEncontrado();
    }

    // ── Condôminos ──────────────────────────────────────────────────────

    private static function rotasCondominios(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/CondominoController.php';
        $ctrl = new CondominoController();

        if ($seg[1] === null)                              { $ctrl->listar(); return; }
        if ($seg[1] === 'novo')                            { $ctrl->formulario(); return; }
        if ($seg[1] === 'salvar' && $metodo === 'POST')    { $ctrl->salvar(); return; }

        $id = (int) $seg[1];
        if ($seg[2] === 'editar')                          { $_GET['id'] = $id; $ctrl->formulario(); return; }
        if ($seg[2] === 'excluir')                         { $_GET['id'] = $id; $ctrl->excluir(); return; }

        self::naoEncontrado();
    }

    // ── Taxas condominiais ───────────────────────────────────────────────

    private static function rotasTaxas(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/TaxaCondominialController.php';
        $ctrl = new TaxaCondominialController();

        // GET|POST /taxas/gerar-lote
        if ($seg[1] === 'gerar-lote') {
            $metodo === 'POST' ? $ctrl->gerarEmLote() : $ctrl->formularioGerarLote();
            return;
        }
        // GET /taxas/gerar-lote-auto — geração automática do mês atual com config salva
        if ($seg[1] === 'gerar-lote-auto') {
            $ctrl->gerarLoteAutomatico();
            return;
        }
        // GET /taxas/unidade/{id}
        if ($seg[1] === 'unidade' && is_numeric($seg[2] ?? '')) {
            $_GET['id'] = (int) $seg[2];
            $ctrl->detalheUnidade();
            return;
        }
        // GET /taxas/{id}/aprovar
        if ($seg[2] === 'aprovar') {
            $_GET['id'] = (int) $seg[1];
            $ctrl->aprovarComprovante();
            return;
        }
        // POST /taxas/marcar-pago
        if ($seg[1] === 'marcar-pago' && $metodo === 'POST') {
            $ctrl->marcarPago();
            return;
        }
        // GET /taxas  |  GET /taxas?competencia=YYYY-MM
        $ctrl->listar();
    }

    // ── Taxas extras ─────────────────────────────────────────────────────

    private static function rotasTaxasExtra(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/TaxaExtraController.php';
        $ctrl = new TaxaExtraController();

        if ($seg[1] === null)                              { $ctrl->listar(); return; }
        if ($seg[1] === 'nova')                            { $ctrl->formulario(); return; }
        if ($seg[1] === 'gerar' && $metodo === 'POST')     { $ctrl->gerar(); return; }

        $id = (int) $seg[1];
        if ($id > 0) { $_GET['id'] = $id; $ctrl->ver(); return; }

        self::naoEncontrado();
    }

    // ── Vistorias ────────────────────────────────────────────────────────

    private static function rotasVistorias(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/VistoriaController.php';
        $ctrl = new VistoriaController();

        if ($seg[1] === null)                              { $ctrl->listar();    return; }
        if ($seg[1] === 'nova')                            { $ctrl->formulario(); return; }
        if ($seg[1] === 'salvar' && $metodo === 'POST')    { $ctrl->salvar();    return; }

        $id = (int) $seg[1];

        if ($seg[2] === 'editar')  { $_GET['id'] = $id; $ctrl->formulario(); return; }
        if ($seg[2] === 'excluir') { $_GET['id'] = $id; $ctrl->excluir();    return; }

        if ($seg[2] === 'anexos' && $metodo === 'POST') {
            $ctrl->adicionarAnexo();
            return;
        }
        if ($seg[2] === 'anexos' && $seg[4] === 'remover') {
            $_GET['vistoria_id'] = $id;
            $_GET['anexo_id']    = (int) $seg[3];
            $ctrl->removerAnexo();
            return;
        }

        if ($id > 0) { $_GET['id'] = $id; $ctrl->ver(); return; }

        self::naoEncontrado();
    }

    // ── Gestões ──────────────────────────────────────────────────────────

    private static function rotasGestoes(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/GestaoController.php';
        $ctrl = new GestaoController();

        if ($seg[1] === null)                              { $ctrl->listar();    return; }
        if ($seg[1] === 'nova')                            { $ctrl->formulario(); return; }
        if ($seg[1] === 'salvar' && $metodo === 'POST')    { $ctrl->salvar();    return; }

        $id = (int) $seg[1];

        if ($seg[2] === 'editar')   { $_GET['id'] = $id; $ctrl->formulario(); return; }
        if ($seg[2] === 'encerrar') { $_GET['id'] = $id; $ctrl->encerrar();   return; }
        if ($seg[2] === 'excluir')  { $_GET['id'] = $id; $ctrl->excluir();    return; }
        if ($id > 0)                { $_GET['id'] = $id; $ctrl->ver();        return; }

        self::naoEncontrado();
    }

    // ── Projetos ─────────────────────────────────────────────────────────

    private static function rotasProjetos(array $seg, string $metodo, bool $ehAdmin): void
    {
        require_once RAIZ . '/src/controllers/ProjetoController.php';
        $ctrl = new ProjetoController();

        if ($seg[1] === null)                                { $ctrl->listar(); return; }
        if ($seg[1] === 'novo')                              { $ctrl->formulario(); return; }
        if ($seg[1] === 'salvar' && $metodo === 'POST')      { $ctrl->salvar(); return; }

        $id = (int) $seg[1];

        if ($seg[2] === 'editar')                            { $_GET['id'] = $id; $ctrl->formulario(); return; }
        if ($seg[2] === 'status' && $metodo === 'POST')      { $_POST['projeto_id'] = $id; $ctrl->atualizarStatus(); return; }
        if ($seg[2] === 'anexos' && $metodo === 'POST')      { $_POST['projeto_id'] = $id; $ctrl->adicionarAnexo(); return; }
        if ($seg[2] === 'anexos' && $seg[4] === 'remover')   {
            $_GET['projeto_id'] = $id;
            $_GET['anexo_id']   = (int) $seg[3];
            $ctrl->removerAnexo();
            return;
        }
        if ($id > 0) { $_GET['id'] = $id; $ctrl->ver(); return; }

        self::naoEncontrado();
    }

    // ── Prestadoras ──────────────────────────────────────────────────────

    private static function rotasPrestadoras(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/PrestadoraController.php';
        $ctrl = new PrestadoraController();

        if ($seg[1] === null)                              { $ctrl->listar();    return; }
        if ($seg[1] === 'nova')                            { $ctrl->formulario(); return; }
        if ($seg[1] === 'salvar' && $metodo === 'POST')    { $ctrl->salvar();    return; }

        $id = (int) $seg[1];
        if ($seg[2] === 'editar')  { $_GET['id'] = $id; $ctrl->formulario(); return; }
        if ($seg[2] === 'excluir') { $_GET['id'] = $id; $ctrl->excluir();    return; }
        self::naoEncontrado();
    }

    // ── Funcionários ─────────────────────────────────────────────────────

    private static function rotasFuncionarios(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }

        require_once RAIZ . '/src/controllers/FuncionarioController.php';
        $ctrl = new FuncionarioController();

        if ($seg[1] === null)                              { $ctrl->listar();    return; }
        if ($seg[1] === 'novo')                            { $ctrl->formulario(); return; }
        if ($seg[1] === 'salvar' && $metodo === 'POST')    { $ctrl->salvar();    return; }

        $id = (int) $seg[1];

        if ($seg[2] === 'editar')  { $_GET['id'] = $id; $ctrl->formulario(); return; }
        if ($seg[2] === 'excluir') { $_GET['id'] = $id; $ctrl->excluir();    return; }

        // POST /funcionarios/{id}/pagamentos
        if ($seg[2] === 'pagamentos' && $metodo === 'POST') {
            $_POST['funcionario_id'] = $id;
            $ctrl->registrarPagamento();
            return;
        }
        // GET /funcionarios/{id}/pagamentos/{pid}/excluir
        if ($seg[2] === 'pagamentos' && $seg[4] === 'excluir') {
            $_GET['funcionario_id'] = $id;
            $_GET['id']             = (int) $seg[3];
            $ctrl->excluirPagamento();
            return;
        }
        // POST /funcionarios/{id}/ocorrencias
        if ($seg[2] === 'ocorrencias' && $metodo === 'POST') {
            $_POST['funcionario_id'] = $id;
            $ctrl->registrarOcorrencia();
            return;
        }
        // GET /funcionarios/{id}/ocorrencias/{oid}/excluir
        if ($seg[2] === 'ocorrencias' && $seg[4] === 'excluir') {
            $_GET['funcionario_id'] = $id;
            $_GET['id']             = (int) $seg[3];
            $ctrl->excluirOcorrencia();
            return;
        }

        if ($id > 0) { $_GET['id'] = $id; $ctrl->ver(); return; }

        self::naoEncontrado();
    }

    // ── Folha de pagamento ───────────────────────────────────────────────

    private static function rotasFolhaPagamento(bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }
        require_once RAIZ . '/src/controllers/FuncionarioController.php';
        (new FuncionarioController())->folhaPagamento();
    }

    // ── Contas ────────────────────────────────────────────────────────────

    private static function rotasContas(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }
        require_once RAIZ . '/src/controllers/ContaController.php';
        $ctrl = new ContaController();

        if ($seg[1] === 'salvar' && $metodo === 'POST') { $ctrl->salvar(); return; }
        if ($seg[1] === 'pagar'  && $metodo === 'POST') { $ctrl->marcarPago(); return; }

        $id = (int) ($seg[1] ?? 0);
        if ($id > 0 && $seg[2] === 'excluir') {
            $_GET['id']   = $id;
            $_GET['comp'] = $_GET['comp'] ?? date('Y-m');
            $ctrl->excluir();
            return;
        }

        $ctrl->listar();
    }

    // ── Comunicados ───────────────────────────────────────────────────────

    private static function rotasComunicados(array $seg, string $metodo, bool $ehAdmin): void
    {
        require_once RAIZ . '/src/controllers/ComunicadoController.php';
        $ctrl = new ComunicadoController();

        // Admin: gestão
        if ($ehAdmin) {
            if ($seg[1] === 'novo')                                   { $ctrl->formulario(); return; }
            if ($seg[1] === 'salvar' && $metodo === 'POST')           { $ctrl->salvar();     return; }
            $id = (int) ($seg[1] ?? 0);
            if ($id > 0 && $seg[2] === 'editar')  { $_GET['id'] = $id; $ctrl->formulario(); return; }
            if ($id > 0 && $seg[2] === 'excluir') { $_GET['id'] = $id; $ctrl->excluir();    return; }
            $ctrl->listarAdmin();
            return;
        }

        // Morador: apenas leitura
        $ctrl->listarMorador();
    }

    // ── Push notifications ────────────────────────────────────────────────

    private static function rotasPush(array $seg, string $metodo): void
    {
        require_once RAIZ . '/src/controllers/PushController.php';
        $ctrl = new PushController();
        match ($seg[1]) {
            'subscribe'      => $ctrl->subscribe(),
            'unsubscribe'    => $ctrl->unsubscribe(),
            'vapid-public-key' => $ctrl->vapidKey(),
            default          => self::naoEncontrado(),
        };
    }

    // ── Configurações da plataforma ───────────────────────────────────────

    private static function rotasConfiguracoes(array $seg, string $metodo, bool $ehAdmin): void
    {
        if (!$ehAdmin) { self::naoAutorizado(); return; }
        require_once RAIZ . '/src/controllers/ConfiguracaoController.php';
        $ctrl = new ConfiguracaoController();
        if ($seg[1] === 'salvar' && $metodo === 'POST') {
            $ctrl->salvar();
        } else {
            $ctrl->exibir();
        }
    }

    // ── Minhas taxas (morador) ────────────────────────────────────────────

    private static function rotasMinhasTaxas(array $seg, string $metodo): void
    {
        require_once RAIZ . '/src/controllers/TaxaCondominialController.php';
        $ctrl = new TaxaCondominialController();

        if ($seg[1] === 'comprovante' && $metodo === 'POST') {
            $ctrl->enviarComprovante();
            return;
        }
        $ctrl->listarMinhasTaxas();
    }

    // ── Portal da transparência (morador) ─────────────────────────────────

    private static function rotasTransparencia(array $seg): void
    {
        require_once RAIZ . '/src/controllers/TransparenciaController.php';
        (new TransparenciaController())->exibir();
    }

    // ── Relatório ─────────────────────────────────────────────────────────

    private static function carregarRelatorio(): void
    {
        require_once RAIZ . '/src/controllers/RelatorioController.php';
        (new RelatorioController())->exibir();
    }

    // ── Perfil do usuário logado ───────────────────────────────────────────

    private static function rotasPerfil(array $seg, string $metodo): void
    {
        require_once RAIZ . '/src/controllers/PerfilController.php';
        $ctrl = new PerfilController();
        if ($seg[1] === 'salvar' && $metodo === 'POST') {
            $ctrl->salvar();
        } else {
            $ctrl->exibir();
        }
    }

    // ── Tickets ───────────────────────────────────────────────────────────

    private static function rotasTickets(array $seg, string $metodo, bool $ehAdmin): void
    {
        require_once RAIZ . '/src/controllers/TicketController.php';
        $ctrl = new TicketController();

        if ($seg[1] === null) {
            $ehAdmin ? $ctrl->listarAdmin() : $ctrl->listarMorador();
            return;
        }
        if ($seg[1] === 'novo')                              { $ctrl->formulario(); return; }
        if ($seg[1] === 'salvar' && $metodo === 'POST')      { $ctrl->salvar();     return; }

        $id = (int) $seg[1];
        if ($id > 0 && $seg[2] === 'responder' && $metodo === 'POST') { $ctrl->responder();     return; }
        if ($id > 0 && $seg[2] === 'status'    && $metodo === 'POST') { $ctrl->alterarStatus(); return; }
        if ($id > 0) { $_GET['id'] = $id; $ctrl->ver(); return; }

        self::naoEncontrado();
    }

    // ── Utilitários ───────────────────────────────────────────────────────

    public static function redirecionar(string $caminho): void
    {
        header('Location: ' . url($caminho));
        exit;
    }

    private static function segmentosUri(): array
    {
        $uri  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $base = BASE_URL;

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $partes = array_values(array_filter(explode('/', $uri), fn($s) => $s !== ''));
        return array_pad($partes, 5, null);
    }

    private static function naoEncontrado(): void
    {
        http_response_code(404);
        echo '<h1 style="font-family:sans-serif">404 — Página não encontrada.</h1>';
    }

    private static function naoAutorizado(): void
    {
        http_response_code(403);
        echo '<h1 style="font-family:sans-serif">403 — Acesso não autorizado.</h1>';
    }
}
