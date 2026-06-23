<?php
/**
 * @var Unidade|null         $unidade
 * @var TaxaCondominial|null $taxaCond
 * @var array[]              $extrasDoMes
 * @var string               $competencia
 * @var string|null          $mensagem
 * @var string|null          $erroMensagem
 */
$nomeUnidade  = $unidade?->identificacao() ?? "Unidade #{$_GET['id']}";
$tituloPagina = $nomeUnidade . ' — ' . formatarCompetencia($competencia);
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-building"></i> <?= htmlspecialchars($nomeUnidade) ?></h4>
    <nav aria-label="breadcrumb" class="mt-1">
      <ol class="breadcrumb mb-0" style="font-size:.82rem;">
        <li class="breadcrumb-item"><a href="<?= url('taxas') ?>">Meses</a></li>
        <li class="breadcrumb-item"><a href="<?= url('taxas?competencia=' . $competencia) ?>"><?= formatarCompetencia($competencia) ?></a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($nomeUnidade) ?></li>
      </ol>
    </nav>
  </div>
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

<!-- Taxa Condominial -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
    <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></span>
    <span class="fw-semibold">Taxa Condominial</span>
  </div>

  <?php if ($taxaCond === null): ?>
    <div class="card-body text-body-secondary py-4 text-center">
      <i class="bi bi-dash-circle opacity-25 fs-3 d-block mb-2"></i>
      Nenhuma taxa condominial gerada para <?= formatarCompetencia($competencia) ?>.
    </div>
  <?php else:
    $statusEf = ($taxaCond->status === 'vencido' || ($taxaCond->status === 'pendente' && $taxaCond->vencimento < date('Y-m-d')))
                ? 'vencido' : $taxaCond->status;
    $labels   = ['pago' => 'Pago', 'vencido' => 'Atrasado', 'pendente' => 'Pendente', 'isento' => 'Isento', 'aguardando' => 'Aguardando'];
  ?>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-6 col-md-3">
        <div class="text-body-secondary mb-1" style="font-size:.75rem;">VALOR</div>
        <div class="fw-semibold"><?= dinheiro($taxaCond->valor) ?></div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-body-secondary mb-1" style="font-size:.75rem;">VENCIMENTO</div>
        <div class="fw-semibold"><?= dataBR($taxaCond->vencimento) ?></div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-body-secondary mb-1" style="font-size:.75rem;">STATUS</div>
        <span class="badge rounded-pill badge-<?= $statusEf ?>"><?= $labels[$statusEf] ?? ucfirst($statusEf) ?></span>
      </div>
      <?php if ($taxaCond->dataPagamento): ?>
      <div class="col-6 col-md-3">
        <div class="text-body-secondary mb-1" style="font-size:.75rem;">PAGO EM</div>
        <div class="fw-semibold"><?= dataBR($taxaCond->dataPagamento) ?></div>
      </div>
      <?php endif; ?>
      <?php if ($taxaCond->formaPagamento): ?>
      <div class="col-6 col-md-3">
        <div class="text-body-secondary mb-1" style="font-size:.75rem;">FORMA</div>
        <?php $formas = ['pix'=>'PIX','transferencia'=>'Transferência','dinheiro'=>'Dinheiro','boleto'=>'Boleto','cartao'=>'Cartão','cheque'=>'Cheque','outro'=>'Outro']; ?>
        <div class="fw-semibold"><?= $formas[$taxaCond->formaPagamento] ?? ucfirst($taxaCond->formaPagamento) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($taxaCond->observacao): ?>
    <div class="mt-3 text-body-secondary" style="font-size:.85rem;">
      <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($taxaCond->observacao) ?>
    </div>
    <?php endif; ?>

    <?php if (!in_array($taxaCond->status, ['pago', 'isento'], true) || $taxaCond->comprovante): ?>
    <div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top flex-wrap">
      <?php if ($taxaCond->comprovante): ?>
        <a href="<?= url('uploads/' . $taxaCond->comprovante) ?>" target="_blank"
           class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-paperclip"></i> Ver comprovante
        </a>
      <?php endif; ?>

      <?php if (!in_array($taxaCond->status, ['pago', 'isento'], true)): ?>
        <?php if ($taxaCond->status === 'aguardando'): ?>
          <a href="<?= url("taxas/{$taxaCond->id}/aprovar?competencia={$competencia}&unidade_id={$unidade->id}") ?>"
             class="btn btn-success btn-sm"
             onclick="return confirm('Confirmar aprovação do comprovante?')">
            <i class="bi bi-check-lg"></i> Aprovar comprovante
          </a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary btn-sm"
                data-bs-toggle="modal" data-bs-target="#modalMarcarPago">
          <i class="bi bi-cash-coin"></i> Registrar pagamento
        </button>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Modal registrar pagamento -->
    <?php if (!in_array($taxaCond->status, ['pago', 'isento'], true)): ?>
    <div class="modal fade" id="modalMarcarPago" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Registrar pagamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form method="POST" action="<?= url('taxas/marcar-pago') ?>" enctype="multipart/form-data">
            <div class="modal-body">
              <input type="hidden" name="taxa_id"    value="<?= $taxaCond->id ?>">
              <input type="hidden" name="unidade_id" value="<?= $unidade->id ?>">
              <input type="hidden" name="competencia" value="<?= htmlspecialchars($competencia) ?>">

              <div class="mb-3">
                <label class="form-label fw-semibold">Forma de pagamento <span class="text-danger">*</span></label>
                <select name="forma_pagamento" class="form-select" required>
                  <option value="">Selecione…</option>
                  <option value="pix">PIX</option>
                  <option value="transferencia">Transferência bancária</option>
                  <option value="dinheiro">Dinheiro</option>
                  <option value="boleto">Boleto bancário</option>
                  <option value="cartao">Cartão</option>
                  <option value="cheque">Cheque</option>
                  <option value="outro">Outro</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Data do pagamento <span class="text-danger">*</span></label>
                <input type="date" name="data_pagamento" class="form-control"
                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
              </div>

              <div class="mb-1">
                <label class="form-label fw-semibold">Comprovante <span class="text-body-secondary fw-normal">(opcional)</span></label>
                <input type="file" name="comprovante" class="form-control"
                       accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">PDF, JPG ou PNG — máx. 10 MB</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Confirmar pagamento
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Taxas Extras -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
    <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-plus-circle"></i></span>
    <span class="fw-semibold">Taxas Extras</span>
    <?php if (!empty($extrasDoMes)): ?>
      <span class="badge bg-secondary bg-opacity-10 text-body ms-auto"><?= count($extrasDoMes) ?></span>
    <?php endif; ?>
  </div>

  <?php if (empty($extrasDoMes)): ?>
    <div class="card-body text-body-secondary py-4 text-center">
      <i class="bi bi-dash-circle opacity-25 fs-3 d-block mb-2"></i>
      Nenhuma taxa extra com vencimento em <?= formatarCompetencia($competencia) ?>.
    </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Descrição</th>
          <th class="d-none d-sm-table-cell">Projeto</th>
          <th>Valor</th>
          <th class="d-none d-md-table-cell">Vencimento</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($extrasDoMes as $e):
          $statusEx = $e['status'] ?? 'pendente';
          $eAtras   = $statusEx === 'vencido' || ($statusEx === 'pendente' && $e['vencimento'] < date('Y-m-d'));
          $statusEf = $eAtras ? 'vencido' : $statusEx;
          $labelsEx = ['pago' => 'Pago', 'vencido' => 'Atrasado', 'pendente' => 'Pendente', 'isento' => 'Isento'];
        ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($e['nome']) ?></div>
            <?php if ($e['parcela']): ?>
              <div class="text-body-secondary" style="font-size:.75rem;">
                Parcela <?= $e['parcela'] ?>/<?= $e['total_parcelas'] ?>
              </div>
            <?php endif; ?>
          </td>
          <td class="d-none d-sm-table-cell text-body-secondary">
            <?= htmlspecialchars($e['nome_projeto'] ?? '—') ?>
          </td>
          <td><?= dinheiro((float)$e['valor_original']) ?></td>
          <td class="d-none d-md-table-cell"><?= dataBR($e['vencimento']) ?></td>
          <td>
            <span class="badge rounded-pill badge-<?= $statusEf ?>"><?= $labelsEx[$statusEf] ?? ucfirst($statusEf) ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
