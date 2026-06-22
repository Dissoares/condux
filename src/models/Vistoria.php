<?php

declare(strict_types=1);

class Vistoria
{
    public const STATUS_AGENDADA  = 'agendada';
    public const STATUS_REALIZADA = 'realizada';
    public const STATUS_CANCELADA = 'cancelada';

    public static array $tiposRotulo = [
        'predial'   => 'Inspeção Predial',
        'bombeiros' => 'Bombeiros / AVCB',
        'elevador'  => 'Elevador',
        'sanitaria' => 'Vigilância Sanitária',
        'orcamento' => 'Visita para Orçamento',
        'unidade'   => 'Vistoria de Unidade',
        'outro'     => 'Outro',
    ];

    public static array $tiposIcone = [
        'predial'   => 'bi-building-check',
        'bombeiros' => 'bi-fire',
        'elevador'  => 'bi-arrow-up-square',
        'sanitaria' => 'bi-shield-check',
        'orcamento' => 'bi-calculator',
        'unidade'   => 'bi-house-check',
        'outro'     => 'bi-clipboard2',
    ];

    public static array $resultadosRotulo = [
        'aprovado'    => 'Aprovado',
        'reprovado'   => 'Reprovado',
        'condicional' => 'Condicional',
    ];

    /** @var array<array{id:int,tipo:string,caminho:string,nome_original:string,enviado_em:string}> */
    public array $anexos = [];

    public function __construct(
        public readonly ?int $id,
        public string        $dataVistoria,
        public string        $status       = self::STATUS_AGENDADA,
        public string        $tipo         = 'predial',
        public ?string       $descricao    = null,
        public ?string       $categoria    = null,
        public ?int          $unidadeId    = null,
        public ?int          $responsavelId = null,
        public ?int          $prestadoraId  = null,
        public ?string       $numeroDocumento = null,
        public ?string       $validade     = null,
        public ?string       $resultado    = null,
        public ?string       $nomeResponsavel = null,
        public ?string       $nomePrestadora  = null,
        public ?string       $identificacaoUnidade = null,
        public ?string       $criadoEm    = null,
    ) {}

    public static function fromArray(array $d): self
    {
        $v = new self(
            id:                   isset($d['id'])             ? (int) $d['id'] : null,
            dataVistoria:         $d['data_vistoria'],
            status:               $d['status']               ?? self::STATUS_AGENDADA,
            tipo:                 $d['tipo']                 ?? 'predial',
            descricao:            $d['descricao']            ?? null,
            categoria:            $d['categoria']            ?? null,
            unidadeId:            isset($d['unidade_id'])    ? (int) $d['unidade_id']    : null,
            responsavelId:        isset($d['responsavel_id'])? (int) $d['responsavel_id']: null,
            prestadoraId:         isset($d['prestadora_id']) ? (int) $d['prestadora_id'] : null,
            numeroDocumento:      $d['numero_documento']     ?? null,
            validade:             $d['validade']             ?? null,
            resultado:            $d['resultado']            ?? null,
            nomeResponsavel:      $d['nome_responsavel']     ?? null,
            nomePrestadora:       $d['nome_prestadora']      ?? null,
            identificacaoUnidade: $d['identificacao_unidade'] ?? null,
            criadoEm:             $d['criado_em']            ?? null,
        );
        return $v;
    }

    public function rotuloTipo(): string
    {
        return self::$tiposRotulo[$this->tipo] ?? $this->tipo;
    }

    public function icone(): string
    {
        return self::$tiposIcone[$this->tipo] ?? 'bi-clipboard2';
    }

    public function rotuloStatus(): string
    {
        return match ($this->status) {
            'realizada' => 'Realizada',
            'cancelada' => 'Cancelada',
            default     => 'Agendada',
        };
    }

    public function rotuloResultado(): string
    {
        return self::$resultadosRotulo[$this->resultado ?? ''] ?? '—';
    }

    public function validadeProxima(): bool
    {
        if (!$this->validade) return false;
        $diff = (new DateTimeImmutable($this->validade))->diff(new DateTimeImmutable())->days;
        return $diff <= 60 && $this->validade >= date('Y-m-d');
    }

    public function validadeVencida(): bool
    {
        if (!$this->validade) return false;
        return $this->validade < date('Y-m-d');
    }
}
