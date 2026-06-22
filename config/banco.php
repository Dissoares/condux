<?php

declare(strict_types=1);

/**
 * Configuração de conexão com o banco de dados.
 * Em produção, substitua os valores por variáveis de ambiente ou arquivo .env.
 */
return [
    'driver'   => 'mysql',
    'host'     => $_ENV['DB_HOST']     ?? 'localhost',
    'porta'    => $_ENV['DB_PORT']     ?? '3306',
    'banco'    => $_ENV['DB_BANCO']    ?? 'condux',
    'usuario'  => $_ENV['DB_USUARIO']  ?? 'root',
    'senha'    => $_ENV['DB_SENHA']    ?? '',
    'charset'  => 'utf8mb4',
    'timezone' => '-03:00',
];
