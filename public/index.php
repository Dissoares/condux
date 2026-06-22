<?php

declare(strict_types=1);

define('RAIZ',     dirname(__DIR__));
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

date_default_timezone_set('America/Sao_Paulo');

require_once RAIZ . '/src/Conexao.php';
require_once RAIZ . '/src/Sessao.php';
require_once RAIZ . '/src/Ajudantes.php';
require_once RAIZ . '/src/Roteador.php';

try {
    Roteador::despachar();
} catch (PDOException $e) {
    // Qualquer erro de banco redireciona para o painel de setup,
    // exceto se já estivermos lá (evita loop).
    $uriAtual = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $emSetup  = str_contains($uriAtual, '/setup');

    if ($emSetup) {
        // Dentro do setup o SetupController trata os erros de conexão.
        // Se mesmo assim chegou aqui, exibe mensagem simples.
        http_response_code(500);
        echo '<p style="font-family:sans-serif;padding:2rem;">Erro de conexão: '
            . htmlspecialchars($e->getMessage()) . '</p>';
        return;
    }

    Sessao::iniciar();
    Sessao::flash('erro', 'Banco de dados indisponível. Configure o sistema antes de continuar.');
    header('Location: ' . BASE_URL . '/setup');
    exit;
}
