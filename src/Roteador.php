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
            'taxas'        => self::rotasTaxas($seg, $metodo, $ehAdmin),
            'projetos'     => self::rotasProjetos($seg, $metodo, $ehAdmin),
            'minhas-taxas' => self::rotasMinhasTaxas($seg, $metodo),
            'transparencia'=> self::rotasTransparencia($seg),
            'relatorios'   => self::carregarRelatorio(),
            default        => self::naoEncontrado(),
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
        // GET /taxas/{id}/aprovar
        if ($seg[2] === 'aprovar') {
            $_GET['id'] = (int) $seg[1];
            $ctrl->aprovarComprovante();
            return;
        }
        // GET /taxas
        $ctrl->listar();
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
        require_once RAIZ . '/src/controllers/ProjetoController.php';
        $ctrl = new ProjetoController();

        if ($seg[1] !== null) { $_GET['id'] = (int) $seg[1]; $ctrl->ver(); return; }
        $ctrl->listar();
    }

    // ── Relatório ─────────────────────────────────────────────────────────

    private static function carregarRelatorio(): void
    {
        require_once RAIZ . '/src/controllers/RelatorioController.php';
        (new RelatorioController())->exibir();
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
