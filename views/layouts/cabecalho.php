<?php
/** @var string $tituloPagina */
$usuarioAtual = Sessao::usuarioAtual();
$ehAdmin      = in_array($usuarioAtual['perfil'] ?? '', ['sindico', 'subsindico'], true);

// Segmento ativo baseado na URI atual
$uriAtual    = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$segAtivo    = trim(substr($uriAtual, strlen(BASE_URL)), '/');
$segAtivo    = explode('/', $segAtivo)[0] ?? '';

$inicialNome = strtoupper(mb_substr($usuarioAtual['nome'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloPagina ?? 'Condux') ?> — Condux</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/condux.css') ?>">
</head>
<body>

<aside class="barra-lateral" id="barraLateral">
  <div class="logo-cabecalho">
    Con<span>dux</span>
  </div>

  <ul class="menu-navegacao">
    <li class="item-menu <?= $segAtivo === '' || $segAtivo === 'painel' ? 'ativo' : '' ?>">
      <a href="<?= url('painel') ?>"><i class="bi bi-speedometer2"></i> Painel</a>
    </li>

    <?php if ($ehAdmin): ?>
    <li class="item-menu <?= $segAtivo === 'unidades' ? 'ativo' : '' ?>">
      <a href="<?= url('unidades') ?>"><i class="bi bi-building"></i> Unidades</a>
    </li>
    <li class="item-menu <?= $segAtivo === 'taxas' ? 'ativo' : '' ?>">
      <a href="<?= url('taxas') ?>"><i class="bi bi-cash-stack"></i> Taxas Mensais</a>
    </li>
    <li class="item-menu <?= $segAtivo === 'taxas-extra' ? 'ativo' : '' ?>">
      <a href="<?= url('taxas-extra') ?>"><i class="bi bi-plus-circle"></i> Taxas Extras</a>
    </li>
    <li class="item-menu <?= $segAtivo === 'projetos' ? 'ativo' : '' ?>">
      <a href="<?= url('projetos') ?>"><i class="bi bi-kanban"></i> Projetos</a>
    </li>
    <li class="item-menu <?= $segAtivo === 'vistorias' ? 'ativo' : '' ?>">
      <a href="<?= url('vistorias') ?>"><i class="bi bi-clipboard-check"></i> Vistorias</a>
    </li>
    <li class="item-menu <?= $segAtivo === 'relatorios' ? 'ativo' : '' ?>">
      <a href="<?= url('relatorios') ?>"><i class="bi bi-bar-chart-line"></i> Relatórios</a>
    </li>
    <?php else: ?>
    <li class="item-menu <?= $segAtivo === 'minhas-taxas' ? 'ativo' : '' ?>">
      <a href="<?= url('minhas-taxas') ?>"><i class="bi bi-receipt"></i> Minhas Taxas</a>
    </li>
    <li class="item-menu <?= $segAtivo === 'transparencia' ? 'ativo' : '' ?>">
      <a href="<?= url('transparencia') ?>"><i class="bi bi-eye"></i> Transparência</a>
    </li>
    <li class="item-menu <?= $segAtivo === 'relatorios' ? 'ativo' : '' ?>">
      <a href="<?= url('relatorios') ?>"><i class="bi bi-file-earmark-text"></i> Relatórios</a>
    </li>
    <?php endif; ?>
  </ul>

  <div style="padding:1rem 1.25rem; border-top:1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:.65rem;">
    <div class="usuario-avatar"><?= $inicialNome ?></div>
    <div style="flex:1; overflow:hidden;">
      <div style="color:#fff; font-size:.82rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
        <?= htmlspecialchars($usuarioAtual['nome'] ?? '') ?>
      </div>
      <div style="color:rgba(255,255,255,.6); font-size:.72rem; text-transform:capitalize;">
        <?= htmlspecialchars($usuarioAtual['perfil'] ?? '') ?>
      </div>
    </div>
    <a href="<?= url('sair') ?>" title="Sair" style="color:rgba(255,255,255,.6);">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</aside>

<div class="overlay-menu" id="overlayMenu"></div>

<button class="botao-menu-mobile" id="botaoToggleMenu" aria-label="Abrir menu">
  <i class="bi bi-list"></i>
</button>

<main class="conteudo-principal">
