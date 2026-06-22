<?php
/** @var TaxaCondominial[] $taxas @var array $resumo @var string $competencia */
$tituloPagina = 'Taxas Mensais';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-cash-stack"></i> Taxas Mensais</h4>
  <a href="<?= url('taxas/gerar-lote') ?>" class="btn btn-primary">
    <i class="bi bi-lightning-fill"></i> Gerar em lote
  </a>
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

<form method="GET" action="<?= url('taxas') ?>" class="d-flex align-items-end gap-2 mb-4">
  <div>
    <label for="filtro-competencia" class="form-label mb-1">Competência</label>
    <input type="month" id="filtro-competencia" name="competencia" class="form-control"
           value="<?= htmlspecialchars($competencia) ?>" style="width:auto;">
  </div>
  <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filtrar</button>
</form>

<div class="row g-3 mb-4">
  <div class="col-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-success bg-opacity-10 text-success" style="width:44px;height:44px;font-size:1.2rem;">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold lh-1"><?= $resumo['total_pagas'] ?? 0 ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">Pagas</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-warning bg-opacity-10 text-warning" style="width:44px;height:44px;font-size:1.2rem;">
          <i class="bi bi-clock-fill"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold lh-1"><?= $resumo['total_pendentes'] ?? 0 ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">Pendentes/Vencidas</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary" style="width:44px;height:44px;font-size:1.2rem;">
          <i class="bi bi-cash"></i>
        </div>
        <div>
          <div class="fw-bold lh-1"><?= dinheiro((float)($resumo['valor_arrecadado'] ?? 0)) ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">Arrecadado</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Unidade</th><th>Competência</th><th>Valor</th>
          <th>Vencimento</th><th>Status</th><th>Comprovante</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($taxas)): ?>
          <tr><td colspan="7" class="text-center text-body-secondary py-4">Nenhuma taxa encontrada.</td></tr>
        <?php else: ?>
          <?php foreach ($taxas as $taxa): ?>
          <tr>
            <td><?= htmlspecialchars($taxa->identificacaoUnidade ?? '—') ?></td>
            <td><?= htmlspecialchars($taxa->competenciaFormatada()) ?></td>
            <td><?= dinheiro($taxa->valor) ?></td>
            <td><?= dataBR($taxa->vencimento) ?></td>
            <td><span class="badge rounded-pill badge-<?= $taxa->status ?>"><?= ucfirst($taxa->status) ?></span></td>
            <td>
              <?php if ($taxa->comprovante): ?>
                <a href="<?= url('uploads/' . $taxa->comprovante) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-paperclip"></i> Ver
                </a>
              <?php else: ?>
                <span class="text-body-tertiary">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($taxa->comprovante && $taxa->status !== 'pago'): ?>
                <a href="<?= url("taxas/{$taxa->id}/aprovar?competencia={$competencia}") ?>"
                   class="btn btn-success btn-sm"
                   onclick="return confirm('Aprovar este pagamento?')">
                  <i class="bi bi-check-lg"></i> Aprovar
                </a>
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
