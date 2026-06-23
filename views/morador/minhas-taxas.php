<?php
/** @var array[]|null $anos @var TaxaCondominial[]|null $taxas @var string|null $anoFiltro */
$tituloPagina = $tituloPagina ?? 'Minhas Taxas';
require_once RAIZ . '/views/layouts/cabecalho.php';

$rotulos = [
    'pago'       => ['label' => 'Pago',     'badge' => 'badge-pago'],
    'aguardando' => ['label' => 'Enviado',   'badge' => 'badge-aguardando'],
    'isento'     => ['label' => 'Isento',    'badge' => 'badge-pago'],
    'vencido'    => ['label' => 'Atrasado',  'badge' => 'bg-danger text-white'],
    'pendente'   => ['label' => 'Pendente',  'badge' => 'badge-pendente'],
];
?>

<div class="mb-4 d-flex align-items-center gap-2">
  <?php if ($anoFiltro): ?>
    <a href="<?= url('minhas-taxas') ?>" class="btn btn-outline-secondary btn-sm py-1 px-2">
      <i class="bi bi-chevron-left"></i>
    </a>
  <?php endif; ?>
  <h4 class="fw-semibold mb-0">
    <i class="bi bi-receipt"></i>
    Minhas Taxas<?= $anoFiltro ? ' — ' . $anoFiltro : '' ?>
  </h4>
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


<?php /* ══════════════════════════════════════════════
       TELA 1 — Lista de anos
       ══════════════════════════════════════════════ */
if (!$anoFiltro): ?>

<?php if (empty($anos)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center text-body-secondary py-5">
      <i class="bi bi-receipt" style="font-size:2rem;opacity:.3;"></i>
      <p class="mt-2 mb-0">Nenhuma taxa encontrada.</p>
    </div>
  </div>
<?php else: ?>

<div class="card border-0 shadow-sm overflow-hidden">
  <?php foreach ($anos as $i => $r):
    $temProblema = (int)$r['vencidas'] > 0;
    $temPendente = (int)$r['pendentes'] > 0;
    $temAguardando = (int)$r['aguardando'] > 0;
    if ($temProblema) {
        $badgeClass = 'bg-danger-subtle text-danger-emphasis';
        $badgeIcon  = 'bi-exclamation-circle';
        $badgeLabel = 'Atrasado';
    } elseif ($temPendente) {
        $badgeClass = 'bg-warning-subtle text-warning-emphasis';
        $badgeIcon  = 'bi-clock';
        $badgeLabel = 'Pendente';
    } elseif ($temAguardando) {
        $badgeClass = 'bg-primary-subtle text-primary-emphasis';
        $badgeIcon  = 'bi-hourglass-split';
        $badgeLabel = 'Em análise';
    } else {
        $badgeClass = 'bg-success-subtle text-success-emphasis';
        $badgeIcon  = 'bi-check-circle';
        $badgeLabel = 'Em dia';
    }
  ?>
  <a href="<?= url('minhas-taxas?ano=' . $r['ano']) ?>"
     class="d-flex align-items-center justify-content-between px-4 py-3 text-decoration-none text-body <?= $i > 0 ? 'border-top' : '' ?>"
     style="transition:background .12s;"
     onmouseover="this.style.background='var(--bs-tertiary-bg)'"
     onmouseout="this.style.background=''">
    <div class="d-flex align-items-center gap-3">
      <span class="fw-bold" style="font-size:1.1rem;"><?= $r['ano'] ?></span>
      <span class="badge rounded-pill <?= $badgeClass ?>" style="font-size:.72rem;">
        <i class="bi <?= $badgeIcon ?> me-1"></i><?= $badgeLabel ?>
      </span>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span class="text-body-secondary" style="font-size:.82rem;">
        <?= (int)$r['pagas'] ?>/<?= (int)$r['total'] ?> pagas
      </span>
      <i class="bi bi-chevron-right text-body-tertiary" style="font-size:.8rem;"></i>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<?php endif; ?>


<?php /* ══════════════════════════════════════════════
       TELA 2 — Taxas de um ano específico
       ══════════════════════════════════════════════ */
else: ?>

<?php if (empty($taxas)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center text-body-secondary py-5">
      <i class="bi bi-receipt" style="font-size:2rem;opacity:.3;"></i>
      <p class="mt-2 mb-0">Nenhuma taxa em <?= htmlspecialchars($anoFiltro) ?>.</p>
    </div>
  </div>
<?php else: ?>

<!-- ── Mobile: cards ── -->
<div class="d-md-none d-flex flex-column gap-3">
  <?php foreach ($taxas as $taxa):
    $statusEf   = $taxa->estaVencido() ? 'vencido' : $taxa->status;
    $info       = $rotulos[$statusEf] ?? $rotulos['pendente'];
    $podeEnviar = in_array($statusEf, ['pendente', 'vencido'], true);
    $aguardando = $statusEf === 'aguardando';
  ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-3">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($taxa->competenciaFormatada()) ?></div>
          <div class="text-body-secondary" style="font-size:.82rem;">
            Venc. <?= dataBR($taxa->vencimento) ?>
          </div>
        </div>
        <span class="badge rounded-pill <?= $info['badge'] ?>"><?= $info['label'] ?></span>
      </div>
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <div class="fw-bold" style="font-size:1.1rem;"><?= dinheiro($taxa->valor) ?></div>
          <?php if ($taxa->formaPagamento): ?>
            <div class="text-body-secondary" style="font-size:.78rem;">
              <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $taxa->formaPagamento))) ?>
              <?= $taxa->dataPagamento ? ' · ' . dataBR($taxa->dataPagamento) : '' ?>
            </div>
          <?php elseif ($taxa->dataPagamento): ?>
            <div class="text-body-secondary" style="font-size:.78rem;">
              Pago em <?= dataBR($taxa->dataPagamento) ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <?php if ($taxa->comprovante): ?>
            <a href="<?= url('uploads/' . $taxa->comprovante) ?>" target="_blank"
               class="btn btn-outline-secondary btn-sm py-1 px-2">
              <i class="bi bi-paperclip"></i>
            </a>
          <?php endif; ?>
          <?php if ($podeEnviar): ?>
            <button type="button"
                    class="btn btn-primary btn-sm btn-pagar"
                    data-taxa-id="<?= (int)$taxa->id ?>"
                    data-competencia="<?= htmlspecialchars($taxa->competenciaFormatada()) ?>"
                    data-valor="<?= dinheiro($taxa->valor) ?>"
                    data-bs-toggle="modal" data-bs-target="#modalPagar">
              <i class="bi bi-send me-1"></i>Pagar
            </button>
          <?php elseif ($aguardando): ?>
            <span class="text-primary" style="font-size:.78rem;">
              <i class="bi bi-hourglass-split"></i> Análise
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Desktop: tabela ── -->
<div class="d-none d-md-block card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Competência</th>
          <th class="text-end">Valor</th>
          <th>Vencimento</th>
          <th>Status</th>
          <th>Forma pgto.</th>
          <th>Pago em</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($taxas as $taxa):
          $statusEf   = $taxa->estaVencido() ? 'vencido' : $taxa->status;
          $info       = $rotulos[$statusEf] ?? $rotulos['pendente'];
          $podeEnviar = in_array($statusEf, ['pendente', 'vencido'], true);
          $aguardando = $statusEf === 'aguardando';
        ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($taxa->competenciaFormatada()) ?></td>
          <td class="text-end"><?= dinheiro($taxa->valor) ?></td>
          <td><?= dataBR($taxa->vencimento) ?></td>
          <td><span class="badge rounded-pill <?= $info['badge'] ?>"><?= $info['label'] ?></span></td>
          <td class="text-body-secondary" style="font-size:.85rem;">
            <?= $taxa->formaPagamento ? htmlspecialchars(ucfirst(str_replace('_', ' ', $taxa->formaPagamento))) : '—' ?>
          </td>
          <td class="text-body-secondary" style="font-size:.85rem;">
            <?= $taxa->dataPagamento ? dataBR($taxa->dataPagamento) : '—' ?>
          </td>
          <td class="text-end">
            <div class="d-flex gap-1 justify-content-end align-items-center">
              <?php if ($taxa->comprovante): ?>
                <a href="<?= url('uploads/' . $taxa->comprovante) ?>" target="_blank"
                   class="btn btn-outline-secondary btn-sm py-0 px-2" title="Ver comprovante">
                  <i class="bi bi-paperclip"></i>
                </a>
              <?php endif; ?>
              <?php if ($podeEnviar): ?>
                <button type="button"
                        class="btn btn-primary btn-sm py-0 px-2 btn-pagar"
                        data-taxa-id="<?= (int)$taxa->id ?>"
                        data-competencia="<?= htmlspecialchars($taxa->competenciaFormatada()) ?>"
                        data-valor="<?= dinheiro($taxa->valor) ?>"
                        data-bs-toggle="modal" data-bs-target="#modalPagar">
                  <i class="bi bi-send me-1"></i>Pagar
                </button>
              <?php elseif ($aguardando): ?>
                <span class="text-primary" style="font-size:.78rem;">
                  <i class="bi bi-hourglass-split"></i> Análise
                </span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal de envio de comprovante -->
<div class="modal fade" id="modalPagar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= url('minhas-taxas/comprovante') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="taxa_id" id="modal-taxa-id">

        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-bold mb-0">Registrar pagamento</h5>
            <div class="text-body-secondary mt-1" style="font-size:.85rem;" id="modal-competencia-info"></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body pt-3">
          <div class="mb-3">
            <label class="form-label fw-semibold">Forma de pagamento <span class="text-danger">*</span></label>
            <select name="forma_pagamento" class="form-select" required>
              <option value="">— Selecione —</option>
              <option value="pix">Pix</option>
              <option value="transferencia">Transferência bancária</option>
              <option value="boleto">Boleto</option>
              <option value="dinheiro">Dinheiro</option>
              <option value="debito">Cartão de débito</option>
              <option value="credito">Cartão de crédito</option>
            </select>
          </div>
          <div class="mb-1">
            <label class="form-label fw-semibold">Comprovante <span class="text-danger">*</span></label>
            <input type="file" name="comprovante" class="form-control"
                   accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="form-text">PDF, JPG ou PNG · máx. 10 MB</div>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>Enviar comprovante
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-pagar').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.getElementById('modal-taxa-id').value = this.dataset.taxaId;
    document.getElementById('modal-competencia-info').textContent =
      this.dataset.competencia + ' · ' + this.dataset.valor;
    document.querySelector('#modalPagar select[name="forma_pagamento"]').value = '';
    document.querySelector('#modalPagar input[type="file"]').value = '';
  });
});
</script>

<?php endif; ?>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
