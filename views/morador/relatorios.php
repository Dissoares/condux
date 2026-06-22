<?php
/**
 * @var int    $ano
 * @var int[]  $anos
 * @var array  $extrato   — linhas: competencia, valor, vencimento, status, data_pagamento, observacao
 */
$tituloPagina = 'Meu Extrato';
require_once RAIZ . '/views/layouts/cabecalho.php';

$totalPago    = array_sum(array_column(array_filter($extrato, fn($r) => $r['status'] === 'pago'), 'valor'));
$totalAberto  = array_sum(array_column(array_filter($extrato, fn($r) => in_array($r['status'], ['pendente','vencido'])), 'valor'));
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-receipt"></i> Meu extrato</h4>

  <form method="GET" action="<?= url('relatorios') ?>" style="display:flex; gap:.5rem; align-items:center;">
    <select name="ano" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
      <?php if (empty($anos)): ?>
        <option value="<?= $ano ?>"><?= $ano ?></option>
      <?php else: ?>
        <?php foreach ($anos as $anoOpcao): ?>
          <option value="<?= $anoOpcao ?>" <?= $anoOpcao === $ano ? 'selected' : '' ?>><?= $anoOpcao ?></option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
  </form>
</div>

<div class="row g-3 mb-4">
  <div class="col-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;">Total pago em <?= $ano ?></div>
        <div class="fw-bold fs-5 text-success"><?= dinheiro($totalPago) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;">Em aberto em <?= $ano ?></div>
        <div class="fw-bold fs-5 text-danger"><?= dinheiro($totalAberto) ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Extrato -->
<div class="card border-0 shadow-sm mb-4"><div class="card-body">
  <h6 class="fw-semibold border-bottom pb-2 mb-3">Extrato — <?= $ano ?></h6>

  <?php if (empty($extrato)): ?>
    <p style="color:#6b7280; font-size:.9rem;">Nenhuma taxa encontrada para <?= $ano ?>.</p>
  <?php else: ?>
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Competência</th><th>Vencimento</th><th>Valor</th><th>Pago em</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($extrato as $linha): ?>
        <?php
          [$anoC, $mesC] = explode('-', $linha['competencia']);
          $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
          $rotulo = ($meses[(int)$mesC] ?? $mesC) . '/' . $anoC;
        ?>
        <tr>
          <td><?= $rotulo ?></td>
          <td><?= dataBR($linha['vencimento']) ?></td>
          <td><?= dinheiro((float)$linha['valor']) ?></td>
          <td><?= dataBR($linha['data_pagamento']) ?></td>
          <td><span class="badge rounded-pill badge-<?= $linha['status'] ?>"><?= ucfirst($linha['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div></div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
