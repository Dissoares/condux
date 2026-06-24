<?php
/**
 * @var Funcionario[] $funcionarios
 * @var array[]       $pagamentos    — linhas da folha para a competência
 * @var array[]       $pagPorFunc    — indexado por funcionario_id
 * @var string[]      $competencias  — YYYY-MM disponíveis
 * @var string        $compFiltro    — competência selecionada
 * @var string|null   $mensagem
 * @var string|null   $erroMensagem
 */
$tituloPagina = 'Folha de Pagamento';
require_once RAIZ . '/views/layouts/cabecalho.php';

$nomesMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$fmtComp = function(string $c) use ($nomesMeses): string {
    [$ano, $mes] = explode('-', $c);
    return ($nomesMeses[(int)$mes] ?? $mes) . '/' . $ano;
};
$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;
$fmtVal  = fn(?float $v)  => $v !== null ? 'R$ ' . number_format((float)$v, 2, ',', '.') : null;
$hoje    = date('Y-m-d');

// Totais da competência exibida
$totalVal    = 0.0;
$totalPagos  = 0;
$totalPend   = 0;
$totalAtras  = 0;
foreach ($funcionarios as $f) {
    $p = $pagPorFunc[$f->id] ?? null;
    if (!$p) continue;
    $totalVal += (float)$p['valor'];
    if ($p['status'] === 'pago') { $totalPagos++; continue; }
    $atrasado = !empty($p['data_prevista']) && $p['data_prevista'] < $hoje;
    $atrasado ? $totalAtras++ : $totalPend++;
}
?>

<style>
.func-card { transition: box-shadow .12s; }
.func-card:hover { box-shadow: 0 .4rem 1rem rgba(0,0,0,.1) !important; }
.status-pill { font-size:.7rem; padding:.2em .6em; border-radius:2rem; }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-people"></i> Folha de Pagamento</h4>
    <span class="text-body-secondary" style="font-size:.82rem;"><?= $fmtComp($compFiltro) ?></span>
  </div>
  <a href="<?= url('funcionarios') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-person-badge"></i> Funcionários
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<!-- Filtro de competência + totais -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
  <form method="GET" action="<?= url('folha-pagamento') ?>" class="d-flex align-items-center gap-2">
    <label class="form-label mb-0 text-body-secondary" style="font-size:.82rem;white-space:nowrap;">Competência:</label>
    <input type="month" name="comp" class="form-control form-control-sm" style="width:160px;"
           value="<?= htmlspecialchars($compFiltro) ?>" onchange="this.form.submit()">
  </form>

  <?php if ($funcionarios): ?>
  <div class="d-flex gap-2 flex-wrap ms-auto">
    <?php foreach ([
      [$fmtVal($totalVal) ?? 'R$ 0,00', 'Total folha',  'primary'],
      [$totalPagos,  'Pago' . ($totalPagos  !== 1 ? 's' : ''), 'success'],
      [$totalPend,   'Pendente' . ($totalPend   !== 1 ? 's' : ''), 'warning'],
      [$totalAtras,  'Atrasado' . ($totalAtras  !== 1 ? 's' : ''), 'danger'],
    ] as [$val, $lbl, $cor]): ?>
    <div class="text-center px-3 py-1 rounded-2 bg-<?= $cor ?>-subtle">
      <div class="fw-bold text-<?= $cor ?>-emphasis" style="font-size:.95rem;"><?= $val ?></div>
      <div class="text-<?= $cor ?>-emphasis" style="font-size:.68rem;"><?= $lbl ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Cards de funcionários -->
<?php if (empty($funcionarios)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-people fs-1 opacity-25 d-block mb-3"></i>
    Nenhum funcionário ativo cadastrado.
    <div class="mt-3">
      <a href="<?= url('funcionarios/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Cadastrar funcionário
      </a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($funcionarios as $f): ?>
  <?php
    $p         = $pagPorFunc[$f->id] ?? null;
    $atrasado  = $p && $p['status'] === 'pendente' && !empty($p['data_prevista']) && $p['data_prevista'] < $hoje;
    $pago      = $p && $p['status'] === 'pago';
    $pendente  = $p && !$pago && !$atrasado;
    $semReg    = !$p;

    // Calcular data prevista padrão para este mês
    $dataPrevPad = $f->diaPagamento
        ? substr($compFiltro, 0, 8) . str_pad((string)$f->diaPagamento, 2, '0', STR_PAD_LEFT)
        : null;

    if ($atrasado)     [$corBorda, $corAvatar, $ico] = ['danger',    'danger',    'exclamation-triangle-fill'];
    elseif ($pago)     [$corBorda, $corAvatar, $ico] = ['success',   'success',   'check-circle-fill'];
    elseif ($pendente) [$corBorda, $corAvatar, $ico] = ['warning',   'warning',   'clock-fill'];
    else               [$corBorda, $corAvatar, $ico] = ['secondary', 'secondary', 'dash-circle'];
  ?>
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm func-card h-100"
         style="border-left:3px solid var(--bs-<?= $corBorda ?>)!important;cursor:pointer;"
         onclick="window.location='<?= url("funcionarios/{$f->id}?aba=pagamentos") ?>'"
         role="link" tabindex="0"
         onkeydown="if(event.key==='Enter')window.location='<?= url("funcionarios/{$f->id}?aba=pagamentos") ?>'">
      <div class="card-body p-3">

        <!-- Linha superior: avatar + nome + status -->
        <div class="d-flex align-items-start gap-3 mb-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                      bg-<?= $corAvatar ?>-subtle text-<?= $corAvatar ?>-emphasis"
               style="width:40px;height:40px;font-size:.9rem;font-weight:700;">
            <?= mb_strtoupper(mb_substr($f->nome, 0, 1)) ?>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold text-truncate"><?= htmlspecialchars($f->nome) ?></div>
            <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($f->cargo) ?></div>
          </div>
          <i class="bi bi-<?= $ico ?> text-<?= $corBorda ?> flex-shrink-0 mt-1" style="font-size:1rem;"></i>
        </div>

        <!-- Salário + status -->
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div>
            <div class="fw-bold" style="font-size:1.05rem;">
              <?= $p ? $fmtVal((float)$p['valor']) : ($f->salario !== null ? $fmtVal($f->salario) : '—') ?>
            </div>
            <div class="text-body-secondary" style="font-size:.72rem;">
              <?php if ($f->diaPagamento): ?>
                Vence dia <?= $f->diaPagamento ?>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($pago): ?>
            <span class="badge bg-success-subtle text-success-emphasis status-pill">Pago</span>
          <?php elseif ($atrasado): ?>
            <span class="badge bg-danger-subtle text-danger-emphasis status-pill">Atrasado</span>
          <?php elseif ($pendente): ?>
            <span class="badge bg-warning-subtle text-warning-emphasis status-pill">Pendente</span>
          <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary-emphasis status-pill">Sem registro</span>
          <?php endif; ?>
        </div>

        <?php if ($p && $p['data_pagamento']): ?>
          <div class="text-success" style="font-size:.75rem;">
            <i class="bi bi-check2 me-1"></i>Pago em <?= $fmtData($p['data_pagamento']) ?>
          </div>
        <?php elseif ($p && $p['data_prevista']): ?>
          <div class="text-<?= $atrasado ? 'danger' : 'body-secondary' ?>" style="font-size:.75rem;">
            <i class="bi bi-calendar me-1"></i>Previsto: <?= $fmtData($p['data_prevista']) ?>
          </div>
        <?php endif; ?>

        <!-- Ações -->
        <div class="d-flex gap-2 mt-3" onclick="event.stopPropagation()">
          <?php if (!$pago): ?>
          <form action="<?= url("funcionarios/{$f->id}/pagamentos") ?>" method="POST" class="d-flex gap-1 flex-grow-1">
            <input type="hidden" name="competencia"    value="<?= htmlspecialchars($compFiltro) ?>">
            <input type="hidden" name="valor"          value="<?= $f->salario !== null ? number_format($f->salario, 2, '.', '') : ($p['valor'] ?? '') ?>">
            <input type="hidden" name="data_prevista"  value="<?= htmlspecialchars($dataPrevPad ?? $p['data_prevista'] ?? '') ?>">
            <input type="hidden" name="data_pagamento" value="<?= $hoje ?>">
            <?php if ($p): ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><?php endif; ?>
            <button type="submit" class="btn btn-success btn-sm flex-grow-1"
                    onclick="return confirm('Confirmar pagamento de <?= htmlspecialchars(addslashes($f->nome)) ?> hoje?')">
              <i class="bi bi-check2"></i> Confirmar pago
            </button>
          </form>
          <?php endif; ?>
          <a href="<?= url("funcionarios/{$f->id}?aba=pagamentos") ?>"
             class="btn btn-outline-secondary btn-sm" title="Ver histórico">
            <i class="bi bi-clock-history"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
