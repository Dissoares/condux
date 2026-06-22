<?php

declare(strict_types=1);

/**
 * Roteador simples baseado em query string: ?pagina=unidades&acao=listar
 * Resolve o controller e método corretos de acordo com o perfil da sessão.
 */
class Roteador
{
    /** Mapa de rotas: perfil → pagina → acao → [controller, metodo] */
    private static array $rotas = [
        'admin' => [
            'painel'      => ['acao' => 'exibirPainel',     'controller' => 'PainelAdminController'],
            'unidades'    => ['acao' => 'listar',            'controller' => 'UnidadeController'],
            'moradores'   => ['acao' => 'listar',            'controller' => 'MoradorController'],
            'taxas'       => ['acao' => 'listar',            'controller' => 'TaxaCondominialController'],
            'taxas-extra' => ['acao' => 'listar',            'controller' => 'TaxaExtraController'],
            'projetos'    => ['acao' => 'listar',            'controller' => 'ProjetoController'],
            'vistorias'   => ['acao' => 'listar',            'controller' => 'VistoriaController'],
            'orcamentos'  => ['acao' => 'listar',            'controller' => 'OrcamentoController'],
            'relatorios'  => ['acao' => 'exibir',            'controller' => 'RelatorioController'],
            'prestadoras' => ['acao' => 'listar',            'controller' => 'PrestadoraController'],
        ],
        'morador' => [
            'painel'      => ['acao' => 'exibirPainel',     'controller' => 'PainelMoradorController'],
            'minhas-taxas'=> ['acao' => 'listar',            'controller' => 'TaxaCondominialController'],
            'transparencia'=> ['acao' => 'listar',           'controller' => 'ProjetoController'],
            'relatorios'  => ['acao' => 'exibir',            'controller' => 'RelatorioController'],
        ],
    ];

    public static function despachar(): void
    {
        Sessao::iniciar();

        $pagina = $_GET['pagina'] ?? 'painel';
        $acao   = $_GET['acao']   ?? null;

        // Rota de autenticação — acessível sem sessão
        if ($pagina === 'login' || $pagina === 'sair') {
            $controller = new AutenticacaoController();
            $metodo = $pagina === 'sair' ? 'sair' : 'exibir';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $metodo = 'entrar';
            }
            $controller->$metodo();
            return;
        }

        // Protege todas as outras rotas
        if (!Sessao::estaAutenticado()) {
            header('Location: ' . self::urlLogin());
            exit;
        }

        $perfil    = in_array(Sessao::perfilAtual(), ['sindico', 'subsindico']) ? 'admin' : 'morador';
        $rotasPerfil = self::$rotas[$perfil] ?? [];
        $rota        = $rotasPerfil[$pagina] ?? $rotasPerfil['painel'];

        $nomeController = $rota['controller'];
        $metodoPadrao   = $rota['acao'];
        $metodo         = $acao ?? $metodoPadrao;

        require_once __DIR__ . "/controllers/{$nomeController}.php";

        $controller = new $nomeController();

        if (!method_exists($controller, $metodo)) {
            http_response_code(404);
            echo 'Página não encontrada.';
            return;
        }

        $controller->$metodo();
    }

    public static function urlLogin(): string
    {
        $app = require __DIR__ . '/../config/app.php';
        return $app['url_base'] . '/index.php?pagina=login';
    }
}
