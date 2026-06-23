<?php

declare(strict_types=1);

class Funcionario
{
    public function __construct(
        public readonly ?int $id,
        public string        $nome,
        public string        $cargo,
        public ?string       $cpf          = null,
        public ?string       $departamento = null,
        public ?string       $telefone     = null,
        public ?string       $email        = null,
        public ?float        $salario      = null,
        public ?string       $dataAdmissao = null,
        public ?string       $dataDemissao = null,
        public ?string       $observacoes  = null,
        public bool          $ativo        = true,
        public ?string       $criadoEm     = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            id:           isset($d['id'])      ? (int) $d['id'] : null,
            nome:         $d['nome'],
            cargo:        $d['cargo'],
            cpf:          $d['cpf']          ?? null,
            departamento: $d['departamento'] ?? null,
            telefone:     $d['telefone']     ?? null,
            email:        $d['email']        ?? null,
            salario:      isset($d['salario']) && $d['salario'] !== null ? (float) $d['salario'] : null,
            dataAdmissao: $d['data_admissao'] ?? null,
            dataDemissao: $d['data_demissao'] ?? null,
            observacoes:  $d['observacoes']  ?? null,
            ativo:        isset($d['ativo']) ? (bool) $d['ativo'] : true,
            criadoEm:     $d['criado_em']   ?? null,
        );
    }
}
