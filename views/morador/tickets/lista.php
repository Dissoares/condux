<?php
/** @var Ticket[] $tickets @var string|null $mensagem */
$tituloPagina = 'Meus Tickets';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-ticket-perforated"></i> Meus Tickets</h4>
  <a href="<?= url('tickets/novo') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Novo ticket
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<?php if (empty($tickets)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-ticket-perforated fs-1 opacity-25 d-block mb-3"></i>
    Você ainda não abriu nenhum ticket.
    <div class="mt-3">
      <a href="<?= url('tickets/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Abrir ticket
      </a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm overflow-hidden">
  <?php foreach ($tickets as $i => $t): ?>
  <a href="<?= url('tickets/' . $t->id) ?>"
     class="d-block px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?> text-decoration-none
            border-start border-3 border-<?= $t->corStatus() ?>">
    <div class="d-flex align-items-start gap-3">
      <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle
                  bg-<?= $t->corStatus() ?>-subtle text-<?= $t->corStatus() ?>-emphasis"
           style="width:40px; height:40px; font-size:1rem;">
        <i class="bi <?= $t->iconeCategoria() ?>"></i>
      </div>
      <div class="flex-grow-1 min-width-0">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
          <span class="fw-semibold text-body" style="font-size:.93rem;">
            #<?= $t->id ?> <?= htmlspecialchars($t->titulo) ?>
          </span>
          <span class="badge bg-<?= $t->corStatus() ?>-subtle text-<?= $t->corStatus() ?>-emphasis flex-shrink-0"
                style="font-size:.65rem;"><?= $t->rotuloStatus() ?></span>
        </div>
        <div class="text-body-secondary mt-1" style="font-size:.78rem;">
          <?= $t->rotuloCategoria() ?>
          · <?= $t->criadoEm ? date('d/m/Y', strtotime($t->criadoEm)) : '' ?>
          <?php if ($t->totalMensagens > 0): ?>
            · <i class="bi bi-chat me-1"></i><?= $t->totalMensagens ?> resposta<?= $t->totalMensagens != 1 ? 's' : '' ?>
          <?php endif; ?>
        </div>
      </div>
      <i class="bi bi-chevron-right text-body-tertiary flex-shrink-0"></i>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
