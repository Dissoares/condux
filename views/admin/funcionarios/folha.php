<?php
/**
 * @var array[]     $resumos      — resumo por competência
 * @var array[]     $pagamentos   — linhas da folha filtrada (ou vazia)
 * @var string[]    $competencias — lista de YYYY-MM disponíveis
 * @var string|null $compFiltro   — competência selecionada
 * @var string|null $mensagem
 * @var string|null $erroMensagem
 */
$tituloPagina = 'Folha de Pagamento';
require_once RAIZ . '/views/layouts/cabecalho.php';

$nomesMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$fmtComp = function(string $c) use ($nomesMeses): string {
    [$ano, $mes] = explode('-', $c);
    return ($nomesMeses[(int)$mes] ?? $mes) . '/' . $ano;
};
$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$fmtVal  = fn(?float $v)  => $v !== null ? 'R$ ' . number_format((float)$v, 2, ',', '.') : '—';
$hoje    = date('Y-m-d');
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h4 class="fw-semibold mb-0"><i class="bi bi-people"></i> Folha de Pagamento</h4>
  <a href="<?= url('funcionarios') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-person-badge"></i> Ver funcionários
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<!-- ── Resumo por competência ── -->
<?php if (!empty($resumos)): ?>
<div class="row g-2 mb-4">
  <?php foreach ($resumos as $r): ?>
  <?php
    $atrasados = (int) $r['total_atrasados'];
    $pagas     = (int) $r['total_pagas'];
    $total     = (int) $r['total'];
    $pendentes = (int) $r['total_pendentes'];
    $cor       = $atrasados > 0 ? 'danger' : ($pendentes > 0 ? 'warning' : 'success');
    $ativo     = $compFiltro === $r['competencia'];
  ?>
  <div class="col-6 col-md-4 col-lg-3">
    <a href="<?= url('folha-pagamento?comp=' . $r['competencia']) ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 <?= $ativo ? 'border border-primary' : '' ?>"
           style="<?= $ativo ? 'border-width:2px!important;' : '' ?>">
        <div class="card-body py-3 px-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="fw-semibold" style="font-size:.9rem;"><?= $fmtComp($r['competencia']) ?></span>
            <?php if ($atrasados > 0): ?>
              <span class="badge bg-danger-subtle text-danger-emphasis" style="font-size:.65rem;"><?= $atrasados ?> atrasado<?= $atrasados > 1 ? 's' : '' ?></span>
            <?php elseif ($pendentes > 0): ?>
              <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:.65rem;"><?= $pendentes ?> pendente<?= $pendentes > 1 ? 's' : '' ?></span>
            <?php else: ?>
              <span class="badge bg-success-subtle text-success-emphasis" style="font-size:.65rem;">Quitado</span>
            <?php endif; ?>
          </div>
          <div class="fw-bold text-<?= $cor ?>" style="font-size:1rem;"><?= $fmtVal((float)$r['valor_total']) ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;"><?= $pagas ?>/<?= $total ?> pagos</div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Detalhe da competência selecionada ── -->
<?php if ($compFiltro): ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <h5 class="mb-0 fw-semibold"><?= $fmtComp($compFiltro) ?></h5>
  <a href="<?= url('folha-pagamento') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-x"></i> Limpar filtro
  </a>
</div>

<?php if (empty($pagamentos)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-body-secondary">
      <i class="bi bi-cash-stack fs-1 opacity-25 d-block mb-2"></i>
      Nenhum pagamento registrado para esta competência.
    </div>
  </div>
<?php else: ?>
<?php
  $totalValor  = array_sum(array_column($pagamentos, 'valor'));
  $totalPagos  = count(array_filter($pagamentos, fn($p) => $p['status'] === 'pago'));
  $totalPend   = count(array_filter($pagamentos, fn($p) => $p['status'] === 'pendente'));
  $totalAtraso = count(array_filter($pagamentos, fn($p) => $p['status'] === 'pendente' && $p['data_prevista'] && $p['data_prevista'] < $hoje));
?>

<!-- Mini-resumo -->
<div class="row g-2 mb-3">
  <?php foreach ([
    ['Total folha',  $fmtVal($totalValor),   'primary'],
    ['Pagos',        $totalPagos,             'success'],
    ['Pendentes',    $totalPend - $totalAtraso, 'warning'],
    ['Atrasados',    $totalAtraso,            'danger'],
  ] as [$lbl, $val, $cor]): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fw-bold text-<?= $cor ?>" style="font-size:1.1rem;"><?= $val ?></div>
      <div class="text-body-secondary" style="font-size:.72rem;"><?= $lbl ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
      <thead class="table-light">
        <tr>
          <th>Funcionário</th>
          <th>Cargo</th>
          <th>Valor</th>
          <th>Previsto</th>
          <th>Pago em</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pagamentos as $p): ?>
        <?php
          $atrasado = $p['status'] === 'pendente' && !empty($p['data_prevista']) && $p['data_prevista'] < $hoje;
        ?>
        <tr>
          <td>
            <a href="<?= url('funcionarios/' . $p['funcionario_id'] . '?aba=pagamentos') ?>"
               class="text-decoration-none fw-semibold">
              <?= htmlspecialchars($p['nome']) ?>
            </a>
          </td>
          <td class="text-body-secondary"><?= htmlspecialchars($p['cargo']) ?></td>
          <td class="fw-semibold"><?= $fmtVal((float)$p['valor']) ?></td>
          <td><?= $fmtData($p['data_prevista']) ?></td>
          <td><?= $fmtData($p['data_pagamento']) ?></td>
          <td>
            <?php if ($p['status'] === 'pago'): ?>
              <span class="badge bg-success-subtle text-success-emphasis">Pago</span>
            <?php elseif ($atrasado): ?>
              <span class="badge bg-danger-subtle text-danger-emphasis">Atrasado</span>
            <?php else: ?>
              <span class="badge bg-warning-subtle text-warning-emphasis">Pendente</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= url('funcionarios/' . $p['funcionario_id'] . '?aba=pagamentos') ?>"
               class="btn btn-outline-secondary btn-sm py-0 px-2" title="Ver no perfil">
              <i class="bi bi-box-arrow-up-right"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="2" class="fw-semibold">Total</td>
          <td class="fw-bold"><?= $fmtVal($totalValor) ?></td>
          <td colspan="4"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php endif; ?>

<?php elseif (empty($resumos)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-people fs-1 opacity-25 d-block mb-3"></i>
    Nenhum pagamento registrado ainda.<br>
    <span style="font-size:.85rem;">Acesse o perfil de um funcionário para lançar pagamentos.</span>
    <div class="mt-3">
      <a href="<?= url('funcionarios') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-person-badge"></i> Ver funcionários
      </a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
