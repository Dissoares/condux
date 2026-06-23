<?php
/**
 * @var Conta[]     $contas
 * @var array[]     $resumos
 * @var array       $totais
 * @var string      $compFiltro
 * @var string|null $mensagem
 * @var string|null $erroMensagem
 */
$tituloPagina = 'Contas';
require_once RAIZ . '/views/layouts/cabecalho.php';

$nomesMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$fmtComp = function(string $c) use ($nomesMeses): string {
    [$ano, $mes] = explode('-', $c);
    return ($nomesMeses[(int)$mes] ?? $mes) . '/' . $ano;
};
$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;
$fmtVal  = fn(float $v)   => 'R$ ' . number_format($v, 2, ',', '.');
$hoje    = date('Y-m-d');

$valorTotal    = (float) ($totais['valor_total']    ?? 0);
$valorPago     = (float) ($totais['valor_pago']     ?? 0);
$valorPendente = (float) ($totais['valor_pendente'] ?? 0);
$valorAtrasado = (float) ($totais['valor_atrasado'] ?? 0);
$qtdAtrasadas  = (int)   ($totais['total_atrasadas'] ?? 0);
?>

<style>
.conta-row td { vertical-align:middle; font-size:.875rem; }
.cat-icon { width:32px; height:32px; border-radius:.5rem; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <h4 class="fw-semibold mb-0"><i class="bi bi-receipt"></i> Contas</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#form-nova-conta">
    <i class="bi bi-plus-lg"></i> Nova conta
  </button>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<!-- Formulário colapsável -->
<div class="collapse mb-3" id="form-nova-conta">
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent fw-semibold py-3">Nova conta — <?= $fmtComp($compFiltro) ?></div>
    <div class="card-body p-4">
      <form action="<?= url('contas/salvar') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="competencia" value="<?= htmlspecialchars($compFiltro) ?>">

        <div class="row g-3 mb-3">
          <div class="col-sm-5">
            <label class="form-label">Descrição *</label>
            <input type="text" name="descricao" class="form-control" required
                   placeholder="Ex: Conta de Água — SABESP">
          </div>
          <div class="col-sm-3">
            <label class="form-label">Categoria</label>
            <select name="categoria" class="form-select">
              <?php foreach (Conta::$categorias as $val => [$rot, $ico, $cor]): ?>
                <option value="<?= $val ?>"><?= $rot ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-4">
            <label class="form-label">Fornecedor</label>
            <input type="text" name="fornecedor" class="form-control" placeholder="Nome da empresa">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-sm-3">
            <label class="form-label">Valor (R$) *</label>
            <input type="text" name="valor" class="form-control" required placeholder="0,00">
          </div>
          <div class="col-sm-3">
            <label class="form-label">Vencimento</label>
            <input type="date" name="data_vencimento" class="form-control">
          </div>
          <div class="col-sm-3">
            <label class="form-label">Data pagamento</label>
            <input type="date" name="data_pagamento" class="form-control">
          </div>
          <div class="col-sm-3">
            <label class="form-label">Comprovante</label>
            <input type="file" name="anexo" class="form-control" accept="image/*,.pdf">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Observações</label>
          <input type="text" name="observacoes" class="form-control" placeholder="Opcional...">
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Adicionar</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#form-nova-conta">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Filtro de competência -->
<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
  <form method="GET" action="<?= url('contas') ?>" class="d-flex align-items-center gap-2">
    <label class="form-label mb-0 text-body-secondary" style="font-size:.82rem;white-space:nowrap;">Competência:</label>
    <input type="month" name="comp" class="form-control form-control-sm" style="width:160px;"
           value="<?= htmlspecialchars($compFiltro) ?>" onchange="this.form.submit()">
  </form>

  <!-- Resumo dos meses anteriores como pills -->
  <div class="d-flex gap-1 flex-wrap">
    <?php foreach (array_slice($resumos, 0, 6) as $r): ?>
    <?php $atras = (int)($r['total_atrasadas'] ?? 0); $pend = (int)$r['total_pendentes']; ?>
    <a href="<?= url('contas?comp=' . $r['competencia']) ?>"
       class="badge text-decoration-none
              <?= $compFiltro === $r['competencia'] ? 'bg-primary' : ($atras > 0 ? 'bg-danger-subtle text-danger-emphasis' : ($pend > 0 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success-emphasis')) ?>"
       style="font-size:.72rem;">
      <?= $fmtComp($r['competencia']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Totais da competência -->
<?php if ($valorTotal > 0): ?>
<div class="row g-2 mb-3">
  <?php foreach ([
    [$fmtVal($valorTotal),    'Total do mês',  'primary'],
    [$fmtVal($valorPago),     'Pago',          'success'],
    [$fmtVal($valorPendente - $valorAtrasado), 'Pendente', 'warning'],
    [$fmtVal($valorAtrasado), 'Atrasado',      'danger'],
  ] as [$val, $lbl, $cor]): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-2">
      <div class="fw-bold text-<?= $cor ?>" style="font-size:1rem;"><?= $val ?></div>
      <div class="text-body-secondary" style="font-size:.7rem;"><?= $lbl ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Lista de contas -->
<?php if (empty($contas)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-receipt fs-1 opacity-25 d-block mb-3"></i>
    Nenhuma conta registrada para <?= $fmtComp($compFiltro) ?>.
    <div class="mt-3">
      <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#form-nova-conta">
        <i class="bi bi-plus-lg"></i> Adicionar conta
      </button>
    </div>
  </div>
</div>
<?php else: ?>

<?php
// Agrupar por categoria
$porCategoria = [];
foreach ($contas as $c) {
    $porCategoria[$c->categoria][] = $c;
}
// Ordenar: atrasadas/pendentes primeiro, depois pagas
uksort($porCategoria, fn($a, $b) => strcmp($a, $b));
?>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:32px;"></th>
          <th>Conta</th>
          <th class="d-none d-md-table-cell">Fornecedor</th>
          <th>Valor</th>
          <th class="d-none d-md-table-cell">Vencimento</th>
          <th>Status</th>
          <th style="width:120px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contas as $c): ?>
        <?php $atrasada = $c->estaAtrasada(); ?>
        <tr class="conta-row <?= $atrasada ? 'table-danger bg-opacity-25' : ($c->status === 'pago' ? '' : '') ?>">
          <td>
            <div class="cat-icon bg-<?= $c->corCategoria() ?>-subtle text-<?= $c->corCategoria() ?>-emphasis">
              <i class="bi <?= $c->iconeCategoria() ?>"></i>
            </div>
          </td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($c->descricao) ?></div>
            <div class="text-body-secondary" style="font-size:.75rem;"><?= $c->rotuloCategoria() ?></div>
          </td>
          <td class="d-none d-md-table-cell text-body-secondary">
            <?= htmlspecialchars($c->fornecedor ?? '—') ?>
          </td>
          <td class="fw-semibold"><?= $fmtVal($c->valor) ?></td>
          <td class="d-none d-md-table-cell">
            <?php if ($c->dataVencimento): ?>
              <span class="<?= $atrasada ? 'text-danger fw-semibold' : 'text-body-secondary' ?>">
                <?= $fmtData($c->dataVencimento) ?>
              </span>
            <?php else: ?><span class="text-body-secondary">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($c->status === 'pago'): ?>
              <span class="badge bg-success-subtle text-success-emphasis" style="font-size:.7rem;">Pago</span>
              <?php if ($c->dataPagamento): ?>
                <div class="text-body-secondary" style="font-size:.7rem;"><?= $fmtData($c->dataPagamento) ?></div>
              <?php endif; ?>
            <?php elseif ($atrasada): ?>
              <span class="badge bg-danger-subtle text-danger-emphasis" style="font-size:.7rem;">Atrasada</span>
            <?php else: ?>
              <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:.7rem;">Pendente</span>
            <?php endif; ?>
            <?php if ($c->anexo): ?>
              <a href="<?= url('uploads/' . $c->anexo) ?>" target="_blank"
                 class="ms-1 text-primary" title="Ver comprovante" style="font-size:.75rem;">
                <i class="bi bi-paperclip"></i>
              </a>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex gap-1 justify-content-end">
              <?php if ($c->status !== 'pago'): ?>
              <form action="<?= url('contas/pagar') ?>" method="POST" class="d-inline">
                <input type="hidden" name="id"             value="<?= $c->id ?>">
                <input type="hidden" name="comp"           value="<?= htmlspecialchars($compFiltro) ?>">
                <input type="hidden" name="data_pagamento" value="<?= $hoje ?>">
                <button type="submit" class="btn btn-success btn-sm py-0 px-2" title="Marcar como pago"
                        onclick="return confirm('Marcar como pago hoje?')">
                  <i class="bi bi-check2"></i>
                </button>
              </form>
              <?php endif; ?>
              <a href="<?= url('contas/' . $c->id . '/excluir?comp=' . urlencode($compFiltro)) ?>"
                 onclick="return confirm('Remover esta conta?')"
                 class="btn btn-outline-danger btn-sm py-0 px-2">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="3" class="fw-semibold" style="font-size:.875rem;">Total</td>
          <td class="fw-bold" style="font-size:.875rem;"><?= $fmtVal($valorTotal) ?></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
