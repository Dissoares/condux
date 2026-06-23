<?php
/** @var Projeto[] $projetos @var string|null $mensagem @var bool $ehAdmin */
$tituloPagina      = 'Projetos';
$statusDisponiveis = Projeto::$rotulosStatus;
$filtroStatus      = $_GET['status'] ?? '';
require_once RAIZ . '/views/layouts/cabecalho.php';

$iconeStatus = [
    'pendente'     => 'bi-hourglass',
    'aprovado'     => 'bi-check-circle',
    'em_andamento' => 'bi-arrow-repeat',
    'concluido'    => 'bi-check2-all',
    'cancelado'    => 'bi-x-circle',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-kanban"></i> Projetos</h4>
  <a href="<?= url('projetos/novo') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Novo projeto
  </a>
</div>

<?php if (!empty($mensagem)): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<!-- Filtros de status -->
<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="<?= url('projetos') ?>"
     class="btn btn-sm <?= $filtroStatus === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Todos</a>
  <?php foreach ($statusDisponiveis as $chave => $rotulo): ?>
    <a href="<?= url("projetos?status={$chave}") ?>"
       class="btn btn-sm <?= $filtroStatus === $chave ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= htmlspecialchars($rotulo) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($projetos)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-kanban fs-1 opacity-25 d-block mb-3"></i>
    Nenhum projeto encontrado.
    <div class="mt-3">
      <a href="<?= url('projetos/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Criar projeto
      </a>
    </div>
  </div>
</div>

<?php else: ?>

<!-- ── Mobile: cards ──────────────────────────────────── -->
<div class="d-md-none card border-0 shadow-sm overflow-hidden">
  <?php foreach ($projetos as $i => $projeto): ?>
  <div class="px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?>">
    <div class="d-flex align-items-start gap-3">

      <!-- Ícone de status -->
      <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
           style="width:40px; height:40px; background:var(--bs-<?= badgeCorStatus($projeto->status) ?>-bg, #e5e7eb);">
        <i class="bi <?= $iconeStatus[$projeto->status] ?? 'bi-circle' ?> text-<?= badgeCorStatus($projeto->status) ?>"
           style="font-size:1.1rem;"></i>
      </div>

      <!-- Info -->
      <div class="flex-grow-1 min-width-0">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <span class="fw-semibold" style="font-size:.95rem;"><?= htmlspecialchars($projeto->nome) ?></span>
          <span class="badge rounded-pill badge-<?= $projeto->status ?> flex-shrink-0" style="font-size:.68rem;">
            <?= htmlspecialchars($projeto->rotuloStatus()) ?>
          </span>
        </div>

        <div class="mt-1 d-flex flex-wrap gap-x-3 gap-2" style="font-size:.78rem; color:var(--bs-body-secondary);">
          <?php if ($projeto->nomeResponsavel): ?>
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($projeto->nomeResponsavel) ?></span>
          <?php endif; ?>
          <?php if ($projeto->nomePrestadora): ?>
            <span><i class="bi bi-building me-1"></i><?= htmlspecialchars($projeto->nomePrestadora) ?></span>
          <?php endif; ?>
          <?php if ($projeto->valorEstimado): ?>
            <span class="fw-semibold text-body"><i class="bi bi-cash me-1"></i><?= dinheiro($projeto->valorEstimado) ?></span>
          <?php endif; ?>
        </div>

        <?php if ($projeto->idealizador): ?>
          <div class="text-body-secondary" style="font-size:.75rem; margin-top:.2rem;">
            Idealizador: <?= htmlspecialchars($projeto->idealizador) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Botão ver -->
    <div class="mt-2 d-flex justify-content-end">
      <a href="<?= url("projetos/{$projeto->id}") ?>" class="btn btn-outline-secondary btn-sm py-1 px-3">
        <i class="bi bi-eye me-1"></i> Ver detalhes
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Desktop: tabela ────────────────────────────────── -->
<div class="d-none d-md-block card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Projeto</th>
          <th>Responsável</th>
          <th>Prestadora</th>
          <th>Valor estimado</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
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
          <td>
            <span class="badge rounded-pill badge-<?= $projeto->status ?>">
              <?= htmlspecialchars($projeto->rotuloStatus()) ?>
            </span>
          </td>
          <td>
            <a href="<?= url("projetos/{$projeto->id}") ?>" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-eye"></i> Ver
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php
function badgeCorStatus(string $status): string {
    return match($status) {
        'aprovado'     => 'success',
        'em_andamento' => 'primary',
        'concluido'    => 'success',
        'cancelado'    => 'danger',
        default        => 'warning',
    };
}
?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
