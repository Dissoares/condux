<?php
/** @var string $tituloPagina */
$usuarioAtual = Sessao::usuarioAtual();
$ehAdmin      = in_array($usuarioAtual['perfil'] ?? '', ['sindico', 'subsindico'], true);

$uriAtual  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$segAtivo  = trim(substr($uriAtual, strlen(BASE_URL)), '/');
$segAtivo  = explode('/', $segAtivo)[0] ?? '';

$inicialNome = strtoupper(mb_substr($usuarioAtual['nome'] ?? 'U', 0, 1));
$ePainel     = ($segAtivo === '' || $segAtivo === 'painel');

// Configurações dinâmicas da plataforma
require_once RAIZ . '/src/repository/ConfiguracaoRepository.php';
$_cfg = (new ConfiguracaoRepository(Conexao::obter()))->todas();
$_appNome    = htmlspecialchars($_cfg['app_nome']      ?? 'Condux');
$_appCurto   = htmlspecialchars($_cfg['app_nome_curto'] ?? 'Condux');
$_corPrimaria = htmlspecialchars($_cfg['cor_primaria']  ?? '#1a3c5e');
$_corEscura   = htmlspecialchars($_cfg['cor_escura']    ?? '#0f2540');
$_corAcento   = htmlspecialchars($_cfg['cor_acento']    ?? '#f0a500');
$_logoUrl     = !empty($_cfg['app_logo']) ? url('uploads/' . $_cfg['app_logo']) : null;
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
  <title><?= htmlspecialchars($tituloPagina ?? 'Condux') ?> — <?= $_appCurto ?></title>
  <meta name="theme-color" content="<?= $_corPrimaria ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="<?= $_appCurto ?>">
  <link rel="manifest" href="<?= url('manifest.json') ?>">
  <link rel="apple-touch-icon" href="<?= url('icons/icon-192.png') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/condux.css') ?>">
  <style>
    :root {
      --condux-primaria: <?= $_corPrimaria ?>;
      --condux-escura:   <?= $_corEscura ?>;
      --condux-acento:   <?= $_corAcento ?>;
    }
  </style>
</head>
<body>

<!-- ══ Mobile: header fixo no topo ══════════════════════ -->
<header class="condux-top-bar" id="conduxTopBar">
  <a href="<?= url('painel') ?>" class="condux-logo-mobile">
    <?php if ($_logoUrl): ?>
      <img src="<?= $_logoUrl ?>" alt="<?= $_appCurto ?>" style="max-height:28px; max-width:120px; object-fit:contain;">
    <?php else: ?>
      <?= $_appNome ?>
    <?php endif; ?>
  </a>
  <div class="condux-top-bar-acoes">
    <button class="condux-top-btn condux-btn-tema" onclick="conduxToggleTema()" title="Tema">
      <i class="bi bi-moon-fill condux-tema-icone"></i>
    </button>
    <div class="condux-avatar-sm"><?= $inicialNome ?></div>
  </div>
</header>

<!-- ══ Sidebar (desktop sempre visível; mobile = drawer) ═ -->
<aside class="condux-sidebar" id="barraLateral">
  <a href="<?= url('painel') ?>" class="condux-logo d-block">
    <?php if ($_logoUrl): ?>
      <img src="<?= $_logoUrl ?>" alt="<?= $_appCurto ?>"
           style="max-height:36px; max-width:160px; object-fit:contain; display:block;">
    <?php else: ?>
      <?= $_appNome ?>
    <?php endif; ?>
  </a>

  <nav class="flex-grow-1 pb-2">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="<?= url('painel') ?>" class="nav-link <?= $ePainel ? 'ativo' : '' ?>">
          <i class="bi bi-speedometer2"></i> Painel
        </a>
      </li>

      <?php if ($ehAdmin): ?>

      <p class="condux-nav-label">Gestão</p>
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
        <a href="<?= url('gestoes') ?>" class="nav-link <?= $segAtivo === 'gestoes' ? 'ativo' : '' ?>">
          <i class="bi bi-person-badge"></i> Gestões
        </a>
      </li>

      <p class="condux-nav-label">Financeiro</p>
      <li class="nav-item">
        <a href="<?= url('taxas') ?>" class="nav-link <?= $segAtivo === 'taxas' ? 'ativo' : '' ?>">
          <i class="bi bi-cash-stack"></i> Taxa Condominial
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('taxas-extra') ?>" class="nav-link <?= $segAtivo === 'taxas-extra' ? 'ativo' : '' ?>">
          <i class="bi bi-plus-circle"></i> Taxas Extras
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('folha-pagamento') ?>" class="nav-link <?= $segAtivo === 'folha-pagamento' ? 'ativo' : '' ?>">
          <i class="bi bi-people"></i> Folha de Pagamento
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('contas') ?>" class="nav-link <?= $segAtivo === 'contas' ? 'ativo' : '' ?>">
          <i class="bi bi-receipt"></i> Contas
        </a>
      </li>

      <p class="condux-nav-label">Obras</p>
      <li class="nav-item">
        <a href="<?= url('projetos') ?>" class="nav-link <?= $segAtivo === 'projetos' ? 'ativo' : '' ?>">
          <i class="bi bi-kanban"></i> Projetos
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('prestadoras') ?>" class="nav-link <?= $segAtivo === 'prestadoras' ? 'ativo' : '' ?>">
          <i class="bi bi-building"></i> Prestadoras
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('funcionarios') ?>" class="nav-link <?= $segAtivo === 'funcionarios' ? 'ativo' : '' ?>">
          <i class="bi bi-person-badge"></i> Funcionários
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('vistorias') ?>" class="nav-link <?= $segAtivo === 'vistorias' ? 'ativo' : '' ?>">
          <i class="bi bi-clipboard-check"></i> Vistorias
        </a>
      </li>

      <p class="condux-nav-label">Sistema</p>
      <li class="nav-item">
        <a href="<?= url('configuracoes') ?>" class="nav-link <?= $segAtivo === 'configuracoes' ? 'ativo' : '' ?>">
          <i class="bi bi-gear"></i> Configurações
        </a>
      </li>

      <p class="condux-nav-label">Comunicação</p>
      <li class="nav-item">
        <a href="<?= url('comunicados') ?>" class="nav-link <?= $segAtivo === 'comunicados' ? 'ativo' : '' ?>">
          <i class="bi bi-megaphone"></i> Comunicados
        </a>
      </li>

      <p class="condux-nav-label">Análises</p>
      <li class="nav-item">
        <a href="<?= url('relatorios') ?>" class="nav-link <?= $segAtivo === 'relatorios' ? 'ativo' : '' ?>">
          <i class="bi bi-bar-chart-line"></i> Relatórios
        </a>
      </li>

      <?php else: ?>

      <p class="condux-nav-label">Minha conta</p>
      <li class="nav-item">
        <a href="<?= url('minhas-taxas') ?>" class="nav-link <?= $segAtivo === 'minhas-taxas' ? 'ativo' : '' ?>">
          <i class="bi bi-receipt"></i> Minhas Taxas
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('relatorios') ?>" class="nav-link <?= $segAtivo === 'relatorios' ? 'ativo' : '' ?>">
          <i class="bi bi-file-earmark-text"></i> Extrato
        </a>
      </li>

      <p class="condux-nav-label">Condomínio</p>
      <li class="nav-item">
        <a href="<?= url('comunicados') ?>" class="nav-link <?= $segAtivo === 'comunicados' ? 'ativo' : '' ?>">
          <i class="bi bi-megaphone"></i> Comunicados
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= url('transparencia') ?>" class="nav-link <?= $segAtivo === 'transparencia' ? 'ativo' : '' ?>">
          <i class="bi bi-eye"></i> Transparência
        </a>
      </li>

      <?php endif; ?>
    </ul>
  </nav>

  <!-- Usuário + toggle de tema (desktop) -->
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
    <?php if (file_exists(RAIZ . '/config/vapid.php')): ?>
    <button id="condux-push-btn" class="btn btn-link p-0 border-0 condux-push-off"
            title="Ativar notificações"
            style="color:rgba(255,255,255,.6); font-size:1rem;">
      <i class="bi bi-bell"></i>
    </button>
    <?php endif; ?>
    <a href="<?= url('sair') ?>" title="Sair" style="color:rgba(255,255,255,.6); font-size:1rem;">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</aside>

<!-- ══ Overlay (fecha o drawer mobile) ══════════════════ -->
<div class="condux-overlay" id="conduxOverlay"></div>

<!-- ══ Mobile: bottom nav ═══════════════════════════════ -->
<nav class="condux-bottom-nav" id="conduxBottomNav" aria-label="Navegação principal">
  <?php if ($ehAdmin): ?>
  <a href="<?= url('painel') ?>" class="condux-bnav-item <?= $ePainel ? 'ativo' : '' ?>">
    <i class="bi bi-speedometer2"></i>
    <span>Início</span>
  </a>
  <a href="<?= url('unidades') ?>" class="condux-bnav-item <?= $segAtivo === 'unidades' ? 'ativo' : '' ?>">
    <i class="bi bi-building"></i>
    <span>Unidades</span>
  </a>
  <a href="<?= url('condominios') ?>" class="condux-bnav-item <?= $segAtivo === 'condominios' ? 'ativo' : '' ?>">
    <i class="bi bi-people"></i>
    <span>Condôminos</span>
  </a>
  <a href="<?= url('taxas') ?>" class="condux-bnav-item <?= $segAtivo === 'taxas' ? 'ativo' : '' ?>">
    <i class="bi bi-cash-stack"></i>
    <span>Taxas</span>
  </a>
  <button class="condux-bnav-item" id="conduxMaisBtn" aria-label="Mais opções">
    <i class="bi bi-grid"></i>
    <span>Mais</span>
  </button>
  <?php else: ?>
  <a href="<?= url('painel') ?>" class="condux-bnav-item <?= $ePainel ? 'ativo' : '' ?>">
    <i class="bi bi-house-fill"></i>
    <span>Início</span>
  </a>
  <a href="<?= url('minhas-taxas') ?>" class="condux-bnav-item <?= $segAtivo === 'minhas-taxas' ? 'ativo' : '' ?>">
    <i class="bi bi-receipt"></i>
    <span>Taxas</span>
  </a>
  <a href="<?= url('transparencia') ?>" class="condux-bnav-item <?= $segAtivo === 'transparencia' ? 'ativo' : '' ?>">
    <i class="bi bi-eye"></i>
    <span>Transparência</span>
  </a>
  <button class="condux-bnav-item" id="conduxMaisBtn" aria-label="Mais opções">
    <i class="bi bi-grid"></i>
    <span>Mais</span>
  </button>
  <?php endif; ?>
</nav>

<main class="condux-conteudo">
