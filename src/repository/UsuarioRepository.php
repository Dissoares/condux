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
            'INSERT INTO usuarios (nome, email, senha, perfil, ativo)
             VALUES (:nome, :email, :senha, :perfil, :ativo)'
        );
        $stmt->execute([
            ':nome'   => $usuario->nome,
            ':email'  => $usuario->email,
            ':senha'  => $usuario->senha,
            ':perfil' => $usuario->perfil,
            ':ativo'  => (int) $usuario->ativo,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(Usuario $usuario): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE usuarios SET nome = :nome, email = :email, senha = :senha,
             perfil = :perfil, ativo = :ativo WHERE id = :id'
        );
        $stmt->execute([
            ':nome'   => $usuario->nome,
            ':email'  => $usuario->email,
            ':senha'  => $usuario->senha,
            ':perfil' => $usuario->perfil,
            ':ativo'  => (int) $usuario->ativo,
            ':id'     => $usuario->id,
        ]);
    }

    public function desativar(int $id): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE usuarios SET ativo = 0 WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }
}
