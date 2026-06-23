<?php

declare(strict_types=1);

/**
 * Gerencia a execução e o rastreamento de migrations SQL.
 * Cada arquivo em database/migracoes/ é uma migration independente.
 */
class MigracaoRunner
{
    private const TABELA_CONTROLE = 'migracoes';
    private string $pastaMigracoes;

    public function __construct(private readonly PDO $conexao)
    {
        $this->pastaMigracoes = RAIZ . '/database/migracoes';
        $this->garantirTabelaDeControle();
    }

    /**
     * Retorna todas as migrations com seu status.
     * @return array<array{nome: string, executada: bool, executada_em: string|null}>
     */
    public function listarTodas(): array
    {
        $executadas   = $this->buscarExecutadas();
        $arquivos     = $this->listarArquivos();
        $resultado    = [];

        foreach ($arquivos as $arquivo) {
            $nome = basename($arquivo);
            $resultado[] = [
                'nome'        => $nome,
                'executada'   => isset($executadas[$nome]),
                'executada_em'=> $executadas[$nome] ?? null,
            ];
        }

        return $resultado;
    }

    /**
     * Executa todas as migrations pendentes em ordem.
     * @return array<array{nome: string, sucesso: bool, erro: string|null}>
     */
    public function executarPendentes(): array
    {
        $executadas = $this->buscarExecutadas();
        $arquivos   = $this->listarArquivos();
        $resultado  = [];

        foreach ($arquivos as $arquivo) {
            $nome = basename($arquivo);

            if (isset($executadas[$nome])) {
                continue;
            }

            try {
                $sql        = file_get_contents($arquivo);
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    fn($s) => $s !== '' && !preg_match('/^--/m', $s) || trim(preg_replace('/--[^\n]*/', '', $s)) !== ''
                );
                foreach ($statements as $stmt) {
                    $stmt = trim(preg_replace('/--[^\n]*/', '', $stmt));
                    if ($stmt !== '') {
                        $this->conexao->exec($stmt);
                    }
                }
                $this->registrarExecucao($nome);
                $resultado[] = ['nome' => $nome, 'sucesso' => true, 'erro' => null];
            } catch (PDOException $e) {
                $resultado[] = ['nome' => $nome, 'sucesso' => false, 'erro' => $e->getMessage()];
                break; // Para na primeira falha para não executar migrations dependentes
            }
        }

        return $resultado;
    }

    public function temPendentes(): bool
    {
        $executadas = $this->buscarExecutadas();
        foreach ($this->listarArquivos() as $arquivo) {
            if (!isset($executadas[basename($arquivo)])) {
                return true;
            }
        }
        return false;
    }

    private function garantirTabelaDeControle(): void
    {
        $this->conexao->exec('CREATE TABLE IF NOT EXISTS ' . self::TABELA_CONTROLE . ' (
            id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome         VARCHAR(255) NOT NULL UNIQUE,
            executada_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    private function buscarExecutadas(): array
    {
        $stmt = $this->conexao->query(
            'SELECT nome, executada_em FROM ' . self::TABELA_CONTROLE . ' ORDER BY executada_em'
        );
        return array_column($stmt->fetchAll(), 'executada_em', 'nome');
    }

    /** @return string[] — caminhos completos ordenados */
    private function listarArquivos(): array
    {
        $arquivos = glob($this->pastaMigracoes . '/*.sql') ?: [];
        sort($arquivos);
        return $arquivos;
    }

    private function registrarExecucao(string $nome): void
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO ' . self::TABELA_CONTROLE . ' (nome) VALUES (:nome)'
        );
        $stmt->execute([':nome' => $nome]);
    }
}
