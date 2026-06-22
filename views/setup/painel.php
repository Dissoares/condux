<?php
/**
 * @var bool          $statusConexao
 * @var bool          $statusBanco
 * @var array         $migrations
 * @var bool          $temPendentes
 * @var array|null    $resultadoExec
 */
$config = require RAIZ . '/config/banco.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup — Condux</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/condux.css') ?>">
</head>
<body class="bg-body-tertiary" style="padding:2rem 1rem;">

<div style="max-width:680px; margin:0 auto;">

  <!-- Cabeçalho -->
  <div style="text-align:center; margin-bottom:2rem;">
    <h1 style="font-size:2rem; font-weight:800; color:#1a3c5e;">
      Con<span style="color:#f0a500;">dux</span>
    </h1>
    <p style="color:#6b7280;">Painel de Configuração Inicial</p>
  </div>

  <!-- Resultado da última execução -->
  <?php if (!empty($resultadoExec)): ?>
  <div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <h6 class="fw-semibold border-bottom pb-2 mb-3">Resultado da execução</h6>
    <?php foreach ((array) $resultadoExec as $item): ?>
      <div class="alert alert-<?= $item['sucesso'] ? 'success' : 'danger' ?> py-2 d-flex align-items-start gap-2 mb-2">
        <i class="bi bi-<?= $item['sucesso'] ? 'check-circle-fill' : 'x-circle-fill' ?> flex-shrink-0 mt-1"></i>
        <div>
          <strong><?= htmlspecialchars($item['nome']) ?></strong>
          <?php if ($item['erro']): ?>
            <br><small><?= htmlspecialchars($item['erro']) ?></small>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 1. Conexão MySQL -->
  <div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <h6 class="fw-semibold border-bottom pb-2 mb-3">
      <i class="bi bi-<?= $statusConexao ? 'check-circle-fill' : 'x-circle-fill' ?>"
         style="color:<?= $statusConexao ? '#198754' : '#dc3545' ?>"></i>
      Conexão MySQL
    </h6>

    <table style="width:100%; font-size:.875rem; border-collapse:collapse;">
      <?php foreach (['host' => 'Host', 'porta' => 'Porta', 'usuario' => 'Usuário', 'banco' => 'Banco'] as $chave => $rotulo): ?>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.4rem .5rem; color:#6b7280; width:120px;"><?= $rotulo ?></td>
        <td style="padding:.4rem .5rem; font-family:monospace;"><?= htmlspecialchars($config[$chave]) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <?php if (!$statusConexao): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2" style="margin-top:1rem;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Não foi possível conectar ao MySQL. Verifique as credenciais em <code>config/banco.php</code>.
      </div>
    <?php endif; ?>
  </div>

  <!-- 2. Banco de dados -->
  <?php if ($statusConexao): ?>
  <div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <h6 class="fw-semibold border-bottom pb-2 mb-3">
      <i class="bi bi-<?= $statusBanco ? 'check-circle-fill' : 'x-circle-fill' ?>"
         style="color:<?= $statusBanco ? '#198754' : '#dc3545' ?>"></i>
      Banco de dados: <code><?= htmlspecialchars($config['banco']) ?></code>
    </h6>

    <?php if (!$statusBanco): ?>
      <p style="color:#6b7280; font-size:.9rem; margin-bottom:1rem;">
        O banco <strong><?= htmlspecialchars($config['banco']) ?></strong> não existe ainda.
      </p>
      <form action="<?= url('setup/criar-banco') ?>" method="POST">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-database-add"></i> Criar banco de dados
        </button>
      </form>
    <?php else: ?>
      <p style="color:#198754; font-size:.9rem;">
        <i class="bi bi-check2"></i> Banco encontrado.
      </p>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- 3. Migrations -->
  <?php if ($statusBanco): ?>
  <div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <div style="display:flex; justify-content:space-between; align-items:center;" class="fw-semibold border-bottom pb-2 mb-3">
      <span>
        <i class="bi bi-layers"></i> Migrations
        <?php if ($temPendentes): ?>
          <span class="badge rounded-pill badge-vencido" style="margin-left:.5rem;">Pendentes</span>
        <?php else: ?>
          <span class="badge rounded-pill badge-pago" style="margin-left:.5rem;">Em dia</span>
        <?php endif; ?>
      </span>

      <?php if ($temPendentes): ?>
      <form action="<?= url('setup/executar') ?>" method="POST">
        <button type="submit" class="btn btn-primary" style="font-size:.85rem;">
          <i class="bi bi-play-fill"></i> Executar pendentes
        </button>
      </form>
      <?php endif; ?>
    </div>

    <?php if (empty($migrations)): ?>
      <p style="color:#6b7280; font-size:.9rem;">Nenhuma migration encontrada.</p>
    <?php else: ?>
      <table style="width:100%; font-size:.875rem; border-collapse:collapse;">
        <thead>
          <tr style="background:#f8fafc;">
            <th style="padding:.5rem .75rem; text-align:left; font-weight:600; color:#374151;">Arquivo</th>
            <th style="padding:.5rem .75rem; text-align:left; font-weight:600; color:#374151;">Status</th>
            <th style="padding:.5rem .75rem; text-align:left; font-weight:600; color:#374151;">Executada em</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($migrations as $m): ?>
          <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="padding:.5rem .75rem; font-family:monospace; font-size:.82rem;">
              <?= htmlspecialchars($m['nome']) ?>
            </td>
            <td style="padding:.5rem .75rem;">
              <?php if ($m['executada']): ?>
                <span class="badge rounded-pill badge-pago">Executada</span>
              <?php else: ?>
                <span class="badge rounded-pill badge-pendente">Pendente</span>
              <?php endif; ?>
            </td>
            <td style="padding:.5rem .75rem; color:#6b7280; font-size:.82rem;">
              <?= $m['executada_em'] ? date('d/m/Y H:i', strtotime($m['executada_em'])) : '—' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Link para o sistema após tudo ok -->
  <?php if (!$temPendentes): ?>
  <div style="text-align:center; padding:1.5rem;">
    <a href="<?= url('login') ?>" class="btn btn-primary" style="font-size:1rem; padding:.75rem 2rem;">
      <i class="bi bi-box-arrow-in-right"></i> Acessar o sistema
    </a>
    <p style="color:#9ca3af; font-size:.8rem; margin-top:.75rem;">
      Login padrão: <strong>admin@condux.com</strong> / <strong>condux@2025</strong>
    </p>
  </div>
  <?php endif; ?>

  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
