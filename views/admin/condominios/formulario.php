<?php
/** @var Usuario|null $usuario @var int $retornarUnidade @var string|null $mensagem @var string|null $erroMensagem */
$editando     = $usuario !== null;
$tituloPagina = $editando ? 'Editar condômino' : 'Novo condômino';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0">
    <i class="bi bi-person-<?= $editando ? 'gear' : 'plus' ?>"></i>
    <?= $editando ? 'Editar condômino' : 'Novo condômino' ?>
  </h4>
  <a href="<?= $retornarUnidade ? url("unidades/{$retornarUnidade}") : url('condominios') ?>"
     class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:540px;">
  <div class="card-body">
    <form action="<?= url('condominios/salvar') ?>" method="POST">

      <?php if ($editando): ?>
        <?php $uid = (int)$usuario->id; ?>
        <input type="hidden" name="id" value="<?= $uid ?>">
      <?php endif; ?>
      <input type="hidden" name="retornar_unidade" value="<?= $retornarUnidade ?>">

      <div class="mb-3">
        <label for="campo-nome" class="form-label">Nome completo *</label>
        <input type="text" id="campo-nome" name="nome" class="form-control" required
               value="<?= htmlspecialchars($usuario->nome ?? '') ?>" placeholder="João da Silva">
      </div>

      <div class="mb-3">
        <label for="campo-email" class="form-label">E-mail *</label>
        <input type="email" id="campo-email" name="email" class="form-control" required
               value="<?= htmlspecialchars($usuario->email ?? '') ?>" placeholder="joao@email.com">
      </div>

      <div class="mb-3">
        <label for="campo-senha" class="form-label">
          Senha <?= $editando ? '<span class="text-body-secondary fw-normal">(deixe em branco para manter a atual)</span>' : '*' ?>
        </label>
        <input type="password" id="campo-senha" name="senha" class="form-control"
               <?= !$editando ? 'required' : '' ?> placeholder="Mínimo 6 caracteres" autocomplete="new-password">
      </div>

      <?php if ($editando): ?>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="campo-ativo" name="ativo" value="1"
               <?= $usuario->ativo ? 'checked' : '' ?>>
        <label class="form-check-label" for="campo-ativo">Conta ativa</label>
      </div>
      <?php endif; ?>

      <?php if ($retornarUnidade > 0 && !$editando): ?>
        <div class="alert alert-info d-flex align-items-center gap-2 py-2" style="font-size:.875rem;">
          <i class="bi bi-info-circle-fill flex-shrink-0"></i>
          Após cadastrar, você retornará para a unidade para fazer a vinculação.
        </div>
      <?php endif; ?>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg"></i> <?= $editando ? 'Salvar alterações' : 'Cadastrar' ?>
        </button>
        <a href="<?= $retornarUnidade ? url("unidades/{$retornarUnidade}") : url('condominios') ?>"
           class="btn btn-outline-secondary">Cancelar</a>
      </div>

    </form>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
