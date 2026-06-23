<?php
/** @var array[]|null $anos @var array[]|null $competencias @var string|null $ano */
$tituloPagina = isset($ano) ? "Taxa Condominial — {$ano}" : 'Taxa Condominial';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div class="d-flex align-items-center gap-2">
    <?php if ($ano): ?>
      <a href="<?= url('taxas') ?>" class="btn btn-outline-secondary btn-sm py-1 px-2">
        <i class="bi bi-chevron-left"></i>
      </a>
    <?php endif; ?>
    <div>
      <h4 class="fw-semibold mb-0">
        <i class="bi bi-cash-stack"></i>
        Taxa Condominial<?= $ano ? ' — ' . $ano : '' ?>
      </h4>
    </div>
  </div>
  <a href="<?= url('taxas/gerar-lote') ?>" class="btn btn-primary btn-sm">
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


<?php /* ══════════════════════════════════════
       NÍVEL 1 — Lista de anos
       ══════════════════════════════════════ */
if (!$ano): ?>

<?php if (empty($anos)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center text-body-secondary py-5">
      <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
      Nenhuma taxa gerada ainda. Use o botão <strong>Gerar em lote</strong> para começar.
    </div>
  </div>
<?php else: ?>

<div class="card border-0 shadow-sm overflow-hidden">
  <?php foreach ($anos as $i => $r):
    $atrasadas = (int)$r['total_atrasadas'];
    $pendentes = (int)$r['total_pendentes'];
    $pagas     = (int)$r['total_pagas'];
    if ($atrasadas > 0) {
        $badgeClass = 'bg-danger-subtle text-danger-emphasis';
        $badgeIcon  = 'bi-exclamation-circle-fill';
        $badgeLabel = $atrasadas . ' atrasada' . ($atrasadas !== 1 ? 's' : '');
    } elseif ($pendentes > 0) {
        $badgeClass = 'bg-warning-subtle text-warning-emphasis';
        $badgeIcon  = 'bi-clock-fill';
        $badgeLabel = $pendentes . ' pendente' . ($pendentes !== 1 ? 's' : '');
    } else {
        $badgeClass = 'bg-success-subtle text-success-emphasis';
        $badgeIcon  = 'bi-check-circle-fill';
        $badgeLabel = 'Em dia';
    }
  ?>
  <a href="<?= url('taxas?ano=' . $r['ano']) ?>"
     class="d-flex align-items-center justify-content-between px-4 py-3 text-decoration-none text-body <?= $i > 0 ? 'border-top' : '' ?>"
     style="transition:background .12s;"
     onmouseover="this.style.background='var(--bs-tertiary-bg)'"
     onmouseout="this.style.background=''">
    <div class="d-flex align-items-center gap-3">
      <span class="fw-bold" style="font-size:1.1rem;"><?= $r['ano'] ?></span>
      <span class="badge rounded-pill <?= $badgeClass ?>" style="font-size:.72rem;">
        <i class="bi <?= $badgeIcon ?> me-1"></i><?= $badgeLabel ?>
      </span>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span class="text-body-secondary" style="font-size:.82rem;">
        <?= (int)$r['total_meses'] ?> mes<?= (int)$r['total_meses'] !== 1 ? 'es' : '' ?>
        · <?= dinheiro((float)$r['valor_arrecadado']) ?> arrecadado
      </span>
      <i class="bi bi-chevron-right text-body-tertiary" style="font-size:.8rem;"></i>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<?php endif; ?>


<?php /* ══════════════════════════════════════
       NÍVEL 2 — Meses de um ano
       ══════════════════════════════════════ */
else: ?>

<?php if (empty($competencias)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center text-body-secondary py-5">
      <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
      Nenhuma taxa gerada em <?= htmlspecialchars($ano) ?>.
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
  <div class="col-6 col-md-4 col-lg-3">
    <a href="<?= url('taxas?competencia=' . $comp) ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body p-3">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
              <div class="fw-bold fs-6"><?= $nome ?></div>
              <div class="text-body-secondary" style="font-size:.75rem;"><?= $total ?> unidade<?= $total !== 1 ? 's' : '' ?></div>
            </div>
            <?php if ($atrasadas > 0): ?>
              <span class="badge bg-danger" style="font-size:.65rem;"><?= $atrasadas ?> atras.</span>
            <?php elseif ($pendentes > 0): ?>
              <span class="badge bg-warning text-dark" style="font-size:.65rem;"><?= $pendentes ?> pend.</span>
            <?php else: ?>
              <span class="badge bg-success" style="font-size:.65rem;">Em dia</span>
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

          <div class="border-top pt-2">
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
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
