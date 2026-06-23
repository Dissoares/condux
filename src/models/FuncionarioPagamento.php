<?php

declare(strict_types=1);

class FuncionarioPagamento
{
    public function __construct(
        public readonly ?int $id,
        public int           $funcionarioId,
        public string        $competencia,   // YYYY-MM
        public float         $valor,
        public ?string       $dataPrevista   = null,
        public ?string       $dataPagamento  = null,
        public string        $status         = 'pendente',
        public ?string       $observacoes    = null,
        public ?string       $criadoEm      = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:            isset($d['id']) ? (int) $d['id'] : null,
            funcionarioId: (int) $d['funcionario_id'],
            competencia:   $d['competencia'],
            valor:         (float) $d['valor'],
            dataPrevista:  $d['data_prevista']  ?? null,
            dataPagamento: $d['data_pagamento'] ?? null,
            status:        $d['status']         ?? 'pendente',
            observacoes:   $d['observacoes']    ?? null,
            criadoEm:      $d['criado_em']      ?? null,
        );
    }

    public function estaAtrasado(): bool
    {
        if ($this->status === 'pago') return false;
        if (!$this->dataPrevista) return false;
        return date('Y-m-d') > $this->dataPrevista;
    }

    public function competenciaBR(): string
    {
        [$ano, $mes] = explode('-', $this->competencia);
        $nomes = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        return ($nomes[(int)$mes] ?? $mes) . '/' . $ano;
    }
}
