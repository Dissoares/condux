<?php /** @var string|null $erroLogin */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar — Condux</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/condux.css') ?>">
</head>
<body>

<div class="tela-login">
  <div class="card-login">

    <div class="marca-login">
      <h1>Con<span>dux</span></h1>
      <p>Sistema de Gestão de Condomínio</p>
    </div>

    <?php if (!empty($erroLogin)): ?>
      <div class="alerta-flash erro">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= htmlspecialchars($erroLogin) ?>
      </div>
    <?php endif; ?>

    <form action="<?= url('login') ?>" method="POST" novalidate>
      <div class="campo-formulario" style="margin-bottom:1rem;">
        <label for="campo-email">E-mail</label>
        <input type="email" id="campo-email" name="email" placeholder="seu@email.com"
               required autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="campo-formulario" style="margin-bottom:1.5rem;">
        <label for="campo-senha">Senha</label>
        <input type="password" id="campo-senha" name="senha" placeholder="••••••••"
               required autocomplete="current-password">
      </div>

      <button type="submit" class="botao-primario" style="width:100%; justify-content:center; padding:.65rem;">
        <i class="bi bi-box-arrow-in-right"></i> Entrar
      </button>
    </form>

  </div>
</div>

</body>
</html>
