<?php
/** @var Funcionario[] $funcionarios @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Funcionários';
require_once RAIZ . '/views/layouts/cabecalho.php';

$ativos   = array_filter($funcionarios, fn($f) => $f->ativo);
$inativos = array_filter($funcionarios, fn($f) => !$f->ativo);
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-person-badge"></i> Funcionários</h4>
    <span class="text-body-secondary" style="font-size:.82rem;">
      <?= count($ativos) ?> ativo<?= count($ativos) !== 1 ? 's' : '' ?>
      <?php if ($inativos): ?> · <?= count($inativos) ?> inativo<?= count($inativos) !== 1 ? 's' : '' ?><?php endif; ?>
    </span>
  </div>
  <a href="<?= url('funcionarios/novo') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Novo funcionário
  </a>
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

<?php if (empty($funcionarios)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-body-secondary">
      <i class="bi bi-person-badge fs-1 opacity-25 d-block mb-3"></i>
      Nenhum funcionário cadastrado.
      <div class="mt-3">
        <a href="<?= url('funcionarios/novo') ?>" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg"></i> Cadastrar funcionário
        </a>
      </div>
    </div>
  </div>
<?php else: ?>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Nome</th>
          <th>Cargo</th>
          <th class="d-none d-md-table-cell">Departamento</th>
          <th class="d-none d-md-table-cell">Contato</th>
          <th class="d-none d-lg-table-cell">Admissão</th>
          <th>Situação</th>
          <th style="width:80px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($funcionarios as $f): ?>
        <tr class="<?= !$f->ativo ? 'text-body-tertiary' : '' ?>">
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                          <?= $f->ativo ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-secondary' ?>"
                   style="width:34px;height:34px;font-size:.78rem;font-weight:700;">
                <?= mb_strtoupper(mb_substr($f->nome, 0, 1)) ?>
              </div>
              <div>
                <div class="fw-semibold"><?= htmlspecialchars($f->nome) ?></div>
                <?php if ($f->cpf): ?>
                  <div class="text-body-secondary" style="font-size:.75rem;"><?= htmlspecialchars($f->cpf) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($f->cargo) ?></td>
          <td class="d-none d-md-table-cell text-body-secondary">
            <?= htmlspecialchars($f->departamento ?? '—') ?>
          </td>
          <td class="d-none d-md-table-cell" style="font-size:.82rem;">
            <?php if ($f->telefone): ?>
              <div><i class="bi bi-telephone text-body-secondary me-1"></i><?= htmlspecialchars($f->telefone) ?></div>
            <?php endif; ?>
            <?php if ($f->email): ?>
              <div><i class="bi bi-envelope text-body-secondary me-1"></i>
                <a href="mailto:<?= htmlspecialchars($f->email) ?>" class="text-decoration-none">
                  <?= htmlspecialchars($f->email) ?>
                </a>
              </div>
            <?php endif; ?>
            <?php if (!$f->telefone && !$f->email): ?>
              <span class="text-body-secondary">—</span>
            <?php endif; ?>
          </td>
          <td class="d-none d-lg-table-cell" style="font-size:.82rem;">
            <?= $f->dataAdmissao ? date('d/m/Y', strtotime($f->dataAdmissao)) : '—' ?>
          </td>
          <td>
            <?php if ($f->ativo): ?>
              <span class="badge bg-success-subtle text-success-emphasis">Ativo</span>
            <?php else: ?>
              <span class="badge bg-secondary-subtle text-secondary-emphasis">Inativo</span>
              <?php if ($f->dataDemissao): ?>
                <div class="text-body-secondary" style="font-size:.7rem;">
                  desde <?= date('d/m/Y', strtotime($f->dataDemissao)) ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= url("funcionarios/{$f->id}/editar") ?>"
                 class="btn btn-outline-secondary btn-sm py-0 px-2" title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="<?= url("funcionarios/{$f->id}/excluir") ?>"
                 onclick="return confirm('Remover <?= htmlspecialchars(addslashes($f->nome)) ?>?')"
                 class="btn btn-outline-danger btn-sm py-0 px-2" title="Remover">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
