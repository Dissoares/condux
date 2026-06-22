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
  <?php $sindico = $g->sindico(); $sub = $g->subsindico(); ?>
  <div class="card border-0 shadow-sm mb-4" style="border-left:4px solid var(--condux-acento) !important;">
    <div class="card-body">
      <div class="d-flex align-items-start gap-3 flex-wrap">
        <div class="flex-grow-1">
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="fw-bold fs-6"><?= htmlspecialchars($g->descricao) ?></span>
            <span class="badge bg-success bg-opacity-10 text-success fw-semibold" style="font-size:.7rem;">Ativa</span>
          </div>
          <p class="text-body-secondary mb-3" style="font-size:.82rem;">
            <i class="bi bi-calendar3 me-1"></i><?= $g->periodo() ?>
          </p>

          <!-- Membros em destaque -->
          <div class="row g-3">
            <?php if ($sindico): ?>
            <div class="col-sm-6 col-md-4">
              <div class="p-3 rounded-2 bg-primary bg-opacity-10 h-100">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-person-badge text-primary"></i>
                  <span class="text-primary fw-semibold" style="font-size:.75rem;text-transform:uppercase;">Síndico</span>
                </div>
                <div class="fw-semibold"><?= htmlspecialchars($sindico['nome']) ?></div>
                <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($sindico['email']) ?></div>
              </div>
            </div>
            <?php endif; ?>

            <?php if ($sub): ?>
            <div class="col-sm-6 col-md-4">
              <div class="p-3 rounded-2 bg-secondary bg-opacity-10 h-100">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-person-check text-secondary"></i>
                  <span class="text-body-secondary fw-semibold" style="font-size:.75rem;text-transform:uppercase;">Subsíndico</span>
                </div>
                <div class="fw-semibold"><?= htmlspecialchars($sub['nome']) ?></div>
                <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($sub['email']) ?></div>
              </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($g->conselheiros())): ?>
            <div class="col-sm-6 col-md-4">
              <div class="p-3 rounded-2 h-100" style="background:rgba(99,102,241,.08);">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-people" style="color:#6366f1;"></i>
                  <span class="fw-semibold" style="font-size:.75rem;text-transform:uppercase;color:#6366f1;">Conselho</span>
                </div>
                <?php foreach ($g->conselheiros() as $c): ?>
                  <div class="fw-semibold" style="font-size:.88rem;"><?= htmlspecialchars($c['nome']) ?></div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <a href="<?= url("gestoes/{$g->id}") ?>" class="btn btn-outline-primary btn-sm flex-shrink-0">
          <i class="bi bi-eye"></i> Ver detalhes
        </a>
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

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
