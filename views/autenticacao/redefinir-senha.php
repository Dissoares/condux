<?php $tituloPagina = 'Redefinir senha'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <script>(function(){ var t=localStorage.getItem('condux-tema')||'light'; document.documentElement.setAttribute('data-bs-theme',t); }());</script>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Redefinir senha</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-body-secondary d-flex align-items-center justify-content-center min-vh-100">
<div class="card border-0 shadow-sm" style="width:100%;max-width:380px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-1">Redefinir senha</h5>
    <p class="text-body-secondary mb-4" style="font-size:.88rem;">Escolha uma nova senha para sua conta.</p>

    <?php if (!empty($mensagem)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <?php if (!empty($erroMensagem)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erroMensagem) ?></div>
    <?php endif; ?>

    <form action="<?= url('redefinir-senha/salvar') ?>" method="POST">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold">Nova senha</label>
        <input type="password" name="senha_nova" class="form-control" required minlength="6" autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Confirmar nova senha</label>
        <input type="password" name="senha_conf" class="form-control" required minlength="6">
      </div>
      <button type="submit" class="btn btn-primary w-100">Redefinir senha</button>
    </form>
  </div>
</div>
</body></html>
