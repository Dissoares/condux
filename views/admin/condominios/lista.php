<?php
/** @var array[] $condominios @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Condôminos';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-people"></i> Condôminos</h4>
  <a href="<?= url('condominios/novo') ?>" class="btn btn-primary">
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

<div class="card border-0 shadow-sm">
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
        <?php if (empty($condominios)): ?>
          <tr>
            <td colspan="6" class="text-center text-body-secondary py-4">Nenhum condômino cadastrado.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($condominios as $c): ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($c['nome']) ?></div>
              <?php if (!empty($c['cpf'])): ?>
                <div class="text-body-secondary" style="font-size:.75rem;">CPF: <?= htmlspecialchars($c['cpf']) ?></div>
              <?php endif; ?>
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
              <?php
                $papeis = [];
                if ($c['eh_proprietario']) $papeis[] = '<span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><i class="bi bi-person-badge me-1"></i>Proprietário</span>';
                if ($c['eh_inquilino'])    $papeis[] = '<span class="badge bg-warning-subtle text-warning-emphasis rounded-pill"><i class="bi bi-key me-1"></i>Inquilino</span>';
                if ($c['responsavel'] && !$c['eh_inquilino']) $papeis[] = '<span class="badge badge-pago rounded-pill">Responsável</span>';
                if ($c['morador_id'] && !$c['responsavel'] && !$c['eh_inquilino'] && !$c['eh_proprietario']) $papeis[] = '<span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill"><i class="bi bi-person me-1"></i>Morador</span>';
              ?>
              <?php if ($papeis): ?>
                <div class="d-flex flex-wrap gap-1"><?= implode('', $papeis) ?></div>
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
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
