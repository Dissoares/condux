<?php
/**
 * @var array[]     $unidades     — linhas unidade + status taxa (LEFT JOIN)
 * @var array       $resumo
 * @var string      $competencia
 * @var string|null $mensagem
 * @var string|null $erroMensagem
 */
$tituloPagina = 'Taxa Condominial — ' . formatarCompetencia($competencia);
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-cash-stack"></i> Taxa Condominial</h4>
    <nav aria-label="breadcrumb" class="mt-1">
      <ol class="breadcrumb mb-0" style="font-size:.82rem;">
        <li class="breadcrumb-item"><a href="<?= url('taxas') ?>">Meses</a></li>
        <li class="breadcrumb-item active"><?= formatarCompetencia($competencia) ?></li>
      </ol>
    </nav>
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

<!-- Resumo -->
<div class="row g-2 g-md-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
        <div class="rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0 bg-success bg-opacity-10 text-success" style="width:38px;height:38px;">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold lh-1"><?= (int)($resumo['total_pagas'] ?? 0) ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;">Pagas</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
        <div class="rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0 bg-danger bg-opacity-10 text-danger" style="width:38px;height:38px;">
          <i class="bi bi-exclamation-circle-fill"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold lh-1"><?= (int)($resumo['total_atrasadas'] ?? 0) ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;">Atrasadas</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
        <div class="rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0 bg-warning bg-opacity-10 text-warning" style="width:38px;height:38px;">
          <i class="bi bi-clock-fill"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold lh-1"><?= (int)($resumo['total_pendentes'] ?? 0) ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;">Pendentes</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
        <div class="rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary" style="width:38px;height:38px;">
          <i class="bi bi-cash"></i>
        </div>
        <div>
          <div class="fw-bold lh-1" style="font-size:.85rem;"><?= dinheiro((float)($resumo['valor_arrecadado'] ?? 0)) ?></div>
          <div class="text-body-secondary" style="font-size:.72rem;">Arrecadado</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabela de unidades -->
<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Unidade</th>
          <th>Status</th>
          <th class="d-none d-md-table-cell">Valor</th>
          <th class="d-none d-md-table-cell">Vencimento</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($unidades)): ?>
          <tr><td colspan="5" class="text-center text-body-secondary py-4">Nenhuma unidade ativa.</td></tr>
        <?php else: ?>
          <?php foreach ($unidades as $u):
            $semTaxa    = $u['taxa_id'] === null;
            $statusBd   = $u['status'] ?? null;
            $venc       = $u['vencimento'] ?? null;
            $estaAtras  = !$semTaxa && ($statusBd === 'vencido' || ($statusBd === 'pendente' && $venc && $venc < date('Y-m-d')));
            $statusEf   = $semTaxa ? 'sem_taxa' : ($estaAtras ? 'vencido' : $statusBd);
            $labels     = ['pago' => 'Pago', 'vencido' => 'Atrasado', 'pendente' => 'Pendente', 'isento' => 'Isento', 'sem_taxa' => 'Sem taxa'];
            $badgeCls   = ['pago' => 'badge-pago', 'vencido' => 'badge-vencido', 'pendente' => 'badge-pendente', 'isento' => 'badge-isento', 'sem_taxa' => 'bg-secondary bg-opacity-15 text-body'];
          ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($u['identificacao_unidade']) ?></td>
            <td>
              <span class="badge rounded-pill <?= $badgeCls[$statusEf] ?? 'bg-secondary' ?>">
                <?= $labels[$statusEf] ?? $statusEf ?>
              </span>
            </td>
            <td class="d-none d-md-table-cell">
              <?= $semTaxa ? '<span class="text-body-tertiary">—</span>' : dinheiro((float)$u['valor']) ?>
            </td>
            <td class="d-none d-md-table-cell">
              <?= $semTaxa ? '<span class="text-body-tertiary">—</span>' : dataBR($venc) ?>
            </td>
            <td class="text-end">
              <a href="<?= url("taxas/unidade/{$u['unidade_id']}?competencia={$competencia}") ?>"
                 class="btn btn-outline-primary btn-sm">
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
