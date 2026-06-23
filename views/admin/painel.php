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

$nomeAdmin  = explode(' ', Sessao::usuarioAtual()['nome'] ?? 'Administrador')[0];
$mesAtual   = (int) date('m');
$nomesMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$nomeMes    = $nomesMeses[$mesAtual] . '/' . date('Y');

$mPagas      = (int)   ($resumoMes['total_pagas']     ?? 0);
$mAtrasadas  = (int)   ($resumoMes['total_atrasadas']  ?? 0);
$mPendentes  = (int)   ($resumoMes['total_pendentes']  ?? 0);
$mArrecadado = (float) ($resumoMes['valor_arrecadado'] ?? 0);
$mAtrasVal   = (float) ($resumoMes['valor_atrasado']   ?? 0);
$mTotal      = $mPagas + $mAtrasadas + $mPendentes;
$pct         = $mTotal > 0 ? round(($mPagas / $mTotal) * 100) : 0;

$gAtrasVal         = (float) ($totaisGlobais['valor_total_atrasado']       ?? 0);
$gPendVal          = (float) ($totaisGlobais['valor_total_pendente']        ?? 0);
$gAtrasQtd         = (int)   ($totaisGlobais['qtd_atrasadas']               ?? 0);
$gPendQtd          = (int)   ($totaisGlobais['qtd_pendentes']               ?? 0);
$qtdInadimplentes  = (int)   ($totaisGlobais['qtd_unidades_inadimplentes']  ?? 0);

$ePagas       = (int)   ($resumoExtras['total_pagas']        ?? 0);
$eAtras       = (int)   ($resumoExtras['total_atrasadas']    ?? 0);
$ePend        = (int)   ($resumoExtras['total_pendentes']    ?? 0);
$eAtrasVal    = (float) ($resumoExtras['valor_atrasado']     ?? 0);
$ePendVal     = (float) ($resumoExtras['valor_pendente']     ?? 0);
$eArrecadado  = (float) ($resumoExtras['valor_arrecadado_mes'] ?? 0);
$ePendMes     = (int)   ($resumoExtras['total_pendentes_mes']  ?? 0);
$ePendMesVal  = (float) ($resumoExtras['valor_pendente_mes']   ?? 0);
$eTotal       = (int)   ($resumoExtras['total_cobranças']      ?? ($ePagas + $eAtras + $ePend));
$ePct         = $eTotal > 0 ? round(($ePagas / $eTotal) * 100) : 0;
$totalArrecadadoMes = $mArrecadado + $eArrecadado;
?>

<style>
.card-hover { transition: transform .12s, box-shadow .12s; cursor:pointer; }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
.secao-header {
  display: flex; align-items: center; gap: .5rem;
  font-size: .72rem; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--bs-secondary-color);
  margin-bottom: .75rem;
}
.global-item {
  display: flex; align-items: center; gap: .75rem;
  padding: .6rem .75rem; border-radius: .5rem;
}
</style>

<!-- Cabeçalho da página -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
  <div>
    <h4 class="fw-bold mb-0">Olá, <?= htmlspecialchars($nomeAdmin) ?> 👋</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.875rem;">
      <i class="bi bi-calendar3 me-1"></i><?= $nomeMes ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('taxas/gerar-lote') ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-lightning-fill"></i> Gerar taxas
    </a>
    <a href="<?= url('unidades/nova') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-plus-lg"></i> Nova unidade
    </a>
  </div>
</div>

<!-- ══ BLOCO 2+3: TAXA CONDOMINIAL + TAXAS EXTRAS (lado a lado) ═══════════ -->

<?php if (!empty($qtdAguardando) && $qtdAguardando > 0): ?>
<div class="alert alert-warning d-flex align-items-center justify-content-between gap-3 mb-3 shadow-sm" role="alert">
  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-hourglass-split fs-5"></i>
    <div>
      <strong><?= $qtdAguardando ?> comprovante<?= $qtdAguardando > 1 ? 's' : '' ?> aguardando aprovação</strong>
      <div style="font-size:.8rem;" class="text-body-secondary">Condôminos enviaram comprovantes de pagamento para análise.</div>
    </div>
  </div>
  <a href="<?= url('taxas') ?>" class="btn btn-warning btn-sm text-nowrap">
    <i class="bi bi-eye"></i> Revisar
  </a>
</div>
<?php endif; ?>

<!-- Total arrecadado do mês -->
<div class="card border-0 shadow-sm mb-3" style="background:linear-gradient(135deg,#198754 0%,#0d6efd 100%);">
  <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
    <div class="d-flex align-items-center gap-3">
      <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width:42px;height:42px;">
        <i class="bi bi-graph-up-arrow text-white fs-5"></i>
      </div>
      <div>
        <div class="text-white-50" style="font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;">Total arrecadado — <?= $nomeMes ?></div>
        <div class="text-white fw-bold" style="font-size:1.5rem;line-height:1.1;"><?= dinheiro($totalArrecadadoMes) ?></div>
      </div>
    </div>
    <div class="d-flex gap-3">
      <div class="text-center">
        <div class="text-white-50" style="font-size:.7rem;">Cond.</div>
        <div class="text-white fw-semibold"><?= dinheiro($mArrecadado) ?></div>
      </div>
      <div class="border-start border-white border-opacity-25 mx-1"></div>
      <div class="text-center">
        <div class="text-white-50" style="font-size:.7rem;">Extras</div>
        <div class="text-white fw-semibold"><?= dinheiro($eArrecadado) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">

<div class="col-lg-6 d-flex flex-column">
<div class="secao-header"><i class="bi bi-cash-stack"></i> Taxa Condominial</div>
<div class="card border-0 shadow-sm flex-grow-1">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center gap-2">
      <span class="icone-secao bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></span>
      <span class="fw-semibold">Situação — <?= $nomeMes ?></span>
    </div>
    <a href="<?= url('taxas') ?>" class="btn btn-outline-secondary btn-sm">Ver taxas</a>
  </div>
  <div class="card-body">

    <!-- Stats do mês -->
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-secondary">
          <div class="card-body">
            <i class="bi bi-list-check stat-icone"></i>
            <div class="stat-label">Total</div>
            <div class="stat-valor"><?= $mTotal ?></div>
            <div class="stat-detalhe">unidades</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-success">
          <div class="card-body">
            <i class="bi bi-check-circle-fill stat-icone"></i>
            <div class="stat-label">Pagas</div>
            <div class="stat-valor"><?= $mPagas ?></div>
            <div class="stat-detalhe"><?= dinheiro($mArrecadado) ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-danger">
          <div class="card-body">
            <i class="bi bi-exclamation-circle-fill stat-icone"></i>
            <div class="stat-label">Atrasadas</div>
            <div class="stat-valor"><?= $mAtrasadas ?></div>
            <div class="stat-detalhe"><?= dinheiro($mAtrasVal) ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-warning">
          <div class="card-body">
            <i class="bi bi-clock-fill stat-icone"></i>
            <div class="stat-label">Pendentes</div>
            <div class="stat-valor"><?= $mPendentes ?></div>
            <div class="stat-detalhe"><?= dinheiro((float)($resumoMes['valor_pendente'] ?? 0)) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra de progresso -->
    <?php if ($mTotal > 0): ?>
    <div class="mb-3 px-1">
      <div class="d-flex justify-content-between mb-1" style="font-size:.8rem;">
        <span class="text-body-secondary">Progresso de arrecadação</span>
        <span class="fw-semibold text-success"><?= $pct ?>%</span>
      </div>
      <div class="progress" style="height:7px;border-radius:999px;">
        <div class="progress-bar bg-success" style="width:<?= $pct ?>%;border-radius:999px;" role="progressbar"></div>
      </div>
      <div class="d-flex justify-content-between mt-1" style="font-size:.72rem;color:var(--bs-secondary-color);">
        <span><?= $mPagas ?> pagas</span>
        <?php if ($mAtrasadas): ?><span class="text-danger"><?= $mAtrasadas ?> atrasadas</span><?php endif; ?>
        <?php if ($mPendentes): ?><span><?= $mPendentes ?> a vencer</span><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Totais gerais (todos os meses) -->
    <div class="border-top pt-3">
      <div class="text-body-secondary mb-2" style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;">
        Posição geral — todos os meses
      </div>
      <div class="row g-2">
        <div class="col-md-6">
          <div class="global-item bg-danger bg-opacity-10">
            <div class="text-danger" style="font-size:1.1rem;"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div>
              <div class="fw-bold text-danger"><?= dinheiro($gAtrasVal) ?></div>
              <div class="text-body-secondary" style="font-size:.75rem;"><?= $gAtrasQtd ?> taxa<?= $gAtrasQtd !== 1 ? 's' : '' ?> em atraso</div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="global-item bg-warning bg-opacity-10">
            <div class="text-warning" style="font-size:1.1rem;"><i class="bi bi-clock-fill"></i></div>
            <div>
              <div class="fw-bold"><?= dinheiro($gPendVal) ?></div>
              <div class="text-body-secondary" style="font-size:.75rem;"><?= $gPendQtd ?> taxa<?= $gPendQtd !== 1 ? 's' : '' ?> a vencer</div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</div><!-- /col Taxa Condominial -->

<div class="col-lg-6 d-flex flex-column">
<div class="secao-header"><i class="bi bi-plus-circle"></i> Taxas Extras</div>
<div class="card border-0 shadow-sm flex-grow-1">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center gap-2">
      <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-plus-circle-fill"></i></span>
      <span class="fw-semibold">Cobranças ativas</span>
    </div>
    <a href="<?= url('taxas-extra') ?>" class="btn btn-outline-secondary btn-sm">Ver todas</a>
  </div>
  <div class="card-body">

    <!-- Stats de extras -->
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-secondary">
          <div class="card-body">
            <i class="bi bi-list-check stat-icone"></i>
            <div class="stat-label">Total</div>
            <div class="stat-valor"><?= $eTotal ?></div>
            <div class="stat-detalhe">cobranças</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-success">
          <div class="card-body">
            <i class="bi bi-check-circle-fill stat-icone"></i>
            <div class="stat-label">Pagas</div>
            <div class="stat-valor"><?= $ePagas ?></div>
            <div class="stat-detalhe"><?= $ePct ?>% do total</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-danger">
          <div class="card-body">
            <i class="bi bi-exclamation-circle-fill stat-icone"></i>
            <div class="stat-label">Atrasadas</div>
            <div class="stat-valor"><?= $eAtras ?></div>
            <div class="stat-detalhe"><?= dinheiro($eAtrasVal) ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-stat card-stat-warning">
          <div class="card-body">
            <i class="bi bi-clock-fill stat-icone"></i>
            <div class="stat-label">Pendentes</div>
            <div class="stat-valor"><?= $ePendMes ?></div>
            <div class="stat-detalhe"><?= dinheiro($ePendMesVal) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra de progresso -->
    <?php if ($eTotal > 0): ?>
    <div class="mb-3 px-1">
      <div class="d-flex justify-content-between mb-1" style="font-size:.8rem;">
        <span class="text-body-secondary">Progresso de arrecadação</span>
        <span class="fw-semibold text-success"><?= $ePct ?>%</span>
      </div>
      <div class="progress" style="height:7px;border-radius:999px;">
        <div class="progress-bar bg-success" style="width:<?= $ePct ?>%;border-radius:999px;" role="progressbar"></div>
      </div>
      <div class="d-flex justify-content-between mt-1" style="font-size:.72rem;color:var(--bs-secondary-color);">
        <span><?= $ePagas ?> pagas</span>
        <?php if ($eAtras): ?><span class="text-danger"><?= $eAtras ?> atrasadas</span><?php endif; ?>
        <?php if ($ePend): ?><span><?= $ePend ?> a vencer</span><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tabela de grupos recentes -->
    <?php if (!empty($extrasRecentes)): ?>
    <div class="border-top pt-3">
      <div class="text-body-secondary mb-2" style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;">
        Grupos recentes
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
          <thead class="table-light">
            <tr>
              <th>Taxa extra</th>
              <th class="d-none d-md-table-cell">Vencimento</th>
              <th class="text-end">Pgtos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($extrasRecentes as $ex):
              $exAtras = (int) $ex['atrasadas'];
              $exPagas = (int) $ex['pagas'];
              $exTotal = (int) $ex['total_unidades'];
              $cls     = $exAtras > 0 ? 'danger' : ($exPagas >= $exTotal && $exTotal > 0 ? 'success' : 'warning');
            ?>
            <tr>
              <td>
                <a href="<?= url("taxas-extra/{$ex['id']}") ?>" class="text-decoration-none fw-semibold text-body">
                  <?= htmlspecialchars($ex['nome_projeto'] ?? $ex['nome']) ?>
                </a>
                <div class="text-body-secondary" style="font-size:.72rem;"><?= htmlspecialchars($ex['nome']) ?></div>
              </td>
              <td class="d-none d-md-table-cell text-body-secondary"><?= dataBR($ex['vencimento']) ?></td>
              <td class="text-end"><span class="badge bg-<?= $cls ?>"><?= $exPagas ?>/<?= $exTotal ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="border-top pt-4 text-center text-body-secondary">
      <i class="bi bi-plus-circle opacity-25 fs-2 d-block mb-2"></i>
      Nenhuma taxa extra cadastrada.
    </div>
    <?php endif; ?>

  </div>
</div>
</div><!-- /col Taxas Extras -->

</div><!-- /row bloco 2+3 -->

<!-- ══ BLOCO 3: CONDOMÍNIO ═══════════════════════════════════════════════ -->
<div class="secao-header"><i class="bi bi-building"></i> Condomínio</div>
<div class="row g-2 mb-4">

  <!-- Unidades (com badge de inadimplentes) -->
  <div class="col">
    <a href="<?= url('unidades') ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 text-center py-3 px-2 card-hover position-relative">
        <?php if ($qtdInadimplentes > 0): ?>
          <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;margin:.4rem;">
            <?= $qtdInadimplentes ?>
          </span>
        <?php endif; ?>
        <div class="text-secondary mb-1" style="font-size:1.2rem;"><i class="bi bi-building"></i></div>
        <div class="fw-bold fs-5 lh-1"><?= $totalUnidades ?></div>
        <div class="text-body-secondary mt-1" style="font-size:.68rem;">Unidades</div>
        <?php if ($qtdInadimplentes > 0): ?>
          <div class="text-danger mt-1" style="font-size:.65rem;font-weight:600;"><?= $qtdInadimplentes ?> inadimplentes</div>
        <?php endif; ?>
      </div>
    </a>
  </div>

  <?php foreach ([
    ['icon'=>'bi-people-fill',     'cor'=>'info',      'label'=>'Moradores',           'valor'=>$totalMoradores,          'link'=>'condominios'],
    ['icon'=>'bi-hammer',          'cor'=>'secondary', 'label'=>'Prestadoras',         'valor'=>$totalPrestadoras,        'link'=>'prestadoras'],
    ['icon'=>'bi-kanban-fill',     'cor'=>'primary',   'label'=>'Projetos ativos',     'valor'=>$totalProjetosAtivos,     'link'=>'projetos'],
    ['icon'=>'bi-clipboard-check', 'cor'=>count($vistoriasAVencer)>0?'warning':'secondary',
                                                        'label'=>'Vistorias agendadas','valor'=>count($vistoriasAVencer),'link'=>'vistorias'],
  ] as $s): ?>
  <div class="col">
    <a href="<?= url($s['link']) ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 text-center py-3 px-2 card-hover">
        <div class="text-<?= $s['cor'] ?> mb-1" style="font-size:1.2rem;"><i class="bi <?= $s['icon'] ?>"></i></div>
        <div class="fw-bold fs-5 lh-1"><?= $s['valor'] ?></div>
        <div class="text-body-secondary mt-1" style="font-size:.68rem;"><?= $s['label'] ?></div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- ══ BLOCO 4: VISTORIAS ══════════════════════════════════════════════════ -->
<div class="secao-header"><i class="bi bi-clipboard-check"></i> Vistorias agendadas</div>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center gap-2">
      <span class="icone-secao bg-info bg-opacity-10 text-info"><i class="bi bi-clipboard-check-fill"></i></span>
      <span class="fw-semibold">Pendentes de realização</span>
      <?php if (!empty($vistoriasAVencer)): ?>
        <span class="badge bg-secondary bg-opacity-10 text-body"><?= count($vistoriasAVencer) ?></span>
      <?php endif; ?>
    </div>
    <a href="<?= url('vistorias') ?>" class="btn btn-outline-secondary btn-sm">Ver todas</a>
  </div>
  <?php if (empty($vistoriasAVencer)): ?>
    <div class="card-body text-center text-body-secondary py-4">
      <i class="bi bi-clipboard-check opacity-25 fs-2 d-block mb-2"></i>
      Nenhuma vistoria agendada.
    </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
      <thead class="table-light">
        <tr>
          <th>Tipo</th>
          <th class="d-none d-sm-table-cell">Data</th>
          <th class="d-none d-md-table-cell">Prestadora</th>
          <th class="text-end"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vistoriasAVencer as $v):
          $dias  = (int) round((strtotime($v->dataVistoria) - time()) / 86400);
          $cor   = $dias < 0 ? 'danger' : ($dias <= 7 ? 'warning' : 'secondary');
          $badge = $dias < 0 ? abs($dias) . 'd atraso' : ($dias === 0 ? 'Hoje' : $dias . 'd');
        ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($v->rotuloTipo()) ?></td>
          <td class="d-none d-sm-table-cell text-body-secondary"><?= dataBR($v->dataVistoria) ?></td>
          <td class="d-none d-md-table-cell text-body-secondary">
            <?= htmlspecialchars($v->nomePrestadora ?? $v->nomeResponsavel ?? '—') ?>
          </td>
          <td class="text-end">
            <a href="<?= url("vistorias/{$v->id}") ?>" class="btn btn-outline-secondary btn-sm me-1"><i class="bi bi-eye"></i></a>
            <span class="badge bg-<?= $cor ?>"><?= $badge ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- ══ BLOCO 4: PROJETOS ══════════════════════════════════════════════════ -->
<div class="secao-header"><i class="bi bi-kanban"></i> Projetos recentes</div>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center gap-2">
      <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-kanban"></i></span>
      <span class="fw-semibold">Em andamento</span>
      <?php if ($totalProjetosAtivos): ?>
        <span class="badge bg-primary bg-opacity-75"><?= $totalProjetosAtivos ?></span>
      <?php endif; ?>
    </div>
    <a href="<?= url('projetos') ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
  </div>
  <?php if (empty($projetosRecentes)): ?>
    <div class="card-body text-center text-body-secondary py-5">
      <i class="bi bi-kanban mb-2 d-block" style="font-size:2.5rem;opacity:.25;"></i>
      <p class="mb-2">Nenhum projeto cadastrado.</p>
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
        <?php foreach ($projetosRecentes as $p): ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($p->nome) ?></td>
          <td class="d-none d-sm-table-cell text-body-secondary"><?= htmlspecialchars($p->nomeResponsavel ?? '—') ?></td>
          <td class="d-none d-md-table-cell"><?= $p->valorEstimado ? dinheiro($p->valorEstimado) : '—' ?></td>
          <td><span class="badge rounded-pill badge-<?= $p->status ?>"><?= htmlspecialchars($p->rotuloStatus()) ?></span></td>
          <td><a href="<?= url("projetos/{$p->id}") ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
