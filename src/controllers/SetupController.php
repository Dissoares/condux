<?php

declare(strict_types=1);

require_once __DIR__ . '/../MigracaoRunner.php';

/**
 * Painel de configuração inicial do sistema.
 * Acessível em /setup sem autenticação.
 * Responsável por criar o banco, executar migrations e exibir status.
 */
class SetupController
{
    public function exibir(): void
    {
        $config         = require RAIZ . '/config/banco.php';
        $statusConexao  = $this->testarConexao($config);
        $statusBanco    = false;
        $runner         = null;
        $migrations     = [];
        $temPendentes   = false;
        $resultadoExec  = Sessao::lerFlash('resultado_migracoes');

        if ($statusConexao) {
            $statusBanco = $this->bancoDadosExiste($config);

            if ($statusBanco) {
                try {
                    $runner       = new MigracaoRunner(Conexao::obter());
                    $migrations   = $runner->listarTodas();
                    $temPendentes = $runner->temPendentes();
                } catch (PDOException $e) {
                    // Banco existe mas sem tabela de controle ainda — normal na primeira vez
                    $temPendentes = true;
                }
            }
        }

        require_once RAIZ . '/views/setup/painel.php';
    }

    public function criarBanco(): void
    {
        $config = require RAIZ . '/config/banco.php';

        try {
            $pdo = $this->conectarSemBanco($config);
            $nomeBanco = $config['banco'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$nomeBanco}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            Sessao::flash('resultado_migracoes', [
                [['nome' => "Banco '{$nomeBanco}' criado.", 'sucesso' => true, 'erro' => null]]
            ]);
        } catch (PDOException $e) {
            Sessao::flash('resultado_migracoes', [
                [['nome' => 'Erro ao criar banco', 'sucesso' => false, 'erro' => $e->getMessage()]]
            ]);
        }

        Roteador::redirecionar('/setup');
    }

    public function executarMigracoes(): void
    {
        try {
            $runner    = new MigracaoRunner(Conexao::obter());
            $resultado = $runner->executarPendentes();
            Sessao::flash('resultado_migracoes', $resultado);
        } catch (PDOException $e) {
            Sessao::flash('resultado_migracoes', [[
                'nome'    => 'Erro de conexão',
                'sucesso' => false,
                'erro'    => $e->getMessage(),
            ]]);
        }

        Roteador::redirecionar('/setup');
    }

    public function gerarVapid(): void
    {
        require_once RAIZ . '/src/services/PushNotificationService.php';
        try {
            PushNotificationService::gerarChaves();
            Sessao::flash('resultado_migracoes', [
                ['nome' => 'Chaves VAPID geradas em config/vapid.php', 'sucesso' => true, 'erro' => null]
            ]);
        } catch (Throwable $e) {
            Sessao::flash('resultado_migracoes', [
                ['nome' => 'Erro ao gerar VAPID', 'sucesso' => false, 'erro' => $e->getMessage()]
            ]);
        }
        Roteador::redirecionar('/setup');
    }

    // ── Utilitários de conexão ──────────────────────────────────────────

    private function testarConexao(array $config): bool
    {
        try {
            $this->conectarSemBanco($config);
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    private function bancoDadosExiste(array $config): bool
    {
        try {
            $pdo  = $this->conectarSemBanco($config);
            $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :nome');
            $stmt->execute([':nome' => $config['banco']]);
            return $stmt->fetch() !== false;
        } catch (PDOException) {
            return false;
        }
    }

    private function conectarSemBanco(array $config): PDO
    {
        $dsn = sprintf('%s:host=%s;port=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['porta'],
            $config['charset'],
        );
        return new PDO($dsn, $config['usuario'], $config['senha'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
