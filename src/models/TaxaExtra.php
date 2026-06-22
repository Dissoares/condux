<?php

declare(strict_types=1);

class TaxaExtra
{
    public function __construct(
        public readonly ?int $id,
        public string        $nome,
        public float         $valor,
        public string        $vencimento,
        public ?string       $descricao      = null,
        public ?int          $projetoId      = null,
        public ?int          $parcela        = null,
        public ?int          $totalParcelas  = null,
        public ?float        $valorTotal     = null,
        public ?string       $nomeProjeto    = null,
        public ?string       $criadoEm       = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:             isset($d['id'])             ? (int)   $d['id']             : null,
            nome:           $d['nome'],
            valor:          (float) $d['valor'],
            vencimento:     $d['vencimento'],
            descricao:      $d['descricao']      ?? null,
            projetoId:      isset($d['projeto_id'])     ? (int)   $d['projeto_id']     : null,
            parcela:        isset($d['parcela'])         ? (int)   $d['parcela']         : null,
            totalParcelas:  isset($d['total_parcelas'])  ? (int)   $d['total_parcelas']  : null,
            valorTotal:     isset($d['valor_total'])     ? (float) $d['valor_total']     : null,
            nomeProjeto:    $d['nome_projeto']   ?? null,
            criadoEm:       $d['criado_em']      ?? null,
        );
    }

    public function labelParcela(): string
    {
        if ($this->parcela && $this->totalParcelas) {
            return "{$this->parcela}/{$this->totalParcelas}";
        }
        return '—';
    }
}
