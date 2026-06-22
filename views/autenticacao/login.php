<?php /** @var string|null $erroLogin */ ?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <script>(function(){var t=localStorage.getItem('condux-tema')||'light';document.documentElement.setAttribute('data-bs-theme',t);}());</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar — Condux</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/condux.css') ?>">
</head>
<body>

<div class="condux-login-bg">
  <div class="card shadow-lg border-0 p-4" style="width:100%; max-width:380px; border-radius:14px;">

    <div class="text-center mb-4">
      <div class="condux-login-logo">Con<span>dux</span></div>
      <p class="text-body-secondary mb-0" style="font-size:.875rem;">Sistema de Gestão de Condomínio</p>
    </div>

    <?php if (!empty($erroLogin)): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <span><?= htmlspecialchars($erroLogin) ?></span>
      </div>
    <?php endif; ?>

    <form action="<?= url('login') ?>" method="POST" novalidate>
      <div class="mb-3">
        <label for="campo-email" class="form-label">E-mail</label>
        <input type="email" id="campo-email" name="email" class="form-control"
               placeholder="seu@email.com" required autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-4">
        <label for="campo-senha" class="form-label">Senha</label>
        <input type="password" id="campo-senha" name="senha" class="form-control"
               placeholder="••••••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right"></i> Entrar
      </button>
    </form>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
