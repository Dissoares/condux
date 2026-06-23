<?php

declare(strict_types=1);

class Ticket
{
    public static array $rotuloCategorias = [
        'sugestao'    => 'Sugestão',
        'reclamacao'  => 'Reclamação',
        'manutencao'  => 'Manutenção',
        'informacao'  => 'Informação',
        'outro'       => 'Outro',
    ];

    public static array $rotuloStatus = [
        'aberto'       => 'Aberto',
        'em_andamento' => 'Em andamento',
        'resolvido'    => 'Resolvido',
        'fechado'      => 'Fechado',
    ];

    public static array $rotuloPrioridade = [
        'baixa'   => 'Baixa',
        'normal'  => 'Normal',
        'alta'    => 'Alta',
        'urgente' => 'Urgente',
    ];

    public function __construct(
        public readonly ?int $id,
        public string        $titulo,
        public string        $descricao,
        public string        $categoria     = 'outro',
        public string        $status        = 'aberto',
        public string        $prioridade    = 'normal',
        public int           $usuarioId     = 0,
        public ?int          $responsavelId = null,
        public ?string       $criadoEm      = null,
        public ?string       $atualizadoEm  = null,
        // Joins
        public ?string       $nomeUsuario        = null,
        public ?string       $fotoUsuario        = null,
        public ?string       $nomeResponsavel    = null,
        public ?int          $totalMensagens     = null,
        public ?string       $perfilUltimaMsg    = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:              (int) $d['id'],
            titulo:          $d['titulo'],
            descricao:       $d['descricao'],
            categoria:       $d['categoria']      ?? 'outro',
            status:          $d['status']         ?? 'aberto',
            prioridade:      $d['prioridade']     ?? 'normal',
            usuarioId:       (int) $d['usuario_id'],
            responsavelId:   isset($d['responsavel_id']) ? (int) $d['responsavel_id'] : null,
            criadoEm:        $d['criado_em']      ?? null,
            atualizadoEm:    $d['atualizado_em']  ?? null,
            nomeUsuario:     $d['nome_usuario']   ?? null,
            fotoUsuario:     $d['foto_usuario']   ?? null,
            nomeResponsavel: $d['nome_responsavel'] ?? null,
            totalMensagens:  isset($d['total_mensagens']) ? (int) $d['total_mensagens'] : null,
            perfilUltimaMsg: $d['perfil_ultima_msg'] ?? null,
        );
    }

    public function rotuloCategoria(): string
    {
        return self::$rotuloCategorias[$this->categoria] ?? $this->categoria;
    }

    public function rotuloStatus(): string
    {
        return self::$rotuloStatus[$this->status] ?? $this->status;
    }

    public function rotuloPrioridade(): string
    {
        return self::$rotuloPrioridade[$this->prioridade] ?? $this->prioridade;
    }

    public function corStatus(): string
    {
        return match($this->status) {
            'aberto'       => 'primary',
            'em_andamento' => 'warning',
            'resolvido'    => 'success',
            'fechado'      => 'secondary',
            default        => 'secondary',
        };
    }

    public function corPrioridade(): string
    {
        return match($this->prioridade) {
            'urgente' => 'danger',
            'alta'    => 'warning',
            'normal'  => 'primary',
            'baixa'   => 'secondary',
            default   => 'secondary',
        };
    }

    public function iconeCategoria(): string
    {
        return match($this->categoria) {
            'sugestao'   => 'bi-lightbulb',
            'reclamacao' => 'bi-exclamation-circle',
            'manutencao' => 'bi-tools',
            'informacao' => 'bi-info-circle',
            default      => 'bi-chat-text',
        };
    }

    public function estaFechado(): bool
    {
        return in_array($this->status, ['resolvido', 'fechado'], true);
    }

    /** Ticket aguarda resposta do admin: sem msgs (status aberto) ou última msg é de morador */
    public function aguardaRespostaAdmin(): bool
    {
        if ($this->estaFechado()) return false;
        if ($this->status === 'aberto' && ($this->totalMensagens ?? 0) === 0) return true;
        return !in_array($this->perfilUltimaMsg, ['sindico', 'subsindico'], true)
               && $this->perfilUltimaMsg !== null;
    }
}
