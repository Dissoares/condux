<?php

declare(strict_types=1);

/**
 * Projeto do portal da transparência.
 */
class Projeto
{
    public const STATUS_PENDENTE     = 'pendente';
    public const STATUS_APROVADO     = 'aprovado';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_CONCLUIDO    = 'concluido';
    public const STATUS_CANCELADO    = 'cancelado';

    public static array $rotulosStatus = [
        self::STATUS_PENDENTE     => 'Pendente',
        self::STATUS_APROVADO     => 'Aprovado',
        self::STATUS_EM_ANDAMENTO => 'Em andamento',
        self::STATUS_CONCLUIDO    => 'Concluído',
        self::STATUS_CANCELADO    => 'Cancelado',
    ];

    public function __construct(
        public readonly ?int    $id,
        public string           $nome,
        public string           $status          = self::STATUS_PENDENTE,
        public ?string          $descricao       = null,
        public ?string          $idealizador     = null,
        public ?int             $responsavelId   = null,
        public ?int             $prestadoraId    = null,
        public ?float           $valorEstimado   = null,
        public ?float           $valorRealizado  = null,
        public ?string          $dataInicio      = null,
        public ?string          $dataConclusao   = null,
        public ?string          $criadoEm        = null,
        // Dados extras via JOIN
        public ?string          $nomeResponsavel    = null,
        public ?string          $nomePrestadora     = null,
        public ?string          $prestadoraCnpj     = null,
        public ?string          $prestadoraContato  = null,
        public ?string          $prestadoraTelefone = null,
        public ?string          $prestadoraEmail    = null,
        public array            $anexos             = [],
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:              isset($dados['id']) ? (int) $dados['id'] : null,
            nome:            $dados['nome'],
            status:          $dados['status']           ?? self::STATUS_PENDENTE,
            descricao:       $dados['descricao']        ?? null,
            idealizador:     $dados['idealizador']      ?? null,
            responsavelId:   isset($dados['responsavel_id'])  ? (int) $dados['responsavel_id']  : null,
            prestadoraId:    isset($dados['prestadora_id'])   ? (int) $dados['prestadora_id']   : null,
            valorEstimado:   isset($dados['valor_estimado'])  ? (float) $dados['valor_estimado']  : null,
            valorRealizado:  isset($dados['valor_realizado']) ? (float) $dados['valor_realizado'] : null,
            dataInicio:      $dados['data_inicio']      ?? null,
            dataConclusao:   $dados['data_conclusao']   ?? null,
            criadoEm:        $dados['criado_em']        ?? null,
            nomeResponsavel:    $dados['nome_responsavel']    ?? null,
            nomePrestadora:     $dados['nome_prestadora']     ?? null,
            prestadoraCnpj:     $dados['prestadora_cnpj']     ?? null,
            prestadoraContato:  $dados['prestadora_contato']  ?? null,
            prestadoraTelefone: $dados['prestadora_telefone'] ?? null,
            prestadoraEmail:    $dados['prestadora_email']    ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'nome'            => $this->nome,
            'descricao'       => $this->descricao,
            'idealizador'     => $this->idealizador,
            'responsavel_id'  => $this->responsavelId,
            'prestadora_id'   => $this->prestadoraId,
            'status'          => $this->status,
            'valor_estimado'  => $this->valorEstimado,
            'valor_realizado' => $this->valorRealizado,
            'data_inicio'     => $this->dataInicio,
            'data_conclusao'  => $this->dataConclusao,
        ];
    }

    public function rotuloStatus(): string
    {
        return self::$rotulosStatus[$this->status] ?? $this->status;
    }
}
