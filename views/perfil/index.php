<?php
/** @var Usuario $usuario @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Meu Perfil';
require_once RAIZ . '/views/layouts/cabecalho.php';
$inicialGrande = strtoupper(mb_substr($usuario->nome, 0, 1));
?>

<div class="d-flex align-items-center gap-3 mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-person-circle"></i> Meu Perfil</h4>
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

<!-- Avatar + nome -->
<div class="d-flex align-items-center gap-3 mb-4">
  <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
       style="width:64px; height:64px; font-size:1.6rem;
              background:var(--condux-primaria); color:#fff; flex-shrink:0;">
    <?= $inicialGrande ?>
  </div>
  <div>
    <div class="fw-bold fs-5"><?= htmlspecialchars($usuario->nome) ?></div>
    <div class="text-body-secondary" style="font-size:.85rem; text-transform:capitalize;">
      <?= htmlspecialchars($usuario->perfil) ?>
      · membro desde <?= $usuario->criadoEm ? date('M/Y', strtotime($usuario->criadoEm)) : '—' ?>
    </div>
  </div>
</div>

<form action="<?= url('perfil/salvar') ?>" method="POST">

  <!-- Dados pessoais -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold py-3">
      <i class="bi bi-person"></i> Dados pessoais
    </div>
    <div class="card-body p-4">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nome completo *</label>
          <input type="text" name="nome" class="form-control" required maxlength="120"
                 value="<?= htmlspecialchars($usuario->nome) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">E-mail *</label>
          <input type="email" name="email" class="form-control" required maxlength="150"
                 value="<?= htmlspecialchars($usuario->email) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Telefone</label>
          <input type="tel" name="telefone" class="form-control" maxlength="20"
                 placeholder="(11) 9 0000-0000"
                 value="<?= htmlspecialchars($usuario->telefone ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Perfil</label>
          <input type="text" class="form-control" readonly
                 value="<?= htmlspecialchars(ucfirst($usuario->perfil)) ?>"
                 style="background:var(--bs-secondary-bg); cursor:default;">
          <div class="form-text">Não pode ser alterado pelo próprio usuário.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Alterar senha -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold py-3">
      <i class="bi bi-lock"></i> Alterar senha
      <span class="text-body-secondary fw-normal ms-2" style="font-size:.8rem;">opcional — preencha apenas se quiser trocar</span>
    </div>
    <div class="card-body p-4">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Senha atual</label>
          <input type="password" name="senha_atual" class="form-control"
                 autocomplete="current-password" placeholder="••••••••">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Nova senha</label>
          <input type="password" name="senha_nova" class="form-control"
                 autocomplete="new-password" placeholder="mín. 6 caracteres">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Confirmar nova senha</label>
          <input type="password" name="senha_conf" class="form-control"
                 autocomplete="new-password" placeholder="repita a nova senha">
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-floppy"></i> Salvar alterações
    </button>
    <a href="javascript:history.back()" class="btn btn-outline-secondary">Voltar</a>
  </div>

</form>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
