<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Prestadora.php';

class PrestadoraRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** @return Prestadora[] */
    public function listarAtivas(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM prestadoras WHERE ativo = 1 ORDER BY nome'
        );
        return array_map(fn($l) => Prestadora::fromArray($l), $stmt->fetchAll());
    }

    /** @return Prestadora[] */
    public function listarTodas(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM prestadoras ORDER BY ativo DESC, nome'
        );
        return array_map(fn($l) => Prestadora::fromArray($l), $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?Prestadora
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM prestadoras WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? Prestadora::fromArray($linha) : null;
    }

    public function salvar(Prestadora $p): int
    {
        if ($p->id === null) {
            $stmt = $this->conexao->prepare(
                'INSERT INTO prestadoras (nome, cnpj, contato, telefone, email, ativo)
                 VALUES (:nome, :cnpj, :contato, :telefone, :email, :ativo)'
            );
        } else {
            $stmt = $this->conexao->prepare(
                'UPDATE prestadoras SET
                 nome = :nome, cnpj = :cnpj, contato = :contato,
                 telefone = :telefone, email = :email, ativo = :ativo
                 WHERE id = :id'
            );
        }

        $params = [
            ':nome'     => $p->nome,
            ':cnpj'     => $p->cnpj,
            ':contato'  => $p->contato,
            ':telefone' => $p->telefone,
            ':email'    => $p->email,
            ':ativo'    => $p->ativo ? 1 : 0,
        ];
        if ($p->id !== null) {
            $params[':id'] = $p->id;
        }

        $stmt->execute($params);
        return $p->id ?? (int) $this->conexao->lastInsertId();
    }

    public function excluir(int $id): void
    {
        $this->conexao->prepare('DELETE FROM prestadoras WHERE id = :id')
            ->execute([':id' => $id]);
    }
}
