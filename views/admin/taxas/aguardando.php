<?php
/** @var TaxaCondominial[] $aguardando @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Comprovantes para Aprovação';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-clipboard-check"></i> Comprovantes para aprovação</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.88rem;">
      Revise o comprovante e confirme o pagamento.
    </p>
  </div>
  <a href="<?= url('taxas') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<?php if (!empty($mensagem)): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if (!empty($erroMensagem)): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<?php if (empty($aguardando)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center text-body-secondary py-5">
      <i class="bi bi-check-circle" style="font-size:2.5rem;opacity:.3;"></i>
      <p class="mt-2 mb-0">Nenhum comprovante aguardando aprovação.</p>
    </div>
  </div>
<?php else: ?>

<div class="row g-3">
  <?php foreach ($aguardando as $taxa): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">

        <!-- Unidade + competência -->
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div>
            <div class="fw-bold"><?= htmlspecialchars($taxa->identificacaoUnidade ?? '—') ?></div>
            <div class="text-body-secondary" style="font-size:.82rem;">
              <?= htmlspecialchars($taxa->competenciaFormatada()) ?> · <?= dinheiro($taxa->valor) ?>
            </div>
          </div>
          <span class="badge rounded-pill badge-aguardando">Aguardando</span>
        </div>

        <!-- Detalhes do pagamento -->
        <table class="table table-sm table-borderless mb-3" style="font-size:.85rem;">
          <tr>
            <td class="text-muted ps-0" style="width:110px;">Vencimento</td>
            <td><?= dataBR($taxa->vencimento) ?></td>
          </tr>
          <?php if ($taxa->formaPagamento): ?>
          <tr>
            <td class="text-muted ps-0">Forma pgto.</td>
            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $taxa->formaPagamento))) ?></td>
          </tr>
          <?php endif; ?>
        </table>

        <!-- Comprovante -->
        <?php if ($taxa->comprovante): ?>
          <?php $ext = strtolower(pathinfo($taxa->comprovante, PATHINFO_EXTENSION)); ?>
          <?php if (in_array($ext, ['jpg','jpeg','png'])): ?>
            <a href="<?= url('uploads/' . $taxa->comprovante) ?>" target="_blank" class="d-block mb-3">
              <img src="<?= url('uploads/' . $taxa->comprovante) ?>"
                   alt="Comprovante"
                   class="img-fluid rounded border"
                   style="max-height:160px;object-fit:cover;width:100%;cursor:zoom-in;">
            </a>
          <?php else: ?>
            <a href="<?= url('uploads/' . $taxa->comprovante) ?>" target="_blank"
               class="btn btn-outline-secondary btn-sm w-100 mb-3">
              <i class="bi bi-file-earmark-pdf me-1"></i> Ver comprovante (PDF)
            </a>
          <?php endif; ?>
        <?php else: ?>
          <div class="text-body-secondary mb-3" style="font-size:.82rem;">
            <i class="bi bi-exclamation-circle me-1"></i>Nenhum arquivo anexado.
          </div>
        <?php endif; ?>

        <!-- Ações -->
        <div class="d-flex gap-2">
          <a href="<?= url("taxas/{$taxa->id}/aprovar?unidade_id={$taxa->unidadeId}&competencia={$taxa->competencia}") ?>"
             class="btn btn-success btn-sm flex-grow-1"
             onclick="return confirm('Confirmar pagamento de <?= htmlspecialchars($taxa->identificacaoUnidade ?? '') ?> — <?= htmlspecialchars($taxa->competenciaFormatada()) ?>?')">
            <i class="bi bi-check-circle-fill me-1"></i> Confirmar pagamento
          </a>
          <a href="<?= url("taxas/unidade/{$taxa->unidadeId}?competencia={$taxa->competencia}") ?>"
             class="btn btn-outline-secondary btn-sm" title="Ver detalhes">
            <i class="bi bi-eye"></i>
          </a>
        </div>

      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
