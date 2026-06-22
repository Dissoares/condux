<?php
/** @var array $resumoFinanceiro @var int $totalInadimplentes @var Projeto[] $projetosRecentes */
$tituloPagina = 'Painel';
require_once RAIZ . '/views/layouts/cabecalho.php';

$nomeAdmin    = explode(' ', Sessao::usuarioAtual()['nome'] ?? 'Administrador')[0];
$mesAtual     = strftime('%B de %Y') ?: date('m/Y');
$totalPagas   = (int)($resumoFinanceiro['total_pagas']    ?? 0);
$totalPendentes = (int)($resumoFinanceiro['total_pendentes'] ?? 0);
$valorArrecadado = (float)($resumoFinanceiro['valor_arrecadado'] ?? 0);
$valorPendente   = (float)($resumoFinanceiro['valor_pendente']   ?? 0);
?>

<!-- Cabeçalho de boas-vindas -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
  <div>
    <h4 class="fw-bold mb-0">Olá, <?= htmlspecialchars($nomeAdmin) ?> 👋</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.875rem;">
      <i class="bi bi-calendar3 me-1"></i>
      Resumo financeiro de <?= date('m/Y') ?>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('taxas/gerar-lote') ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-lightning-fill"></i> Gerar taxas
    </a>
    <a href="<?= url('unidades/nova') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-plus-lg"></i> Nova unidade
    </a>
  </div>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-success">
      <div class="card-body">
        <i class="bi bi-check-circle-fill stat-icone"></i>
        <div class="stat-label">Taxas pagas</div>
        <div class="stat-valor"><?= $totalPagas ?></div>
        <div class="stat-detalhe"><?= dinheiro($valorArrecadado) ?> arrecadados</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-warning">
      <div class="card-body">
        <i class="bi bi-clock-fill stat-icone"></i>
        <div class="stat-label">Pendentes</div>
        <div class="stat-valor"><?= $totalPendentes ?></div>
        <div class="stat-detalhe"><?= dinheiro($valorPendente) ?> em aberto</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-danger">
      <div class="card-body">
        <i class="bi bi-exclamation-triangle-fill stat-icone"></i>
        <div class="stat-label">Inadimplentes</div>
        <div class="stat-valor"><?= $totalInadimplentes ?></div>
        <div class="stat-detalhe">unidades em atraso</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card card-stat card-stat-primary">
      <div class="card-body">
        <i class="bi bi-cash-coin stat-icone"></i>
        <div class="stat-label">Arrecadado</div>
        <div class="stat-valor" style="font-size:1.4rem;"><?= dinheiro($valorArrecadado) ?></div>
        <?php
          $total = $valorArrecadado + $valorPendente;
          $pct   = $total > 0 ? round(($valorArrecadado / $total) * 100) : 0;
        ?>
        <div class="stat-detalhe"><?= $pct ?>% da meta mensal</div>
      </div>
    </div>
  </div>
</div>

<!-- Barra de progresso da arrecadação -->
<?php if ($totalPagas + $totalPendentes > 0): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-semibold" style="font-size:.875rem;">Progresso de arrecadação — <?= date('m/Y') ?></span>
      <span class="fw-bold text-success"><?= $pct ?>%</span>
    </div>
    <div class="progress" style="height:10px; border-radius:999px;">
      <div class="progress-bar bg-success" style="width:<?= $pct ?>%; border-radius:999px; transition:width .6s ease;" role="progressbar"></div>
    </div>
    <div class="d-flex justify-content-between mt-2" style="font-size:.75rem; color:var(--bs-secondary-color);">
      <span><?= $totalPagas ?> pagos</span>
      <span><?= $totalPendentes ?> pendentes</span>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Projetos recentes -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center gap-2">
      <span class="icone-secao bg-primary bg-opacity-10 text-primary">
        <i class="bi bi-kanban"></i>
      </span>
      <span class="fw-semibold">Projetos recentes</span>
    </div>
    <a href="<?= url('projetos') ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
  </div>
  <?php if (empty($projetosRecentes)): ?>
    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5 text-center">
      <i class="bi bi-kanban text-body-secondary mb-2" style="font-size:2.5rem; opacity:.35;"></i>
      <p class="text-body-secondary mb-2">Nenhum projeto cadastrado.</p>
      <a href="<?= url('projetos/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Criar primeiro projeto
      </a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Projeto</th>
            <th>Responsável</th>
            <th>Valor estimado</th>
            <th>Status</th>
            <th style="width:60px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($projetosRecentes as $projeto): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($projeto->nome) ?></td>
            <td class="text-body-secondary"><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td>
            <td><?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?></td>
            <td><span class="badge rounded-pill badge-<?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span></td>
            <td>
              <a href="<?= url("projetos/{$projeto->id}") ?>" class="btn btn-outline-secondary btn-sm">
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
