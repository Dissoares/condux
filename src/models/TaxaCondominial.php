<?php

declare(strict_types=1);

/**
 * Taxa condominial mensal vinculada a uma unidade.
 */
class TaxaCondominial
{
    public const STATUS_PENDENTE   = 'pendente';
    public const STATUS_AGUARDANDO = 'aguardando';
    public const STATUS_PAGO       = 'pago';
    public const STATUS_VENCIDO    = 'vencido';
    public const STATUS_ISENTO     = 'isento';

    public function __construct(
        public readonly ?int    $id,
        public int              $unidadeId,
        public string           $competencia,   // formato: 2025-01
        public float            $valor,
        public string           $vencimento,    // formato: Y-m-d
        public string           $status         = self::STATUS_PENDENTE,
        public ?string          $dataPagamento  = null,
        public ?string          $formaPagamento = null,
        public ?string          $comprovante    = null,
        public ?string          $observacao     = null,
        public ?string          $criadoEm      = null,
        // Dados extras via JOIN
        public ?string          $identificacaoUnidade = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:                   isset($dados['id']) ? (int) $dados['id'] : null,
            unidadeId:            (int) $dados['unidade_id'],
            competencia:          $dados['competencia'],
            valor:                (float) $dados['valor'],
            vencimento:           $dados['vencimento'],
            status:               $dados['status'] ?? self::STATUS_PENDENTE,
            dataPagamento:        $dados['data_pagamento']   ?? null,
            formaPagamento:       $dados['forma_pagamento']  ?? null,
            comprovante:          $dados['comprovante'] ?? null,
            observacao:           $dados['observacao'] ?? null,
            criadoEm:             $dados['criado_em'] ?? null,
            identificacaoUnidade: $dados['identificacao_unidade'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'unidade_id'     => $this->unidadeId,
            'competencia'    => $this->competencia,
            'valor'          => $this->valor,
            'vencimento'     => $this->vencimento,
            'status'          => $this->status,
            'data_pagamento'  => $this->dataPagamento,
            'forma_pagamento' => $this->formaPagamento,
            'comprovante'     => $this->comprovante,
            'observacao'      => $this->observacao,
        ];
    }

    public function estaPago(): bool
    {
        return $this->status === self::STATUS_PAGO;
    }

    public function estaAguardando(): bool
    {
        return $this->status === self::STATUS_AGUARDANDO;
    }

    public function estaVencido(): bool
    {
        return $this->status === self::STATUS_VENCIDO
            || ($this->status === self::STATUS_PENDENTE && $this->vencimento < date('Y-m-d'));
    }

    /** Formata competência 2025-01 → Janeiro/2025 */
    public function competenciaFormatada(): string
    {
        [$ano, $mes] = explode('-', $this->competencia);
        $meses = [
            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
            '04' => 'Abril',   '05' => 'Maio',      '06' => 'Junho',
            '07' => 'Julho',   '08' => 'Agosto',    '09' => 'Setembro',
            '10' => 'Outubro', '11' => 'Novembro',  '12' => 'Dezembro',
        ];
        return ($meses[$mes] ?? $mes) . "/{$ano}";
    }
}
