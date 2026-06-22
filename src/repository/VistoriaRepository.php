<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Vistoria.php';

class VistoriaRepository
{
    public function __construct(private readonly PDO $conexao) {}

    public function buscarPorId(int $id): ?Vistoria
    {
        $stmt = $this->conexao->prepare(
            'SELECT v.*,
                    u.nome  AS nome_responsavel,
                    pr.nome AS nome_prestadora,
                    CASE WHEN un.numero IS NOT NULL
                         THEN CONCAT("Bloco ", un.bloco, " — Apto ", un.numero)
                         ELSE NULL END AS identificacao_unidade
             FROM vistorias v
             LEFT JOIN usuarios    u  ON u.id  = v.responsavel_id
             LEFT JOIN prestadoras pr ON pr.id = v.prestadora_id
             LEFT JOIN unidades    un ON un.id = v.unidade_id
             WHERE v.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();
        if (!$linha) return null;

        $vistoria         = Vistoria::fromArray($linha);
        $vistoria->anexos = $this->buscarAnexos($id);
        return $vistoria;
    }

    /** @return Vistoria[] */
    public function listarTodas(?string $tipo = null, ?string $status = null): array
    {
        $where  = [];
        $params = [];

        if ($tipo)   { $where[] = 'v.tipo = :tipo';     $params[':tipo']   = $tipo; }
        if ($status) { $where[] = 'v.status = :status'; $params[':status'] = $status; }

        $cond = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->conexao->prepare(
            "SELECT v.*,
                    u.nome  AS nome_responsavel,
                    pr.nome AS nome_prestadora,
                    CASE WHEN un.numero IS NOT NULL
                         THEN CONCAT('Bloco ', un.bloco, ' — Apto ', un.numero)
                         ELSE NULL END AS identificacao_unidade
             FROM vistorias v
             LEFT JOIN usuarios    u  ON u.id  = v.responsavel_id
             LEFT JOIN prestadoras pr ON pr.id = v.prestadora_id
             LEFT JOIN unidades    un ON un.id = v.unidade_id
             {$cond}
             ORDER BY
               CASE v.status WHEN 'agendada' THEN 0 WHEN 'realizada' THEN 1 ELSE 2 END,
               v.data_vistoria DESC"
        );
        $stmt->execute($params);
        return array_map(fn($l) => Vistoria::fromArray($l), $stmt->fetchAll());
    }

    /** Validades vencendo nos próximos 60 dias */
    public function listarValidadesProximas(): array
    {
        $stmt = $this->conexao->query(
            "SELECT v.*, u.nome AS nome_responsavel
             FROM vistorias v
             LEFT JOIN usuarios u ON u.id = v.responsavel_id
             WHERE v.validade IS NOT NULL
               AND v.validade >= CURDATE()
               AND v.validade <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
               AND v.status = 'realizada'
             ORDER BY v.validade"
        );
        return array_map(fn($l) => Vistoria::fromArray($l), $stmt->fetchAll());
    }

    /** @return Vistoria[] */
    public function listarPorPrestadora(int $prestadoraId): array
    {
        $stmt = $this->conexao->prepare(
            "SELECT v.*, u.nome AS nome_responsavel,
                    CASE WHEN un.numero IS NOT NULL
                         THEN CONCAT('Bloco ', un.bloco, ' — Apto ', un.numero)
                         ELSE NULL END AS identificacao_unidade
             FROM vistorias v
             LEFT JOIN usuarios u  ON u.id  = v.responsavel_id
             LEFT JOIN unidades un ON un.id = v.unidade_id
             WHERE v.prestadora_id = :prestadora_id
             ORDER BY v.data_vistoria DESC"
        );
        $stmt->execute([':prestadora_id' => $prestadoraId]);
        return array_map(fn($l) => Vistoria::fromArray($l), $stmt->fetchAll());
    }

    public function salvar(array $dados): int
    {
        if (!empty($dados['id'])) {
            return $this->atualizar($dados);
        }
        return $this->inserir($dados);
    }

    private function inserir(array $d): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO vistorias
                (data_vistoria, status, tipo, categoria, descricao,
                 unidade_id, responsavel_id, prestadora_id,
                 numero_documento, validade, resultado)
             VALUES
                (:data_vistoria, :status, :tipo, :categoria, :descricao,
                 :unidade_id, :responsavel_id, :prestadora_id,
                 :numero_documento, :validade, :resultado)'
        );
        $stmt->execute($this->bindParams($d));
        return (int) $this->conexao->lastInsertId();
    }

    private function atualizar(array $d): int
    {
        $stmt = $this->conexao->prepare(
            'UPDATE vistorias SET
                data_vistoria    = :data_vistoria,
                status           = :status,
                tipo             = :tipo,
                categoria        = :categoria,
                descricao        = :descricao,
                unidade_id       = :unidade_id,
                responsavel_id   = :responsavel_id,
                prestadora_id    = :prestadora_id,
                numero_documento = :numero_documento,
                validade         = :validade,
                resultado        = :resultado
             WHERE id = :id'
        );
        $params       = $this->bindParams($d);
        $params[':id'] = (int) $d['id'];
        $stmt->execute($params);
        return (int) $d['id'];
    }

    private function bindParams(array $d): array
    {
        return [
            ':data_vistoria'    => $d['data_vistoria'],
            ':status'           => $d['status']           ?? 'agendada',
            ':tipo'             => $d['tipo']             ?? 'predial',
            ':categoria'        => $d['categoria']        ?: null,
            ':descricao'        => $d['descricao']        ?: null,
            ':unidade_id'       => !empty($d['unidade_id'])      ? (int) $d['unidade_id']      : null,
            ':responsavel_id'   => !empty($d['responsavel_id'])  ? (int) $d['responsavel_id']  : null,
            ':prestadora_id'    => !empty($d['prestadora_id'])   ? (int) $d['prestadora_id']   : null,
            ':numero_documento' => $d['numero_documento']  ?: null,
            ':validade'         => $d['validade']          ?: null,
            ':resultado'        => $d['resultado']         ?: null,
        ];
    }

    public function excluir(int $id): void
    {
        $this->conexao->prepare('DELETE FROM vistorias WHERE id = :id')
                      ->execute([':id' => $id]);
    }

    public function salvarAnexo(int $vistoriaId, string $tipo, string $caminho, string $nomeOriginal): int
    {
        $stmt = $this->conexao->prepare(
            'INSERT INTO vistoria_anexos (vistoria_id, tipo, caminho, nome_original)
             VALUES (:vistoria_id, :tipo, :caminho, :nome_original)'
        );
        $stmt->execute([
            ':vistoria_id'  => $vistoriaId,
            ':tipo'         => $tipo,
            ':caminho'      => $caminho,
            ':nome_original'=> $nomeOriginal,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    public function removerAnexo(int $anexoId): ?string
    {
        $stmt = $this->conexao->prepare('SELECT caminho FROM vistoria_anexos WHERE id = :id');
        $stmt->execute([':id' => $anexoId]);
        $linha = $stmt->fetch();
        if (!$linha) return null;

        $this->conexao->prepare('DELETE FROM vistoria_anexos WHERE id = :id')
                      ->execute([':id' => $anexoId]);
        return $linha['caminho'];
    }

    private function buscarAnexos(int $vistoriaId): array
    {
        $stmt = $this->conexao->prepare(
            'SELECT * FROM vistoria_anexos WHERE vistoria_id = :id ORDER BY enviado_em DESC'
        );
        $stmt->execute([':id' => $vistoriaId]);
        return $stmt->fetchAll();
    }
}
