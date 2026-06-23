<?php

declare(strict_types=1);

class FuncionarioOcorrencia
{
    public static array $rotulosTipo = [
        'folga'        => 'Folga',
        'ferias'       => 'Férias',
        'falta'        => 'Falta',
        'atestado'     => 'Atestado',
        'adiantamento' => 'Adiantamento',
    ];

    public static array $corTipo = [
        'folga'        => 'info',
        'ferias'       => 'success',
        'falta'        => 'danger',
        'atestado'     => 'warning',
        'adiantamento' => 'primary',
    ];

    public function __construct(
        public readonly ?int $id,
        public int           $funcionarioId,
        public string        $tipo,
        public string        $dataInicio,
        public ?string       $dataFim      = null,
        public ?float        $valor        = null,
        public ?string       $justificativa = null,
        public ?string       $anexo        = null,
        public ?string       $nomeOriginal = null,
        public string        $status       = 'aprovado',
        public ?string       $criadoEm    = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:            isset($d['id']) ? (int) $d['id'] : null,
            funcionarioId: (int) $d['funcionario_id'],
            tipo:          $d['tipo'],
            dataInicio:    $d['data_inicio'],
            dataFim:       $d['data_fim']       ?? null,
            valor:         isset($d['valor']) && $d['valor'] !== null ? (float) $d['valor'] : null,
            justificativa: $d['justificativa']  ?? null,
            anexo:         $d['anexo']          ?? null,
            nomeOriginal:  $d['nome_original']  ?? null,
            status:        $d['status']         ?? 'aprovado',
            criadoEm:      $d['criado_em']      ?? null,
        );
    }

    public function rotuloTipo(): string
    {
        return self::$rotulosTipo[$this->tipo] ?? $this->tipo;
    }

    public function cor(): string
    {
        return self::$corTipo[$this->tipo] ?? 'secondary';
    }

    public function diasDuracao(): ?int
    {
        if (!$this->dataFim) return null;
        $diff = (new DateTime($this->dataInicio))->diff(new DateTime($this->dataFim));
        return $diff->days + 1;
    }
}
