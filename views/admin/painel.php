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

$nomeAdmin    = explode(' ', Sessao::usuarioAtual()['nome'] ?? 'Administrador')[0];
$mesAtual     = (int) date('m');
$anoAtual     = (int) date('Y');
$nomesMeses   = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                 'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$nomeMes      = $nomesMeses[$mesAtual] . '/' . $anoAtual;

$mPagas      = (int)   ($resumoMes['total_pagas']     ?? 0);
$mAtrasadas  = (int)   ($resumoMes['total_atrasadas']  ?? 0);
$mPendentes  = (int)   ($resumoMes['total_pendentes']  ?? 0);
$mArrecadado = (float) ($resumoMes['valor_arrecadado'] ?? 0);
$mAtrasVal   = (float) ($resumoMes['valor_atrasado']   ?? 0);
$mTotal      = $mPagas + $mAtrasadas + $mPendentes;
$pct         = $mTotal > 0 ? round(($mPagas / $mTotal) * 100) : 0;

$gAtrasVal  = (float) ($totaisGlobais['valor_total_atrasado'] ?? 0);
$gPendVal   = (float) ($totaisGlobais['valor_total_pendente'] ?? 0);
$gAtrasQtd  = (int)   ($totaisGlobais['qtd_atrasadas']        ?? 0);
$gPendQtd   = (int)   ($totaisGlobais['qtd_pendentes']        ?? 0);

$eAtras = (int) ($resumoExtras['total_atrasadas'] ?? 0);
$ePend  = (int) ($resumoExtras['total_pendentes'] ?? 0);
?>

<style>
.painel-secao { margin-bottom: 2rem; }
.painel-secao-titulo {
  display: flex; align-items: center; gap: .5rem;
  font-size: .7rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--bs-secondary-color);
  padding-bottom: .5rem; margin-bottom: 1rem;
  border-bottom: 1px solid var(--bs-border-color);
}
.card-hover { transition: transform .12s, box-shadow .12s; cursor: pointer; }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
</style>

<!-- Cabeçalho -->
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

<!-- ══ CONDOMÍNIO ══════════════════════════════════════════════════════════ -->
<div class="painel-secao">
  <div class="painel-secao-titulo"><i class="bi bi-building"></i> Condomínio</div>
  <div class="row g-2">
    <?php
    $stats = [
      ['icon'=>'bi-building',        'cor'=>'secondary','label'=>'Unidades',          'valor'=>$totalUnidades,            'link'=>'unidades'],
      ['icon'=>'bi-people-fill',     'cor'=>'info',     'label'=>'Moradores',          'valor'=>$totalMoradores,           'link'=>'condominios'],
      ['icon'=>'bi-hammer',          'cor'=>'secondary','label'=>'Prestadoras',        'valor'=>$totalPrestadoras,         'link'=>'prestadoras'],
      ['icon'=>'bi-kanban-fill',     'cor'=>'primary',  'label'=>'Projetos ativos',    'valor'=>$totalProjetosAtivos,      'link'=>'projetos'],
      ['icon'=>'bi-clipboard-check', 'cor'=>count($vistoriasAVencer)>0?'warning':'secondary','label'=>'Vistorias agendadas','valor'=>count($vistoriasAVencer),'link'=>'vistorias'],
    ];
    foreach ($stats as $s): ?>
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
</div>

<!-- ══ TAXA CONDOMINIAL — MÊS ATUAL ════════════════════════════════════════ -->
<div class="painel-secao">
  <div class="painel-secao-titulo"><i class="bi bi-cash-stack"></i> Taxa Condominial — <?= $nomeMes ?></div>
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card card-stat card-stat-success">
        <div class="card-body">
          <i class="bi bi-check-circle-fill stat-icone"></i>
          <div class="stat-label">Pagas</div>
          <div class="stat-valor"><?= $mPagas ?></div>
          <div class="stat-detalhe"><?= dinheiro($mArrecadado) ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-stat card-stat-danger">
        <div class="card-body">
          <i class="bi bi-exclamation-circle-fill stat-icone"></i>
          <div class="stat-label">Atrasadas</div>
          <div class="stat-valor"><?= $mAtrasadas ?></div>
          <div class="stat-detalhe"><?= dinheiro($mAtrasVal) ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
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
  <?php if ($mTotal > 0): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body py-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span style="font-size:.85rem;">Progresso de arrecadação</span>
        <span class="fw-bold text-success"><?= $pct ?>%</span>
      </div>
      <div class="progress" style="height:8px;border-radius:999px;">
        <div class="progress-bar bg-success" style="width:<?= $pct ?>%;border-radius:999px;" role="progressbar"></div>
      </div>
      <div class="d-flex justify-content-between mt-2" style="font-size:.75rem;color:var(--bs-secondary-color);">
        <span><?= $mPagas ?> pagas</span>
        <?php if ($mAtrasadas): ?><span class="text-danger"><?= $mAtrasadas ?> atrasadas</span><?php endif; ?>
        <?php if ($mPendentes): ?><span><?= $mPendentes ?> a vencer</span><?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ══ POSIÇÃO GERAL — TODOS OS MESES ══════════════════════════════════════ -->
<div class="painel-secao">
  <div class="painel-secao-titulo"><i class="bi bi-globe"></i> Posição geral — todos os meses</div>
  <div class="row g-3">
    <div class="col-md-6">
      <a href="<?= url('taxas') ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm card-hover border-start border-4 border-danger">
          <div class="card-body d-flex align-items-center gap-3 p-3">
            <div class="rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger" style="width:46px;height:46px;font-size:1.2rem;">
              <i class="bi bi-exclamation-circle-fill"></i>
            </div>
            <div>
              <div class="text-body-secondary mb-1" style="font-size:.72rem;">TOTAL EM ATRASO</div>
              <div class="fw-bold fs-5 text-danger lh-1"><?= dinheiro($gAtrasVal) ?></div>
              <div class="text-body-secondary mt-1" style="font-size:.78rem;"><?= $gAtrasQtd ?> taxa<?= $gAtrasQtd !== 1 ? 's' : '' ?></div>
            </div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-6">
      <a href="<?= url('taxas') ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm card-hover border-start border-4 border-warning">
          <div class="card-body d-flex align-items-center gap-3 p-3">
            <div class="rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width:46px;height:46px;font-size:1.2rem;">
              <i class="bi bi-clock-fill"></i>
            </div>
            <div>
              <div class="text-body-secondary mb-1" style="font-size:.72rem;">TOTAL A VENCER</div>
              <div class="fw-bold fs-5 lh-1"><?= dinheiro($gPendVal) ?></div>
              <div class="text-body-secondary mt-1" style="font-size:.78rem;"><?= $gPendQtd ?> taxa<?= $gPendQtd !== 1 ? 's' : '' ?> pendente<?= $gPendQtd !== 1 ? 's' : '' ?></div>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- ══ TAXAS EXTRAS ═════════════════════════════════════════════════════════ -->
<div class="painel-secao">
  <div class="painel-secao-titulo"><i class="bi bi-plus-circle"></i> Taxas Extras</div>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
      <div class="d-flex align-items-center gap-2">
        <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-plus-circle-fill"></i></span>
        <span class="fw-semibold">Cobranças ativas</span>
        <?php if ($eAtras > 0): ?>
          <span class="badge bg-danger"><?= $eAtras ?> atrasadas</span>
        <?php elseif ($ePend > 0): ?>
          <span class="badge bg-warning text-dark"><?= $ePend ?> pendentes</span>
        <?php endif; ?>
      </div>
      <a href="<?= url('taxas-extra') ?>" class="btn btn-outline-secondary btn-sm">Ver todas</a>
    </div>
    <?php if (empty($extrasRecentes)): ?>
      <div class="card-body text-center text-body-secondary py-4">
        <i class="bi bi-plus-circle opacity-25 fs-2 d-block mb-2"></i>
        Nenhuma taxa extra cadastrada.
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
        <thead class="table-light">
          <tr>
            <th>Taxa extra</th>
            <th class="d-none d-md-table-cell">Vencimento</th>
            <th class="text-end">Pagamentos</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($extrasRecentes as $ex):
            $exAtras  = (int) $ex['atrasadas'];
            $exPagas  = (int) $ex['pagas'];
            $exTotal  = (int) $ex['total_unidades'];
            $cls      = $exAtras > 0 ? 'danger' : ($exPagas >= $exTotal && $exTotal > 0 ? 'success' : 'warning');
          ?>
          <tr>
            <td>
              <a href="<?= url("taxas-extra/{$ex['id']}") ?>" class="text-decoration-none fw-semibold text-body">
                <?= htmlspecialchars($ex['nome_projeto'] ?? $ex['nome']) ?>
              </a>
              <div class="text-body-secondary" style="font-size:.72rem;"><?= htmlspecialchars($ex['nome']) ?></div>
            </td>
            <td class="d-none d-md-table-cell text-body-secondary"><?= dataBR($ex['vencimento']) ?></td>
            <td class="text-end">
              <span class="badge bg-<?= $cls ?>"><?= $exPagas ?>/<?= $exTotal ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══ VISTORIAS AGENDADAS ══════════════════════════════════════════════════ -->
<div class="painel-secao">
  <div class="painel-secao-titulo"><i class="bi bi-clipboard-check"></i> Vistorias agendadas</div>
  <div class="card border-0 shadow-sm">
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
            <th class="d-none d-md-table-cell">Prestadora / Responsável</th>
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
              <a href="<?= url("vistorias/{$v->id}") ?>" class="btn btn-outline-secondary btn-sm me-1">
                <i class="bi bi-eye"></i>
              </a>
              <span class="badge bg-<?= $cor ?>"><?= $badge ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══ PROJETOS RECENTES ════════════════════════════════════════════════════ -->
<div class="painel-secao">
  <div class="painel-secao-titulo"><i class="bi bi-kanban"></i> Projetos recentes</div>
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
            <td>
              <a href="<?= url("projetos/{$p->id}") ?>" class="btn btn-outline-secondary btn-sm">
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
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
