<?php
/** @var array[] $competencias @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Taxa Condominial';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-cash-stack"></i> Taxa Condominial</h4>
  <a href="<?= url('taxas/gerar-lote') ?>" class="btn btn-primary">
    <i class="bi bi-lightning-fill"></i> Gerar em lote
  </a>
</div>

<?php if (!empty($mensagem)): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if (!empty($erroMensagem)): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<?php if (empty($competencias)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center text-body-secondary py-5">
      <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
      Nenhuma taxa gerada ainda. Use o botão <strong>Gerar em lote</strong> para começar.
    </div>
  </div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($competencias as $c):
    $atrasadas  = (int) $c['total_atrasadas'];
    $pendentes  = (int) $c['total_pendentes'];
    $pagas      = (int) $c['total_pagas'];
    $total      = (int) $c['total'];
    $arrecadado = (float) $c['valor_arrecadado'];
    $comp       = $c['competencia'];
    $nome       = formatarCompetencia($comp);
  ?>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <a href="<?= url('taxas?competencia=' . $comp) ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body p-3">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
              <div class="fw-bold fs-6"><?= $nome ?></div>
              <div class="text-body-secondary" style="font-size:.75rem;"><?= $total ?> unidade<?= $total !== 1 ? 's' : '' ?></div>
            </div>
            <?php if ($atrasadas > 0): ?>
              <span class="badge bg-danger"><?= $atrasadas ?> atrasada<?= $atrasadas !== 1 ? 's' : '' ?></span>
            <?php elseif ($pendentes > 0): ?>
              <span class="badge bg-warning text-dark"><?= $pendentes ?> pendente<?= $pendentes !== 1 ? 's' : '' ?></span>
            <?php else: ?>
              <span class="badge bg-success">Em dia</span>
            <?php endif; ?>
          </div>

          <div class="d-flex gap-3 mb-3" style="font-size:.8rem;">
            <div class="d-flex align-items-center gap-1 text-success">
              <i class="bi bi-check-circle-fill"></i> <?= $pagas ?>
            </div>
            <?php if ($atrasadas > 0): ?>
            <div class="d-flex align-items-center gap-1 text-danger">
              <i class="bi bi-exclamation-circle-fill"></i> <?= $atrasadas ?>
            </div>
            <?php endif; ?>
            <?php if ($pendentes > 0): ?>
            <div class="d-flex align-items-center gap-1 text-warning">
              <i class="bi bi-clock-fill"></i> <?= $pendentes ?>
            </div>
            <?php endif; ?>
          </div>

          <div class="border-top pt-2 mt-auto">
            <div class="text-body-secondary" style="font-size:.7rem;">Arrecadado</div>
            <div class="fw-semibold" style="font-size:.9rem;"><?= dinheiro($arrecadado) ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<style>
.card-hover { transition: transform .12s, box-shadow .12s; }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
</style>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
