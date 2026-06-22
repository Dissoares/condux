<?php

declare(strict_types=1);

// Ponto de entrada único da aplicação
define('RAIZ', dirname(__DIR__));

date_default_timezone_set('America/Sao_Paulo');

// Carregamento das classes principais (sem autoloader para hospedagem compartilhada)
require_once RAIZ . '/src/Conexao.php';
require_once RAIZ . '/src/Sessao.php';
require_once RAIZ . '/src/Roteador.php';

// Controllers necessários
require_once RAIZ . '/src/controllers/AutenticacaoController.php';

// Dispara a rota correta
Roteador::despachar();
