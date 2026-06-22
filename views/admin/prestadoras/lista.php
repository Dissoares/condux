<?php
/** @var Prestadora[] $prestadoras @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Prestadoras';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h4 class="fw-semibold mb-0"><i class="bi bi-building"></i> Prestadoras</h4>
  <a href="<?= url('prestadoras/nova') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Nova empresa
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<?php if (empty($prestadoras)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-body-secondary">
      <i class="bi bi-building fs-1 opacity-25 d-block mb-3"></i>
      Nenhuma empresa cadastrada ainda.
      <div class="mt-3">
        <a href="<?= url('prestadoras/nova') ?>" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg"></i> Cadastrar empresa
        </a>
      </div>
    </div>
  </div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($prestadoras as $p): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100 <?= !$p->ativo ? 'opacity-60' : '' ?>">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-3">
          <div class="rounded-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success flex-shrink-0"
               style="width:40px;height:40px;font-size:1rem;">
            <i class="bi bi-building"></i>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-bold text-truncate"><?= htmlspecialchars($p->nome) ?></div>
            <?php if ($p->cnpj): ?>
              <div class="text-body-secondary" style="font-size:.78rem;">CNPJ: <?= htmlspecialchars($p->cnpj) ?></div>
            <?php endif; ?>
            <?php if (!$p->ativo): ?>
              <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size:.7rem;">Inativa</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="d-flex flex-column gap-1 mb-3" style="font-size:.82rem;">
          <?php if ($p->contato): ?>
          <div class="d-flex align-items-center gap-2 text-body-secondary">
            <i class="bi bi-person-fill" style="width:14px;"></i>
            <span><?= htmlspecialchars($p->contato) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($p->telefone): ?>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-telephone-fill text-body-secondary" style="width:14px;"></i>
            <a href="tel:<?= htmlspecialchars($p->telefone) ?>" class="text-decoration-none">
              <?= htmlspecialchars($p->telefone) ?>
            </a>
          </div>
          <?php endif; ?>
          <?php if ($p->email): ?>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-envelope-fill text-body-secondary" style="width:14px;"></i>
            <a href="mailto:<?= htmlspecialchars($p->email) ?>" class="text-decoration-none text-truncate">
              <?= htmlspecialchars($p->email) ?>
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-footer bg-transparent d-flex gap-2">
        <a href="<?= url("prestadoras/{$p->id}/editar") ?>" class="btn btn-outline-secondary btn-sm flex-grow-1">
          <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="<?= url("prestadoras/{$p->id}/excluir") ?>"
           onclick="return confirm('Remover empresa <?= htmlspecialchars(addslashes($p->nome)) ?>?')"
           class="btn btn-outline-danger btn-sm">
          <i class="bi bi-trash"></i>
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
