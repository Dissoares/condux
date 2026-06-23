<?php
/** @var array[] $condominios @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Condôminos';
require_once RAIZ . '/views/layouts/cabecalho.php';

function papeisBadges(array $c): string {
    $b = [];
    if ($c['eh_proprietario']) $b[] = '<span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><i class="bi bi-person-badge me-1"></i>Proprietário</span>';
    if ($c['eh_inquilino'])    $b[] = '<span class="badge bg-warning-subtle text-warning-emphasis rounded-pill"><i class="bi bi-key me-1"></i>Inquilino</span>';
    if ($c['responsavel'] && !$c['eh_inquilino']) $b[] = '<span class="badge badge-pago rounded-pill">Responsável</span>';
    if ($c['morador_id'] && !$c['responsavel'] && !$c['eh_inquilino'] && !$c['eh_proprietario'])
        $b[] = '<span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill"><i class="bi bi-person me-1"></i>Morador</span>';
    return implode('', $b);
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-people"></i> Condôminos</h4>
  <a href="<?= url('condominios/novo') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Novo condômino
  </a>
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

<?php if (empty($condominios)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-people fs-1 opacity-25 d-block mb-3"></i>
    Nenhum condômino cadastrado.
    <div class="mt-3">
      <a href="<?= url('condominios/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Adicionar
      </a>
    </div>
  </div>
</div>

<?php else: ?>

<!-- ── Mobile: cards ──────────────────────────────────── -->
<div class="d-md-none card border-0 shadow-sm overflow-hidden">
  <?php foreach ($condominios as $i => $c): ?>
  <div class="px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?>">
    <div class="d-flex align-items-center gap-3">

      <!-- Avatar inicial -->
      <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold"
           style="width:42px; height:42px; background:var(--condux-primaria); color:#fff; font-size:1rem;">
        <?= strtoupper(mb_substr($c['nome'], 0, 1)) ?>
      </div>

      <!-- Info principal -->
      <div class="flex-grow-1 min-width-0">
        <div class="fw-semibold" style="font-size:.95rem;"><?= htmlspecialchars($c['nome']) ?></div>
        <div class="text-body-secondary" style="font-size:.78rem;">
          <?php if ($c['identificacao_unidade']): ?>
            <i class="bi bi-building" style="font-size:.7rem;"></i>
            <?= htmlspecialchars($c['identificacao_unidade']) ?>
          <?php else: ?>
            <span class="text-body-tertiary">Sem unidade</span>
          <?php endif; ?>
        </div>
        <?php $badges = papeisBadges($c); if ($badges): ?>
          <div class="d-flex flex-wrap gap-1 mt-1"><?= $badges ?></div>
        <?php endif; ?>
      </div>

      <!-- Ações -->
      <div class="flex-shrink-0 d-flex gap-1">
        <a href="<?= url('condominios/' . $c['id'] . '/editar') ?>"
           class="btn btn-outline-secondary btn-sm py-1 px-2">
          <i class="bi bi-pencil"></i>
        </a>
        <a href="<?= url('condominios/' . $c['id'] . '/excluir') ?>"
           onclick="return confirm('Remover este condômino?')"
           class="btn btn-outline-danger btn-sm py-1 px-2">
          <i class="bi bi-trash"></i>
        </a>
      </div>
    </div>

    <!-- Contatos em linha discreta -->
    <?php if (!empty($c['email']) || !empty($c['telefone'])): ?>
    <div class="mt-2 d-flex gap-3" style="font-size:.78rem; color:var(--bs-body-secondary);">
      <?php if (!empty($c['email'])): ?>
        <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="text-decoration-none text-body-secondary">
          <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($c['email']) ?>
        </a>
      <?php endif; ?>
      <?php if (!empty($c['telefone'])): ?>
        <a href="tel:<?= htmlspecialchars($c['telefone']) ?>" class="text-decoration-none text-body-secondary">
          <i class="bi bi-phone me-1"></i><?= htmlspecialchars($c['telefone']) ?>
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
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
          <th>Telefone</th>
          <th>E-mail</th>
          <th>Unidade</th>
          <th>Papel</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($condominios as $c): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <?php
                $foto   = $c['foto'] ?? null;
                $inicialAvatar = strtoupper(mb_substr($c['nome'], 0, 1));
              ?>
              <?php if ($foto): ?>
                <img src="<?= url('uploads/' . htmlspecialchars($foto)) ?>"
                     alt="<?= htmlspecialchars($inicialAvatar) ?>"
                     class="rounded-circle flex-shrink-0"
                     style="width:38px;height:38px;object-fit:cover;">
              <?php else: ?>
                <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold"
                     style="width:38px;height:38px;background:var(--condux-primaria,#1a3c5e);color:#fff;font-size:.95rem;">
                  <?= $inicialAvatar ?>
                </div>
              <?php endif; ?>
              <div>
                <div class="fw-semibold"><?= htmlspecialchars($c['nome']) ?></div>
                <?php if (!empty($c['cpf'])): ?>
                  <div class="text-body-secondary" style="font-size:.75rem;">CPF: <?= htmlspecialchars($c['cpf']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td class="text-body-secondary" style="font-size:.875rem;">
            <?php if (!empty($c['telefone'])): ?>
              <a href="tel:<?= htmlspecialchars($c['telefone']) ?>" class="text-decoration-none">
                <?= htmlspecialchars($c['telefone']) ?>
              </a>
            <?php else: ?>
              <span class="text-body-tertiary">—</span>
            <?php endif; ?>
          </td>
          <td class="text-body-secondary" style="font-size:.875rem;"><?= htmlspecialchars($c['email']) ?></td>
          <td>
            <?php if ($c['identificacao_unidade']): ?>
              <a href="<?= url('unidades/' . $c['unidade_id_vinculo']) ?>" class="text-decoration-none">
                <?= htmlspecialchars($c['identificacao_unidade']) ?>
              </a>
            <?php else: ?>
              <span class="text-body-tertiary">Sem unidade</span>
            <?php endif; ?>
          </td>
          <td>
            <?php $badges = papeisBadges($c); ?>
            <?php if ($badges): ?>
              <div class="d-flex flex-wrap gap-1"><?= $badges ?></div>
            <?php else: ?>
              <span class="text-body-tertiary">—</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <a href="<?= url('condominios/' . $c['id'] . '/editar') ?>"
               class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-pencil"></i>
            </a>
            <a href="<?= url('condominios/' . $c['id'] . '/excluir') ?>"
               class="btn btn-outline-danger btn-sm"
               onclick="return confirm('Remover este condômino?')">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
