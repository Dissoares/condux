<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';

class UsuarioRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM usuarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? Usuario::fromArray($linha) : null;
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $linha = $stmt->fetch();

        return $linha ? Usuario::fromArray($linha) : null;
    }

    /** @return Usuario[] */
    public function listarTodos(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM usuarios ORDER BY nome'
        );
        return array_map(fn($l) => Usuario::fromArray($l), $stmt->fetchAll());
    }

    /**
     * Lista todos os usuários ativos com flags de papel (proprietário / inquilino).
     * @return array[] Cada item: id, nome, email, eh_proprietario (0|1), eh_inquilino (0|1)
     */
    public function listarTodosComPapeis(): array
    {
        $stmt = $this->conexao->query(
            'SELECT u.id, u.nome, u.email,
                    (SELECT COUNT(*) FROM unidades WHERE proprietario_id = u.id) AS eh_proprietario,
                    (SELECT COUNT(*) FROM unidades WHERE inquilino_id    = u.id) AS eh_inquilino
             FROM usuarios u
             WHERE u.ativo = 1
             ORDER BY u.nome'
        );
        return $stmt->fetchAll();
    }

    /** @return Usuario[] Apenas usuários que são proprietários de alguma unidade */
    public function listarProprietarios(): array
    {
        $stmt = $this->conexao->query(
            'SELECT DISTINCT u.*
             FROM usuarios u
             INNER JOIN unidades un ON un.proprietario_id = u.id
             WHERE u.ativo = 1
             ORDER BY u.nome'
        );
        return array_map(fn($l) => Usuario::fromArray($l), $stmt->fetchAll());
    }

    /** @return Usuario[] */
    public function listarPorPerfil(string $perfil): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM usuarios WHERE perfil = :perfil AND ativo = 1 ORDER BY nome'
        );
        $stmt->execute([':perfil' => $perfil]);
        return array_map(fn($l) => Usuario::fromArray($l), $stmt->fetchAll());
    }

    public function salvar(Usuario $usuario): int
    {
        if ($usuario->id === null) {
            return $this->inserir($usuario);
        }
        $this->atualizar($usuario);
        return $usuario->id;
    }

    private function inserir(Usuario $usuario): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO usuarios (nome, email, senha, perfil, ativo, telefone, cpf, data_nascimento, observacoes, foto)
             VALUES (:nome, :email, :senha, :perfil, :ativo, :telefone, :cpf, :data_nascimento, :observacoes, :foto)'
        );
        $stmt->execute($this->parametros($usuario));
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(Usuario $usuario): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE usuarios
             SET nome = :nome, email = :email, senha = :senha, perfil = :perfil, ativo = :ativo,
                 telefone = :telefone, cpf = :cpf, data_nascimento = :data_nascimento, observacoes = :observacoes,
                 foto = :foto
             WHERE id = :id'
        );
        $stmt->execute([...$this->parametros($usuario), ':id' => $usuario->id]);
    }

    private function parametros(Usuario $usuario): array
    {
        return [
            ':nome'            => $usuario->nome,
            ':email'           => $usuario->email,
            ':senha'           => $usuario->senha,
            ':perfil'          => $usuario->perfil,
            ':ativo'           => (int) $usuario->ativo,
            ':telefone'        => $usuario->telefone       ?: null,
            ':cpf'             => $usuario->cpf            ?: null,
            ':data_nascimento' => $usuario->dataNascimento ?: null,
            ':observacoes'     => $usuario->observacoes    ?: null,
            ':foto'            => $usuario->foto           ?: null,
        ];
    }

    public function desativar(int $id): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE usuarios SET ativo = 0 WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    /** @return array[] Moradores com info de unidade (LEFT JOIN) */
    public function listarMoradoresComUnidade(): array
    {
        $stmt = $this->conexao->query(
            'SELECT u.*,
                    m.id AS morador_id,
                    m.responsavel,
                    m.data_entrada,
                    un.id   AS unidade_id_vinculo,
                    CONCAT("Apto ", un.numero, IF(un.bloco IS NOT NULL, CONCAT(" — Bloco ", un.bloco), ""))
                            AS identificacao_unidade,
                    (SELECT COUNT(*) FROM unidades WHERE proprietario_id = u.id) AS eh_proprietario,
                    (SELECT COUNT(*) FROM unidades WHERE inquilino_id    = u.id) AS eh_inquilino
             FROM usuarios u
             LEFT JOIN moradores m ON m.usuario_id = u.id AND m.ativo = 1
             LEFT JOIN unidades un ON un.id = m.unidade_id
             WHERE u.perfil = \'morador\' AND u.ativo = 1
             ORDER BY u.nome'
        );
        return $stmt->fetchAll();
    }

    /** @return array[] Busca moradores por nome ou e-mail */
    public function pesquisarMoradores(string $termo): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT u.*,
                    m.id AS morador_id,
                    un.id AS unidade_id_vinculo,
                    CONCAT("Apto ", un.numero, IF(un.bloco IS NOT NULL, CONCAT(" — Bloco ", un.bloco), "")) AS identificacao_unidade
             FROM usuarios u
             LEFT JOIN moradores m ON m.usuario_id = u.id AND m.ativo = 1
             LEFT JOIN unidades un ON un.id = m.unidade_id
             WHERE u.perfil = \'morador\' AND u.ativo = 1
               AND (u.nome LIKE :termo OR u.email LIKE :termo)
             ORDER BY u.nome LIMIT 20'
        );
        $stmt->execute([':termo' => '%' . $termo . '%']);
        return $stmt->fetchAll();
    }

    public function salvarTokenReset(int $id, string $token, string $expiraEm): void
    {
        $this->conexao->prepare(
            'UPDATE usuarios SET reset_token = :token, reset_expira_em = :expira WHERE id = :id'
        )->execute([':token' => $token, ':expira' => $expiraEm, ':id' => $id]);
    }

    public function buscarPorToken(string $token): ?Usuario
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM usuarios WHERE reset_token = :token AND reset_expira_em > NOW() LIMIT 1'
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromArray($row) : null;
    }

    public function atualizarSenha(int $id, string $hashSenha): void
    {
        $this->conexao->prepare(
            'UPDATE usuarios SET senha = :senha WHERE id = :id'
        )->execute([':senha' => $hashSenha, ':id' => $id]);
    }

    public function limparTokenReset(int $id): void
    {
        $this->conexao->prepare(
            'UPDATE usuarios SET reset_token = NULL, reset_expira_em = NULL WHERE id = :id'
        )->execute([':id' => $id]);
    }
}
