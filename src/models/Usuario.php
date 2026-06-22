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
        public bool             $ativo          = true,
        public ?string          $criadoEm       = null,
        public ?string          $telefone       = null,
        public ?string          $cpf            = null,
        public ?string          $dataNascimento = null,
        public ?string          $observacoes    = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:             isset($dados['id']) ? (int) $dados['id'] : null,
            nome:           $dados['nome'],
            email:          $dados['email'],
            senha:          $dados['senha'],
            perfil:         $dados['perfil'],
            ativo:          (bool) ($dados['ativo'] ?? true),
            criadoEm:       $dados['criado_em']       ?? null,
            telefone:       $dados['telefone']         ?? null,
            cpf:            $dados['cpf']              ?? null,
            dataNascimento: $dados['data_nascimento']  ?? null,
            observacoes:    $dados['observacoes']      ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'nome'            => $this->nome,
            'email'           => $this->email,
            'senha'           => $this->senha,
            'perfil'          => $this->perfil,
            'ativo'           => (int) $this->ativo,
            'criado_em'       => $this->criadoEm,
            'telefone'        => $this->telefone,
            'cpf'             => $this->cpf,
            'data_nascimento' => $this->dataNascimento,
            'observacoes'     => $this->observacoes,
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
