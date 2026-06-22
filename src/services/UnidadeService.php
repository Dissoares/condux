<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../repository/MoradorRepository.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';
require_once __DIR__ . '/../models/Unidade.php';
require_once __DIR__ . '/../models/Morador.php';
require_once __DIR__ . '/../models/Usuario.php';

class UnidadeService
{
    public function __construct(
        private readonly UnidadeRepository  $unidadeRepository,
        private readonly MoradorRepository  $moradorRepository,
        private readonly UsuarioRepository  $usuarioRepository,
    ) {}

    /** @return Unidade[] */
    public function listarUnidades(): array
    {
        return $this->unidadeRepository->listarAtivas();
    }

    public function buscarUnidade(int $id): ?Unidade
    {
        return $this->unidadeRepository->buscarPorId($id);
    }

    public function salvarUnidade(array $dados): int
    {
        $this->validarDadosUnidade($dados);

        $tipoOcupacao = in_array($dados['tipo_ocupacao'] ?? '', ['proprio','alugado'])
            ? $dados['tipo_ocupacao']
            : 'proprio';

        $unidade = new Unidade(
            id:                   isset($dados['id']) && $dados['id'] ? (int) $dados['id'] : null,
            numero:               trim($dados['numero']),
            bloco:                !empty($dados['bloco'])                 ? trim($dados['bloco'])                 : null,
            andar:                !empty($dados['andar'])                 ? (int) $dados['andar']                 : null,
            descricao:            !empty($dados['descricao'])             ? trim($dados['descricao'])             : null,
            tipoOcupacao:         $tipoOcupacao,
            nomeProprietario:     !empty($dados['nome_proprietario'])     ? trim($dados['nome_proprietario'])     : null,
            telefoneProprietario: !empty($dados['telefone_proprietario']) ? trim($dados['telefone_proprietario']) : null,
            emailProprietario:    !empty($dados['email_proprietario'])    ? trim($dados['email_proprietario'])    : null,
            nomeInquilino:        !empty($dados['nome_inquilino'])        ? trim($dados['nome_inquilino'])        : null,
            telefoneInquilino:    !empty($dados['telefone_inquilino'])    ? trim($dados['telefone_inquilino'])    : null,
            emailInquilino:       !empty($dados['email_inquilino'])       ? trim($dados['email_inquilino'])       : null,
        );

        return $this->unidadeRepository->salvar($unidade);
    }

    /**
     * Vincula um usuário existente ou cria um novo morador para a unidade.
     * Garante que apenas um responsável financeiro por unidade seja marcado.
     */
    public function vincularMorador(int $unidadeId, array $dados): int
    {
        // Busca usuário existente ou cria um novo
        $usuario = $this->usuarioRepository->buscarPorEmail($dados['email']);

        if ($usuario === null) {
            $this->validarDadosNovoMorador($dados);
            $usuario = new Usuario(
                id:     null,
                nome:   trim($dados['nome']),
                email:  trim($dados['email']),
                senha:  password_hash($dados['senha'] ?? uniqid('', true), PASSWORD_BCRYPT, ['cost' => 12]),
                perfil: 'morador',
            );
            $this->usuarioRepository->salvar($usuario);
            // Recarrega com ID
            $usuario = $this->usuarioRepository->buscarPorEmail($dados['email']);
        }

        $ehResponsavel = !empty($dados['responsavel']);

        // Se for responsável, remove o anterior
        if ($ehResponsavel) {
            $this->removerResponsavelAnterior($unidadeId);
        }

        $morador = new Morador(
            id:          null,
            usuarioId:   $usuario->id,
            unidadeId:   $unidadeId,
            responsavel: $ehResponsavel,
            dataEntrada: $dados['data_entrada'] ?? date('Y-m-d'),
        );

        return $this->moradorRepository->salvar($morador);
    }

    public function desvincularMorador(int $moradorId): void
    {
        $this->moradorRepository->desativar($moradorId);
    }

    /** @return Morador[] */
    public function listarMoradoresDaUnidade(int $unidadeId): array
    {
        return $this->moradorRepository->listarAtivosPorUnidade($unidadeId);
    }

    private function removerResponsavelAnterior(int $unidadeId): void
    {
        $moradores = $this->moradorRepository->listarAtivosPorUnidade($unidadeId);
        foreach ($moradores as $morador) {
            if ($morador->responsavel) {
                $morador->responsavel = false;
                $this->moradorRepository->salvar($morador);
            }
        }
    }

    private function validarDadosUnidade(array $dados): void
    {
        if (empty($dados['numero'])) {
            throw new InvalidArgumentException('Número da unidade é obrigatório.');
        }
    }

    private function validarDadosNovoMorador(array $dados): void
    {
        if (empty($dados['nome'])) {
            throw new InvalidArgumentException('Nome do morador é obrigatório.');
        }
        if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }
    }
}
