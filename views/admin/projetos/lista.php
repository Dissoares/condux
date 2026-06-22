<?php
/** @var Projeto[] $projetos @var string|null $mensagem @var bool $ehAdmin */
$tituloPagina = 'Projetos';
require_once RAIZ . '/views/layouts/cabecalho.php';

$statusDisponiveis = Projeto::$rotulosStatus;
$filtroStatus = $_GET['status'] ?? '';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-kanban"></i> Projetos</h1>
  <a href="<?= $urlBase ?>/index.php?pagina=projetos&acao=formulario" class="botao-primario">
    <i class="bi bi-plus-lg"></i> Novo projeto
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alerta-flash sucesso"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<!-- Filtro por status -->
<div style="display:flex; gap:.5rem; margin-bottom:1.25rem; flex-wrap:wrap;">
  <a href="<?= $urlBase ?>/index.php?pagina=projetos"
     class="botao-<?= $filtroStatus === '' ? 'primario' : 'secundario' ?>" style="font-size:.82rem; padding:.35rem .8rem;">
    Todos
  </a>
  <?php foreach ($statusDisponiveis as $chave => $rotulo): ?>
    <a href="<?= $urlBase ?>/index.php?pagina=projetos&status=<?= $chave ?>"
       class="botao-<?= $filtroStatus === $chave ? 'primario' : 'secundario' ?>" style="font-size:.82rem; padding:.35rem .8rem;">
      <?= htmlspecialchars($rotulo) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="tabela-responsiva">
<table class="tabela-condux">
    <thead>
      <tr>
        <th>Projeto</th>
        <th>Responsável</th>
        <th>Prestadora</th>
        <th>Valor estimado</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($projetos)): ?>
        <tr><td colspan="6" style="text-align:center; color:#6b7280; padding:2rem;">Nenhum projeto encontrado.</td></tr>
      <?php else: ?>
        <?php foreach ($projetos as $projeto): ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($projeto->nome) ?></strong>
            <?php if ($projeto->idealizador): ?>
              <br><small style="color:#6b7280;">Idealizador: <?= htmlspecialchars($projeto->idealizador) ?></small>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td>
          <td><?= htmlspecialchars($projeto->nomePrestadora ?? '—') ?></td>
          <td><?= $projeto->valorEstimado ? 'R$ ' . number_format($projeto->valorEstimado, 2, ',', '.') : '—' ?></td>
          <td><span class="badge-status <?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span></td>
          <td>
            <a href="<?= $urlBase ?>/index.php?pagina=projetos&acao=ver&id=<?= $projeto->id ?>"
               class="botao-secundario" style="font-size:.78rem; padding:.25rem .6rem;">
              <i class="bi bi-eye"></i> Ver
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
