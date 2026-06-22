<?php

declare(strict_types=1);

/**
 * Taxa extra cobrada pontualmente (obra, evento, fundo de reserva etc.)
 */
class TaxaExtra
{
    public function __construct(
        public readonly ?int $id,
        public string        $nome,
        public float         $valor,
        public string        $vencimento,
        public ?string       $descricao  = null,
        public ?string       $criadoEm  = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:         isset($dados['id']) ? (int) $dados['id'] : null,
            nome:       $dados['nome'],
            valor:      (float) $dados['valor'],
            vencimento: $dados['vencimento'],
            descricao:  $dados['descricao'] ?? null,
            criadoEm:   $dados['criado_em'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'nome'       => $this->nome,
            'valor'      => $this->valor,
            'vencimento' => $this->vencimento,
            'descricao'  => $this->descricao,
        ];
    }
}
