<?php
/** @var array $resumoFinanceiro @var int $totalInadimplentes @var Projeto[] $projetosRecentes */
$tituloPagina = 'Painel';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0">Painel — <?= date('m/Y') ?></h4>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-success bg-opacity-10 text-success" style="width:48px;height:48px;font-size:1.3rem;">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold lh-1"><?= (int)($resumoFinanceiro['total_pagas'] ?? 0) ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">Taxas pagas no mês</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-warning bg-opacity-10 text-warning" style="width:48px;height:48px;font-size:1.3rem;">
          <i class="bi bi-clock-fill"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold lh-1"><?= (int)($resumoFinanceiro['total_pendentes'] ?? 0) ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">Pendentes / vencidas</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-danger bg-opacity-10 text-danger" style="width:48px;height:48px;font-size:1.3rem;">
          <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold lh-1"><?= $totalInadimplentes ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">Unidades inadimplentes</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary" style="width:48px;height:48px;font-size:1.3rem;">
          <i class="bi bi-cash"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold lh-1"><?= dinheiro((float)($resumoFinanceiro['valor_arrecadado'] ?? 0)) ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">Arrecadado no mês</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <span class="fw-semibold">Projetos recentes</span>
    <a href="<?= url('projetos') ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
  </div>
  <div class="card-body p-0">
    <?php if (empty($projetosRecentes)): ?>
      <p class="text-body-secondary p-3 mb-0" style="font-size:.9rem;">Nenhum projeto cadastrado.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Projeto</th><th>Responsável</th><th>Valor estimado</th><th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($projetosRecentes as $projeto): ?>
            <tr>
              <td><?= htmlspecialchars($projeto->nome) ?></td>
              <td><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td>
              <td><?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?></td>
              <td><span class="badge rounded-pill badge-<?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span></td>
              <td><a href="<?= url("projetos/{$projeto->id}") ?>" class="btn btn-outline-secondary btn-sm">Ver</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
