<?php

declare(strict_types=1);

class ConfiguracaoRepository
{
    private static ?array $cache = null;

    public function __construct(private readonly PDO $conexao) {}

    /** Retorna todas as configurações como array associativo chave => valor */
    public function todas(): array
    {
        if (self::$cache !== null) return self::$cache;

        try {
            $rows = $this->conexao->query(
                'SELECT chave, valor FROM configuracoes'
            )->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException) {
            $rows = [];
        }

        self::$cache = array_merge(self::padroes(), $rows);
        return self::$cache;
    }

    public function obter(string $chave, mixed $padrao = null): mixed
    {
        return $this->todas()[$chave] ?? $padrao;
    }

    public function salvar(string $chave, ?string $valor): void
    {
        $this->conexao->prepare(
            'INSERT INTO configuracoes (chave, valor) VALUES (:c, :v)
             ON DUPLICATE KEY UPDATE valor = :v2'
        )->execute([':c' => $chave, ':v' => $valor, ':v2' => $valor]);
        self::$cache = null;
    }

    public function salvarVarios(array $pares): void
    {
        foreach ($pares as $chave => $valor) {
            $this->salvar($chave, $valor);
        }
    }

    public static function padroes(): array
    {
        return [
            'app_nome'       => 'Condux',
            'app_nome_curto' => 'Condux',
            'app_descricao'  => 'Gestão de condomínio',
            'app_logo'       => null,
            'cor_primaria'   => '#1a3c5e',
            'cor_escura'     => '#0f2540',
            'cor_acento'     => '#f0a500',
        ];
    }
}
