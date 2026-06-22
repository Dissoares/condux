<?php
/** @var Projeto[] $projetos @var string|null $mensagem @var bool $ehAdmin */
$tituloPagina      = 'Projetos';
$statusDisponiveis = Projeto::$rotulosStatus;
$filtroStatus      = $_GET['status'] ?? '';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-kanban"></i> Projetos</h4>
  <a href="<?= url('projetos/novo') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo projeto</a>
</div>

<?php if (!empty($mensagem)): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="<?= url('projetos') ?>" class="btn btn-sm <?= $filtroStatus === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Todos</a>
  <?php foreach ($statusDisponiveis as $chave => $rotulo): ?>
    <a href="<?= url("projetos?status={$chave}") ?>"
       class="btn btn-sm <?= $filtroStatus === $chave ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= htmlspecialchars($rotulo) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Projeto</th><th>Responsável</th><th>Prestadora</th><th>Valor estimado</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($projetos)): ?>
          <tr><td colspan="6" class="text-center text-body-secondary py-4">Nenhum projeto encontrado.</td></tr>
        <?php else: ?>
          <?php foreach ($projetos as $projeto): ?>
          <tr>
            <td>
              <span class="fw-semibold"><?= htmlspecialchars($projeto->nome) ?></span>
              <?php if ($projeto->idealizador): ?>
                <br><small class="text-body-secondary">Idealizador: <?= htmlspecialchars($projeto->idealizador) ?></small>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td>
            <td><?= htmlspecialchars($projeto->nomePrestadora ?? '—') ?></td>
            <td><?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?></td>
            <td><span class="badge rounded-pill badge-<?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span></td>
            <td>
              <a href="<?= url("projetos/{$projeto->id}") ?>" class="btn btn-outline-secondary btn-sm">
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
