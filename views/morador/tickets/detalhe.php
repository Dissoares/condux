<?php
/** @var Ticket $ticket @var array[] $mensagens @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Ticket #' . $ticket->id;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
  <a href="<?= url('tickets') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <div class="flex-grow-1">
    <div class="fw-semibold" style="font-size:.9rem; color:var(--bs-body-secondary);">#<?= $ticket->id ?> · <?= $ticket->rotuloCategoria() ?></div>
    <h5 class="fw-bold mb-0"><?= htmlspecialchars($ticket->titulo) ?></h5>
  </div>
  <span class="badge bg-<?= $ticket->corStatus() ?>-subtle text-<?= $ticket->corStatus() ?>-emphasis">
    <?= $ticket->rotuloStatus() ?>
  </span>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<!-- Thread -->
<div class="d-flex flex-column gap-3 mb-4">

  <!-- Mensagem original -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
        <span class="fw-semibold" style="font-size:.85rem;">Você</span>
        <span class="text-body-secondary" style="font-size:.72rem;">
          <?= $ticket->criadoEm ? date('d/m/Y H:i', strtotime($ticket->criadoEm)) : '' ?>
        </span>
      </div>
      <p class="mb-0" style="white-space:pre-line; font-size:.9rem;"><?= htmlspecialchars($ticket->descricao) ?></p>
    </div>
  </div>

  <!-- Respostas -->
  <?php foreach ($mensagens as $msg):
    $ehEquipe = in_array($msg['perfil_usuario'], ['sindico','subsindico'], true);
  ?>
  <div class="card border-0 shadow-sm <?= $ehEquipe ? 'ms-3 border-start border-primary border-2' : '' ?>">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
        <span class="fw-semibold" style="font-size:.85rem;">
          <?= htmlspecialchars($msg['nome_usuario'] ?? '') ?>
          <?php if ($ehEquipe): ?>
            <span class="badge bg-primary-subtle text-primary-emphasis ms-1" style="font-size:.6rem;">Equipe</span>
          <?php endif; ?>
        </span>
        <span class="text-body-secondary" style="font-size:.72rem;">
          <?= date('d/m/Y H:i', strtotime($msg['criado_em'])) ?>
        </span>
      </div>
      <p class="mb-0" style="white-space:pre-line; font-size:.9rem;"><?= htmlspecialchars($msg['mensagem']) ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Responder -->
<?php if (!$ticket->estaFechado()): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="<?= url('tickets/' . $ticket->id . '/responder') ?>" method="POST">
      <input type="hidden" name="ticket_id" value="<?= $ticket->id ?>">
      <textarea name="mensagem" class="form-control mb-3" rows="3" required
                placeholder="Adicione mais informações ou uma resposta..."></textarea>
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-send"></i> Enviar
        </button>
      </div>
    </form>
  </div>
</div>
<?php else: ?>
<div class="alert alert-secondary d-flex align-items-center gap-2" style="font-size:.88rem;">
  <i class="bi bi-lock"></i> Ticket <?= $ticket->rotuloStatus() ?>.
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
