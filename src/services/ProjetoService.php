<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/ProjetoRepository.php';
require_once __DIR__ . '/../models/Projeto.php';

class ProjetoService
{
    public function __construct(private readonly ProjetoRepository $projetoRepository) {}

    /** @return Projeto[] */
    public function listarTodos(?string $status = null): array
    {
        return $this->projetoRepository->listarTodos($status);
    }

    public function buscarProjeto(int $id): ?Projeto
    {
        return $this->projetoRepository->buscarPorId($id);
    }

    public function salvarProjeto(array $dados): int
    {
        $this->validarDadosProjeto($dados);

        $projeto = new Projeto(
            id:             isset($dados['id']) && $dados['id'] ? (int) $dados['id'] : null,
            nome:           trim($dados['nome']),
            status:         $dados['status']         ?? Projeto::STATUS_PENDENTE,
            descricao:      $dados['descricao']      ?? null,
            idealizador:    $dados['idealizador']    ?? null,
            responsavelId:  !empty($dados['responsavel_id'])  ? (int) $dados['responsavel_id']  : null,
            prestadoraId:   !empty($dados['prestadora_id'])   ? (int) $dados['prestadora_id']   : null,
            valorEstimado:  !empty($dados['valor_estimado'])  ? (float) $dados['valor_estimado']  : null,
            valorRealizado: !empty($dados['valor_realizado']) ? (float) $dados['valor_realizado'] : null,
            dataInicio:     $dados['data_inicio']    ?? null,
            dataConclusao:  $dados['data_conclusao'] ?? null,
        );

        return $this->projetoRepository->salvar($projeto);
    }

    public function atualizarStatus(int $projetoId, string $novoStatus): void
    {
        $statusValidos = array_keys(Projeto::$rotulosStatus);

        if (!in_array($novoStatus, $statusValidos, true)) {
            throw new InvalidArgumentException("Status inválido: {$novoStatus}");
        }

        $projeto = $this->projetoRepository->buscarPorId($projetoId);

        if ($projeto === null) {
            throw new InvalidArgumentException('Projeto não encontrado.');
        }

        $projeto->status = $novoStatus;
        $this->projetoRepository->salvar($projeto);
    }

    public function adicionarAnexo(int $projetoId, string $tipo, array $arquivoUpload): int
    {
        $tiposPermitidos = ['foto', 'video', 'nota_fiscal', 'documento'];

        if (!in_array($tipo, $tiposPermitidos, true)) {
            throw new InvalidArgumentException("Tipo de anexo inválido: {$tipo}");
        }

        $caminho      = $this->salvarArquivoAnexo($tipo, $arquivoUpload);
        $nomeOriginal = basename($arquivoUpload['name']);

        return $this->projetoRepository->salvarAnexo($projetoId, $tipo, $caminho, $nomeOriginal);
    }

    public function removerAnexo(int $anexoId): void
    {
        $caminho = $this->projetoRepository->removerAnexo($anexoId);

        if ($caminho !== null) {
            $config   = require __DIR__ . '/../../config/app.php';
            $arquivoFisico = $config['pasta_uploads'] . '/' . $caminho;
            if (file_exists($arquivoFisico)) {
                unlink($arquivoFisico);
            }
        }
    }

    private function salvarArquivoAnexo(string $tipo, array $arquivo): string
    {
        $config = require __DIR__ . '/../../config/app.php';

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = match ($tipo) {
            'foto'        => $config['extensoes_imagem'],
            'video'       => $config['extensoes_video'],
            'nota_fiscal' => $config['extensoes_documento'],
            'documento'   => $config['extensoes_documento'],
        };

        if (!in_array($extensao, $permitidas, true)) {
            throw new InvalidArgumentException("Extensão .{$extensao} não permitida para o tipo {$tipo}.");
        }

        if ($arquivo['size'] > $config['tamanho_maximo_upload']) {
            throw new InvalidArgumentException('Arquivo muito grande. Máximo 10 MB.');
        }

        $subpasta    = 'projetos/' . $tipo . 's';
        $nomeArquivo = $tipo . '_' . uniqid('', true) . '.' . $extensao;
        $destino     = $config['pasta_uploads'] . '/' . $subpasta . '/' . $nomeArquivo;

        if (!is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0755, true);
        }

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            throw new RuntimeException('Falha ao salvar o arquivo.');
        }

        return $subpasta . '/' . $nomeArquivo;
    }

    private function validarDadosProjeto(array $dados): void
    {
        if (empty($dados['nome'])) {
            throw new InvalidArgumentException('Nome do projeto é obrigatório.');
        }
    }
}
