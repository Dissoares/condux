<?php

declare(strict_types=1);

class Prestadora
{
    public function __construct(
        public readonly ?int $id,
        public string        $nome,
        public ?string       $cnpj      = null,
        public ?string       $contato   = null,
        public ?string       $telefone  = null,
        public ?string       $email     = null,
        public bool          $ativo     = true,
        public ?string       $criadoEm = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:       isset($d['id'])      ? (int) $d['id'] : null,
            nome:     $d['nome'],
            cnpj:     $d['cnpj']     ?? null,
            contato:  $d['contato']  ?? null,
            telefone: $d['telefone'] ?? null,
            email:    $d['email']    ?? null,
            ativo:    isset($d['ativo']) ? (bool) $d['ativo'] : true,
            criadoEm: $d['criado_em'] ?? null,
        );
    }
}
