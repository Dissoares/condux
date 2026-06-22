<?php

declare(strict_types=1);

/**
 * Representa uma unidade (apartamento/sala) do condomínio.
 */
class Unidade
{
    public function __construct(
        public readonly ?int $id,
        public string        $numero,
        public ?string       $bloco                = null,
        public ?int          $andar                = null,
        public ?string       $descricao            = null,
        public string        $tipoOcupacao         = 'proprio',
        public ?string       $nomeProprietario     = null,
        public ?string       $telefoneProprietario = null,
        public ?string       $emailProprietario    = null,
        public ?string       $nomeInquilino        = null,
        public ?string       $telefoneInquilino    = null,
        public ?string       $emailInquilino       = null,
        public bool          $ativo                = true,
        public ?string       $criadoEm             = null,
        // Dados extras via JOIN — não pertencem à tabela
        public ?string       $nomeResponsavel      = null,
        public ?string       $statusTaxaAtual      = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:                   isset($dados['id']) ? (int) $dados['id'] : null,
            numero:               $dados['numero'],
            bloco:                $dados['bloco']                ?? null,
            andar:                isset($dados['andar']) ? (int) $dados['andar'] : null,
            descricao:            $dados['descricao']            ?? null,
            tipoOcupacao:         $dados['tipo_ocupacao']        ?? 'proprio',
            nomeProprietario:     $dados['nome_proprietario']    ?? null,
            telefoneProprietario: $dados['telefone_proprietario'] ?? null,
            emailProprietario:    $dados['email_proprietario']   ?? null,
            nomeInquilino:        $dados['nome_inquilino']       ?? null,
            telefoneInquilino:    $dados['telefone_inquilino']   ?? null,
            emailInquilino:       $dados['email_inquilino']      ?? null,
            ativo:                (bool) ($dados['ativo']        ?? true),
            criadoEm:             $dados['criado_em']            ?? null,
            nomeResponsavel:      $dados['nome_responsavel']     ?? null,
            statusTaxaAtual:      $dados['status_taxa_atual']    ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'numero'               => $this->numero,
            'bloco'                => $this->bloco,
            'andar'                => $this->andar,
            'descricao'            => $this->descricao,
            'tipo_ocupacao'        => $this->tipoOcupacao,
            'nome_proprietario'    => $this->nomeProprietario,
            'telefone_proprietario'=> $this->telefoneProprietario,
            'email_proprietario'   => $this->emailProprietario,
            'nome_inquilino'       => $this->nomeInquilino,
            'telefone_inquilino'   => $this->telefoneInquilino,
            'email_inquilino'      => $this->emailInquilino,
            'ativo'                => (int) $this->ativo,
        ];
    }

    public function identificacao(): string
    {
        if ($this->bloco) {
            return "Bloco {$this->bloco} — Apto {$this->numero}";
        }
        return "Apto {$this->numero}";
    }

    public function estaAlugada(): bool
    {
        return $this->tipoOcupacao === 'alugado';
    }
}
