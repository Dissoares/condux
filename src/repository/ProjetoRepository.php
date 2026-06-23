<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Projeto.php';

class ProjetoRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?Projeto
    {
        $stmt = $this->conexao->prepare(
            'SELECT p.*,
                    u.nome      AS nome_responsavel,
                    pr.nome     AS nome_prestadora,
                    pr.cnpj     AS prestadora_cnpj,
                    pr.contato  AS prestadora_contato,
                    pr.telefone AS prestadora_telefone,
                    pr.email    AS prestadora_email
             FROM projetos p
             LEFT JOIN usuarios    u  ON u.id  = p.responsavel_id
             LEFT JOIN prestadoras pr ON pr.id = p.prestadora_id
             WHERE p.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        $projeto = Projeto::fromArray($linha);
        $projeto->anexos = $this->buscarAnexosDoProjeto($id);
        return $projeto;
    }

    /** @return Projeto[] */
    public function listarTodos(?string $status = null): array
    {
        $sql = 'SELECT p.*,
                       u.nome AS nome_responsavel,
                       pr.nome AS nome_prestadora
                FROM projetos p
                LEFT JOIN usuarios    u  ON u.id  = p.responsavel_id
                LEFT JOIN prestadoras pr ON pr.id = p.prestadora_id';

        $params = [];
        if ($status !== null) {
            $sql .= ' WHERE p.status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY p.criado_em DESC';

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);
        return array_map(fn($l) => Projeto::fromArray($l), $stmt->fetchAll());
    }

    /** @return Projeto[] */
    public function listarPorPrestadora(int $prestadoraId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT p.*, u.nome AS nome_responsavel
             FROM projetos p
             LEFT JOIN usuarios u ON u.id = p.responsavel_id
             WHERE p.prestadora_id = :prestadora_id
             ORDER BY p.criado_em DESC'
        );
        $stmt->execute([':prestadora_id' => $prestadoraId]);
        return array_map(fn($l) => Projeto::fromArray($l), $stmt->fetchAll());
    }

    public function salvar(Projeto $projeto): int
    {
        if ($projeto->id === null) {
            return $this->inserir($projeto);
        }
        $this->atualizar($projeto);
        return $projeto->id;
    }

    private function inserir(Projeto $projeto): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO projetos
             (nome, descricao, idealizador, responsavel_id, prestadora_id, status,
              valor_estimado, valor_realizado, data_inicio, data_conclusao)
             VALUES
             (:nome, :descricao, :idealizador, :responsavel_id, :prestadora_id, :status,
              :valor_estimado, :valor_realizado, :data_inicio, :data_conclusao)'
        );
        $stmt->execute($this->extrairParametros($projeto));
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(Projeto $projeto): void
    {
        $stmt = $this->conexao->prepare(
            'UPDATE projetos SET
             nome = :nome, descricao = :descricao, idealizador = :idealizador,
             responsavel_id = :responsavel_id, prestadora_id = :prestadora_id,
             status = :status, valor_estimado = :valor_estimado,
             valor_realizado = :valor_realizado, data_inicio = :data_inicio,
             data_conclusao = :data_conclusao
             WHERE id = :id'
        );
        $params = $this->extrairParametros($projeto);
        $params[':id'] = $projeto->id;
        $stmt->execute($params);
    }

    private function extrairParametros(Projeto $projeto): array
    {
        return [
            ':nome'            => $projeto->nome,
            ':descricao'       => $projeto->descricao,
            ':idealizador'     => $projeto->idealizador,
            ':responsavel_id'  => $projeto->responsavelId,
            ':prestadora_id'   => $projeto->prestadoraId,
            ':status'          => $projeto->status,
            ':valor_estimado'  => $projeto->valorEstimado,
            ':valor_realizado' => $projeto->valorRealizado,
            ':data_inicio'     => $projeto->dataInicio,
            ':data_conclusao'  => $projeto->dataConclusao,
        ];
    }

    public function salvarAnexo(int $projetoId, string $tipo, string $caminho, string $nomeOriginal, ?string $descricao = null): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO projeto_anexos (projeto_id, tipo, caminho, nome_original, descricao)
             VALUES (:projeto_id, :tipo, :caminho, :nome_original, :descricao)'
        );
        $stmt->execute([
            ':projeto_id'    => $projetoId,
            ':tipo'          => $tipo,
            ':caminho'       => $caminho,
            ':nome_original' => $nomeOriginal,
            ':descricao'     => $descricao,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    public function removerAnexo(int $anexoId): ?string
    {
        $stmt = $this->conexao->prepare(
            'SELECT caminho FROM projeto_anexos WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $anexoId]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        $this->conexao->prepare('DELETE FROM projeto_anexos WHERE id = :id')
            ->execute([':id' => $anexoId]);

        return $linha['caminho'];
    }

    private function buscarAnexosDoProjeto(int $projetoId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM projeto_anexos WHERE projeto_id = :projeto_id ORDER BY enviado_em'
        );
        $stmt->execute([':projeto_id' => $projetoId]);
        return $stmt->fetchAll();
    }

    public function contarPorStatus(string $status): int
    {
        $stmt = $this->conexao->prepare('SELECT COUNT(*) FROM projetos WHERE status = :status');
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }
}
