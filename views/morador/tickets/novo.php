<?php
/** @var string|null $erroMensagem */
$tituloPagina = 'Novo Ticket';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= url('tickets') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-semibold mb-0"><i class="bi bi-ticket-perforated"></i> Novo ticket</h4>
</div>

<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    <form action="<?= url('tickets/salvar') ?>" method="POST">

      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" class="form-control" required maxlength="160"
               placeholder="Descreva o assunto em poucas palavras">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-semibold">Categoria</label>
          <select name="categoria" class="form-select">
            <?php foreach (Ticket::$rotuloCategorias as $chave => $label): ?>
              <option value="<?= $chave ?>"><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-6">
          <label class="form-label fw-semibold">Prioridade</label>
          <select name="prioridade" class="form-select">
            <?php foreach (Ticket::$rotuloPrioridade as $chave => $label): ?>
              <option value="<?= $chave ?>" <?= $chave === 'normal' ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Descrição detalhada *</label>
        <textarea name="descricao" class="form-control" rows="6" required
                  placeholder="Descreva com detalhes o que aconteceu, quando, onde..."></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-send"></i> Abrir ticket
        </button>
        <a href="<?= url('tickets') ?>" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
