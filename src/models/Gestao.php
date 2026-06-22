<?php

declare(strict_types=1);

class Gestao
{
    public static array $cargosRotulo = [
        'sindico'    => 'Síndico',
        'subsindico' => 'Subsíndico',
        'conselheiro'=> 'Conselheiro',
        'suplente'   => 'Suplente',
    ];

    public static array $cargosIcone = [
        'sindico'    => 'bi-person-badge',
        'subsindico' => 'bi-person-check',
        'conselheiro'=> 'bi-people',
        'suplente'   => 'bi-person-dash',
    ];

    public ?int    $id;
    public string  $descricao;
    public string  $inicio;
    public ?string $fim;
    public string  $status;
    public ?string $observacoes;
    public string  $criadoEm;

    /** @var array<int,array{id:int,nome:string,email:string,cargo:string}> */
    public array $membros = [];

    public function __construct(
        ?int    $id,
        string  $descricao,
        string  $inicio,
        ?string $fim,
        string  $status,
        ?string $observacoes,
        string  $criadoEm,
    ) {
        $this->id          = $id;
        $this->descricao   = $descricao;
        $this->inicio      = $inicio;
        $this->fim         = $fim;
        $this->status      = $status;
        $this->observacoes = $observacoes;
        $this->criadoEm    = $criadoEm;
    }

    public static function fromArray(array $l): self
    {
        return new self(
            id:          isset($l['id'])          ? (int) $l['id'] : null,
            descricao:   $l['descricao']           ?? '',
            inicio:      $l['inicio']              ?? '',
            fim:         $l['fim']                 ?? null,
            status:      $l['status']              ?? 'ativa',
            observacoes: $l['observacoes']         ?? null,
            criadoEm:    $l['criado_em']           ?? '',
        );
    }

    public function rotuloStatus(): string
    {
        return $this->status === 'ativa' ? 'Ativa' : 'Encerrada';
    }

    public function sindico(): ?array
    {
        foreach ($this->membros as $m) {
            if ($m['cargo'] === 'sindico') return $m;
        }
        return null;
    }

    public function subsindico(): ?array
    {
        foreach ($this->membros as $m) {
            if ($m['cargo'] === 'subsindico') return $m;
        }
        return null;
    }

    /** @return array[] */
    public function conselheiros(): array
    {
        return array_values(array_filter($this->membros, fn($m) => $m['cargo'] === 'conselheiro'));
    }

    /** @return array[] */
    public function suplentes(): array
    {
        return array_values(array_filter($this->membros, fn($m) => $m['cargo'] === 'suplente'));
    }

    public function ativa(): bool
    {
        return $this->status === 'ativa';
    }

    public function periodo(): string
    {
        $ini = dataBR($this->inicio);
        return $this->fim ? "{$ini} – " . dataBR($this->fim) : "Desde {$ini}";
    }
}
