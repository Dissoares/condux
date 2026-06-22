<?php
/** @var Unidade[] $unidades @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Unidades';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-building"></i> Unidades</h4>
  <a href="<?= url('unidades/nova') ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Nova unidade
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Unidade</th>
          <th>Responsável</th>
          <th>Status mês atual</th>
          <th style="width:100px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($unidades)): ?>
          <tr><td colspan="4" class="text-center text-body-secondary py-4">Nenhuma unidade cadastrada.</td></tr>
        <?php else: ?>
          <?php foreach ($unidades as $unidade): ?>
          <tr>
            <td>
              <span class="fw-semibold"><?= htmlspecialchars($unidade->identificacao()) ?></span>
              <?php if ($unidade->andar): ?>
                <br><small class="text-body-secondary"><?= $unidade->andar ?>º andar</small>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($unidade->nomeResponsavel ?? '—') ?></td>
            <td>
              <?php $status = $unidade->statusTaxaAtual ?? 'sem_taxa'; ?>
              <span class="badge rounded-pill badge-<?= $status ?>">
                <?= match($status) { 'pago' => 'Pago', 'vencido' => 'Vencido', 'isento' => 'Isento', 'pendente' => 'Pendente', default => 'Sem taxa' } ?>
              </span>
            </td>
            <td>
              <a href="<?= url("unidades/{$unidade->id}") ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-eye"></i> Ver
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
