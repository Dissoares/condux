<?php

declare(strict_types=1);

/**
 * Vínculo entre um usuário (morador) e uma unidade.
 */
class Morador
{
    public function __construct(
        public readonly ?int $id,
        public int           $usuarioId,
        public int           $unidadeId,
        public bool          $responsavel   = false,
        public string        $dataEntrada   = '',
        public ?string       $dataSaida     = null,
        public bool          $ativo         = true,
        // Dados extras via JOIN
        public ?string       $nomeUsuario         = null,
        public ?string       $emailUsuario         = null,
        public ?string       $identificacaoUnidade = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:                   isset($dados['id']) ? (int) $dados['id'] : null,
            usuarioId:            (int) $dados['usuario_id'],
            unidadeId:            (int) $dados['unidade_id'],
            responsavel:          (bool) ($dados['responsavel'] ?? false),
            dataEntrada:          $dados['data_entrada'] ?? date('Y-m-d'),
            dataSaida:            $dados['data_saida']   ?? null,
            ativo:                (bool) ($dados['ativo'] ?? true),
            nomeUsuario:          $dados['nome_usuario']          ?? null,
            emailUsuario:         $dados['email_usuario']         ?? null,
            identificacaoUnidade: $dados['identificacao_unidade'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'usuario_id'   => $this->usuarioId,
            'unidade_id'   => $this->unidadeId,
            'responsavel'  => (int) $this->responsavel,
            'data_entrada' => $this->dataEntrada,
            'data_saida'   => $this->dataSaida,
            'ativo'        => (int) $this->ativo,
        ];
    }
}
