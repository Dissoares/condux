<?php
/**
 * @var int    $ano
 * @var int[]  $anos
 * @var array  $mensalidade      — linhas: competencia, total_cobrado, total_pago, total_unidades, total_pagas, total_inadimplentes
 * @var array  $inadimplentes    — linhas: unidade, responsavel, valor, vencimento, dias_atraso
 * @var string $competenciaAtual
 */
$tituloPagina = 'Relatórios';
require_once RAIZ . '/views/layouts/cabecalho.php';

$totalArrecadado = array_sum(array_column($mensalidade, 'total_pago'));
$totalCobrado    = array_sum(array_column($mensalidade, 'total_cobrado'));
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-bar-chart-line"></i> Relatórios</h4>

  <!-- Filtro de ano -->
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
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;">Cobrado em <?= $ano ?></div>
        <div class="fw-bold fs-5"><?= dinheiro($totalCobrado) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;">Arrecadado em <?= $ano ?></div>
        <div class="fw-bold fs-5 text-success"><?= dinheiro($totalArrecadado) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;">Em aberto em <?= $ano ?></div>
        <div class="fw-bold fs-5 text-danger"><?= dinheiro($totalCobrado - $totalArrecadado) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;">Taxa de adimplência</div>
        <div class="fw-bold fs-5"><?= $totalCobrado > 0 ? number_format(($totalArrecadado / $totalCobrado) * 100, 1) : '0,0' ?>%</div>
      </div>
    </div>
  </div>
</div>

<!-- Arrecadação mensal -->
<div class="card border-0 shadow-sm mb-4"><div class="card-body">
  <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bi bi-calendar3"></i> Arrecadação mensal — <?= $ano ?></h6>

  <?php if (empty($mensalidade)): ?>
    <p style="color:#6b7280; font-size:.9rem;">Nenhuma taxa cadastrada para <?= $ano ?>.</p>
  <?php else: ?>
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Competência</th>
          <th>Unidades</th>
          <th>Pagas</th>
          <th>Inadimplentes</th>
          <th>Cobrado</th>
          <th>Arrecadado</th>
          <th>%</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mensalidade as $linha): ?>
        <?php
          $pct = $linha['total_cobrado'] > 0
              ? round(($linha['total_pago'] / $linha['total_cobrado']) * 100)
              : 0;
          [$anoC, $mesC] = explode('-', $linha['competencia']);
          $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
          $rotulo = ($meses[(int)$mesC] ?? $mesC) . '/' . $anoC;
        ?>
        <tr>
          <td><?= $rotulo ?></td>
          <td><?= $linha['total_unidades'] ?></td>
          <td><span class="badge rounded-pill badge-pago"><?= $linha['total_pagas'] ?></span></td>
          <td>
            <?php if ($linha['total_inadimplentes'] > 0): ?>
              <span class="badge rounded-pill badge-vencido"><?= $linha['total_inadimplentes'] ?></span>
            <?php else: ?>
              <span class="badge rounded-pill badge-pago">0</span>
            <?php endif; ?>
          </td>
          <td><?= dinheiro((float)$linha['total_cobrado']) ?></td>
          <td><?= dinheiro((float)$linha['total_pago']) ?></td>
          <td>
            <div style="display:flex; align-items:center; gap:.4rem;">
              <div style="flex:1; height:6px; background:#e5e7eb; border-radius:3px; min-width:50px;">
                <div style="width:<?= $pct ?>%; height:100%; background:#198754; border-radius:3px;"></div>
              </div>
              <span style="font-size:.8rem; color:#6b7280; white-space:nowrap;"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div></div>

<!-- Inadimplentes do mês atual -->
<div class="card border-0 shadow-sm mb-4"><div class="card-body">
  <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bi bi-exclamation-triangle"></i> Inadimplentes — competência atual</h6>

  <?php if (empty($inadimplentes)): ?>
    <p style="color:#198754; font-size:.9rem;">
      <i class="bi bi-check-circle-fill"></i> Nenhuma inadimplência na competência atual.
    </p>
  <?php else: ?>
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr><th>Unidade</th><th>Responsável</th><th>Valor</th><th>Vencimento</th><th>Atraso</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($inadimplentes as $linha): ?>
        <tr>
          <td><?= htmlspecialchars($linha['unidade']) ?></td>
          <td><?= htmlspecialchars($linha['responsavel'] ?? '—') ?></td>
          <td><?= dinheiro((float)$linha['valor']) ?></td>
          <td><?= dataBR($linha['vencimento']) ?></td>
          <td>
            <?php if ($linha['dias_atraso'] > 0): ?>
              <span style="color:#dc3545; font-size:.85rem;"><?= $linha['dias_atraso'] ?> dias</span>
            <?php else: ?>
              <span style="color:#6b7280; font-size:.85rem;">No prazo</span>
            <?php endif; ?>
          </td>
          <td><span class="badge rounded-pill badge-<?= $linha['status'] ?>"><?= ucfirst($linha['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div></div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
