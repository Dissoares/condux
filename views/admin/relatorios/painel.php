<?php
/**
 * @var int     $ano
 * @var int[]   $anos
 * @var string  $aba
 * @var array   $dados
 * @var array   $unidades
 * @var int|null $unidadeId
 * @var string  $competencia
 */

$meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
          'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

function rotulomes(string $comp): string {
    global $meses;
    [$a, $m] = explode('-', $comp);
    return ($meses[(int)$m] ?? $m) . '/' . $a;
}

$urlBase   = url('relatorios');
$urlExport = $urlBase . '?acao=exportar';

$abas = [
    'arrecadacao'   => ['label' => 'Arrecadação',   'icon' => 'cash-stack'],
    'balancete'     => ['label' => 'Balancete',      'icon' => 'calculator'],
    'inadimplencia' => ['label' => 'Inadimplência',  'icon' => 'exclamation-triangle'],
    'despesas'      => ['label' => 'Despesas',        'icon' => 'receipt'],
    'folha'         => ['label' => 'Folha de Pessoal','icon' => 'people'],
    'unidade'       => ['label' => 'Por Unidade',     'icon' => 'building'],
];
?>

<style>
@media print {
  .condux-sidebar, .condux-top-bar, .condux-bottom-nav,
  .no-print, .btn, nav, footer { display: none !important; }
  .condux-conteudo { margin: 0 !important; padding: 0 !important; }
  .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
  body { background: white !important; }
  .print-header { display: block !important; }
}
.print-header { display: none; }
</style>

<!-- Cabeçalho para impressão -->
<div class="print-header mb-4">
  <h2 style="font-size:1.4rem; font-weight:800;">Relatório — <?= $abas[$aba]['label'] ?? '' ?></h2>
  <p style="font-size:.85rem; color:#6b7280;">
    Gerado em <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp;
    <?= in_array($aba, ['inadimplencia']) ? 'Competência: ' . $competencia : 'Ano: ' . $ano ?>
  </p>
  <hr>
</div>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 no-print">
  <h4 class="fw-semibold mb-0"><i class="bi bi-bar-chart-line"></i> Relatórios</h4>

  <!-- Ações de exportação -->
  <div class="d-flex gap-2 flex-wrap">
    <?php
    $exportParams = match ($aba) {
        'inadimplencia' => "tipo={$aba}&comp={$competencia}",
        'unidade'       => "tipo={$aba}&ano={$ano}&unidade=" . ($unidadeId ?? 0),
        default         => "tipo={$aba}&ano={$ano}",
    };
    ?>
    <a href="<?= $urlExport . '&' . $exportParams ?>"
       class="btn btn-sm btn-outline-success">
      <i class="bi bi-file-earmark-excel"></i> Excel / CSV
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-printer"></i> Imprimir / PDF
    </button>
  </div>
</div>

<!-- Abas de relatório -->
<ul class="nav nav-tabs mb-4 no-print" style="flex-wrap:nowrap; overflow-x:auto;">
  <?php foreach ($abas as $chave => $info): ?>
  <li class="nav-item" style="white-space:nowrap;">
    <a class="nav-link <?= $aba === $chave ? 'active' : '' ?>"
       href="<?= $urlBase ?>?aba=<?= $chave ?>&ano=<?= $ano ?>&comp=<?= urlencode($competencia) ?>&unidade=<?= $unidadeId ?? '' ?>">
      <i class="bi bi-<?= $info['icon'] ?>"></i>
      <span class="d-none d-md-inline"> <?= $info['label'] ?></span>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<!-- Filtros por aba -->
<div class="d-flex flex-wrap gap-3 align-items-end mb-4 no-print">

  <?php if (in_array($aba, ['arrecadacao','balancete','despesas','folha','unidade'])): ?>
  <div>
    <label class="form-label mb-1 text-body-secondary" style="font-size:.8rem;">Ano</label>
    <select class="form-select form-select-sm" style="width:auto;"
            onchange="mudarFiltro('ano', this.value)">
      <?php foreach ($anos as $a): ?>
        <option value="<?= $a ?>" <?= $a === $ano ? 'selected' : '' ?>><?= $a ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>

  <?php if ($aba === 'inadimplencia'): ?>
  <div>
    <label class="form-label mb-1 text-body-secondary" style="font-size:.8rem;">Competência</label>
    <input type="month" class="form-control form-control-sm" value="<?= htmlspecialchars($competencia) ?>"
           style="width:auto;" onchange="mudarFiltro('comp', this.value)">
  </div>
  <?php endif; ?>

  <?php if ($aba === 'unidade'): ?>
  <div>
    <label class="form-label mb-1 text-body-secondary" style="font-size:.8rem;">Unidade</label>
    <select class="form-select form-select-sm" style="width:auto;"
            onchange="mudarFiltro('unidade', this.value)">
      <option value="">— selecione —</option>
      <?php foreach ($unidades as $u): ?>
        <option value="<?= $u['id'] ?>" <?= $u['id'] == $unidadeId ? 'selected' : '' ?>>
          <?= htmlspecialchars($u['identificacao']) ?><?= $u['responsavel'] ? ' — ' . htmlspecialchars($u['responsavel']) : '' ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ABA: ARRECADAÇÃO                                                        -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<?php if ($aba === 'arrecadacao'): ?>

<?php
$totalCobrado    = array_sum(array_column($dados, 'total_cobrado'));
$totalArrecadado = array_sum(array_column($dados, 'total_pago'));
$totalAberto     = $totalCobrado - $totalArrecadado;
$adimplencia     = $totalCobrado > 0 ? round(($totalArrecadado / $totalCobrado) * 100, 1) : 0;
?>

<div class="row g-3 mb-4">
  <?php foreach ([
    ['Cobrado em '.$ano,    $totalCobrado,    'text-body'],
    ['Arrecadado',          $totalArrecadado, 'text-success'],
    ['Em aberto',           $totalAberto,     'text-danger'],
    ['Taxa de adimplência', $adimplencia.'%', 'fw-bold'],
  ] as [$label, $val, $cls]): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;"><?= $label ?></div>
        <div class="fw-bold fs-5 <?= $cls ?>">
          <?= is_string($val) ? $val : dinheiro((float)$val) ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-calendar3"></i> Arrecadação mensal — <?= $ano ?>
  </div>
  <?php if (empty($dados)): ?>
    <div class="card-body text-body-secondary">Nenhum dado para <?= $ano ?>.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Competência</th><th class="text-center">Unidades</th>
          <th class="text-center">Pagas</th><th class="text-center">Inadim.</th>
          <th class="text-end">Cobrado</th><th class="text-end">Arrecadado</th>
          <th style="min-width:120px">Adimplência</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $r):
          $pct = $r['total_cobrado'] > 0 ? round(($r['total_pago'] / $r['total_cobrado']) * 100) : 0;
        ?>
        <tr>
          <td class="fw-semibold"><?= rotulomes($r['competencia']) ?></td>
          <td class="text-center"><?= $r['total_unidades'] ?></td>
          <td class="text-center"><span class="badge rounded-pill badge-pago"><?= $r['total_pagas'] ?></span></td>
          <td class="text-center">
            <span class="badge rounded-pill <?= $r['total_inadimplentes'] > 0 ? 'badge-vencido' : 'badge-pago' ?>">
              <?= $r['total_inadimplentes'] ?>
            </span>
          </td>
          <td class="text-end"><?= dinheiro((float)$r['total_cobrado']) ?></td>
          <td class="text-end fw-semibold text-success"><?= dinheiro((float)$r['total_pago']) ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div style="flex:1; height:6px; background:#e5e7eb; border-radius:3px;">
                <div style="width:<?= $pct ?>%; height:100%; background:#198754; border-radius:3px;"></div>
              </div>
              <span style="font-size:.8rem; color:#6b7280; white-space:nowrap;"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="4">Total</td>
          <td class="text-end"><?= dinheiro($totalCobrado) ?></td>
          <td class="text-end text-success"><?= dinheiro($totalArrecadado) ?></td>
          <td><?= $adimplencia ?>%</td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ABA: BALANCETE                                                          -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<?php if ($aba === 'balancete'): ?>

<?php
$totArrecadado = array_sum(array_column($dados, 'arrecadado'));
$totDespesas   = array_sum(array_column($dados, 'despesas'));
$totFolha      = array_sum(array_column($dados, 'folha'));
$totSaldo      = array_sum(array_column($dados, 'saldo'));
?>

<div class="row g-3 mb-4">
  <?php foreach ([
    ['Arrecadado',  $totArrecadado, 'text-success'],
    ['Despesas',    $totDespesas,   'text-danger'],
    ['Folha',       $totFolha,      'text-danger'],
    ['Saldo',       $totSaldo,      $totSaldo >= 0 ? 'text-success' : 'text-danger'],
  ] as [$label, $val, $cls]): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary mb-1" style="font-size:.8rem;"><?= $label ?> <?= $ano ?></div>
        <div class="fw-bold fs-5 <?= $cls ?>"><?= dinheiro((float)$val) ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-calculator"></i> Balancete — <?= $ano ?>
  </div>
  <?php if (empty($dados)): ?>
    <div class="card-body text-body-secondary">Nenhum dado para <?= $ano ?>.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Competência</th>
          <th class="text-end">Arrecadado</th>
          <th class="text-end">Despesas</th>
          <th class="text-end">Folha</th>
          <th class="text-end">Saldo</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $r): ?>
        <tr>
          <td class="fw-semibold"><?= rotulomes($r['competencia']) ?></td>
          <td class="text-end text-success"><?= dinheiro((float)$r['arrecadado']) ?></td>
          <td class="text-end text-danger"><?= dinheiro((float)$r['despesas']) ?></td>
          <td class="text-end text-danger"><?= dinheiro((float)$r['folha']) ?></td>
          <td class="text-end fw-semibold <?= $r['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
            <?= dinheiro((float)$r['saldo']) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td>Total</td>
          <td class="text-end text-success"><?= dinheiro($totArrecadado) ?></td>
          <td class="text-end text-danger"><?= dinheiro($totDespesas) ?></td>
          <td class="text-end text-danger"><?= dinheiro($totFolha) ?></td>
          <td class="text-end <?= $totSaldo >= 0 ? 'text-success' : 'text-danger' ?>"><?= dinheiro($totSaldo) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ABA: INADIMPLÊNCIA                                                      -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<?php if ($aba === 'inadimplencia'): ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3 d-flex justify-content-between align-items-center">
    <span><i class="bi bi-exclamation-triangle"></i> Inadimplentes — <?= htmlspecialchars($competencia) ?></span>
    <span class="badge rounded-pill badge-vencido"><?= count($dados) ?> unidade(s)</span>
  </div>
  <?php if (empty($dados)): ?>
    <div class="card-body text-center py-4" style="color:#198754; font-size:.9rem;">
      <i class="bi bi-check-circle-fill"></i> Nenhuma inadimplência nesta competência.
    </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Unidade</th><th>Responsável</th>
          <th class="text-end">Valor</th><th>Vencimento</th><th>Atraso</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $r): ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($r['unidade']) ?></td>
          <td><?= htmlspecialchars($r['responsavel'] ?? '—') ?></td>
          <td class="text-end"><?= dinheiro((float)$r['valor']) ?></td>
          <td><?= dataBR($r['vencimento']) ?></td>
          <td>
            <?php if ($r['dias_atraso'] > 0): ?>
              <span style="color:#dc3545; font-size:.85rem;"><?= $r['dias_atraso'] ?> dias</span>
            <?php else: ?>
              <span class="text-body-secondary" style="font-size:.85rem;">No prazo</span>
            <?php endif; ?>
          </td>
          <td><span class="badge rounded-pill badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="2">Total em aberto</td>
          <td class="text-end"><?= dinheiro(array_sum(array_column($dados, 'valor'))) ?></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ABA: DESPESAS                                                           -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<?php if ($aba === 'despesas'): ?>

<?php $totDespAno = array_sum(array_column($dados, 'total')); ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100"><div class="card-body">
      <div class="text-body-secondary mb-1" style="font-size:.8rem;">Total lançado</div>
      <div class="fw-bold fs-5"><?= dinheiro($totDespAno) ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100"><div class="card-body">
      <div class="text-body-secondary mb-1" style="font-size:.8rem;">Pago</div>
      <div class="fw-bold fs-5 text-success"><?= dinheiro(array_sum(array_column($dados, 'total_pago'))) ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100"><div class="card-body">
      <div class="text-body-secondary mb-1" style="font-size:.8rem;">Pendente</div>
      <div class="fw-bold fs-5 text-warning"><?= dinheiro(array_sum(array_column($dados, 'total_pendente'))) ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100"><div class="card-body">
      <div class="text-body-secondary mb-1" style="font-size:.8rem;">Meses com lançamento</div>
      <div class="fw-bold fs-5"><?= count($dados) ?></div>
    </div></div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-receipt"></i> Despesas por mês — <?= $ano ?>
  </div>
  <?php if (empty($dados)): ?>
    <div class="card-body text-body-secondary">Nenhuma despesa registrada em <?= $ano ?>.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Competência</th><th class="text-center">Qtd</th>
          <th class="text-end">Total</th><th class="text-end">Pago</th><th class="text-end">Pendente</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $r): ?>
        <tr>
          <td class="fw-semibold"><?= rotulomes($r['competencia']) ?></td>
          <td class="text-center"><?= $r['qtd'] ?></td>
          <td class="text-end"><?= dinheiro((float)$r['total']) ?></td>
          <td class="text-end text-success"><?= dinheiro((float)$r['total_pago']) ?></td>
          <td class="text-end text-warning"><?= dinheiro((float)$r['total_pendente']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td>Total</td><td></td>
          <td class="text-end"><?= dinheiro($totDespAno) ?></td>
          <td class="text-end text-success"><?= dinheiro(array_sum(array_column($dados, 'total_pago'))) ?></td>
          <td class="text-end text-warning"><?= dinheiro(array_sum(array_column($dados, 'total_pendente'))) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ABA: FOLHA DE PESSOAL                                                   -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<?php if ($aba === 'folha'): ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-people"></i> Folha de pessoal por mês — <?= $ano ?>
  </div>
  <?php if (empty($dados)): ?>
    <div class="card-body text-body-secondary">Nenhum registro de folha em <?= $ano ?>.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Competência</th><th class="text-center">Funcionários</th>
          <th class="text-end">Total Folha</th><th class="text-end">Pago</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $r): ?>
        <tr>
          <td class="fw-semibold"><?= rotulomes($r['competencia']) ?></td>
          <td class="text-center"><?= $r['qtd_funcionarios'] ?></td>
          <td class="text-end"><?= dinheiro((float)$r['total']) ?></td>
          <td class="text-end text-success"><?= dinheiro((float)$r['total_pago']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td>Total</td><td></td>
          <td class="text-end"><?= dinheiro(array_sum(array_column($dados, 'total'))) ?></td>
          <td class="text-end text-success"><?= dinheiro(array_sum(array_column($dados, 'total_pago'))) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ABA: POR UNIDADE                                                        -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<?php if ($aba === 'unidade'): ?>

<?php if (!$unidadeId): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Selecione uma unidade no filtro acima para ver o extrato.
  </div>
<?php elseif (empty($dados)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-body-secondary">Nenhum registro para esta unidade em <?= $ano ?>.</div>
  </div>
<?php else: ?>

<?php
$totalPago   = array_sum(array_map(fn($r) => $r['status'] === 'pago' ? (float)$r['valor'] : 0, $dados));
$totalAberto = array_sum(array_map(fn($r) => $r['status'] !== 'pago' ? (float)$r['valor'] : 0, $dados));
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="card border-0 shadow-sm h-100"><div class="card-body">
      <div class="text-body-secondary mb-1" style="font-size:.8rem;">Total pago</div>
      <div class="fw-bold fs-5 text-success"><?= dinheiro($totalPago) ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="card border-0 shadow-sm h-100"><div class="card-body">
      <div class="text-body-secondary mb-1" style="font-size:.8rem;">Em aberto</div>
      <div class="fw-bold fs-5 <?= $totalAberto > 0 ? 'text-danger' : 'text-success' ?>"><?= dinheiro($totalAberto) ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="card border-0 shadow-sm h-100"><div class="card-body">
      <div class="text-body-secondary mb-1" style="font-size:.8rem;">Competências</div>
      <div class="fw-bold fs-5"><?= count($dados) ?></div>
    </div></div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-building"></i> Extrato — <?= $ano ?>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Competência</th><th class="text-end">Valor</th>
          <th>Vencimento</th><th>Pagamento</th><th>Status</th><th>Observação</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $r): ?>
        <tr>
          <td class="fw-semibold"><?= rotulomes($r['competencia']) ?></td>
          <td class="text-end"><?= dinheiro((float)$r['valor']) ?></td>
          <td><?= dataBR($r['vencimento']) ?></td>
          <td><?= $r['data_pagamento'] ? dataBR($r['data_pagamento']) : '—' ?></td>
          <td><span class="badge rounded-pill badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          <td class="text-body-secondary" style="font-size:.85rem;"><?= htmlspecialchars($r['observacao'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>
<?php endif; ?>

<script>
function mudarFiltro(chave, valor) {
  var url = new URL(window.location.href);
  url.searchParams.set(chave, valor);
  window.location.href = url.toString();
}
</script>
