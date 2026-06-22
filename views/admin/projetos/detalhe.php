<?php
/** @var Projeto $projeto @var bool $ehAdmin @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = $projeto->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';

// Agrupar anexos por tipo
$anexosPorTipo = [];
foreach ($projeto->anexos as $a) {
    $anexosPorTipo[$a['tipo']][] = $a;
}
$fotos    = array_merge($anexosPorTipo['foto']    ?? [], []);
$antes    = $anexosPorTipo['antes']   ?? [];
$depois   = $anexosPorTipo['depois']  ?? [];
$docs     = array_merge(
    $anexosPorTipo['nota_fiscal'] ?? [],
    $anexosPorTipo['documento']   ?? [],
    $anexosPorTipo['video']       ?? []
);
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-semibold mb-1"><?= htmlspecialchars($projeto->nome) ?></h4>
    <span class="badge rounded-pill badge-<?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url("projetos/{$projeto->id}/editar") ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('projetos') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<div class="row g-4 mb-4">

  <!-- ── Informações do projeto ── -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
        <i class="bi bi-info-circle text-primary"></i> Informações
      </div>
      <div class="card-body p-0">
        <dl class="mb-0" style="font-size:.875rem;">
          <?php
          $fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : '—';
          $fmtVal  = fn(?float $v)  => $v !== null ? 'R$ ' . number_format($v, 2, ',', '.') : '—';
          $linhas = [
            ['Idealizador', htmlspecialchars($projeto->idealizador ?? '—')],
            ['Responsável', htmlspecialchars($projeto->nomeResponsavel ?? '—')],
            ['Início',      $fmtData($projeto->dataInicio)],
            ['Conclusão',   $fmtData($projeto->dataConclusao)],
            ['Vlr. estimado',  $fmtVal($projeto->valorEstimado)],
            ['Vlr. realizado', $fmtVal($projeto->valorRealizado)],
          ]; ?>
          <?php foreach ($linhas as [$label, $valor]): ?>
          <div class="d-flex border-bottom px-3 py-2">
            <dt class="fw-normal text-body-secondary me-3" style="min-width:110px;"><?= $label ?></dt>
            <dd class="mb-0 fw-semibold"><?= $valor ?></dd>
          </div>
          <?php endforeach; ?>
        </dl>
        <?php if ($projeto->descricao): ?>
        <div class="px-3 py-3" style="font-size:.875rem; line-height:1.6;">
          <?= nl2br(htmlspecialchars($projeto->descricao)) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── Empresa prestadora ── -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
        <i class="bi bi-building text-success"></i> Empresa Prestadora
      </div>
      <div class="card-body">
        <?php if ($projeto->nomePrestadora): ?>
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success flex-shrink-0"
                 style="width:44px;height:44px;font-size:1.1rem;">
              <i class="bi bi-building"></i>
            </div>
            <div>
              <div class="fw-bold" style="font-size:1rem;"><?= htmlspecialchars($projeto->nomePrestadora) ?></div>
              <?php if ($projeto->prestadoraCnpj): ?>
                <div class="text-body-secondary" style="font-size:.78rem;">CNPJ: <?= htmlspecialchars($projeto->prestadoraCnpj) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="d-flex flex-column gap-2" style="font-size:.875rem;">
            <?php if ($projeto->prestadoraContato): ?>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-person text-body-secondary"></i>
              <span><?= htmlspecialchars($projeto->prestadoraContato) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($projeto->prestadoraTelefone): ?>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-telephone text-body-secondary"></i>
              <a href="tel:<?= htmlspecialchars($projeto->prestadoraTelefone) ?>" class="text-decoration-none">
                <?= htmlspecialchars($projeto->prestadoraTelefone) ?>
              </a>
            </div>
            <?php endif; ?>
            <?php if ($projeto->prestadoraEmail): ?>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-envelope text-body-secondary"></i>
              <a href="mailto:<?= htmlspecialchars($projeto->prestadoraEmail) ?>" class="text-decoration-none">
                <?= htmlspecialchars($projeto->prestadoraEmail) ?>
              </a>
            </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <p class="text-body-secondary mb-0" style="font-size:.875rem;">
            <i class="bi bi-building me-1 opacity-40"></i>Nenhuma prestadora vinculada.
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── Atualizar status ── -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
        <i class="bi bi-arrow-repeat text-warning"></i> Atualizar status
      </div>
      <div class="card-body">
        <form action="<?= url("projetos/{$projeto->id}/status") ?>" method="POST">
          <div class="mb-3">
            <select name="status" class="form-select">
              <?php foreach (Projeto::$rotulosStatus as $chave => $rotulo): ?>
                <option value="<?= $chave ?>" <?= $projeto->status === $chave ? 'selected' : '' ?>>
                  <?= htmlspecialchars($rotulo) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-arrow-repeat"></i> Atualizar
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

<!-- ── Fotos Antes / Depois ── -->
<?php if ($antes || $depois): ?>
<div class="row g-4 mb-4">
  <?php foreach (['antes' => ['Antes', 'clock-history', 'secondary'], 'depois' => ['Depois', 'check-circle', 'success']] as $tipo => [$titulo, $ico, $cor]): ?>
  <?php $fotsGrupo = $tipo === 'antes' ? $antes : $depois; if (empty($fotsGrupo)) continue; ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
        <i class="bi bi-<?= $ico ?> text-<?= $cor ?>"></i>
        Fotos — <?= $titulo ?>
        <span class="badge bg-<?= $cor ?>-subtle text-<?= $cor ?>-emphasis ms-auto"><?= count($fotsGrupo) ?></span>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <?php foreach ($fotsGrupo as $idx => $a): ?>
          <div class="col-6">
            <div class="position-relative">
              <a href="#" class="condux-foto-thumb"
                 data-grupo="grupo-<?= $tipo ?>"
                 data-idx="<?= $idx ?>"
                 data-src="<?= htmlspecialchars(url('uploads/' . $a['caminho'])) ?>"
                 data-desc="<?= htmlspecialchars($a['descricao'] ?? '') ?>"
                 data-nome="<?= htmlspecialchars($a['nome_original']) ?>">
                <img src="<?= url('uploads/' . $a['caminho']) ?>"
                     class="img-fluid rounded-2 w-100" style="height:130px;object-fit:cover;cursor:zoom-in;"
                     alt="<?= htmlspecialchars($a['nome_original']) ?>">
              </a>
              <?php if (!empty($a['descricao'])): ?>
                <div class="position-absolute bottom-0 start-0 end-0 p-1 rounded-bottom-2"
                     style="background:rgba(0,0,0,.5);font-size:.7rem;color:#fff;pointer-events:none;">
                  <?= htmlspecialchars($a['descricao']) ?>
                </div>
              <?php endif; ?>
              <a href="<?= url("projetos/{$projeto->id}/anexos/{$a['id']}/remover") ?>"
                 onclick="return confirm('Remover?')"
                 class="position-absolute top-0 end-0 m-1 btn btn-danger btn-sm py-0 px-1" style="font-size:.7rem;">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Fotos gerais ── -->
<?php if ($fotos): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
    <i class="bi bi-images text-primary"></i> Fotos
    <span class="badge bg-primary-subtle text-primary-emphasis ms-auto"><?= count($fotos) ?></span>
  </div>
  <div class="card-body">
    <div class="row g-2">
      <?php foreach ($fotos as $idx => $a): ?>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="position-relative">
          <a href="#" class="condux-foto-thumb"
             data-grupo="grupo-fotos"
             data-idx="<?= $idx ?>"
             data-src="<?= htmlspecialchars(url('uploads/' . $a['caminho'])) ?>"
             data-desc="<?= htmlspecialchars($a['descricao'] ?? '') ?>"
             data-nome="<?= htmlspecialchars($a['nome_original']) ?>">
            <img src="<?= url('uploads/' . $a['caminho']) ?>"
                 class="img-fluid rounded-2 w-100" style="height:100px;object-fit:cover;cursor:zoom-in;"
                 alt="<?= htmlspecialchars($a['nome_original']) ?>">
          </a>
          <?php if (!empty($a['descricao'])): ?>
            <div class="position-absolute bottom-0 start-0 end-0 p-1 rounded-bottom-2"
                 style="background:rgba(0,0,0,.5);font-size:.68rem;color:#fff;pointer-events:none;">
              <?= htmlspecialchars($a['descricao']) ?>
            </div>
          <?php endif; ?>
          <a href="<?= url("projetos/{$projeto->id}/anexos/{$a['id']}/remover") ?>"
             onclick="return confirm('Remover?')"
             class="position-absolute top-0 end-0 m-1 btn btn-danger btn-sm py-0 px-1" style="font-size:.65rem;">
            <i class="bi bi-trash"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Lightbox ── -->
<div class="modal fade" id="conduxLightbox" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-black border-0">
      <div class="modal-header border-0 pb-0 px-3 pt-2">
        <div class="d-flex align-items-center gap-2 text-white opacity-75" style="font-size:.82rem;">
          <span id="lb-counter"></span>
          <span id="lb-desc" class="ms-2 fst-italic opacity-75"></span>
        </div>
        <div class="ms-auto d-flex gap-2">
          <a id="lb-download" href="#" download class="btn btn-sm btn-outline-light">
            <i class="bi bi-download"></i> Baixar
          </a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body p-2 position-relative text-center" style="min-height:60vh;">
        <button id="lb-prev" class="btn btn-dark opacity-75 position-absolute start-0 top-50 translate-middle-y ms-2" style="z-index:10;">
          <i class="bi bi-chevron-left"></i>
        </button>
        <img id="lb-img" src="" alt=""
             style="max-height:80vh;max-width:100%;object-fit:contain;border-radius:4px;">
        <button id="lb-next" class="btn btn-dark opacity-75 position-absolute end-0 top-50 translate-middle-y me-2" style="z-index:10;">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  let grupo = [], idx = 0;
  const modal   = new bootstrap.Modal(document.getElementById('conduxLightbox'));
  const imgEl   = document.getElementById('lb-img');
  const counter = document.getElementById('lb-counter');
  const desc    = document.getElementById('lb-desc');
  const dlBtn   = document.getElementById('lb-download');

  function atualizar() {
    const f = grupo[idx];
    imgEl.src        = f.src;
    imgEl.alt        = f.nome;
    dlBtn.href       = f.src;
    dlBtn.download   = f.nome;
    counter.textContent = (idx + 1) + ' / ' + grupo.length;
    desc.textContent    = f.desc || '';
    document.getElementById('lb-prev').style.display = grupo.length > 1 ? '' : 'none';
    document.getElementById('lb-next').style.display = grupo.length > 1 ? '' : 'none';
  }

  document.querySelectorAll('.condux-foto-thumb').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const g = a.dataset.grupo;
      grupo = Array.from(document.querySelectorAll(`.condux-foto-thumb[data-grupo="${g}"]`))
                   .map(el => ({ src: el.dataset.src, desc: el.dataset.desc, nome: el.dataset.nome }));
      idx = parseInt(a.dataset.idx, 10);
      atualizar();
      modal.show();
    });
  });

  document.getElementById('lb-prev').addEventListener('click', () => { idx = (idx - 1 + grupo.length) % grupo.length; atualizar(); });
  document.getElementById('lb-next').addEventListener('click', () => { idx = (idx + 1) % grupo.length; atualizar(); });

  document.getElementById('conduxLightbox').addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft')  { idx = (idx - 1 + grupo.length) % grupo.length; atualizar(); }
    if (e.key === 'ArrowRight') { idx = (idx + 1) % grupo.length; atualizar(); }
  });
}());
</script>

<!-- ── Documentos / Vídeos / Notas ── -->
<?php if ($docs): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
    <i class="bi bi-paperclip text-secondary"></i> Documentos e arquivos
  </div>
  <div class="d-flex flex-wrap gap-2 p-3">
    <?php foreach ($docs as $a): ?>
    <?php $icone = match($a['tipo']) {
      'nota_fiscal' => 'receipt',
      'video'       => 'play-circle',
      default       => 'file-earmark-text',
    }; ?>
    <div class="d-flex align-items-center gap-2 border rounded-2 px-3 py-2" style="font-size:.82rem;">
      <i class="bi bi-<?= $icone ?> text-body-secondary"></i>
      <div>
        <div class="fw-semibold"><?= htmlspecialchars($a['nome_original']) ?></div>
        <?php if (!empty($a['descricao'])): ?>
          <div class="text-body-secondary" style="font-size:.75rem;"><?= htmlspecialchars($a['descricao']) ?></div>
        <?php endif; ?>
      </div>
      <a href="<?= url('uploads/' . $a['caminho']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0">
        <i class="bi bi-eye"></i>
      </a>
      <a href="<?= url("projetos/{$projeto->id}/anexos/{$a['id']}/remover") ?>"
         onclick="return confirm('Remover?')"
         class="btn btn-outline-danger btn-sm py-0">
        <i class="bi bi-trash"></i>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ── Adicionar anexo ── -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
    <i class="bi bi-upload text-primary"></i> Adicionar anexo
  </div>
  <div class="card-body">
    <form action="<?= url("projetos/{$projeto->id}/anexos") ?>" method="POST" enctype="multipart/form-data">
      <div class="row g-3 align-items-end">
        <div class="col-sm-auto">
          <label class="form-label">Tipo</label>
          <select name="tipo" class="form-select" style="width:auto;" required>
            <optgroup label="Registro fotográfico">
              <option value="antes">Foto — Antes</option>
              <option value="depois">Foto — Depois</option>
              <option value="foto">Foto geral</option>
            </optgroup>
            <optgroup label="Documentos">
              <option value="nota_fiscal">Nota fiscal</option>
              <option value="documento">Documento</option>
              <option value="video">Vídeo</option>
            </optgroup>
          </select>
        </div>
        <div class="col-sm">
          <label class="form-label">Descrição <span class="text-body-secondary fw-normal" style="font-size:.8rem;">(opcional)</span></label>
          <input type="text" name="descricao" class="form-control" placeholder="Ex: Fachada principal, vista lateral...">
        </div>
        <div class="col-sm">
          <label class="form-label">Arquivo</label>
          <input type="file" name="arquivo" class="form-control" required
                 accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
        </div>
        <div class="col-sm-auto">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-upload"></i> Enviar
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
