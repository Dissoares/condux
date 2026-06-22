<?php
/** @var string $tituloPagina */
$usuarioAtual = Sessao::usuarioAtual();
$ehAdmin      = in_array($usuarioAtual['perfil'] ?? '', ['sindico', 'subsindico'], true);

$uriAtual  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$segAtivo  = trim(substr($uriAtual, strlen(BASE_URL)), '/');
$segAtivo  = explode('/', $segAtivo)[0] ?? '';

$inicialNome = strtoupper(mb_substr($usuarioAtual['nome'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <script>
    (function () {
      var t = localStorage.getItem('condux-tema') || 'light';
      document.documentElement.setAttribute('data-bs-theme', t);
    }());
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloPagina ?? 'Condux') ?> — Condux</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/condux.css') ?>">
</head>
<body>

<aside class="condux-sidebar" id="barraLateral">
  <a href="<?= url('painel') ?>" class="condux-logo d-block">Con<span>dux</span></a>

  <nav class="flex-grow-1 py-2">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="<?= url('painel') ?>" class="nav-link <?= ($segAtivo === '' || $segAtivo === 'painel') ? 'ativo' : '' ?>">
          <i class="bi bi-speedometer2"></i> Painel
        </a>
      </li>

      <?php if ($ehAdmin): ?>
      <li class="nav-item">
        <a href="<?= url('unidades') ?>" class="nav-link <?= $segAtivo === 'unidades' ? 'ativo' : '' ?>">
          <i class="bi bi-building"></i> Unidades
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('condominios') ?>" class="nav-link <?= $segAtivo === 'condominios' ? 'ativo' : '' ?>">
          <i class="bi bi-people"></i> Condôminos
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('taxas') ?>" class="nav-link <?= $segAtivo === 'taxas' ? 'ativo' : '' ?>">
          <i class="bi bi-cash-stack"></i> Taxas Mensais
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('taxas-extra') ?>" class="nav-link <?= $segAtivo === 'taxas-extra' ? 'ativo' : '' ?>">
          <i class="bi bi-plus-circle"></i> Taxas Extras
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('projetos') ?>" class="nav-link <?= $segAtivo === 'projetos' ? 'ativo' : '' ?>">
          <i class="bi bi-kanban"></i> Projetos
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('vistorias') ?>" class="nav-link <?= $segAtivo === 'vistorias' ? 'ativo' : '' ?>">
          <i class="bi bi-clipboard-check"></i> Vistorias
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('relatorios') ?>" class="nav-link <?= $segAtivo === 'relatorios' ? 'ativo' : '' ?>">
          <i class="bi bi-bar-chart-line"></i> Relatórios
        </a>
      </li>
      <?php else: ?>
      <li class="nav-item">
        <a href="<?= url('minhas-taxas') ?>" class="nav-link <?= $segAtivo === 'minhas-taxas' ? 'ativo' : '' ?>">
          <i class="bi bi-receipt"></i> Minhas Taxas
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('transparencia') ?>" class="nav-link <?= $segAtivo === 'transparencia' ? 'ativo' : '' ?>">
          <i class="bi bi-eye"></i> Transparência
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('relatorios') ?>" class="nav-link <?= $segAtivo === 'relatorios' ? 'ativo' : '' ?>">
          <i class="bi bi-file-earmark-text"></i> Relatórios
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- Usuário + toggle de tema -->
  <div class="condux-usuario">
    <div class="condux-avatar"><?= $inicialNome ?></div>
    <div class="flex-grow-1 overflow-hidden">
      <div class="text-white fw-semibold" style="font-size:.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
        <?= htmlspecialchars($usuarioAtual['nome'] ?? '') ?>
      </div>
      <div style="color:rgba(255,255,255,.55); font-size:.72rem; text-transform:capitalize;">
        <?= htmlspecialchars($usuarioAtual['perfil'] ?? '') ?>
      </div>
    </div>
    <button class="btn btn-link p-0 border-0 condux-btn-tema" onclick="conduxToggleTema()" title="Alternar tema"
            style="color:rgba(255,255,255,.6); font-size:1rem;">
      <i class="bi bi-moon-fill condux-tema-icone"></i>
    </button>
    <a href="<?= url('sair') ?>" title="Sair" style="color:rgba(255,255,255,.6); font-size:1rem;">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</aside>

<div class="condux-overlay" id="conduxOverlay"></div>

<button class="botao-menu-mobile" id="botaoToggleMenu" aria-label="Abrir menu">
  <i class="bi bi-list"></i>
</button>

<main class="condux-conteudo">
