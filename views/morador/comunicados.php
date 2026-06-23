<?php
/**
 * @var Comunicado[] $comunicados
 */
$tituloPagina = 'Comunicados';
require_once RAIZ . '/views/layouts/cabecalho.php';

$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;
?>

<div class="mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-megaphone"></i> Comunicados</h4>
  <p class="text-body-secondary" style="font-size:.85rem;">Avisos e informações do condomínio</p>
</div>

<?php if (empty($comunicados)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-megaphone fs-1 opacity-25 d-block mb-2"></i>
    Nenhum comunicado ativo no momento.
  </div>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
<?php foreach ($comunicados as $c): ?>
<?php $cor = $c->cor(); $corCss = $cor === 'purple' ? 'primary' : $cor; ?>
<div class="card border-0 shadow-sm"
     style="border-left:4px solid var(--bs-<?= $corCss ?>)!important;">
  <div class="card-body py-3 px-4">
    <div class="d-flex align-items-start gap-3">

      <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0
                  bg-<?= $corCss ?>-subtle text-<?= $corCss ?>-emphasis"
           style="width:38px;height:38px;font-size:.95rem;">
        <i class="bi <?= $c->icone() ?>"></i>
      </div>

      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
          <span class="fw-semibold"><?= htmlspecialchars($c->titulo) ?></span>
          <span class="badge bg-<?= $corCss ?>-subtle text-<?= $corCss ?>-emphasis"
                style="font-size:.68rem;"><?= $c->rotulo() ?></span>
        </div>
        <div class="mb-2" style="font-size:.875rem;white-space:pre-line;line-height:1.6;">
          <?= nl2br(htmlspecialchars($c->conteudo)) ?>
        </div>
        <div class="text-body-secondary" style="font-size:.75rem;">
          <i class="bi bi-calendar me-1"></i><?= $fmtData($c->dataPublicacao) ?>
          <?php if ($c->nomeAutor): ?>
            &nbsp;·&nbsp; <i class="bi bi-person me-1"></i><?= htmlspecialchars($c->nomeAutor) ?>
          <?php endif; ?>
          <?php if ($c->dataExpiracao): ?>
            &nbsp;·&nbsp; <i class="bi bi-calendar-x me-1"></i>Válido até <?= $fmtData($c->dataExpiracao) ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
