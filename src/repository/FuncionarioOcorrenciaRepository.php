<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/FuncionarioOcorrencia.php';

class FuncionarioOcorrenciaRepository
{
    public function __construct(private readonly PDO $conexao) {}

    /** @return FuncionarioOcorrencia[] */
    public function listarPorFuncionario(int $funcionarioId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM funcionario_ocorrencias
             WHERE funcionario_id = :fid
             ORDER BY data_inicio DESC'
        );
        $stmt->execute([':fid' => $funcionarioId]);
        return array_map(fn($l) => FuncionarioOcorrencia::fromArray($l), $stmt->fetchAll());
    }

    public function buscarPorId(int $id): ?FuncionarioOcorrencia
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM funcionario_ocorrencias WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        return $linha ? FuncionarioOcorrencia::fromArray($linha) : null;
    }

    public function salvar(FuncionarioOcorrencia $o): int
    {
        if ($o->id === null) {
            $stmt = $this->conexao->prepare(
                'INSERT INTO funcionario_ocorrencias
                 (funcionario_id, tipo, data_inicio, data_fim, valor,
                  justificativa, anexo, nome_original, status)
                 VALUES (:fid, :tipo, :di, :df, :valor, :just, :anexo, :nome, :status)'
            );
        } else {
            $stmt = $this->conexao->prepare(
                'UPDATE funcionario_ocorrencias SET
                 tipo = :tipo, data_inicio = :di, data_fim = :df, valor = :valor,
                 justificativa = :just, status = :status
                 WHERE id = :id'
            );
        }

        $params = [
            ':tipo'   => $o->tipo,
            ':di'     => $o->dataInicio,
            ':df'     => $o->dataFim,
            ':valor'  => $o->valor,
            ':just'   => $o->justificativa,
            ':status' => $o->status,
        ];

        if ($o->id === null) {
            $params[':fid']   = $o->funcionarioId;
            $params[':anexo'] = $o->anexo;
            $params[':nome']  = $o->nomeOriginal;
        } else {
            $params[':id'] = $o->id;
        }

        $stmt->execute($params);
        return $o->id ?? (int) $this->conexao->lastInsertId();
    }

    public function excluir(int $id): ?string
    {
        $stmt = $this->conexao->prepare(
            'SELECT anexo FROM funcionario_ocorrencias WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        $this->conexao->prepare('DELETE FROM funcionario_ocorrencias WHERE id = :id')
            ->execute([':id' => $id]);

        return $linha['anexo'] ?? null;
    }
}
