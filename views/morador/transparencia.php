<?php
/**
 * @var string  $comp
 * @var array   $resumoTaxas
 * @var array   $totalContas
 * @var Conta[] $contas
 * @var array   $projetos
 * @var array   $folha
 * @var float   $arrecadado
 * @var float   $totalGastos
 * @var float   $totalFolha
 * @var float   $saldo
 */

$mesLabel = \DateTime::createFromFormat('Y-m', $comp)?->format('F \d\e Y') ?? $comp;
$mesLabel = mb_convert_case($mesLabel, MB_CASE_TITLE, 'UTF-8');

function fmt(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-eye"></i> Transparência</h4>

  <!-- Filtro de competência -->
  <form method="GET" class="d-flex align-items-center gap-2">
    <label class="form-label mb-0 text-body-secondary" style="white-space:nowrap; font-size:.85rem;">Competência</label>
    <input type="month" name="comp" class="form-control form-control-sm"
           value="<?= htmlspecialchars($comp) ?>"
           onchange="this.form.submit()">
  </form>
</div>

<!-- ── Balancete ─────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-calculator"></i> Balancete — <?= htmlspecialchars($mesLabel) ?>
  </div>
  <div class="card-body p-0">
    <div class="row g-0">

      <div class="col-6 col-md-3 p-4 border-end border-bottom">
        <div class="text-body-secondary mb-1" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.05em;">Arrecadado</div>
        <div class="fw-bold fs-5" style="color:#198754;"><?= fmt($arrecadado) ?></div>
        <div class="text-body-secondary mt-1" style="font-size:.78rem;">
          <?= (int)($resumoTaxas['total_pagas'] ?? 0) ?>/<?= (int)($resumoTaxas['total'] ?? 0) ?> taxas pagas
        </div>
      </div>

      <div class="col-6 col-md-3 p-4 border-end border-bottom">
        <div class="text-body-secondary mb-1" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.05em;">Contas</div>
        <div class="fw-bold fs-5" style="color:#dc3545;"><?= fmt($totalGastos) ?></div>
        <div class="text-body-secondary mt-1" style="font-size:.78rem;"><?= count($contas) ?> lançamentos</div>
      </div>

      <div class="col-6 col-md-3 p-4 border-end border-bottom">
        <div class="text-body-secondary mb-1" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.05em;">Folha de Pessoal</div>
        <div class="fw-bold fs-5" style="color:#dc3545;"><?= fmt($totalFolha) ?></div>
        <div class="text-body-secondary mt-1" style="font-size:.78rem;"><?= count($folha) ?> funcionário(s)</div>
      </div>

      <div class="col-6 col-md-3 p-4 border-bottom">
        <div class="text-body-secondary mb-1" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.05em;">Saldo estimado</div>
        <div class="fw-bold fs-5" style="color:<?= $saldo >= 0 ? '#198754' : '#dc3545' ?>;">
          <?= fmt($saldo) ?>
        </div>
        <div class="text-body-secondary mt-1" style="font-size:.78rem;">
          <?= $saldo >= 0 ? 'Superávit' : 'Déficit' ?>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ── Contas do mês ─────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-receipt"></i> Despesas do mês
  </div>
  <?php if (empty($contas)): ?>
    <div class="card-body text-body-secondary text-center py-4" style="font-size:.9rem;">
      Nenhuma despesa lançada para esta competência.
    </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:36px;"></th>
          <th>Descrição</th>
          <th class="d-none d-md-table-cell">Fornecedor</th>
          <th class="text-end">Valor</th>
          <th class="text-center d-none d-sm-table-cell">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contas as $c):
          $cat = Conta::$categorias[$c->categoria] ?? ['label' => $c->categoria, 'icon' => 'receipt', 'color' => '#6b7280'];
        ?>
        <tr>
          <td class="ps-3">
            <i class="bi bi-<?= $cat['icon'] ?>" style="color:<?= $cat['color'] ?>; font-size:1.1rem;"></i>
          </td>
          <td>
            <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($c->descricao) ?></div>
            <div class="text-body-secondary d-md-none" style="font-size:.75rem;"><?= htmlspecialchars($c->fornecedor ?? '') ?></div>
          </td>
          <td class="text-body-secondary d-none d-md-table-cell" style="font-size:.875rem;">
            <?= htmlspecialchars($c->fornecedor ?? '—') ?>
          </td>
          <td class="text-end fw-semibold" style="font-size:.9rem;">
            <?= fmt($c->valor) ?>
          </td>
          <td class="text-center d-none d-sm-table-cell">
            <?php if ($c->status === 'pago'): ?>
              <span class="badge rounded-pill badge-pago">Pago</span>
            <?php else: ?>
              <span class="badge rounded-pill <?= $c->estaAtrasada() ? 'badge-vencido' : 'badge-pendente' ?>">
                <?= $c->estaAtrasada() ? 'Atrasado' : 'Pendente' ?>
              </span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="3" class="fw-semibold ps-3">Total</td>
          <td class="text-end fw-bold"><?= fmt($totalGastos) ?></td>
          <td class="d-none d-sm-table-cell"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- ── Folha de pessoal ──────────────────────────────────────────────── -->
<?php if (!empty($folha)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-people"></i> Folha de pessoal
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">Funcionário</th>
          <th class="d-none d-sm-table-cell">Cargo</th>
          <th class="text-end">Salário</th>
          <th class="text-center d-none d-sm-table-cell">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($folha as $f): ?>
        <tr>
          <td class="ps-3 fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($f['nome']) ?></td>
          <td class="text-body-secondary d-none d-sm-table-cell" style="font-size:.875rem;"><?= htmlspecialchars($f['cargo']) ?></td>
          <td class="text-end fw-semibold" style="font-size:.9rem;"><?= fmt((float)$f['valor']) ?></td>
          <td class="text-center d-none d-sm-table-cell">
            <?php if ($f['status'] === 'pago'): ?>
              <span class="badge rounded-pill badge-pago">Pago</span>
            <?php else: ?>
              <span class="badge rounded-pill badge-pendente">Pendente</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="2" class="fw-semibold ps-3">Total</td>
          <td class="text-end fw-bold"><?= fmt($totalFolha) ?></td>
          <td class="d-none d-sm-table-cell"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- ── Projetos em andamento ─────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-kanban"></i> Obras e projetos em andamento
  </div>
  <?php if (empty($projetos)): ?>
    <div class="card-body text-body-secondary text-center py-4" style="font-size:.9rem;">
      Nenhum projeto em andamento no momento.
    </div>
  <?php else: ?>
  <div class="list-group list-group-flush">
    <?php foreach ($projetos as $p): ?>
    <div class="list-group-item px-4 py-3">
      <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($p->nome) ?></div>
          <?php if (!empty($p->descricao)): ?>
            <div class="text-body-secondary mt-1" style="font-size:.85rem;">
              <?= htmlspecialchars(mb_substr($p->descricao, 0, 120)) ?><?= mb_strlen($p->descricao) > 120 ? '…' : '' ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($p->nomePrestadora)): ?>
            <div class="text-body-secondary mt-1" style="font-size:.78rem;">
              <i class="bi bi-building"></i> <?= htmlspecialchars($p->nomePrestadora) ?>
            </div>
          <?php endif; ?>
        </div>
        <?php if (!empty($p->dataInicio)): ?>
          <div class="text-body-secondary text-end" style="font-size:.78rem; white-space:nowrap;">
            Início<br><?= date('d/m/Y', strtotime($p->dataInicio)) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
