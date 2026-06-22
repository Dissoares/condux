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
          <th class="d-none d-sm-table-cell">Responsável</th>
          <th class="d-none d-md-table-cell">Status mês atual</th>
          <th style="width:80px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($unidades)): ?>
          <tr><td colspan="4" class="text-center text-body-secondary py-4">Nenhuma unidade cadastrada.</td></tr>
        <?php else: ?>
          <?php foreach ($unidades as $unidade): ?>
          <?php $status = $unidade->statusTaxaAtual ?? 'sem_taxa'; ?>
          <tr>
            <td>
              <div class="fw-semibold lh-sm"><?= htmlspecialchars($unidade->identificacao()) ?></div>
              <?php if ($unidade->andar): ?>
                <div class="text-body-secondary" style="font-size:.78rem;"><?= $unidade->andar ?>º andar</div>
              <?php endif; ?>
              <div class="d-sm-none mt-1">
                <span class="badge rounded-pill badge-<?= $status ?>">
                  <?= match($status) { 'pago' => 'Pago', 'vencido' => 'Vencido', 'isento' => 'Isento', 'pendente' => 'Pendente', default => 'Sem taxa' } ?>
                </span>
              </div>
            </td>
            <td class="d-none d-sm-table-cell"><?= htmlspecialchars($unidade->nomeResponsavel ?? '—') ?></td>
            <td class="d-none d-md-table-cell">
              <span class="badge rounded-pill badge-<?= $status ?>">
                <?= match($status) { 'pago' => 'Pago', 'vencido' => 'Vencido', 'isento' => 'Isento', 'pendente' => 'Pendente', default => 'Sem taxa' } ?>
              </span>
            </td>
            <td>
              <a href="<?= url("unidades/{$unidade->id}") ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-eye"></i>
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
