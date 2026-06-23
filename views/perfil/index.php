<?php
/** @var Usuario $usuario @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Meu Perfil';
require_once RAIZ . '/views/layouts/cabecalho.php';
$inicialGrande = strtoupper(mb_substr($usuario->nome, 0, 1));
$fotoUrl = $usuario->foto ? url('uploads/' . $usuario->foto) . '?v=' . time() : null;
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

<form action="<?= url('perfil/salvar') ?>" method="POST" enctype="multipart/form-data">

  <!-- Foto + dados básicos -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold py-3">
      <i class="bi bi-person"></i> Dados pessoais
    </div>
    <div class="card-body p-4">

      <!-- Foto de perfil -->
      <div class="d-flex align-items-start gap-4 mb-4">

        <!-- Avatar / foto atual -->
        <div class="flex-shrink-0 position-relative">
          <div id="foto-preview-wrap"
               class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center"
               style="width:80px; height:80px; background:var(--condux-primaria);">
            <?php if ($fotoUrl): ?>
              <img id="foto-preview-img" src="<?= $fotoUrl ?>" alt="Foto"
                   style="width:80px; height:80px; object-fit:cover;">
            <?php else: ?>
              <span id="foto-inicial" class="fw-bold text-white" style="font-size:2rem;"><?= $inicialGrande ?></span>
            <?php endif; ?>
          </div>
          <!-- Botão câmera sobre o avatar -->
          <label for="foto-input"
                 class="position-absolute d-flex align-items-center justify-content-center rounded-circle
                        border border-2 border-white"
                 style="bottom:0; right:0; width:26px; height:26px; cursor:pointer;
                        background:var(--condux-acento); color:#fff; font-size:.7rem;"
                 title="Alterar foto">
            <i class="bi bi-camera-fill"></i>
          </label>
          <input type="file" name="foto" id="foto-input" accept="image/jpeg,image/png,image/webp"
                 class="d-none">
        </div>

        <!-- Info + opções de foto -->
        <div class="flex-grow-1">
          <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($usuario->nome) ?></div>
          <div class="text-body-secondary mb-2" style="font-size:.85rem; text-transform:capitalize;">
            <?= htmlspecialchars($usuario->perfil) ?>
            · membro desde <?= $usuario->criadoEm ? date('M/Y', strtotime($usuario->criadoEm)) : '—' ?>
          </div>
          <div class="form-text">JPG, PNG ou WebP · máx. 2 MB.</div>
          <?php if ($fotoUrl): ?>
            <label class="d-flex align-items-center gap-2 mt-2" style="cursor:pointer; font-size:.82rem;">
              <input type="checkbox" name="remover_foto" class="form-check-input mt-0">
              <span class="text-danger">Remover foto atual</span>
            </label>
          <?php endif; ?>
        </div>
      </div>

      <hr class="my-3">

      <!-- Campos de texto -->
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
      <span class="text-body-secondary fw-normal ms-2" style="font-size:.8rem;">opcional</span>
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

<script>
document.getElementById('foto-input')?.addEventListener('change', function () {
  var f = this.files[0];
  if (!f) return;
  var url = URL.createObjectURL(f);
  var wrap = document.getElementById('foto-preview-wrap');
  wrap.innerHTML = '<img src="' + url + '" style="width:80px;height:80px;object-fit:cover;">';
});
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
