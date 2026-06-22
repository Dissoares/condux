<?php
/** @var TaxaExtra $taxaExtra @var array[] $cobrancas @var array $resumo @var TaxaExtra[] $parcelas */
$tituloPagina = $taxaExtra->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

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
          <th class="d-none d-md-table-cell">Pagamento</th>
          <th class="d-none d-md-table-cell">Comprovante</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($cobrancas)): ?>
          <tr><td colspan="5" class="text-center text-body-secondary py-4">Nenhuma cobrança gerada.</td></tr>
        <?php else: ?>
          <?php foreach ($cobrancas as $c): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($c['identificacao_unidade'] ?? '—') ?></td>
            <td class="d-none d-sm-table-cell text-body-secondary"><?= htmlspecialchars($c['nome_responsavel'] ?? '—') ?></td>
            <td><span class="badge rounded-pill badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
            <td class="d-none d-md-table-cell"><?= $c['data_pagamento'] ? dataBR($c['data_pagamento']) : '—' ?></td>
            <td class="d-none d-md-table-cell">
              <?php if ($c['comprovante']): ?>
                <a href="<?= url('uploads/' . $c['comprovante']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-paperclip"></i>
                </a>
              <?php else: ?>
                <span class="text-body-secondary">—</span>
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
