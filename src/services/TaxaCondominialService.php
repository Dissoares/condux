<?php

declare(strict_types=1);

require_once __DIR__ . '/../repository/TaxaCondominialRepository.php';
require_once __DIR__ . '/../repository/UnidadeRepository.php';
require_once __DIR__ . '/../models/TaxaCondominial.php';

class TaxaCondominialService
{
    public function __construct(
        private readonly TaxaCondominialRepository $taxaRepository,
        private readonly UnidadeRepository         $unidadeRepository,
    ) {}

    /** @return TaxaCondominial[] */
    public function listarPorUnidade(int $unidadeId): array
    {
        return $this->taxaRepository->listarPorUnidade($unidadeId);
    }

    /** @return TaxaCondominial[] */
    public function listarPorCompetencia(string $competencia): array
    {
        return $this->taxaRepository->listarPorCompetencia($competencia);
    }

    public function buscarTaxa(int $id): ?TaxaCondominial
    {
        return $this->taxaRepository->buscarPorId($id);
    }

    public function buscarTaxaMesAtualDaUnidade(int $unidadeId): ?TaxaCondominial
    {
        return $this->taxaRepository->buscarCompetenciaAtualDaUnidade($unidadeId);
    }

    public function resumoMesAtual(): array
    {
        return $this->taxaRepository->resumoMesAtual();
    }

    /**
     * Gera taxas condominiais em lote para todas as unidades ativas de uma competência.
     * Ignora unidades que já possuem taxa gerada para o período (INSERT IGNORE).
     */
    public function gerarEmLote(string $competencia, float $valor, string $vencimento): int
    {
        $this->validarCompetencia($competencia);
        $this->validarValor($valor);

        $unidades   = $this->unidadeRepository->listarAtivas();
        $unidadeIds = array_map(fn($u) => $u->id, $unidades);

        return $this->taxaRepository->gerarEmLotePorCompetencia(
            $competencia,
            $valor,
            $vencimento,
            $unidadeIds,
        );
    }

    /** Aprova comprovante enviado pelo morador e marca como pago. */
    public function aprovarComprovante(int $taxaId): void
    {
        $taxa = $this->taxaRepository->buscarPorId($taxaId);

        if ($taxa === null) {
            throw new InvalidArgumentException('Taxa não encontrada.');
        }

        $taxa->status        = TaxaCondominial::STATUS_PAGO;
        $taxa->dataPagamento = date('Y-m-d');

        $this->taxaRepository->salvar($taxa);
    }

    /**
     * Morador envia comprovante — salva arquivo e muda status para aguardando aprovação.
     * Retorna o caminho salvo.
     */
    public function enviarComprovante(int $taxaId, array $arquivoUpload): string
    {
        $taxa = $this->taxaRepository->buscarPorId($taxaId);

        if ($taxa === null) {
            throw new InvalidArgumentException('Taxa não encontrada.');
        }

        $caminho = $this->salvarArquivoComprovante($arquivoUpload);

        $taxa->comprovante = $caminho;
        $taxa->observacao  = 'Comprovante enviado — aguardando aprovação.';

        $this->taxaRepository->salvar($taxa);

        return $caminho;
    }

    /** Atualiza taxas vencidas (competências passadas ainda pendentes). */
    public function marcarVencidas(): int
    {
        // Obtém taxas pendentes de meses anteriores via repositório direto
        $conexao = Conexao::obter();
        $stmt = $conexao->prepare(
            'UPDATE taxas_condominiais
             SET status = "vencido"
             WHERE status = "pendente"
               AND vencimento < CURDATE()'
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    private function salvarArquivoComprovante(array $arquivo): string
    {
        $config     = require __DIR__ . '/../../config/app.php';
        $extensao   = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = array_merge($config['extensoes_imagem'], $config['extensoes_documento']);

        if (!in_array($extensao, $permitidas, true)) {
            throw new InvalidArgumentException('Tipo de arquivo não permitido para comprovante.');
        }

        if ($arquivo['size'] > $config['tamanho_maximo_upload']) {
            throw new InvalidArgumentException('Arquivo muito grande. Máximo 10 MB.');
        }

        $nomeArquivo = 'comprovante_' . uniqid('', true) . '.' . $extensao;
        $destino     = $config['pasta_uploads'] . '/comprovantes/' . $nomeArquivo;

        if (!is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0755, true);
        }

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            throw new RuntimeException('Falha ao salvar o comprovante.');
        }

        return 'comprovantes/' . $nomeArquivo;
    }

    private function validarCompetencia(string $competencia): void
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $competencia)) {
            throw new InvalidArgumentException('Competência inválida. Use o formato AAAA-MM.');
        }
    }

    private function validarValor(float $valor): void
    {
        if ($valor <= 0) {
            throw new InvalidArgumentException('O valor da taxa deve ser maior que zero.');
        }
    }
}
