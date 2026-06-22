<?php

declare(strict_types=1);

class ConfigRepository
{
    public function __construct(private readonly PDO $conexao)
    {
        $this->conexao->exec(
            'CREATE TABLE IF NOT EXISTS configuracoes (
                chave         VARCHAR(80) NOT NULL PRIMARY KEY,
                valor         TEXT        NULL,
                atualizado_em DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function obter(string $chave, mixed $padrao = null): mixed
    {
        $stmt = $this->conexao->prepare(
            'SELECT valor FROM configuracoes WHERE chave = :chave LIMIT 1'
        );
        $stmt->execute([':chave' => $chave]);
        $linha = $stmt->fetch();
        return $linha ? $linha['valor'] : $padrao;
    }

    public function salvar(string $chave, mixed $valor): void
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO configuracoes (chave, valor)
             VALUES (:chave, :valor)
             ON DUPLICATE KEY UPDATE valor = :valor_upd'
        );
        $stmt->execute([':chave' => $chave, ':valor' => $valor, ':valor_upd' => $valor]);
    }
}
