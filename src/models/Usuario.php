<?php

declare(strict_types=1);

/**
 * Representa um usuário do sistema.
 * Perfis: sindico | subsindico | morador
 */
class Usuario
{
    public function __construct(
        public readonly ?int    $id,
        public string           $nome,
        public string           $email,
        public string           $senha,
        public string           $perfil,
        public bool             $ativo      = true,
        public ?string          $criadoEm  = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:       isset($dados['id']) ? (int) $dados['id'] : null,
            nome:     $dados['nome'],
            email:    $dados['email'],
            senha:    $dados['senha'],
            perfil:   $dados['perfil'],
            ativo:    (bool) ($dados['ativo'] ?? true),
            criadoEm: $dados['criado_em'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'nome'      => $this->nome,
            'email'     => $this->email,
            'senha'     => $this->senha,
            'perfil'    => $this->perfil,
            'ativo'     => (int) $this->ativo,
            'criado_em' => $this->criadoEm,
        ];
    }

    public function ehAdmin(): bool
    {
        return in_array($this->perfil, ['sindico', 'subsindico'], true);
    }

    public function ehSindico(): bool
    {
        return $this->perfil === 'sindico';
    }
}
