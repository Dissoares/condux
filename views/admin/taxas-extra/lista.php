<?php
/** @var TaxaExtra[] $grupos */
$tituloPagina = 'Taxas Extras';
require_once RAIZ . '/views/layouts/cabecalho.php';

$msgGerado   = isset($_GET['msg']) && $_GET['msg'] === 'gerado';
$qtdParcelas = (int)($_GET['parcelas'] ?? 0);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-plus-circle text-primary"></i> Taxas Extras</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">Cobranças vinculadas a projetos e obras.</p>
  </div>
  <a href="<?= url('taxas-extra/nova') ?>" class="btn btn-primary">
    <i class="bi bi-lightning-fill"></i> Gerar nova cobrança
  </a>
</div>

<?php if ($msgGerado): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
    <?= $qtdParcelas ?> parcela<?= $qtdParcelas !== 1 ? 's' : '' ?> gerada<?= $qtdParcelas !== 1 ? 's' : '' ?> com sucesso para todas as unidades.
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <?php if (empty($grupos)): ?>
    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5 text-center">
      <i class="bi bi-plus-circle text-body-secondary mb-2" style="font-size:2.5rem; opacity:.35;"></i>
      <p class="text-body-secondary mb-3">Nenhuma taxa extra cadastrada.</p>
      <a href="<?= url('taxas-extra/nova') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-lightning-fill"></i> Gerar primeira cobrança
      </a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Projeto / Nome</th>
            <th class="d-none d-sm-table-cell">Parcelas</th>
            <th class="d-none d-md-table-cell">Valor / parcela</th>
            <th class="d-none d-md-table-cell">Vencimento inicial</th>
            <th style="width:80px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($grupos as $g): ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($g->nomeProjeto ?? $g->nome) ?></div>
              <?php if ($g->nomeProjeto): ?>
                <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($g->nome) ?></div>
              <?php endif; ?>
              <?php if ($g->valorTotal): ?>
                <div class="text-body-secondary" style="font-size:.75rem;">
                  Total do projeto: <?= dinheiro($g->valorTotal) ?>
                </div>
              <?php endif; ?>
            </td>
            <td class="d-none d-sm-table-cell">
              <?php if ($g->totalParcelas): ?>
                <span class="badge bg-secondary bg-opacity-10 text-body fw-semibold">
                  <?= $g->totalParcelas ?>x
                </span>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td class="d-none d-md-table-cell"><?= dinheiro($g->valor) ?></td>
            <td class="d-none d-md-table-cell"><?= dataBR($g->vencimento) ?></td>
            <td>
              <a href="<?= url("taxas-extra/{$g->id}") ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
