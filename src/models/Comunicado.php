<?php

declare(strict_types=1);

class Comunicado
{
    public static array $tipos = [
        'aviso'       => ['Aviso',       'bi-megaphone-fill',     'secondary'],
        'urgente'     => ['Urgente',     'bi-exclamation-triangle-fill', 'danger'],
        'informativo' => ['Informativo', 'bi-info-circle-fill',   'primary'],
        'assembleia'  => ['Assembleia',  'bi-people-fill',        'purple'],
        'manutencao'  => ['Manutenção',  'bi-tools',              'warning'],
    ];

    public function __construct(
        public readonly ?int $id,
        public string        $titulo,
        public string        $conteudo,
        public string        $tipo            = 'aviso',
        public ?int          $publicadoPor    = null,
        public string        $dataPublicacao  = '',
        public ?string       $dataExpiracao   = null,
        public bool          $ativo           = true,
        public ?string       $criadoEm        = null,
        public ?string       $nomeAutor       = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:             isset($d['id']) ? (int) $d['id'] : null,
            titulo:         $d['titulo'],
            conteudo:       $d['conteudo'],
            tipo:           $d['tipo']             ?? 'aviso',
            publicadoPor:   isset($d['publicado_por']) ? (int) $d['publicado_por'] : null,
            dataPublicacao: $d['data_publicacao'],
            dataExpiracao:  $d['data_expiracao']   ?? null,
            ativo:          (bool) ($d['ativo']    ?? true),
            criadoEm:       $d['criado_em']         ?? null,
            nomeAutor:      $d['nome_autor']         ?? null,
        );
    }

    public function estaAtivo(): bool
    {
        if (!$this->ativo) return false;
        if ($this->dataExpiracao && date('Y-m-d') > $this->dataExpiracao) return false;
        return true;
    }

    public function rotulo(): string  { return self::$tipos[$this->tipo][0] ?? ucfirst($this->tipo); }
    public function icone(): string   { return self::$tipos[$this->tipo][1] ?? 'bi-bell'; }
    public function cor(): string     { return self::$tipos[$this->tipo][2] ?? 'secondary'; }
}
