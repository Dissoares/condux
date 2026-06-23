<?php
/**
 * @var Comunicado|null $comunicado
 * @var string|null     $erroMensagem
 */
$editando     = $comunicado !== null;
$tituloPagina = $editando ? 'Editar Comunicado' : 'Novo Comunicado';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= url('comunicados') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-semibold mb-0">
    <i class="bi bi-megaphone"></i> <?= $editando ? 'Editar Comunicado' : 'Novo Comunicado' ?>
  </h4>
</div>

<?php if ($erroMensagem): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($erroMensagem) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:760px;">
  <div class="card-body p-4">
    <form action="<?= url('comunicados/salvar') ?>" method="POST">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $comunicado->id ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" class="form-control" required maxlength="200"
               value="<?= htmlspecialchars($comunicado?->titulo ?? '') ?>"
               placeholder="Ex: Manutenção na bomba d'água">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-4">
          <label class="form-label fw-semibold">Tipo</label>
          <select name="tipo" class="form-select">
            <?php foreach (Comunicado::$tipos as $val => [$rot, $ico, $cor]): ?>
              <option value="<?= $val ?>" <?= ($comunicado?->tipo ?? 'aviso') === $val ? 'selected' : '' ?>>
                <?= $rot ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold">Data de publicação</label>
          <input type="date" name="data_publicacao" class="form-control"
                 value="<?= htmlspecialchars($comunicado?->dataPublicacao ?? date('Y-m-d')) ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-semibold">Expira em <span class="text-body-secondary fw-normal">(opcional)</span></label>
          <input type="date" name="data_expiracao" class="form-control"
                 value="<?= htmlspecialchars($comunicado?->dataExpiracao ?? '') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Conteúdo *</label>
        <textarea name="conteudo" class="form-control" rows="8" required
                  placeholder="Texto do comunicado..."><?= htmlspecialchars($comunicado?->conteudo ?? '') ?></textarea>
        <div class="form-text">Dica: use Enter para separar parágrafos. O texto será exibido formatado.</div>
      </div>

      <?php if ($editando): ?>
      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="ativo" id="ativo"
                 <?= $comunicado->ativo ? 'checked' : '' ?>>
          <label class="form-check-label" for="ativo">Comunicado ativo (visível para moradores)</label>
        </div>
      </div>
      <?php else: ?>
        <input type="hidden" name="ativo" value="on">
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-<?= $editando ? 'floppy' : 'send' ?>"></i>
          <?= $editando ? 'Salvar alterações' : 'Publicar comunicado' ?>
        </button>
        <a href="<?= url('comunicados') ?>" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
