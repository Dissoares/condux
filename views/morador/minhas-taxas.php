<?php
/** @var TaxaCondominial[] $taxas @var TaxaCondominial|null $taxaAtual */
$tituloPagina = 'Minhas Taxas';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-receipt"></i> Minhas Taxas</h4>
</div>

<?php if (!empty($mensagem)): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if (!empty($erroMensagem)): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<?php if ($taxaAtual && !$taxaAtual->estaPago() && !$taxaAtual->comprovante): ?>
<div class="card border-0 shadow-sm mb-4" style="max-width:520px;">
  <div class="card-header bg-transparent fw-semibold py-3">
    Enviar comprovante — <?= htmlspecialchars($taxaAtual->competenciaFormatada()) ?>
  </div>
  <div class="card-body">
    <form action="<?= url('minhas-taxas/comprovante') ?>" method="POST" enctype="multipart/form-data">
      <?php $taxaId = (int)$taxaAtual->id; ?>
      <input type="hidden" name="taxa_id" value="<?= $taxaId ?>">
      <div class="mb-3">
        <label for="arquivo-comprovante-lista" class="form-label">Arquivo (PDF, JPG ou PNG)</label>
        <input type="file" id="arquivo-comprovante-lista" name="comprovante"
               class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>
      <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Enviar</button>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Competência</th><th>Valor</th><th>Vencimento</th><th>Status</th><th>Pagamento</th><th>Comprovante</th></tr>
      </thead>
      <tbody>
        <?php if (empty($taxas)): ?>
          <tr><td colspan="6" class="text-center text-body-secondary py-4">Nenhuma taxa encontrada.</td></tr>
        <?php else: ?>
          <?php foreach ($taxas as $taxa): ?>
          <tr>
            <td><?= htmlspecialchars($taxa->competenciaFormatada()) ?></td>
            <td><?= dinheiro($taxa->valor) ?></td>
            <td><?= dataBR($taxa->vencimento) ?></td>
            <?php $statusEf = $taxa->estaVencido() ? 'vencido' : $taxa->status; ?>
            <td><span class="badge rounded-pill badge-<?= $statusEf ?>"><?= ['pago'=>'Pago','vencido'=>'Atrasado','isento'=>'Isento'][$statusEf] ?? 'Pendente' ?></span></td>
            <td><?= dataBR($taxa->dataPagamento) ?></td>
            <td>
              <?php if ($taxa->comprovante): ?>
                <a href="<?= url('uploads/' . $taxa->comprovante) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-paperclip"></i> Ver
                </a>
              <?php else: ?>
                <span class="text-body-tertiary">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
