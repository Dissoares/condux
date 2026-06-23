<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Funcionario.php';

class FuncionarioRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** @return Funcionario[] */
    public function listarTodos(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM funcionarios ORDER BY ativo DESC, nome'
        );
        return array_map(fn($l) => Funcionario::fromArray($l), $stmt->fetchAll());
    }

    /** @return Funcionario[] */
    public function listarAtivos(): array
    {
        $stmt = $this->conexao->query(
            'SELECT * FROM funcionarios WHERE ativo = 1 ORDER BY nome'
        );
        return array_map(fn($l) => Funcionario::fromArray($l), $stmt->fetchAll());
    }

    public function contarAtivos(): int
    {
        return (int) $this->conexao->query(
            'SELECT COUNT(*) FROM funcionarios WHERE ativo = 1'
        )->fetchColumn();
    }

    public function buscarPorId(int $id): ?Funcionario
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM funcionarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? Funcionario::fromArray($linha) : null;
    }

    public function salvar(Funcionario $f): int
    {
        if ($f->id === null) {
            $stmt = $this->conexao->prepare(
                'INSERT INTO funcionarios
                 (nome, cargo, cpf, departamento, telefone, email, salario, dia_pagamento,
                  data_admissao, data_demissao, observacoes, ativo)
                 VALUES
                 (:nome, :cargo, :cpf, :departamento, :telefone, :email, :salario, :dia_pagamento,
                  :data_admissao, :data_demissao, :observacoes, :ativo)'
            );
        } else {
            $stmt = $this->conexao->prepare(
                'UPDATE funcionarios SET
                 nome = :nome, cargo = :cargo, cpf = :cpf,
                 departamento = :departamento, telefone = :telefone,
                 email = :email, salario = :salario, dia_pagamento = :dia_pagamento,
                 data_admissao = :data_admissao, data_demissao = :data_demissao,
                 observacoes = :observacoes, ativo = :ativo
                 WHERE id = :id'
            );
        }

        $params = [
            ':nome'          => $f->nome,
            ':cargo'         => $f->cargo,
            ':cpf'           => $f->cpf,
            ':departamento'  => $f->departamento,
            ':telefone'      => $f->telefone,
            ':email'         => $f->email,
            ':salario'        => $f->salario,
            ':dia_pagamento'  => $f->diaPagamento,
            ':data_admissao'  => $f->dataAdmissao ?: null,
            ':data_demissao' => $f->dataDemissao ?: null,
            ':observacoes'   => $f->observacoes,
            ':ativo'         => $f->ativo ? 1 : 0,
        ];
        if ($f->id !== null) {
            $params[':id'] = $f->id;
        }

        $stmt->execute($params);
        return $f->id ?? (int) $this->conexao->lastInsertId();
    }

    public function excluir(int $id): void
    {
        $this->conexao->prepare('DELETE FROM funcionarios WHERE id = :id')
            ->execute([':id' => $id]);
    }
}
