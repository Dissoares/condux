<?php
/** @var Vistoria[] $vistorias @var Vistoria[] $validadesProximas */
$tituloPagina = 'Vistorias';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-clipboard-check text-primary"></i> Vistorias</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      Inspeções prediais, AVCB, elevadores, orçamentos e laudos técnicos.
    </p>
  </div>
  <a href="<?= url('vistorias/nova') ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Nova vistoria
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<!-- Alertas de validade próxima -->
<?php if (!empty($validadesProximas)): ?>
<div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
  <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
  <div>
    <strong>Documentos vencendo em até 60 dias:</strong>
    <div class="d-flex flex-wrap gap-2 mt-2">
      <?php foreach ($validadesProximas as $v): ?>
        <a href="<?= url("vistorias/{$v->id}") ?>"
           class="badge text-bg-warning text-decoration-none fw-semibold"
           style="font-size:.8rem; padding:.4em .7em;">
          <?= htmlspecialchars($v->rotuloTipo()) ?>
          <?= $v->categoria ? '— ' . htmlspecialchars($v->categoria) : '' ?>
          · vence <?= dataBR($v->validade) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="<?= url('vistorias') ?>" class="d-flex flex-wrap gap-2 mb-4">
  <select name="tipo" class="form-select form-select-sm" style="width:auto;">
    <option value="">Todos os tipos</option>
    <?php foreach (Vistoria::$tiposRotulo as $k => $r): ?>
      <option value="<?= $k ?>" <?= ($_GET['tipo'] ?? '') === $k ? 'selected' : '' ?>><?= $r ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-select form-select-sm" style="width:auto;">
    <option value="">Todos os status</option>
    <option value="agendada"  <?= ($_GET['status'] ?? '') === 'agendada'  ? 'selected' : '' ?>>Agendadas</option>
    <option value="realizada" <?= ($_GET['status'] ?? '') === 'realizada' ? 'selected' : '' ?>>Realizadas</option>
    <option value="cancelada" <?= ($_GET['status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
  </select>
  <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel"></i> Filtrar</button>
  <?php if (!empty($_GET['tipo']) || !empty($_GET['status'])): ?>
    <a href="<?= url('vistorias') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i> Limpar</a>
  <?php endif; ?>
</form>

<!-- Lista -->
<?php if (empty($vistorias)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5 text-center">
      <i class="bi bi-clipboard-check text-body-secondary mb-2" style="font-size:2.5rem; opacity:.35;"></i>
      <p class="text-body-secondary mb-3">Nenhuma vistoria encontrada.</p>
      <a href="<?= url('vistorias/nova') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Agendar primeira vistoria
      </a>
    </div>
  </div>
<?php else: ?>
  <div class="d-flex flex-column gap-3">
    <?php foreach ($vistorias as $v): ?>
    <?php
      $corStatus = match($v->status) {
        'realizada' => 'success', 'cancelada' => 'secondary', default => 'primary'
      };
      $alertaValidade = $v->validadeVencida() ? 'danger' : ($v->validadeProxima() ? 'warning' : null);
    ?>
    <a href="<?= url("vistorias/{$v->id}") ?>"
       class="card border-0 shadow-sm text-decoration-none text-body card-hover <?= $alertaValidade ? "card-acento-{$alertaValidade}" : '' ?>">
      <div class="card-body py-3">
        <div class="d-flex align-items-start gap-3">

          <!-- Ícone do tipo -->
          <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary"
               style="width:44px;height:44px;font-size:1.3rem;">
            <i class="bi <?= $v->icone() ?>"></i>
          </div>

          <!-- Info principal -->
          <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="fw-semibold"><?= htmlspecialchars($v->rotuloTipo()) ?></span>
              <?php if ($v->categoria): ?>
                <span class="text-body-secondary">— <?= htmlspecialchars($v->categoria) ?></span>
              <?php endif; ?>
              <span class="badge rounded-pill bg-<?= $corStatus ?> bg-opacity-10 text-<?= $corStatus ?> fw-semibold" style="font-size:.7rem;">
                <?= $v->rotuloStatus() ?>
              </span>
              <?php if ($v->resultado): ?>
                <span class="badge rounded-pill badge-<?= $v->resultado === 'aprovado' ? 'aprovado' : ($v->resultado === 'reprovado' ? 'vencido' : 'pendente') ?>">
                  <?= $v->rotuloResultado() ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap gap-3 mt-1" style="font-size:.8rem; color:var(--bs-secondary-color);">
              <span><i class="bi bi-calendar3 me-1"></i><?= dataBR($v->dataVistoria) ?></span>
              <?php if ($v->nomeResponsavel): ?>
                <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($v->nomeResponsavel) ?></span>
              <?php endif; ?>
              <?php if ($v->nomePrestadora): ?>
                <span><i class="bi bi-building me-1"></i><?= htmlspecialchars($v->nomePrestadora) ?></span>
              <?php endif; ?>
              <?php if ($v->validade): ?>
                <span class="<?= $alertaValidade ? "text-{$alertaValidade} fw-semibold" : '' ?>">
                  <i class="bi bi-clock me-1"></i>
                  Validade: <?= dataBR($v->validade) ?>
                  <?= $v->validadeVencida() ? '⚠ Vencida' : ($v->validadeProxima() ? '⚠ Vencendo' : '') ?>
                </span>
              <?php endif; ?>
              <?php if ($v->numeroDocumento): ?>
                <span><i class="bi bi-hash me-1"></i><?= htmlspecialchars($v->numeroDocumento) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <i class="bi bi-chevron-right text-body-tertiary flex-shrink-0 align-self-center"></i>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<style>
.card-hover { transition: transform .12s, box-shadow .12s; }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
</style>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
