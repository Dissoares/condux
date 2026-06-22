<?php
/** @var Projeto[] $projetos */
$tituloPagina      = 'Portal da Transparência';
$filtroStatus      = $_GET['status'] ?? '';
$statusDisponiveis = Projeto::$rotulosStatus;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-eye"></i> Portal da Transparência</h4>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="<?= url('transparencia') ?>" class="btn btn-sm <?= $filtroStatus === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Todos</a>
  <?php foreach ($statusDisponiveis as $chave => $rotulo): ?>
    <a href="<?= url("transparencia?status={$chave}") ?>"
       class="btn btn-sm <?= $filtroStatus === $chave ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= htmlspecialchars($rotulo) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($projetos)): ?>
  <div class="card border-0 shadow-sm text-center text-body-secondary py-5">
    <i class="bi bi-folder-x d-block mb-2" style="font-size:2.5rem;"></i>
    Nenhum projeto encontrado.
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($projetos as $projeto): ?>
    <div class="col-md-6 col-xl-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="fw-semibold mb-0"><?= htmlspecialchars($projeto->nome) ?></h6>
            <span class="badge rounded-pill badge-<?= $projeto->status ?> ms-2"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span>
          </div>

          <?php if ($projeto->descricao): ?>
            <p class="text-body-secondary mb-2" style="font-size:.875rem; line-height:1.5;">
              <?= nl2br(htmlspecialchars(mb_substr($projeto->descricao, 0, 120))) ?><?= mb_strlen($projeto->descricao) > 120 ? '…' : '' ?>
            </p>
          <?php endif; ?>

          <div class="text-body-secondary mb-3" style="font-size:.8rem;">
            <?php if ($projeto->nomeResponsavel): ?>
              <div><i class="bi bi-person"></i> <?= htmlspecialchars($projeto->nomeResponsavel) ?></div>
            <?php endif; ?>
            <?php if ($projeto->valorEstimado): ?>
              <div><i class="bi bi-cash"></i> <?= dinheiro($projeto->valorEstimado) ?> estimado</div>
            <?php endif; ?>
          </div>

          <a href="<?= url("transparencia/{$projeto->id}") ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye"></i> Ver detalhes
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
