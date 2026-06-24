<?php
/**
 * @var Comunicado[] $comunicados
 */
$tituloPagina = 'Comunicados';
require_once RAIZ . '/views/layouts/cabecalho.php';

$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;
?>

<div class="mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-megaphone"></i> Comunicados</h4>
  <p class="text-body-secondary" style="font-size:.85rem;">Avisos e informações do condomínio</p>
</div>

<?php if (empty($comunicados)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body text-center py-5 text-body-secondary">
    <i class="bi bi-megaphone fs-1 opacity-25 d-block mb-2"></i>
    Nenhum comunicado ativo no momento.
  </div>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
<?php foreach ($comunicados as $c): ?>
<?php $cor = $c->cor(); $corCss = $cor === 'purple' ? 'primary' : $cor; ?>
<div class="card border-0 shadow-sm"
     style="border-left:4px solid var(--bs-<?= $corCss ?>)!important; cursor:pointer;"
     onclick="abrirComunicado(this)"
     data-titulo="<?= htmlspecialchars($c->titulo) ?>"
     data-conteudo="<?= htmlspecialchars($c->conteudo) ?>"
     data-cor="<?= $corCss ?>"
     data-icone="<?= $c->icone() ?>"
     data-rotulo="<?= htmlspecialchars($c->rotulo()) ?>"
     data-publicado="<?= $fmtData($c->dataPublicacao) ?>"
     data-expira="<?= $fmtData($c->dataExpiracao) ?>"
     data-autor="<?= htmlspecialchars($c->nomeAutor ?? '') ?>">
  <div class="card-body py-3 px-4">
    <div class="d-flex align-items-start gap-3">

      <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0
                  bg-<?= $corCss ?>-subtle text-<?= $corCss ?>-emphasis"
           style="width:38px;height:38px;font-size:.95rem;">
        <i class="bi <?= $c->icone() ?>"></i>
      </div>

      <div class="flex-grow-1 min-w-0">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
          <span class="fw-semibold"><?= htmlspecialchars($c->titulo) ?></span>
          <span class="badge bg-<?= $corCss ?>-subtle text-<?= $corCss ?>-emphasis"
                style="font-size:.68rem;"><?= $c->rotulo() ?></span>
        </div>
        <div class="text-body-secondary mb-1" style="font-size:.82rem;white-space:pre-line;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">
          <?= htmlspecialchars($c->conteudo) ?>
        </div>
        <div class="text-body-secondary" style="font-size:.75rem;">
          <i class="bi bi-calendar me-1"></i><?= $fmtData($c->dataPublicacao) ?>
          <?php if ($c->nomeAutor): ?>
            &nbsp;·&nbsp; <i class="bi bi-person me-1"></i><?= htmlspecialchars($c->nomeAutor) ?>
          <?php endif; ?>
          <?php if ($c->dataExpiracao): ?>
            &nbsp;·&nbsp; <i class="bi bi-calendar-x me-1"></i>Válido até <?= $fmtData($c->dataExpiracao) ?>
          <?php endif; ?>
        </div>
      </div>

      <i class="bi bi-chevron-right text-body-secondary flex-shrink-0 align-self-center"></i>

    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal comunicado completo -->
<div class="modal fade" id="modalComunicado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
               id="mComIconeWrap" style="width:40px;height:40px;font-size:1rem;">
            <i class="bi" id="mComIcone"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold mb-0" id="mComTitulo"></h5>
            <span class="badge mt-1" id="mComRotulo" style="font-size:.68rem;"></span>
          </div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-3">
        <p id="mComConteudo" style="white-space:pre-line;line-height:1.7;font-size:.92rem;" class="mb-3"></p>
        <div class="d-flex gap-3 flex-wrap text-body-secondary" style="font-size:.78rem;" id="mComMeta"></div>
      </div>
    </div>
  </div>
</div>

<script>
function abrirComunicado(el) {
  const cor    = el.dataset.cor;
  const icone  = el.dataset.icone;
  const rotulo = el.dataset.rotulo;

  document.getElementById('mComTitulo').textContent   = el.dataset.titulo;
  document.getElementById('mComConteudo').textContent = el.dataset.conteudo;

  const wrap = document.getElementById('mComIconeWrap');
  wrap.className = `rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 bg-${cor}-subtle text-${cor}-emphasis`;
  document.getElementById('mComIcone').className = `bi ${icone}`;

  const badge = document.getElementById('mComRotulo');
  badge.textContent = rotulo;
  badge.className   = `badge bg-${cor}-subtle text-${cor}-emphasis mt-1`;

  let meta = '';
  if (el.dataset.publicado) meta += `<span><i class="bi bi-calendar me-1"></i>Publicado: ${el.dataset.publicado}</span>`;
  if (el.dataset.expira)    meta += `<span><i class="bi bi-calendar-x me-1"></i>Válido até: ${el.dataset.expira}</span>`;
  if (el.dataset.autor)     meta += `<span><i class="bi bi-person me-1"></i>${el.dataset.autor}</span>`;
  document.getElementById('mComMeta').innerHTML = meta;

  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalComunicado')).show();
}
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
