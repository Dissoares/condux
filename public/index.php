<?php

declare(strict_types=1);

define('RAIZ',     dirname(__DIR__));
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

date_default_timezone_set('America/Sao_Paulo');

require_once RAIZ . '/src/Conexao.php';
require_once RAIZ . '/src/Sessao.php';
require_once RAIZ . '/src/Ajudantes.php';
require_once RAIZ . '/src/Roteador.php';

Roteador::despachar();
