<?php
/** @var Prestadora $prestadora @var Projeto[] $projetos @var Vistoria[] $vistorias @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = $prestadora->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-semibold mb-1"><?= htmlspecialchars($prestadora->nome) ?></h4>
    <?php if (!$prestadora->ativo): ?>
      <span class="badge bg-secondary-subtle text-secondary-emphasis">Inativa</span>
    <?php else: ?>
      <span class="badge bg-success-subtle text-success-emphasis">Ativa</span>
    <?php endif; ?>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url("prestadoras/{$prestadora->id}/editar") ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('prestadoras') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<!-- ── Dados da empresa ── -->
<div class="row g-4 mb-4">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
        <i class="bi bi-building text-success"></i> Dados da empresa
      </div>
      <div class="card-body p-0">
        <dl class="mb-0" style="font-size:.875rem;">
          <?php
          $linhas = [
            ['CNPJ',          $prestadora->cnpj     ? htmlspecialchars($prestadora->cnpj)     : '<span class="text-body-secondary">—</span>'],
            ['Contato',       $prestadora->contato  ? htmlspecialchars($prestadora->contato)  : '<span class="text-body-secondary">—</span>'],
            ['Telefone',      $prestadora->telefone ? '<a href="tel:' . htmlspecialchars($prestadora->telefone) . '" class="text-decoration-none">' . htmlspecialchars($prestadora->telefone) . '</a>' : '<span class="text-body-secondary">—</span>'],
            ['E-mail',        $prestadora->email    ? '<a href="mailto:' . htmlspecialchars($prestadora->email) . '" class="text-decoration-none">' . htmlspecialchars($prestadora->email) . '</a>'       : '<span class="text-body-secondary">—</span>'],
            ['Cadastrada em', $prestadora->criadoEm ? date('d/m/Y', strtotime($prestadora->criadoEm)) : '—'],
          ];
          ?>
          <?php foreach ($linhas as [$label, $valor]): ?>
          <div class="d-flex border-bottom px-3 py-2">
            <dt class="fw-normal text-body-secondary me-3" style="min-width:110px;"><?= $label ?></dt>
            <dd class="mb-0 fw-semibold"><?= $valor ?></dd>
          </div>
          <?php endforeach; ?>
        </dl>
      </div>
    </div>
  </div>

  <!-- ── Resumo ── -->
  <div class="col-lg-7">
    <div class="row g-3 h-100">
      <div class="col-sm-6">
        <div class="card border-0 shadow-sm text-center py-4">
          <div class="fs-1 fw-bold text-primary"><?= count($projetos) ?></div>
          <div class="text-body-secondary" style="font-size:.875rem;">
            <?= count($projetos) === 1 ? 'Projeto' : 'Projetos' ?>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="card border-0 shadow-sm text-center py-4">
          <div class="fs-1 fw-bold text-warning"><?= count($vistorias) ?></div>
          <div class="text-body-secondary" style="font-size:.875rem;">
            <?= count($vistorias) === 1 ? 'Vistoria' : 'Vistorias' ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Projetos vinculados ── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
    <i class="bi bi-kanban text-primary"></i> Projetos
    <span class="badge bg-primary-subtle text-primary-emphasis ms-auto"><?= count($projetos) ?></span>
  </div>
  <?php if (empty($projetos)): ?>
    <div class="card-body text-center py-4 text-body-secondary" style="font-size:.875rem;">
      <i class="bi bi-kanban d-block mb-2 opacity-25" style="font-size:1.8rem;"></i>
      Nenhum projeto vinculado a esta empresa.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
        <thead class="table-light">
          <tr>
            <th>Nome</th>
            <th>Responsável</th>
            <th>Status</th>
            <th>Valor estimado</th>
            <th>Início</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($projetos as $p): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($p->nome) ?></td>
            <td class="text-body-secondary"><?= htmlspecialchars($p->nomeResponsavel ?? '—') ?></td>
            <td>
              <span class="badge rounded-pill badge-<?= $p->status ?>">
                <?= htmlspecialchars($p->rotuloStatus()) ?>
              </span>
            </td>
            <td><?= $p->valorEstimado !== null ? 'R$ ' . number_format($p->valorEstimado, 2, ',', '.') : '—' ?></td>
            <td><?= $p->dataInicio ? date('d/m/Y', strtotime($p->dataInicio)) : '—' ?></td>
            <td class="text-end">
              <a href="<?= url("projetos/{$p->id}") ?>" class="btn btn-outline-secondary btn-sm">
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

<!-- ── Vistorias vinculadas ── -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
    <i class="bi bi-clipboard-check text-warning"></i> Vistorias
    <span class="badge bg-warning-subtle text-warning-emphasis ms-auto"><?= count($vistorias) ?></span>
  </div>
  <?php if (empty($vistorias)): ?>
    <div class="card-body text-center py-4 text-body-secondary" style="font-size:.875rem;">
      <i class="bi bi-clipboard-check d-block mb-2 opacity-25" style="font-size:1.8rem;"></i>
      Nenhuma vistoria vinculada a esta empresa.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
        <thead class="table-light">
          <tr>
            <th>Data</th>
            <th>Tipo</th>
            <th>Status</th>
            <th>Responsável</th>
            <th>Unidade</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vistorias as $v): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($v->dataVistoria)) ?></td>
            <td><?= htmlspecialchars(Vistoria::$tiposRotulo[$v->tipo] ?? $v->tipo) ?></td>
            <td>
              <?php $statusCor = match($v->status) {
                'realizada' => 'success',
                'cancelada' => 'danger',
                default     => 'warning',
              }; ?>
              <span class="badge bg-<?= $statusCor ?>-subtle text-<?= $statusCor ?>-emphasis">
                <?= ucfirst($v->status) ?>
              </span>
            </td>
            <td class="text-body-secondary"><?= htmlspecialchars($v->nomeResponsavel ?? '—') ?></td>
            <td class="text-body-secondary"><?= htmlspecialchars($v->identificacaoUnidade ?? '—') ?></td>
            <td class="text-end">
              <a href="<?= url("vistorias/{$v->id}") ?>" class="btn btn-outline-secondary btn-sm">
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
