<?php

declare(strict_types=1);

class Conta
{
    public static array $categorias = [
        'agua'         => ['Água',              'bi-droplet-fill',        'info'],
        'energia'      => ['Energia elétrica',  'bi-lightning-fill',      'warning'],
        'gas'          => ['Gás',               'bi-fire',                'orange'],
        'telefone'     => ['Telefone',          'bi-telephone-fill',      'primary'],
        'internet'     => ['Internet',          'bi-wifi',                'primary'],
        'limpeza'      => ['Produtos limpeza',  'bi-stars',               'success'],
        'manutencao'   => ['Manutenção',        'bi-tools',               'secondary'],
        'seguranca'    => ['Segurança',         'bi-shield-fill',         'dark'],
        'seguro'       => ['Seguro',            'bi-umbrella-fill',       'teal'],
        'taxa'         => ['Taxa/Imposto',      'bi-bank',                'danger'],
        'outros'       => ['Outros',            'bi-three-dots',          'secondary'],
    ];

    public function __construct(
        public readonly ?int $id,
        public string        $descricao,
        public string        $categoria     = 'outros',
        public string        $competencia   = '',
        public ?string       $fornecedor    = null,
        public float         $valor         = 0.0,
        public ?string       $dataVencimento = null,
        public ?string       $dataPagamento  = null,
        public string        $status         = 'pendente',
        public ?string       $observacoes    = null,
        public ?string       $anexo          = null,
        public ?string       $nomeOriginal   = null,
        public ?string       $criadoEm       = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:             isset($d['id']) ? (int) $d['id'] : null,
            descricao:      $d['descricao'],
            categoria:      $d['categoria']       ?? 'outros',
            competencia:    $d['competencia'],
            fornecedor:     $d['fornecedor']      ?? null,
            valor:          (float) $d['valor'],
            dataVencimento: $d['data_vencimento'] ?? null,
            dataPagamento:  $d['data_pagamento']  ?? null,
            status:         $d['status']          ?? 'pendente',
            observacoes:    $d['observacoes']     ?? null,
            anexo:          $d['anexo']           ?? null,
            nomeOriginal:   $d['nome_original']   ?? null,
            criadoEm:       $d['criado_em']       ?? null,
        );
    }

    public function estaAtrasada(): bool
    {
        return $this->status === 'pendente'
            && $this->dataVencimento !== null
            && date('Y-m-d') > $this->dataVencimento;
    }

    public function rotuloCategoria(): string
    {
        return self::$categorias[$this->categoria][0] ?? ucfirst($this->categoria);
    }

    public function iconeCategoria(): string
    {
        return self::$categorias[$this->categoria][1] ?? 'bi-receipt';
    }

    public function corCategoria(): string
    {
        return self::$categorias[$this->categoria][2] ?? 'secondary';
    }
}
