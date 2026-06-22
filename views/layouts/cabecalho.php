<?php
/** @var string $tituloPagina */
$app            = require RAIZ . '/config/app.php';
$urlBase        = $app['url_base'];
$usuarioAtual   = Sessao::usuarioAtual();
$ehAdmin        = in_array($usuarioAtual['perfil'] ?? '', ['sindico', 'subsindico'], true);
$paginaAtual    = $_GET['pagina'] ?? 'painel';

$inicialNome = strtoupper(mb_substr($usuarioAtual['nome'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloPagina ?? 'Condux') ?> — Condux</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $urlBase ?>/assets/css/condux.css">
</head>
<body>

<!-- Barra lateral de navegação -->
<aside class="barra-lateral" id="barraLateral">
  <div class="logo-cabecalho">
    Con<span>dux</span>
  </div>

  <ul class="menu-navegacao">
    <li class="item-menu <?= $paginaAtual === 'painel' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=painel">
        <i class="bi bi-speedometer2"></i> Painel
      </a>
    </li>

    <?php if ($ehAdmin): ?>
    <li class="item-menu <?= $paginaAtual === 'unidades' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=unidades">
        <i class="bi bi-building"></i> Unidades
      </a>
    </li>
    <li class="item-menu <?= $paginaAtual === 'taxas' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=taxas">
        <i class="bi bi-cash-stack"></i> Taxas Mensais
      </a>
    </li>
    <li class="item-menu <?= $paginaAtual === 'taxas-extra' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=taxas-extra">
        <i class="bi bi-plus-circle"></i> Taxas Extras
      </a>
    </li>
    <li class="item-menu <?= $paginaAtual === 'projetos' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=projetos">
        <i class="bi bi-kanban"></i> Projetos
      </a>
    </li>
    <li class="item-menu <?= $paginaAtual === 'vistorias' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=vistorias">
        <i class="bi bi-clipboard-check"></i> Vistorias
      </a>
    </li>
    <li class="item-menu <?= $paginaAtual === 'relatorios' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=relatorios">
        <i class="bi bi-bar-chart-line"></i> Relatórios
      </a>
    </li>
    <?php else: ?>
    <li class="item-menu <?= $paginaAtual === 'minhas-taxas' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=minhas-taxas">
        <i class="bi bi-receipt"></i> Minhas Taxas
      </a>
    </li>
    <li class="item-menu <?= $paginaAtual === 'transparencia' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=transparencia">
        <i class="bi bi-eye"></i> Transparência
      </a>
    </li>
    <li class="item-menu <?= $paginaAtual === 'relatorios' ? 'ativo' : '' ?>">
      <a href="<?= $urlBase ?>/index.php?pagina=relatorios">
        <i class="bi bi-file-earmark-text"></i> Relatórios
      </a>
    </li>
    <?php endif; ?>
  </ul>

  <!-- Rodapé da barra com usuário logado -->
  <div style="padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:.65rem;">
    <div class="usuario-avatar"><?= $inicialNome ?></div>
    <div style="flex:1; overflow:hidden;">
      <div style="color:#fff; font-size:.82rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
        <?= htmlspecialchars($usuarioAtual['nome'] ?? '') ?>
      </div>
      <div style="color:rgba(255,255,255,.6); font-size:.72rem; text-transform:capitalize;">
        <?= htmlspecialchars($usuarioAtual['perfil'] ?? '') ?>
      </div>
    </div>
    <a href="<?= $urlBase ?>/index.php?pagina=sair" title="Sair" style="color:rgba(255,255,255,.6);">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</aside>

<!-- Conteúdo da página -->
<main class="conteudo-principal">
