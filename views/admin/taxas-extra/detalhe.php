<?php
/** @var TaxaExtra $taxaExtra @var array[] $cobrancas @var array $resumo @var TaxaExtra[] $parcelas */
/** @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = $taxaExtra->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';
$formasPgto = [
    'pix'          => 'Pix',
    'transferencia'=> 'Transferência',
    'dinheiro'     => 'Dinheiro',
    'boleto'       => 'Boleto',
    'cartao'       => 'Cartão',
    'cheque'       => 'Cheque',
    'outro'        => 'Outro',
];
?>

<?php if (!empty($mensagem)): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if (!empty($erroMensagem)): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0">
      <i class="bi bi-plus-circle text-primary"></i>
      <?= htmlspecialchars($taxaExtra->nomeProjeto ?? $taxaExtra->nome) ?>
    </h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      <?= htmlspecialchars($taxaExtra->nome) ?>
      <?php if ($taxaExtra->valorTotal): ?>
        — Total do projeto: <strong><?= dinheiro($taxaExtra->valorTotal) ?></strong>
      <?php endif; ?>
    </p>
  </div>
  <a href="<?= url('taxas-extra') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<!-- Cards de resumo -->
<div class="row g-2 g-md-3 mb-4">
  <div class="col-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2 gap-md-3">
        <div class="rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0 bg-success bg-opacity-10 text-success" style="width:40px;height:40px;font-size:1.1rem;">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold lh-1"><?= (int)$resumo['pagas'] ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;">Pagas</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2 gap-md-3">
        <div class="rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0 bg-warning bg-opacity-10 text-warning" style="width:40px;height:40px;font-size:1.1rem;">
          <i class="bi bi-clock-fill"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold lh-1"><?= (int)$resumo['pendentes'] ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;">Pendentes</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2 gap-md-3">
        <div class="rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary" style="width:40px;height:40px;font-size:1.1rem;">
          <i class="bi bi-building"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold lh-1"><?= (int)$resumo['total'] ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;">Unidades</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($parcelas) && count($parcelas) > 1): ?>
<!-- Linha do tempo de parcelas -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
    <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-calendar-range"></i></span>
    <span class="fw-semibold">Parcelas do projeto</span>
  </div>
  <div class="card-body">
    <div class="d-flex gap-2 flex-wrap">
      <?php foreach ($parcelas as $parc): ?>
        <?php $ativa = $parc->id === $taxaExtra->id; ?>
        <a href="<?= url("taxas-extra/{$parc->id}") ?>"
           class="btn btn-sm <?= $ativa ? 'btn-primary' : 'btn-outline-secondary' ?>"
           style="min-width:70px;">
          <?= $parc->parcela ?>/<?= $parc->totalParcelas ?>
          <div style="font-size:.65rem; opacity:.8;"><?= dataBR($parc->vencimento) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Cobranças por unidade -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
    <span class="icone-secao bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-building"></i></span>
    <span class="fw-semibold">Cobranças por unidade</span>
    <span class="badge bg-secondary bg-opacity-10 text-body ms-auto"><?= dinheiro($taxaExtra->valor) ?> / unidade</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Unidade</th>
          <th class="d-none d-sm-table-cell">Responsável</th>
          <th>Status</th>
          <th class="d-none d-md-table-cell">Forma pgto.</th>
          <th class="d-none d-md-table-cell">Pago em</th>
          <th class="d-none d-md-table-cell">Comprovante</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($cobrancas)): ?>
          <tr><td colspan="7" class="text-center text-body-secondary py-4">Nenhuma cobrança gerada.</td></tr>
        <?php else: ?>
          <?php foreach ($cobrancas as $c): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($c['identificacao_unidade'] ?? '—') ?></td>
            <td class="d-none d-sm-table-cell text-body-secondary"><?= htmlspecialchars($c['nome_responsavel'] ?? '—') ?></td>
            <td><span class="badge rounded-pill badge-<?= $c['status'] ?>"><?= ucfirst($c['status'] === 'aguardando' ? 'Aguardando' : $c['status']) ?></span></td>
            <td class="d-none d-md-table-cell text-body-secondary" style="font-size:.85rem;">
              <?= $c['forma_pagamento'] ? htmlspecialchars($formasPgto[$c['forma_pagamento']] ?? ucfirst($c['forma_pagamento'])) : '—' ?>
            </td>
            <td class="d-none d-md-table-cell text-body-secondary" style="font-size:.85rem;">
              <?= $c['data_pagamento'] ? dataBR($c['data_pagamento']) : '—' ?>
            </td>
            <td class="d-none d-md-table-cell">
              <?php if ($c['comprovante']): ?>
                <a href="<?= url('uploads/' . $c['comprovante']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2">
                  <i class="bi bi-paperclip"></i>
                </a>
              <?php else: ?>
                <span class="text-body-secondary">—</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <?php if ($c['status'] === 'aguardando'): ?>
                <form method="POST" action="<?= url("taxas-extra/{$taxaExtra->id}/aprovar") ?>"
                      class="d-inline" onsubmit="return confirm('Aprovar este comprovante?')">
                  <input type="hidden" name="cobranca_id"    value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="data_pagamento" value="<?= date('Y-m-d') ?>">
                  <button type="submit" class="btn btn-success btn-sm py-0 px-2" title="Aprovar comprovante">
                    <i class="bi bi-check-lg"></i> <span class="d-none d-sm-inline">Aprovar</span>
                  </button>
                </form>
              <?php elseif (in_array($c['status'], ['pendente', 'vencido'], true)): ?>
                <button type="button"
                        class="btn btn-primary btn-sm py-0 px-2 btn-marcar-pago"
                        data-cobranca-id="<?= (int)$c['id'] ?>"
                        data-unidade="<?= htmlspecialchars($c['identificacao_unidade'] ?? '') ?>"
                        data-bs-toggle="modal" data-bs-target="#modalMarcarPago">
                  <i class="bi bi-cash-coin"></i> <span class="d-none d-sm-inline">Pagar</span>
                </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: marcar pago -->
<div class="modal fade" id="modalMarcarPago" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= url('taxas-extra/marcar-pago') ?>" enctype="multipart/form-data">
        <input type="hidden" name="taxa_extra_id" value="<?= (int)$taxaExtra->id ?>">
        <input type="hidden" name="cobranca_id" id="modal-cobranca-id">

        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-bold mb-0">Registrar pagamento</h5>
            <div class="text-body-secondary mt-1" style="font-size:.85rem;" id="modal-unidade-info"></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body pt-3">
          <div class="mb-3">
            <label class="form-label fw-semibold">Data do pagamento <span class="text-danger">*</span></label>
            <input type="date" name="data_pagamento" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Forma de pagamento</label>
            <select name="forma_pagamento" class="form-select">
              <option value="">— Selecione —</option>
              <option value="pix">Pix</option>
              <option value="transferencia">Transferência bancária</option>
              <option value="boleto">Boleto</option>
              <option value="dinheiro">Dinheiro</option>
              <option value="cartao">Cartão</option>
              <option value="cheque">Cheque</option>
              <option value="outro">Outro</option>
            </select>
          </div>
          <div class="mb-1">
            <label class="form-label fw-semibold">Comprovante <span class="text-body-secondary fw-normal">(opcional)</span></label>
            <input type="file" name="comprovante" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
            <div class="form-text">PDF, JPG, PNG ou WEBP · máx. 10 MB</div>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-cash-coin me-1"></i>Confirmar pagamento
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-marcar-pago').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.getElementById('modal-cobranca-id').value = this.dataset.cobrancaId;
    document.getElementById('modal-unidade-info').textContent = this.dataset.unidade;
  });
});
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
