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
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-2 px-1">
      <div class="fw-bold text-primary" style="font-size:.95rem;"><?= $fmtVal($valorTotal) ?></div>
      <div class="text-body-secondary" style="font-size:.7rem;">Total do mês</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-2 px-1">
      <div class="fw-bold text-success" style="font-size:.95rem;"><?= $fmtVal($valorPago) ?></div>
      <div class="text-body-secondary" style="font-size:.7rem;">Pago</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-2 px-1">
      <div class="fw-bold text-warning" style="font-size:.95rem;"><?= $fmtVal($valorPendente - $valorAtrasado) ?></div>
      <div class="text-body-secondary" style="font-size:.7rem;">Pendente</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-2 px-1">
      <div class="fw-bold text-danger" style="font-size:.95rem;"><?= $fmtVal($valorAtrasado) ?></div>
      <div class="text-body-secondary" style="font-size:.7rem;">Atrasado</div>
    </div>
  </div>
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

<!-- ── Mobile: lista de cards ─────────────────────────── -->
<div class="d-md-none card border-0 shadow-sm overflow-hidden">
  <?php foreach ($contas as $i => $c): ?>
  <?php $atrasada = $c->estaAtrasada(); ?>
  <div class="px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?> <?= $atrasada ? 'border-start border-danger border-3' : ($c->status === 'pago' ? 'border-start border-success border-3' : 'border-start border-warning border-3') ?>"
       style="cursor:pointer;" onclick="window.location='<?= url('contas/' . $c->id) ?>'"
       onmouseover="this.style.background='var(--bs-tertiary-bg)'" onmouseout="this.style.background=''">
    <div class="d-flex align-items-center gap-3">

      <!-- Ícone -->
      <div class="cat-icon flex-shrink-0 bg-<?= $c->corCategoria() ?>-subtle text-<?= $c->corCategoria() ?>-emphasis">
        <i class="bi <?= $c->iconeCategoria() ?>"></i>
      </div>

      <!-- Info -->
      <div class="flex-grow-1 min-width-0">
        <div class="fw-semibold" style="font-size:.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
          <?= htmlspecialchars($c->descricao) ?>
        </div>
        <div class="text-body-secondary" style="font-size:.75rem;">
          <?= $c->rotuloCategoria() ?>
          <?php if ($c->fornecedor): ?> · <?= htmlspecialchars($c->fornecedor) ?><?php endif; ?>
          <?php if ($c->dataVencimento): ?> · venc. <?= $fmtData($c->dataVencimento) ?><?php endif; ?>
        </div>
      </div>

      <!-- Valor + Status -->
      <div class="text-end flex-shrink-0">
        <div class="fw-bold" style="font-size:.95rem; white-space:nowrap;"><?= $fmtVal($c->valor) ?></div>
        <div class="mt-1">
          <?php if ($c->status === 'pago'): ?>
            <span class="badge badge-pago" style="font-size:.65rem;">Pago</span>
          <?php elseif ($atrasada): ?>
            <span class="badge badge-vencido" style="font-size:.65rem;">Atrasada</span>
          <?php else: ?>
            <span class="badge badge-pendente" style="font-size:.65rem;">Pendente</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Ações -->
    <div class="d-flex gap-2 mt-2 justify-content-end" onclick="event.stopPropagation()">
      <?php if ($c->anexo): ?>
        <a href="<?= url('uploads/' . $c->anexo) ?>" target="_blank"
           class="btn btn-outline-secondary btn-sm py-0 px-2" title="Comprovante">
          <i class="bi bi-paperclip"></i>
        </a>
      <?php endif; ?>
      <?php if ($c->status !== 'pago'): ?>
      <form action="<?= url('contas/pagar') ?>" method="POST" class="d-inline">
        <input type="hidden" name="id"             value="<?= $c->id ?>">
        <input type="hidden" name="comp"           value="<?= htmlspecialchars($compFiltro) ?>">
        <input type="hidden" name="data_pagamento" value="<?= $hoje ?>">
        <button type="submit" class="btn btn-success btn-sm py-0 px-3"
                onclick="return confirm('Marcar como pago hoje?')">
          <i class="bi bi-check2"></i> Pago
        </button>
      </form>
      <?php endif; ?>
      <a href="<?= url('contas/' . $c->id . '/excluir?comp=' . urlencode($compFiltro)) ?>"
         onclick="return confirm('Remover esta conta?')"
         class="btn btn-outline-danger btn-sm py-0 px-2">
        <i class="bi bi-trash"></i>
      </a>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Total -->
  <div class="border-top bg-body-tertiary px-3 py-2 d-flex justify-content-between align-items-center">
    <span class="fw-semibold text-body-secondary" style="font-size:.8rem;">TOTAL</span>
    <span class="fw-bold"><?= $fmtVal($valorTotal) ?></span>
  </div>
</div>

<!-- ── Desktop: tabela ────────────────────────────────── -->
<div class="d-none d-md-block card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:36px;"></th>
          <th>Conta</th>
          <th>Fornecedor</th>
          <th>Valor</th>
          <th>Vencimento</th>
          <th>Status</th>
          <th style="width:110px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contas as $c): ?>
        <?php $atrasada = $c->estaAtrasada(); ?>
        <tr class="conta-row" style="cursor:pointer;" onclick="window.location='<?= url('contas/' . $c->id) ?>'"
          <td>
            <div class="cat-icon bg-<?= $c->corCategoria() ?>-subtle text-<?= $c->corCategoria() ?>-emphasis">
              <i class="bi <?= $c->iconeCategoria() ?>"></i>
            </div>
          </td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($c->descricao) ?></div>
            <div class="text-body-secondary" style="font-size:.75rem;"><?= $c->rotuloCategoria() ?></div>
          </td>
          <td class="text-body-secondary"><?= htmlspecialchars($c->fornecedor ?? '—') ?></td>
          <td class="fw-semibold"><?= $fmtVal($c->valor) ?></td>
          <td>
            <?php if ($c->dataVencimento): ?>
              <span class="<?= $atrasada ? 'text-danger fw-semibold' : 'text-body-secondary' ?>">
                <?= $fmtData($c->dataVencimento) ?>
              </span>
            <?php else: ?><span class="text-body-secondary">—</span><?php endif; ?>
          </td>
          <td>
            <?php if ($c->status === 'pago'): ?>
              <span class="badge badge-pago">Pago</span>
              <?php if ($c->dataPagamento): ?>
                <div class="text-body-secondary" style="font-size:.7rem;"><?= $fmtData($c->dataPagamento) ?></div>
              <?php endif; ?>
            <?php elseif ($atrasada): ?>
              <span class="badge badge-vencido">Atrasada</span>
            <?php else: ?>
              <span class="badge badge-pendente">Pendente</span>
            <?php endif; ?>
            <?php if ($c->anexo): ?>
              <a href="<?= url('uploads/' . $c->anexo) ?>" target="_blank"
                 class="ms-1 text-primary" style="font-size:.75rem;" title="Comprovante">
                <i class="bi bi-paperclip"></i>
              </a>
            <?php endif; ?>
          </td>
          <td onclick="event.stopPropagation()">
            <div class="d-flex gap-1 justify-content-end">
              <?php if ($c->status !== 'pago'): ?>
              <form action="<?= url('contas/pagar') ?>" method="POST" class="d-inline">
                <input type="hidden" name="id"             value="<?= $c->id ?>">
                <input type="hidden" name="comp"           value="<?= htmlspecialchars($compFiltro) ?>">
                <input type="hidden" name="data_pagamento" value="<?= $hoje ?>">
                <button type="submit" class="btn btn-success btn-sm py-0 px-2"
                        title="Marcar como pago" onclick="return confirm('Marcar como pago hoje?')">
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
