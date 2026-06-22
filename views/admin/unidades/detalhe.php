<?php
/** @var Unidade $unidade @var Morador[] $moradores @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Unidade ' . $unidade->identificacao();
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-building"></i> <?= htmlspecialchars($unidade->identificacao()) ?></h4>
  <div class="d-flex gap-2">
    <a href="<?= url("unidades/{$unidade->id}/editar") ?>" class="btn btn-outline-secondary">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('unidades') ?>" class="btn btn-outline-secondary">
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

<div class="row g-4 mb-4">

  <!-- Informações da unidade -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-semibold border-bottom pb-2 mb-3">Dados da unidade</h6>

        <p class="fw-semibold text-body-secondary mb-2" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">Identificação</p>
        <table class="w-100 mb-3" style="font-size:.9rem; border-collapse:collapse;">
          <tr class="border-bottom"><td class="py-2 text-body-secondary" style="width:110px;">Número</td><td class="py-2 fw-semibold"><?= htmlspecialchars($unidade->numero) ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Bloco</td><td class="py-2"><?= htmlspecialchars($unidade->bloco ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Andar</td><td class="py-2"><?= $unidade->andar ? $unidade->andar . 'º' : '—' ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Ocupação</td><td class="py-2">
            <?php if ($unidade->estaAlugada()): ?>
              <span class="badge rounded-pill badge-pendente">Alugado</span>
            <?php else: ?>
              <span class="badge rounded-pill badge-pago">Próprio</span>
            <?php endif; ?>
          </td></tr>
          <tr><td class="py-2 text-body-secondary">Observações</td><td class="py-2"><?= htmlspecialchars($unidade->descricao ?? '—') ?></td></tr>
        </table>

        <p class="fw-semibold text-body-secondary mb-2" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">Proprietário</p>
        <table class="w-100 mb-3" style="font-size:.9rem; border-collapse:collapse;">
          <tr class="border-bottom"><td class="py-2 text-body-secondary" style="width:110px;">Nome</td><td class="py-2"><?= htmlspecialchars($unidade->nomeProprietario ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Telefone</td><td class="py-2">
            <?php if ($unidade->telefoneProprietario): ?>
              <a href="tel:<?= htmlspecialchars($unidade->telefoneProprietario) ?>"><?= htmlspecialchars($unidade->telefoneProprietario) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
          <tr><td class="py-2 text-body-secondary">E-mail</td><td class="py-2">
            <?php if ($unidade->emailProprietario): ?>
              <a href="mailto:<?= htmlspecialchars($unidade->emailProprietario) ?>"><?= htmlspecialchars($unidade->emailProprietario) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
        </table>

        <?php if ($unidade->estaAlugada()): ?>
        <p class="fw-semibold text-body-secondary mb-2" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">Inquilino</p>
        <table class="w-100" style="font-size:.9rem; border-collapse:collapse;">
          <tr class="border-bottom"><td class="py-2 text-body-secondary" style="width:110px;">Nome</td><td class="py-2"><?= htmlspecialchars($unidade->nomeInquilino ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Telefone</td><td class="py-2">
            <?php if ($unidade->telefoneInquilino): ?>
              <a href="tel:<?= htmlspecialchars($unidade->telefoneInquilino) ?>"><?= htmlspecialchars($unidade->telefoneInquilino) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
          <tr><td class="py-2 text-body-secondary">E-mail</td><td class="py-2">
            <?php if ($unidade->emailInquilino): ?>
              <a href="mailto:<?= htmlspecialchars($unidade->emailInquilino) ?>"><?= htmlspecialchars($unidade->emailInquilino) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Vincular novo morador -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bi bi-person-plus"></i> Vincular morador</h6>
        <form action="<?= url("unidades/{$unidade->id}/vincular-morador") ?>" method="POST">
          <div class="mb-3">
            <label for="campo-nome-morador" class="form-label">Nome *</label>
            <input type="text" id="campo-nome-morador" name="nome" class="form-control" required placeholder="Nome completo">
          </div>
          <div class="mb-3">
            <label for="campo-email-morador" class="form-label">E-mail *</label>
            <input type="email" id="campo-email-morador" name="email" class="form-control" required placeholder="morador@email.com">
            <div class="form-text">Se o e-mail já existir, o usuário é vinculado sem criar novo.</div>
          </div>
          <div class="mb-3">
            <label for="campo-senha-morador" class="form-label">Senha (apenas para novos usuários)</label>
            <input type="password" id="campo-senha-morador" name="senha" class="form-control" placeholder="Senha de acesso">
          </div>
          <div class="mb-3">
            <label for="campo-entrada" class="form-label">Data de entrada</label>
            <?php $dataHoje = date('Y-m-d'); ?>
            <input type="date" id="campo-entrada" name="data_entrada" class="form-control" value="<?= $dataHoje ?>">
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="campo-responsavel" name="responsavel" value="1">
            <label class="form-check-label" for="campo-responsavel">Responsável financeiro</label>
          </div>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus"></i> Vincular
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

<!-- Moradores vinculados -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-people"></i> Moradores vinculados
  </div>
  <?php if (empty($moradores)): ?>
    <div class="card-body text-body-secondary">Nenhum morador vinculado.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Nome</th><th>E-mail</th><th>Entrada</th><th>Responsável</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($moradores as $morador): ?>
          <tr>
            <td><?= htmlspecialchars($morador->nomeUsuario ?? '—') ?></td>
            <td><?= htmlspecialchars($morador->emailUsuario ?? '—') ?></td>
            <td><?= dataBR($morador->dataEntrada) ?></td>
            <td>
              <?php if ($morador->responsavel): ?>
                <span class="badge rounded-pill badge-pago">Responsável</span>
              <?php else: ?>
                <span class="text-body-tertiary">—</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= url("unidades/{$unidade->id}/desvincular-morador/{$morador->id}") ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Desvincular este morador?')">
                <i class="bi bi-x-lg"></i> Desvincular
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
