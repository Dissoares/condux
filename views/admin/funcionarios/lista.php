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

<!-- ── Mobile: cards ──────────────────────────────────── -->
<div class="d-md-none card border-0 shadow-sm overflow-hidden">
  <?php foreach ($funcionarios as $i => $f): ?>
  <div class="px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?> <?= !$f->ativo ? 'opacity-60' : '' ?>">
    <div class="d-flex align-items-center gap-3">

      <!-- Avatar -->
      <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold"
           style="width:44px; height:44px; font-size:.95rem;
                  background:<?= $f->ativo ? 'var(--condux-primaria)' : '#6b7280' ?>;
                  color:#fff;">
        <?= mb_strtoupper(mb_substr($f->nome, 0, 1)) ?>
      </div>

      <!-- Info -->
      <div class="flex-grow-1 min-width-0">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <span class="fw-semibold" style="font-size:.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <?= htmlspecialchars($f->nome) ?>
          </span>
          <?php if ($f->ativo): ?>
            <span class="badge bg-success-subtle text-success-emphasis flex-shrink-0" style="font-size:.65rem;">Ativo</span>
          <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary-emphasis flex-shrink-0" style="font-size:.65rem;">Inativo</span>
          <?php endif; ?>
        </div>
        <div class="text-body-secondary" style="font-size:.8rem;">
          <?= htmlspecialchars($f->cargo) ?>
          <?php if ($f->departamento): ?> · <?= htmlspecialchars($f->departamento) ?><?php endif; ?>
        </div>
        <?php if ($f->telefone || $f->email): ?>
        <div class="mt-1 d-flex flex-wrap gap-2" style="font-size:.75rem; color:var(--bs-body-secondary);">
          <?php if ($f->telefone): ?>
            <a href="tel:<?= htmlspecialchars($f->telefone) ?>" class="text-decoration-none text-body-secondary">
              <i class="bi bi-phone me-1"></i><?= htmlspecialchars($f->telefone) ?>
            </a>
          <?php endif; ?>
          <?php if ($f->email): ?>
            <a href="mailto:<?= htmlspecialchars($f->email) ?>" class="text-decoration-none text-body-secondary">
              <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($f->email) ?>
            </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Ações -->
    <div class="d-flex gap-2 mt-2 justify-content-end">
      <a href="<?= url("funcionarios/{$f->id}") ?>"
         class="btn btn-outline-secondary btn-sm py-1 px-3">
        <i class="bi bi-eye me-1"></i> Ver
      </a>
      <a href="<?= url("funcionarios/{$f->id}/editar") ?>"
         class="btn btn-outline-secondary btn-sm py-1 px-2">
        <i class="bi bi-pencil"></i>
      </a>
      <a href="<?= url("funcionarios/{$f->id}/excluir") ?>"
         onclick="return confirm('Remover <?= htmlspecialchars(addslashes($f->nome)) ?>?')"
         class="btn btn-outline-danger btn-sm py-1 px-2">
        <i class="bi bi-trash"></i>
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Desktop: tabela ────────────────────────────────── -->
<div class="d-none d-md-block card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Nome</th>
          <th>Cargo</th>
          <th>Departamento</th>
          <th>Contato</th>
          <th class="d-none d-lg-table-cell">Admissão</th>
          <th>Situação</th>
          <th style="width:100px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($funcionarios as $f): ?>
        <tr class="<?= !$f->ativo ? 'text-body-tertiary' : '' ?>">
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                          <?= $f->ativo ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-secondary' ?>"
                   style="width:34px; height:34px; font-size:.78rem; font-weight:700;">
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
          <td class="text-body-secondary"><?= htmlspecialchars($f->departamento ?? '—') ?></td>
          <td style="font-size:.82rem;">
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
              <a href="<?= url("funcionarios/{$f->id}") ?>"
                 class="btn btn-outline-secondary btn-sm py-0 px-2" title="Ver detalhe">
                <i class="bi bi-eye"></i>
              </a>
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
