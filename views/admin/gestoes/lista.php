<?php
/** @var Gestao[] $gestoes */
$tituloPagina = 'Gestões';
require_once RAIZ . '/views/layouts/cabecalho.php';

$cargoCor = [
    'sindico'     => 'primary',
    'subsindico'  => 'secondary',
    'conselheiro' => 'info',
    'suplente'    => 'warning',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
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

<?php if ($mensagem ?? null): ?>
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
$ativas     = array_filter($gestoes, fn($g) => $g->ativa());
$encerradas = array_filter($gestoes, fn($g) => !$g->ativa());
?>

<?php if (!empty($ativas)): ?>
<p class="text-body-secondary text-uppercase fw-semibold mb-3" style="font-size:.72rem;letter-spacing:.08em;">
  Gestão atual
</p>
<?php foreach ($ativas as $g): ?>
<div class="card border-0 shadow-sm mb-4" style="border-left:4px solid var(--condux-acento) !important;">
  <div class="card-body p-4">

    <div class="d-flex align-items-start justify-content-between gap-2 mb-1 flex-wrap">
      <div>
        <span class="fw-bold fs-6"><?= htmlspecialchars($g->descricao) ?></span>
        <span class="badge bg-success-subtle text-success-emphasis ms-2" style="font-size:.7rem;">Ativa</span>
      </div>
      <a href="<?= url("gestoes/{$g->id}") ?>" class="btn btn-outline-primary btn-sm flex-shrink-0">
        <i class="bi bi-pencil"></i> Editar
      </a>
    </div>
    <p class="text-body-secondary mb-4" style="font-size:.82rem;">
      <i class="bi bi-calendar3 me-1"></i><?= $g->periodo() ?>
    </p>

    <!-- Membros como cards simples -->
    <div class="d-flex flex-column gap-2">
      <?php foreach ($g->membros as $m):
        $cor = $cargoCor[$m['cargo']] ?? 'secondary';
        $rotulo = Gestao::$cargosRotulo[$m['cargo']] ?? $m['cargo'];
        $inicial = strtoupper(mb_substr($m['nome'], 0, 1));
      ?>
      <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-<?= $cor ?>-subtle">
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0
                    bg-<?= $cor ?>-subtle border border-<?= $cor ?>-subtle text-<?= $cor ?>-emphasis fw-bold"
             style="width:38px;height:38px;font-size:.9rem;">
          <?= $inicial ?>
        </div>
        <div class="flex-grow-1 min-width-0">
          <div class="fw-semibold text-<?= $cor ?>-emphasis" style="font-size:.88rem;">
            <?= htmlspecialchars($m['nome']) ?>
          </div>
          <div class="text-body-secondary" style="font-size:.75rem;">
            <?= htmlspecialchars($m['email']) ?>
          </div>
        </div>
        <span class="badge bg-<?= $cor ?> flex-shrink-0" style="font-size:.62rem;">
          <?= $rotulo ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($encerradas)): ?>
<p class="text-body-secondary text-uppercase fw-semibold mt-2 mb-3" style="font-size:.72rem;letter-spacing:.08em;">
  Gestões anteriores
</p>
<div class="d-flex flex-column gap-3">
  <?php foreach ($encerradas as $g):
    $sindico = $g->sindico();
  ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body py-3 px-4">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                    bg-secondary-subtle text-secondary"
             style="width:42px;height:42px;font-size:1.1rem;">
          <i class="bi bi-archive"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
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

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
