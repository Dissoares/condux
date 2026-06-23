<?php
/**
 * @var array      $resumoMes
 * @var array      $totaisGlobais
 * @var array      $resumoExtras
 * @var array[]    $extrasRecentes
 * @var Projeto[]  $projetosRecentes
 * @var int        $totalProjetosAtivos
 * @var Vistoria[] $vistoriasAVencer
 * @var int        $totalUnidades
 * @var int        $totalMoradores
 * @var int        $totalPrestadoras
 */
$tituloPagina = 'Painel';
require_once RAIZ . '/views/layouts/cabecalho.php';

$nomeAdmin = explode(' ', Sessao::usuarioAtual()['nome'] ?? 'Administrador')[0];

// Mês atual
$mesAtual       = (int) date('m');
$anoAtual       = (int) date('Y');
$nomesMeses     = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                   'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$nomeMesAtual   = $nomesMeses[$mesAtual] . '/' . $anoAtual;

// Financeiro mês
$mPagas      = (int)   ($resumoMes['total_pagas']     ?? 0);
$mAtrasadas  = (int)   ($resumoMes['total_atrasadas']  ?? 0);
$mPendentes  = (int)   ($resumoMes['total_pendentes']  ?? 0);
$mArrecadado = (float) ($resumoMes['valor_arrecadado'] ?? 0);
$mAtrasVal   = (float) ($resumoMes['valor_atrasado']   ?? 0);
$mPendVal    = (float) ($resumoMes['valor_pendente']   ?? 0);
$mTotal      = $mPagas + $mAtrasadas + $mPendentes;
$pct         = $mTotal > 0 ? round(($mPagas / $mTotal) * 100) : 0;

// Globais
$gAtrasVal  = (float) ($totaisGlobais['valor_total_atrasado']  ?? 0);
$gPendVal   = (float) ($totaisGlobais['valor_total_pendente']  ?? 0);
$gAtrasQtd  = (int)   ($totaisGlobais['qtd_atrasadas']         ?? 0);
$gPendQtd   = (int)   ($totaisGlobais['qtd_pendentes']         ?? 0);

// Extras
$eAtras  = (int)   ($resumoExtras['total_atrasadas'] ?? 0);
$ePend   = (int)   ($resumoExtras['total_pendentes'] ?? 0);
$eArrec  = (float) ($resumoExtras['valor_atrasado']  ?? 0);
?>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
  <div>
    <h4 class="fw-bold mb-0">Olá, <?= htmlspecialchars($nomeAdmin) ?> 👋</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.875rem;">
      <i class="bi bi-calendar3 me-1"></i><?= $nomeMesAtual ?>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('taxas/gerar-lote') ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-lightning-fill"></i> Gerar taxas
    </a>
    <a href="<?= url('unidades/nova') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-plus-lg"></i> Nova unidade
    </a>
  </div>
</div>

<!-- Quick stats -->
<div class="row g-2 mb-4">
  <?php
  $quickStats = [
    ['icon' => 'bi-building',        'label' => 'Unidades',        'valor' => $totalUnidades,       'cor' => 'secondary', 'link' => 'unidades'],
    ['icon' => 'bi-people-fill',     'label' => 'Moradores',       'valor' => $totalMoradores,      'cor' => 'info',      'link' => 'condominios'],
    ['icon' => 'bi-hammer',          'label' => 'Prestadoras',     'valor' => $totalPrestadoras,    'cor' => 'secondary', 'link' => 'prestadoras'],
    ['icon' => 'bi-kanban-fill',     'label' => 'Proj. ativos',    'valor' => $totalProjetosAtivos, 'cor' => 'primary',   'link' => 'projetos'],
    ['icon' => 'bi-clipboard-check', 'label' => 'Vistorias agendadas', 'valor' => count($vistoriasAVencer), 'cor' => count($vistoriasAVencer) > 0 ? 'warning' : 'secondary', 'link' => 'vistorias'],
  ];
  foreach ($quickStats as $qs): ?>
  <div class="col">
    <a href="<?= url($qs['link']) ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 text-center py-3 px-2 card-hover">
        <div class="text-<?= $qs['cor'] ?> mb-1" style="font-size:1.3rem;"><i class="bi <?= $qs['icon'] ?>"></i></div>
        <div class="fw-bold fs-5 lh-1"><?= $qs['valor'] ?></div>
        <div class="text-body-secondary mt-1" style="font-size:.7rem;"><?= $qs['label'] ?></div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Financeiro do mês -->
<h6 class="text-body-secondary fw-semibold mb-2 text-uppercase" style="font-size:.7rem;letter-spacing:.08em;">
  <i class="bi bi-cash-stack me-1"></i>Taxa condominial — <?= $nomeMesAtual ?>
</h6>
<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-success">
      <div class="card-body">
        <i class="bi bi-check-circle-fill stat-icone"></i>
        <div class="stat-label">Pagas</div>
        <div class="stat-valor"><?= $mPagas ?></div>
        <div class="stat-detalhe"><?= dinheiro($mArrecadado) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-danger">
      <div class="card-body">
        <i class="bi bi-exclamation-circle-fill stat-icone"></i>
        <div class="stat-label">Atrasadas</div>
        <div class="stat-valor"><?= $mAtrasadas ?></div>
        <div class="stat-detalhe"><?= dinheiro($mAtrasVal) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-warning">
      <div class="card-body">
        <i class="bi bi-clock-fill stat-icone"></i>
        <div class="stat-label">Pendentes (geral)</div>
        <div class="stat-valor"><?= $gPendQtd ?></div>
        <div class="stat-detalhe"><?= dinheiro($gPendVal) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-primary">
      <div class="card-body">
        <i class="bi bi-cash-coin stat-icone"></i>
        <div class="stat-label">Arrecadado</div>
        <div class="stat-valor" style="font-size:1.3rem;"><?= dinheiro($mArrecadado) ?></div>
        <div class="stat-detalhe"><?= $pct ?>% da meta</div>
      </div>
    </div>
  </div>
</div>

<!-- Progress bar -->
<?php if ($mTotal > 0): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-semibold" style="font-size:.875rem;">Progresso de arrecadação — <?= $nomeMesAtual ?></span>
      <span class="fw-bold text-success"><?= $pct ?>%</span>
    </div>
    <div class="progress" style="height:8px;border-radius:999px;">
      <div class="progress-bar bg-success" style="width:<?= $pct ?>%;border-radius:999px;transition:width .6s ease;" role="progressbar"></div>
    </div>
    <div class="d-flex justify-content-between mt-2" style="font-size:.75rem;color:var(--bs-secondary-color);">
      <span><?= $mPagas ?> pagas</span>
      <?php if ($mAtrasadas): ?><span class="text-danger"><?= $mAtrasadas ?> atrasadas</span><?php endif; ?>
      <span><?= $mPendentes ?> a vencer</span>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Visão global de inadimplência -->
<h6 class="text-body-secondary fw-semibold mb-2 text-uppercase" style="font-size:.7rem;letter-spacing:.08em;">
  <i class="bi bi-globe me-1"></i>Visão geral — todos os meses
</h6>
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <a href="<?= url('taxas') ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-danger bg-opacity-10 text-danger" style="width:48px;height:48px;font-size:1.3rem;">
            <i class="bi bi-exclamation-circle-fill"></i>
          </div>
          <div>
            <div class="text-body-secondary mb-1" style="font-size:.72rem;">TOTAL EM ATRASO (TODOS OS MESES)</div>
            <div class="fw-bold fs-5 text-danger lh-1"><?= dinheiro($gAtrasVal) ?></div>
            <div class="text-body-secondary mt-1" style="font-size:.78rem;"><?= $gAtrasQtd ?> taxa<?= $gAtrasQtd !== 1 ? 's' : '' ?> em aberto</div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-6">
    <a href="<?= url('taxas') ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-warning bg-opacity-10 text-warning" style="width:48px;height:48px;font-size:1.3rem;">
            <i class="bi bi-clock-fill"></i>
          </div>
          <div>
            <div class="text-body-secondary mb-1" style="font-size:.72rem;">TOTAL A VENCER (TODOS OS MESES)</div>
            <div class="fw-bold fs-5 lh-1"><?= dinheiro($gPendVal) ?></div>
            <div class="text-body-secondary mt-1" style="font-size:.78rem;"><?= $gPendQtd ?> taxa<?= $gPendQtd !== 1 ? 's' : '' ?> pendente<?= $gPendQtd !== 1 ? 's' : '' ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<!-- Taxas extras + Vistorias -->
<div class="row g-3 mb-4">

  <!-- Taxas extras -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-2">
          <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-plus-circle-fill"></i></span>
          <span class="fw-semibold">Taxas Extras</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <?php if ($eAtras > 0): ?>
            <span class="badge bg-danger"><?= $eAtras ?> atrasadas</span>
          <?php elseif ($ePend > 0): ?>
            <span class="badge bg-warning text-dark"><?= $ePend ?> pendentes</span>
          <?php endif; ?>
          <a href="<?= url('taxas-extra') ?>" class="btn btn-outline-secondary btn-sm">Ver todas</a>
        </div>
      </div>
      <?php if (empty($extrasRecentes)): ?>
        <div class="card-body text-center text-body-secondary py-4">
          <i class="bi bi-plus-circle opacity-25 fs-2 d-block mb-2"></i>
          Nenhuma taxa extra cadastrada.
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
          <tbody>
            <?php foreach ($extrasRecentes as $ex):
              $exAtrasadas = (int) $ex['atrasadas'];
              $exPagas     = (int) $ex['pagas'];
              $exTotal     = (int) $ex['total_unidades'];
              $statusEx    = $exAtrasadas > 0 ? 'danger' : ($exPagas >= $exTotal && $exTotal > 0 ? 'success' : 'warning');
            ?>
            <tr>
              <td>
                <a href="<?= url("taxas-extra/{$ex['id']}") ?>" class="text-decoration-none fw-semibold text-body">
                  <?= htmlspecialchars($ex['nome_projeto'] ?? $ex['nome']) ?>
                </a>
                <div class="text-body-secondary" style="font-size:.72rem;">
                  <?= htmlspecialchars($ex['nome']) ?> — <?= dataBR($ex['vencimento']) ?>
                </div>
              </td>
              <td class="text-end">
                <span class="badge bg-<?= $statusEx ?> bg-opacity-<?= $statusEx === 'success' ? '75' : '85' ?>">
                  <?= $exPagas ?>/<?= $exTotal ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Vistorias agendadas -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-2">
          <span class="icone-secao bg-info bg-opacity-10 text-info"><i class="bi bi-clipboard-check-fill"></i></span>
          <span class="fw-semibold">Vistorias agendadas</span>
          <?php if (!empty($vistoriasAVencer)): ?>
            <span class="badge bg-warning text-dark"><?= count($vistoriasAVencer) ?></span>
          <?php endif; ?>
        </div>
        <a href="<?= url('vistorias') ?>" class="btn btn-outline-secondary btn-sm">Ver todas</a>
      </div>
      <?php if (empty($vistoriasAVencer)): ?>
        <div class="card-body text-center text-body-secondary py-4">
          <i class="bi bi-clipboard-check opacity-25 fs-2 d-block mb-2"></i>
          Nenhuma vistoria com validade próxima.
        </div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($vistoriasAVencer as $v):
          $dias = (int) round((strtotime($v->dataVistoria) - time()) / 86400);
          $cor  = $dias < 0 ? 'danger' : ($dias <= 7 ? 'warning' : 'secondary');
          $badge = $dias < 0 ? abs($dias) . 'd atraso' : $dias . 'd';
        ?>
        <a href="<?= url("vistorias/{$v->id}") ?>"
           class="list-group-item list-group-item-action border-0 py-2 px-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-semibold" style="font-size:.85rem;"><?= htmlspecialchars($v->rotuloTipo()) ?></div>
              <div class="text-body-secondary" style="font-size:.75rem;">
                <?= dataBR($v->dataVistoria) ?> · <?= htmlspecialchars($v->nomePrestadora ?? $v->nomeResponsavel ?? '—') ?>
              </div>
            </div>
            <span class="badge bg-<?= $cor ?> flex-shrink-0 ms-2"><?= $badge ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Projetos recentes -->
<h6 class="text-body-secondary fw-semibold mb-2 text-uppercase" style="font-size:.7rem;letter-spacing:.08em;">
  <i class="bi bi-kanban me-1"></i>Projetos recentes
</h6>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center gap-2">
      <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-kanban"></i></span>
      <span class="fw-semibold">Projetos em andamento</span>
      <?php if ($totalProjetosAtivos): ?>
        <span class="badge bg-primary bg-opacity-75"><?= $totalProjetosAtivos ?></span>
      <?php endif; ?>
    </div>
    <a href="<?= url('projetos') ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
  </div>
  <?php if (empty($projetosRecentes)): ?>
    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5 text-center">
      <i class="bi bi-kanban text-body-secondary mb-2" style="font-size:2.5rem;opacity:.35;"></i>
      <p class="text-body-secondary mb-2">Nenhum projeto cadastrado.</p>
      <a href="<?= url('projetos/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Criar primeiro projeto
      </a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Projeto</th>
            <th class="d-none d-sm-table-cell">Responsável</th>
            <th class="d-none d-md-table-cell">Valor est.</th>
            <th>Status</th>
            <th style="width:50px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($projetosRecentes as $projeto): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($projeto->nome) ?></td>
            <td class="d-none d-sm-table-cell text-body-secondary"><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td>
            <td class="d-none d-md-table-cell"><?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?></td>
            <td><span class="badge rounded-pill badge-<?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span></td>
            <td>
              <a href="<?= url("projetos/{$projeto->id}") ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<style>
.card-hover { transition: transform .12s, box-shadow .12s; cursor: pointer; }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
</style>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
