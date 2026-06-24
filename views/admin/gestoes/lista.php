<?php
/** @var Gestao[] $gestoes */
$tituloPagina = 'Gestões';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-person-badge text-primary"></i> Gestões do condomínio</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      Histórico de administrações — síndicos, subsíndicos e conselho fiscal.
    </p>
  </div>
  <a href="<?= url('gestoes/nova') ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Nova gestão
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<?php if (empty($gestoes)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5 text-center">
      <i class="bi bi-person-badge text-body-secondary mb-2" style="font-size:2.5rem;opacity:.35;"></i>
      <p class="text-body-secondary mb-3">Nenhuma gestão cadastrada ainda.</p>
      <a href="<?= url('gestoes/nova') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Cadastrar primeira gestão
      </a>
    </div>
  </div>
<?php else: ?>

<?php
$ativas      = array_filter($gestoes, fn($g) => $g->ativa());
$encerradas  = array_filter($gestoes, fn($g) => !$g->ativa());
?>

<?php if (!empty($ativas)): ?>
<h6 class="text-body-secondary text-uppercase fw-semibold mb-3" style="font-size:.72rem;letter-spacing:.08em;">
  Gestão atual
</h6>
<?php foreach ($ativas as $g): ?>
<?php
  $orgNiveis = [];
  $oSindico  = $g->sindico();
  $oSub      = $g->subsindico();
  $oCons     = $g->conselheiros();
  $oSupl     = $g->suplentes();
  if ($oSindico) $orgNiveis[] = ['key' => 'sindico',     'list' => [$oSindico]];
  if ($oSub)     $orgNiveis[] = ['key' => 'subsindico',  'list' => [$oSub]];
  if ($oCons)    $orgNiveis[] = ['key' => 'conselheiro', 'list' => $oCons];
  if ($oSupl)    $orgNiveis[] = ['key' => 'suplente',    'list' => $oSupl];
?>
  <div class="card border-0 shadow-sm mb-4" style="border-left:4px solid var(--condux-acento) !important;">
    <div class="card-body">

      <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="fw-bold fs-6"><?= htmlspecialchars($g->descricao) ?></span>
          <span class="badge bg-success bg-opacity-10 text-success fw-semibold" style="font-size:.7rem;">Ativa</span>
        </div>
        <a href="<?= url("gestoes/{$g->id}") ?>" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-eye"></i> Ver detalhes
        </a>
      </div>
      <p class="text-body-secondary mb-4" style="font-size:.82rem;">
        <i class="bi bi-calendar3 me-1"></i><?= $g->periodo() ?>
      </p>

      <!-- Org chart -->
      <div class="org-chart">
        <?php foreach ($orgNiveis as $idx => $nivel): ?>

          <?php if ($idx > 0): ?>
          <div class="org-vline"></div>
          <?php endif; ?>

          <div class="org-row <?= count($nivel['list']) > 1 ? 'org-multi' : 'org-single' ?>">
            <?php foreach ($nivel['list'] as $m): ?>
            <div class="org-node org-node-<?= $nivel['key'] ?>">
              <div class="org-card">
                <span class="org-cargo"><?= Gestao::$cargosRotulo[$nivel['key']] ?></span>
                <div class="org-nome"><?= htmlspecialchars($m['nome']) ?></div>
                <div class="org-email"><?= htmlspecialchars($m['email']) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

        <?php endforeach; ?>
      </div>

    </div>
  </div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($encerradas)): ?>
<h6 class="text-body-secondary text-uppercase fw-semibold mt-2 mb-3" style="font-size:.72rem;letter-spacing:.08em;">
  Gestões anteriores
</h6>
<div class="d-flex flex-column gap-3">
  <?php foreach ($encerradas as $g): ?>
  <?php $sindico = $g->sindico(); ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body py-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 bg-secondary bg-opacity-10 text-secondary"
             style="width:44px;height:44px;font-size:1.2rem;">
          <i class="bi bi-archive"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="fw-semibold"><?= htmlspecialchars($g->descricao) ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">
            <i class="bi bi-calendar3 me-1"></i><?= $g->periodo() ?>
            <?php if ($sindico): ?>
              · <i class="bi bi-person me-1"></i><?= htmlspecialchars($sindico['nome']) ?>
            <?php endif; ?>
            · <?= count($g->membros) ?> membro<?= count($g->membros) !== 1 ? 's' : '' ?>
          </div>
        </div>
        <a href="<?= url("gestoes/{$g->id}") ?>" class="btn btn-outline-secondary btn-sm flex-shrink-0">
          <i class="bi bi-eye"></i>
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<style>
/* ── Org Chart ─────────────────────────────────────────── */
.org-chart {
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow-x: auto;
  padding: 4px 0 8px;
}

/* Vertical connector between levels */
.org-vline {
  width: 2px;
  height: 28px;
  background: var(--bs-border-color);
  flex-shrink: 0;
}

/* A level row */
.org-row {
  display: flex;
  justify-content: center;
}

/* Individual node wrapper — padding creates the "gutter" between siblings */
.org-node {
  padding: 0 10px;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Multi-node rows: reserve space above each card for connector lines */
.org-multi .org-node {
  padding-top: 28px;
}

/* Vertical stub — from horizontal bar down to card */
.org-multi .org-node::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 28px;
  background: var(--bs-border-color);
}

/* Horizontal bar spanning siblings — each node contributes its half */
.org-multi .org-node:not(:only-child)::after {
  content: '';
  position: absolute;
  top: 0;
  height: 2px;
  background: var(--bs-border-color);
}
.org-multi .org-node:first-child:not(:only-child)::after { left: 50%;  right: -10px; }
.org-multi .org-node:last-child:not(:only-child)::after  { left: -10px; right: 50%;  }
.org-multi .org-node:not(:first-child):not(:last-child)::after { left: -10px; right: -10px; }

/* The visual card */
.org-card {
  border-radius: 10px;
  padding: 10px 16px;
  min-width: 138px;
  max-width: 200px;
  text-align: center;
  border: 1.5px solid var(--bs-border-color);
  background: var(--bs-body-bg);
}
.org-node-sindico    .org-card { border-color: var(--bs-primary);   background: rgba(var(--bs-primary-rgb),.07);   }
.org-node-subsindico .org-card { border-color: var(--bs-secondary); background: rgba(var(--bs-secondary-rgb),.06); }
.org-node-conselheiro .org-card { border-color: #6366f1;            background: rgba(99,102,241,.06);              }
.org-node-suplente   .org-card { border-color: var(--bs-info);      background: rgba(var(--bs-info-rgb),.06);      }

.org-cargo {
  display: block;
  font-size: .63rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  margin-bottom: 5px;
}
.org-node-sindico    .org-cargo { color: var(--bs-primary);          }
.org-node-subsindico .org-cargo { color: var(--bs-secondary-emphasis);}
.org-node-conselheiro .org-cargo { color: #6366f1;                   }
.org-node-suplente   .org-cargo { color: var(--bs-info-emphasis);    }

.org-nome  { font-weight: 600; font-size: .86rem; line-height: 1.3; }
.org-email { font-size: .71rem; color: var(--bs-body-secondary); margin-top: 3px; }
</style>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
