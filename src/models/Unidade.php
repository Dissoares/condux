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
        public ?string       $bloco       = null,
        public ?int          $andar       = null,
        public ?string       $descricao   = null,
        public bool          $ativo       = true,
        public ?string       $criadoEm   = null,
        // Dados extras via JOIN — não pertencem à tabela
        public ?string       $nomeResponsavel = null,
        public ?string       $statusTaxaAtual = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            id:               isset($dados['id']) ? (int) $dados['id'] : null,
            numero:           $dados['numero'],
            bloco:            $dados['bloco']     ?? null,
            andar:            isset($dados['andar']) ? (int) $dados['andar'] : null,
            descricao:        $dados['descricao'] ?? null,
            ativo:            (bool) ($dados['ativo'] ?? true),
            criadoEm:         $dados['criado_em'] ?? null,
            nomeResponsavel:  $dados['nome_responsavel'] ?? null,
            statusTaxaAtual:  $dados['status_taxa_atual'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'numero'    => $this->numero,
            'bloco'     => $this->bloco,
            'andar'     => $this->andar,
            'descricao' => $this->descricao,
            'ativo'     => (int) $this->ativo,
        ];
    }

    public function identificacao(): string
    {
        if ($this->bloco) {
            return "Bloco {$this->bloco} — Apto {$this->numero}";
        }
        return "Apto {$this->numero}";
    }
}
