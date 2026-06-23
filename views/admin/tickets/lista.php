<?php
/** @var Ticket[] $tickets @var string|null $mensagem */
$tituloPagina = 'Tickets';
require_once RAIZ . '/views/layouts/cabecalho.php';

$filtroStatus    = $_GET['status']    ?? '';
$filtroCategoria = $_GET['categoria'] ?? '';

$abertos      = count(array_filter($tickets, fn($t) => $t->status === 'aberto'));
$em_andamento = count(array_filter($tickets, fn($t) => $t->status === 'em_andamento'));
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h4 class="fw-semibold mb-0"><i class="bi bi-ticket-perforated"></i> Tickets</h4>
  <?php if ($abertos + $em_andamento > 0): ?>
    <span class="badge bg-danger-subtle text-danger-emphasis" style="font-size:.82rem;">
      <?= $abertos + $em_andamento ?> aguardando atenção
    </span>
  <?php endif; ?>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<!-- Filtros -->
<div class="d-flex flex-wrap gap-2 mb-4">
  <?php
  $url = url('tickets');
  $statusFiltros = ['' => 'Todos'] + Ticket::$rotuloStatus;
  foreach ($statusFiltros as $chave => $label):
    $ativo = $filtroStatus === $chave && $filtroCategoria === '';
  ?>
  <a href="<?= $url ?>?status=<?= $chave ?>"
     class="btn btn-sm <?= $ativo ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
  <div class="vr d-none d-md-block"></div>
  <?php foreach (Ticket::$rotuloCategorias as $chave => $label): ?>
  <a href="<?= $url ?>?categoria=<?= $chave ?>"
     class="btn btn-sm <?= $filtroCategoria === $chave ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if (empty($tickets)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-ticket-perforated fs-1 opacity-25 d-block mb-3"></i>
    Nenhum ticket encontrado.
  </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm overflow-hidden">
  <?php foreach ($tickets as $i => $t): ?>
  <a href="<?= url('tickets/' . $t->id) ?>"
     class="d-block px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?> text-decoration-none
            condux-ticket-row border-start border-3 border-<?= $t->corStatus() ?>">
    <div class="d-flex align-items-start gap-3">

      <!-- Ícone categoria -->
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
          <div class="d-flex gap-1 flex-shrink-0">
            <span class="badge bg-<?= $t->corPrioridade() ?>-subtle text-<?= $t->corPrioridade() ?>-emphasis"
                  style="font-size:.65rem;"><?= $t->rotuloPrioridade() ?></span>
            <span class="badge bg-<?= $t->corStatus() ?>-subtle text-<?= $t->corStatus() ?>-emphasis"
                  style="font-size:.65rem;"><?= $t->rotuloStatus() ?></span>
          </div>
        </div>

        <div class="text-body-secondary mt-1" style="font-size:.78rem;">
          <i class="bi bi-person me-1"></i><?= htmlspecialchars($t->nomeUsuario ?? '') ?>
          · <?= $t->rotuloCategoria() ?>
          · <?= $t->criadoEm ? date('d/m/Y H:i', strtotime($t->criadoEm)) : '' ?>
          <?php if ($t->totalMensagens > 0): ?>
            · <i class="bi bi-chat me-1"></i><?= $t->totalMensagens ?> resposta<?= $t->totalMensagens != 1 ? 's' : '' ?>
          <?php endif; ?>
          <?php if ($t->nomeResponsavel): ?>
            · <i class="bi bi-person-check me-1"></i><?= htmlspecialchars($t->nomeResponsavel) ?>
          <?php endif; ?>
        </div>
      </div>

      <i class="bi bi-chevron-right text-body-tertiary flex-shrink-0 d-none d-md-block"></i>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
