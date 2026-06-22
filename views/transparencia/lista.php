<?php
/** @var Projeto[] $projetos */
$tituloPagina    = 'Portal da Transparência';
$filtroStatus    = $_GET['status'] ?? '';
$statusDisponiveis = Projeto::$rotulosStatus;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-eye"></i> Portal da Transparência</h1>
</div>

<div style="display:flex; gap:.5rem; margin-bottom:1.25rem; flex-wrap:wrap;">
  <a href="<?= url('transparencia') ?>"
     class="botao-<?= $filtroStatus === '' ? 'primario' : 'secundario' ?>" style="font-size:.82rem; padding:.35rem .8rem;">
    Todos
  </a>
  <?php foreach ($statusDisponiveis as $chave => $rotulo): ?>
    <a href="<?= url("transparencia?status={$chave}") ?>"
       class="botao-<?= $filtroStatus === $chave ? 'primario' : 'secundario' ?>" style="font-size:.82rem; padding:.35rem .8rem;">
      <?= htmlspecialchars($rotulo) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($projetos)): ?>
  <div class="card-conteudo" style="text-align:center; color:#6b7280; padding:3rem;">
    <i class="bi bi-folder-x" style="font-size:2.5rem; display:block; margin-bottom:.75rem;"></i>
    Nenhum projeto encontrado.
  </div>
<?php else: ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1.25rem;">
    <?php foreach ($projetos as $projeto): ?>
    <div class="card-conteudo" style="margin-bottom:0;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.75rem;">
        <h3 style="font-size:1rem; font-weight:600; color:var(--cor-primaria); margin:0;">
          <?= htmlspecialchars($projeto->nome) ?>
        </h3>
        <span class="badge-status <?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span>
      </div>

      <?php if ($projeto->descricao): ?>
        <p style="font-size:.875rem; color:#4b5563; margin-bottom:.75rem; line-height:1.5;">
          <?= nl2br(htmlspecialchars(mb_substr($projeto->descricao, 0, 120))) ?>
          <?= mb_strlen($projeto->descricao) > 120 ? '…' : '' ?>
        </p>
      <?php endif; ?>

      <div style="font-size:.8rem; color:#6b7280; margin-bottom:.75rem;">
        <?php if ($projeto->nomeResponsavel): ?>
          <div><i class="bi bi-person"></i> <?= htmlspecialchars($projeto->nomeResponsavel) ?></div>
        <?php endif; ?>
        <?php if ($projeto->valorEstimado): ?>
          <div><i class="bi bi-cash"></i> <?= dinheiro($projeto->valorEstimado) ?> estimado</div>
        <?php endif; ?>
      </div>

      <a href="<?= url("transparencia/{$projeto->id}") ?>" class="botao-secundario" style="font-size:.82rem;">
        <i class="bi bi-eye"></i> Ver detalhes
      </a>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
