<?php

declare(strict_types=1);

/**
 * Gerencia a conexão PDO com o banco de dados.
 * Implementa Singleton para reutilizar a mesma conexão durante a requisição.
 */
class Conexao
{
    private static ?PDO $instancia = null;

    private function __construct() {}

    public static function obter(): PDO
    {
        if (self::$instancia === null) {
            $config = require __DIR__ . '/../config/banco.php';

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['porta'],
                $config['banco'],
                $config['charset']
            );

            self::$instancia = new PDO($dsn, $config['usuario'], $config['senha'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone='{$config['timezone']}'",
            ]);
        }

        return self::$instancia;
    }
}
