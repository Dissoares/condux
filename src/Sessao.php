<?php

declare(strict_types=1);

/**
 * Gerencia a sessão do usuário autenticado.
 */
class Sessao
{
    private static bool $iniciada = false;

    public static function iniciar(): void
    {
        if (!self::$iniciada && session_status() === PHP_SESSION_NONE) {
            session_name('condux_sessao');
            session_start();
            self::$iniciada = true;
        }
    }

    public static function definir(string $chave, mixed $valor): void
    {
        self::iniciar();
        $_SESSION[$chave] = $valor;
    }

    public static function obter(string $chave, mixed $padrao = null): mixed
    {
        self::iniciar();
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function remover(string $chave): void
    {
        self::iniciar();
        unset($_SESSION[$chave]);
    }

    public static function destruir(): void
    {
        self::iniciar();
        session_unset();
        session_destroy();
        self::$iniciada = false;
    }

    public static function estaAutenticado(): bool
    {
        return self::obter('usuario_id') !== null;
    }

    public static function usuarioAtual(): ?array
    {
        return self::obter('usuario');
    }

    public static function perfilAtual(): ?string
    {
        return self::obter('usuario')['perfil'] ?? null;
    }

    /** Armazena valor flash (exibido uma única vez). Aceita string ou array. */
    public static function flash(string $tipo, mixed $valor): void
    {
        self::definir("flash_{$tipo}", $valor);
    }

    /** Retorna e apaga o valor flash. */
    public static function lerFlash(string $tipo): mixed
    {
        $mensagem = self::obter("flash_{$tipo}");
        self::remover("flash_{$tipo}");
        return $mensagem;
    }
}
