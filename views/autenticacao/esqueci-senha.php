<?php $tituloPagina = 'Recuperar senha'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <script>(function(){ var t=localStorage.getItem('condux-tema')||'light'; document.documentElement.setAttribute('data-bs-theme',t); }());</script>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Recuperar senha</title>
  <link rel="icon" type="image/png" href="<?= url('icons/icon-192.png') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-body-secondary d-flex align-items-center justify-content-center min-vh-100">
<div class="card border-0 shadow-sm" style="width:100%;max-width:380px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-1">Recuperar senha</h5>
    <p class="text-body-secondary mb-4" style="font-size:.88rem;">Informe o seu e-mail cadastrado e enviaremos um link para redefinição.</p>

    <?php if (!empty($mensagem)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <?php if (!empty($erroMensagem)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erroMensagem) ?></div>
    <?php endif; ?>

    <form action="<?= url('esqueci-senha/enviar') ?>" method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">E-mail</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <button type="submit" class="btn btn-primary w-100">Enviar link</button>
    </form>
    <div class="text-center mt-3">
      <a href="<?= url('login') ?>" class="text-body-secondary" style="font-size:.85rem;">
        <i class="bi bi-arrow-left"></i> Voltar ao login
      </a>
    </div>
  </div>
</div>
</body></html>
