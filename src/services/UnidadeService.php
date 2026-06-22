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
            id:             isset($dados['id']) && $dados['id'] ? (int) $dados['id'] : null,
            numero:         trim($dados['numero']),
            bloco:          !empty($dados['bloco'])      ? trim($dados['bloco'])  : null,
            andar:          !empty($dados['andar'])      ? (int) $dados['andar']  : null,
            descricao:      !empty($dados['descricao'])  ? trim($dados['descricao']) : null,
            tipoOcupacao:   $tipoOcupacao,
            proprietarioId: !empty($dados['proprietario_id']) ? (int) $dados['proprietario_id'] : null,
            inquilinoId:    !empty($dados['inquilino_id'])    ? (int) $dados['inquilino_id']    : null,
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

    public function vincularPorUsuarioId(int $unidadeId, int $usuarioId, string $dataEntrada, bool $responsavel): void
    {
        if ($responsavel) {
            $this->removerResponsavelAnterior($unidadeId);
        }
        $morador = new Morador(
            id:          null,
            usuarioId:   $usuarioId,
            unidadeId:   $unidadeId,
            responsavel: $responsavel,
            dataEntrada: $dataEntrada ?: date('Y-m-d'),
        );
        $this->moradorRepository->salvar($morador);
    }

    /** @return array[] */
    public function pesquisarCondominios(string $termo): array
    {
        return $this->usuarioRepository->pesquisarMoradores($termo);
    }

    /** @return array[] Todos os moradores cadastrados (para seleção em formulários) */
    public function listarCondominios(): array
    {
        return $this->usuarioRepository->listarMoradoresComUnidade();
    }

    /**
     * Sincroniza os condôminos de uma unidade: adiciona os novos, remove os que saíram.
     * @param int[] $usuarioIds IDs dos usuários que devem estar ativos nesta unidade
     */
    public function sincronizarMoradoresDaUnidade(int $unidadeId, array $usuarioIds): void
    {
        $atuais    = $this->moradorRepository->listarAtivosPorUnidade($unidadeId);
        $idsAtuais = array_map(fn($m) => $m->usuarioId, $atuais);

        // Desativar os que não estão mais na lista
        foreach ($atuais as $morador) {
            if (!in_array($morador->usuarioId, $usuarioIds, true)) {
                $this->moradorRepository->desativar($morador->id);
            }
        }

        // Adicionar os novos
        foreach ($usuarioIds as $usuarioId) {
            if (!in_array($usuarioId, $idsAtuais, true)) {
                $morador = new Morador(
                    id:          null,
                    usuarioId:   $usuarioId,
                    unidadeId:   $unidadeId,
                    responsavel: false,
                    dataEntrada: date('Y-m-d'),
                );
                $this->moradorRepository->salvar($morador);
            }
        }
    }

    /**
     * Sincroniza as unidades de um condômino: adiciona as novas, remove as que saíram.
     * @param int[] $unidadeIds IDs das unidades em que o condômino deve estar ativo
     */
    public function sincronizarUnidadesDoCondomino(int $usuarioId, array $unidadeIds): void
    {
        $atuais    = $this->moradorRepository->listarPorUsuario($usuarioId);
        $idsAtuais = array_map(fn($m) => $m->unidadeId, $atuais);

        // Desativar vínculos removidos
        foreach ($atuais as $morador) {
            if (!in_array($morador->unidadeId, $unidadeIds, true)) {
                $this->moradorRepository->desativar($morador->id);
            }
        }

        // Criar vínculos novos
        foreach ($unidadeIds as $unidadeId) {
            if (!in_array($unidadeId, $idsAtuais, true)) {
                $morador = new Morador(
                    id:          null,
                    usuarioId:   $usuarioId,
                    unidadeId:   $unidadeId,
                    responsavel: false,
                    dataEntrada: date('Y-m-d'),
                );
                $this->moradorRepository->salvar($morador);
            }
        }
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
