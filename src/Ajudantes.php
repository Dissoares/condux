<?php

declare(strict_types=1);

/**
 * Gera URL absoluta a partir de um caminho relativo à raiz do app.
 * Funciona tanto com virtual host (condux.test/) quanto com subpasta (localhost/condux/public/).
 */
function url(string $caminho = ''): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}

/** Formata valor monetário em Real brasileiro. */
function dinheiro(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/** Formata data do banco (Y-m-d) para exibição (d/m/Y). */
function dataBR(?string $data): string
{
    if ($data === null || $data === '') {
        return '—';
    }
    return date('d/m/Y', strtotime($data));
}
