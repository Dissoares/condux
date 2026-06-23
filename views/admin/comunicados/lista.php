<?php
/**
 * @var Comunicado[] $comunicados
 * @var string|null  $mensagem
 * @var string|null  $erroMensagem
 */
$tituloPagina = 'Comunicados';
require_once RAIZ . '/views/layouts/cabecalho.php';

$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;
$hoje    = date('Y-m-d');
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h4 class="fw-semibold mb-0"><i class="bi bi-megaphone"></i> Comunicados</h4>
  <a href="<?= url('comunicados/novo') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Novo comunicado
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<?php if (empty($comunicados)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-megaphone fs-1 opacity-25 d-block mb-3"></i>
    Nenhum comunicado cadastrado.
    <div class="mt-3">
      <a href="<?= url('comunicados/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Criar comunicado
      </a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
<?php foreach ($comunicados as $c): ?>
<?php
  $expirado  = $c->dataExpiracao && $c->dataExpiracao < $hoje;
  $agendado  = $c->dataPublicacao > $hoje;
  $cor       = $c->cor();
?>
<div class="card border-0 shadow-sm" style="border-left:4px solid var(--bs-<?= $cor === 'purple' ? 'primary' : $cor ?>)!important; opacity:<?= (!$c->ativo || $expirado) ? '.6' : '1' ?>">
  <div class="card-body py-3 px-4">
    <div class="d-flex align-items-start gap-3 flex-wrap">

      <!-- Ícone tipo -->
      <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0
                  bg-<?= $cor === 'purple' ? 'primary' : $cor ?>-subtle
                  text-<?= $cor === 'purple' ? 'primary' : $cor ?>-emphasis"
           style="width:40px;height:40px;font-size:1rem;">
        <i class="bi <?= $c->icone() ?>"></i>
      </div>

      <!-- Conteúdo -->
      <div class="flex-grow-1 min-w-0">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
          <span class="fw-semibold"><?= htmlspecialchars($c->titulo) ?></span>
          <span class="badge bg-<?= $cor === 'purple' ? 'primary' : $cor ?>-subtle
                       text-<?= $cor === 'purple' ? 'primary' : $cor ?>-emphasis"
                style="font-size:.68rem;"><?= $c->rotulo() ?></span>
          <?php if (!$c->ativo): ?>
            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size:.68rem;">Inativo</span>
          <?php elseif ($expirado): ?>
            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size:.68rem;">Expirado</span>
          <?php elseif ($agendado): ?>
            <span class="badge bg-info-subtle text-info-emphasis" style="font-size:.68rem;">Agendado</span>
          <?php endif; ?>
        </div>
        <div class="text-body-secondary" style="font-size:.82rem;white-space:pre-line;"><?= nl2br(htmlspecialchars(mb_substr($c->conteudo, 0, 180))) ?><?= mb_strlen($c->conteudo) > 180 ? '…' : '' ?></div>
        <div class="mt-2 d-flex gap-3 flex-wrap" style="font-size:.75rem;color:var(--bs-body-secondary);">
          <span><i class="bi bi-calendar me-1"></i>Publicado: <?= $fmtData($c->dataPublicacao) ?></span>
          <?php if ($c->dataExpiracao): ?>
            <span><i class="bi bi-calendar-x me-1"></i>Expira: <?= $fmtData($c->dataExpiracao) ?></span>
          <?php endif; ?>
          <?php if ($c->nomeAutor): ?>
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($c->nomeAutor) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Ações -->
      <div class="d-flex gap-2 flex-shrink-0 align-items-start">
        <a href="<?= url("comunicados/{$c->id}/editar") ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
          <i class="bi bi-pencil"></i>
        </a>
        <a href="<?= url("comunicados/{$c->id}/excluir") ?>"
           onclick="return confirm('Remover este comunicado?')"
           class="btn btn-outline-danger btn-sm" title="Remover">
          <i class="bi bi-trash"></i>
        </a>
      </div>

    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
