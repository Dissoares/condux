<?php
/** @var Ticket $ticket @var array[] $mensagens @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Ticket #' . $ticket->id;
require_once RAIZ . '/views/layouts/cabecalho.php';

function avatarTicket(?string $foto, string $nome, string $size = '32px'): string {
    $inicial = strtoupper(mb_substr($nome, 0, 1));
    if ($foto) {
        return '<img src="' . url('uploads/' . $foto) . '" alt=""
                     style="width:' . $size . ';height:' . $size . ';object-fit:cover;border-radius:50%;">';
    }
    return '<div style="width:' . $size . ';height:' . $size . ';border-radius:50%;background:var(--condux-primaria);
                        color:#fff;font-weight:700;font-size:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
           . $inicial . '</div>';
}
?>

<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
  <a href="<?= url('tickets') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-semibold mb-0 flex-grow-1">
    <span class="text-body-secondary fw-normal">#<?= $ticket->id ?></span>
    <?= htmlspecialchars($ticket->titulo) ?>
  </h4>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<div class="row g-4">

  <!-- Coluna principal: thread de mensagens -->
  <div class="col-lg-8">

    <!-- Mensagem original -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3">
          <?= avatarTicket($ticket->fotoUsuario, $ticket->nomeUsuario ?? 'U', '40px') ?>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
              <span class="fw-semibold"><?= htmlspecialchars($ticket->nomeUsuario ?? '') ?></span>
              <span class="text-body-secondary" style="font-size:.75rem;">
                <?= $ticket->criadoEm ? date('d/m/Y H:i', strtotime($ticket->criadoEm)) : '' ?>
              </span>
            </div>
            <p class="mb-0" style="white-space:pre-line; font-size:.9rem;"><?= htmlspecialchars($ticket->descricao) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Respostas -->
    <?php foreach ($mensagens as $msg): ?>
    <?php
    $ehAdmin = in_array($msg['perfil_usuario'], ['sindico','subsindico'], true);
    $ehInterno = (bool) $msg['interno'];
    ?>
    <div class="card border-0 shadow-sm mb-3 <?= $ehInterno ? 'border border-warning border-opacity-50' : '' ?>">
      <div class="card-body p-3 px-4">
        <?php if ($ehInterno): ?>
          <div class="d-flex align-items-center gap-1 mb-2" style="font-size:.72rem; color:#d97706;">
            <i class="bi bi-lock-fill"></i> Nota interna
          </div>
        <?php endif; ?>
        <div class="d-flex align-items-start gap-3">
          <?= avatarTicket($msg['foto_usuario'], $msg['nome_usuario'] ?? 'U', '34px') ?>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
              <span class="fw-semibold" style="font-size:.88rem;">
                <?= htmlspecialchars($msg['nome_usuario'] ?? '') ?>
                <?php if ($ehAdmin): ?>
                  <span class="badge bg-primary-subtle text-primary-emphasis ms-1" style="font-size:.6rem;">Equipe</span>
                <?php endif; ?>
              </span>
              <span class="text-body-secondary" style="font-size:.73rem;">
                <?= date('d/m/Y H:i', strtotime($msg['criado_em'])) ?>
              </span>
            </div>
            <p class="mb-0" style="white-space:pre-line; font-size:.88rem;"><?= htmlspecialchars($msg['mensagem']) ?></p>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Formulário de resposta -->
    <?php if (!$ticket->estaFechado()): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3">
        <i class="bi bi-reply"></i> Responder
      </div>
      <div class="card-body p-4">
        <form action="<?= url('tickets/' . $ticket->id . '/responder') ?>" method="POST">
          <input type="hidden" name="ticket_id" value="<?= $ticket->id ?>">
          <div class="mb-3">
            <textarea name="mensagem" class="form-control" rows="4" required
                      placeholder="Escreva sua resposta..."></textarea>
          </div>
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
              <select name="status" class="form-select form-select-sm" style="width:auto;">
                <?php foreach (Ticket::$rotuloStatus as $chave => $label): ?>
                  <option value="<?= $chave ?>" <?= $ticket->status === $chave ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <label class="d-flex align-items-center gap-2" style="cursor:pointer; font-size:.82rem;">
                <input type="checkbox" name="interno" class="form-check-input mt-0">
                <span>Nota interna</span>
              </label>
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-send"></i> Enviar resposta
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php else: ?>
    <div class="alert alert-secondary d-flex align-items-center gap-2">
      <i class="bi bi-lock"></i> Este ticket está <?= $ticket->rotuloStatus() ?>.
      <form action="<?= url('tickets/' . $ticket->id . '/status') ?>" method="POST" class="ms-auto">
        <input type="hidden" name="ticket_id" value="<?= $ticket->id ?>">
        <input type="hidden" name="status" value="aberto">
        <button type="submit" class="btn btn-sm btn-outline-secondary">Reabrir</button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <!-- Coluna lateral: informações -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body p-4">
        <h6 class="fw-semibold mb-3">Detalhes</h6>
        <dl class="row row-cols-1 g-2 mb-0" style="font-size:.85rem;">
          <div>
            <dt class="text-body-secondary fw-normal" style="font-size:.75rem;">STATUS</dt>
            <dd class="mb-0">
              <span class="badge bg-<?= $ticket->corStatus() ?>-subtle text-<?= $ticket->corStatus() ?>-emphasis">
                <?= $ticket->rotuloStatus() ?>
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-body-secondary fw-normal" style="font-size:.75rem;">PRIORIDADE</dt>
            <dd class="mb-0">
              <span class="badge bg-<?= $ticket->corPrioridade() ?>-subtle text-<?= $ticket->corPrioridade() ?>-emphasis">
                <?= $ticket->rotuloPrioridade() ?>
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-body-secondary fw-normal" style="font-size:.75rem;">CATEGORIA</dt>
            <dd class="mb-0"><?= $ticket->rotuloCategoria() ?></dd>
          </div>
          <div>
            <dt class="text-body-secondary fw-normal" style="font-size:.75rem;">ABERTURA</dt>
            <dd class="mb-0"><?= $ticket->criadoEm ? date('d/m/Y H:i', strtotime($ticket->criadoEm)) : '—' ?></dd>
          </div>
          <div>
            <dt class="text-body-secondary fw-normal" style="font-size:.75rem;">ÚLTIMA ATUALIZAÇÃO</dt>
            <dd class="mb-0"><?= $ticket->atualizadoEm ? date('d/m/Y H:i', strtotime($ticket->atualizadoEm)) : '—' ?></dd>
          </div>
          <?php if ($ticket->nomeResponsavel): ?>
          <div>
            <dt class="text-body-secondary fw-normal" style="font-size:.75rem;">RESPONSÁVEL</dt>
            <dd class="mb-0"><?= htmlspecialchars($ticket->nomeResponsavel) ?></dd>
          </div>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h6 class="fw-semibold mb-3">Solicitante</h6>
        <div class="d-flex align-items-center gap-2">
          <?= avatarTicket($ticket->fotoUsuario, $ticket->nomeUsuario ?? 'U', '36px') ?>
          <span style="font-size:.88rem;"><?= htmlspecialchars($ticket->nomeUsuario ?? '') ?></span>
        </div>
      </div>
    </div>
  </div>

</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
