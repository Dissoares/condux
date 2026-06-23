<?php
/**
 * @var array       $config        — chave => valor de todas as configurações
 * @var string|null $mensagem
 * @var string|null $erroMensagem
 */
$tituloPagina = 'Configurações';
require_once RAIZ . '/views/layouts/cabecalho.php';

$logoUrl = !empty($config['app_logo'])
    ? url('uploads/' . $config['app_logo']) . '?v=' . time()
    : null;
?>

<div class="d-flex align-items-center gap-3 mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-gear"></i> Configurações da plataforma</h4>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($erroMensagem) ?></div>
<?php endif; ?>

<form action="<?= url('configuracoes/salvar') ?>" method="POST" enctype="multipart/form-data">

  <!-- ── Identidade ────────────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold py-3">
      <i class="bi bi-building"></i> Identidade
    </div>
    <div class="card-body p-4">

      <!-- Nome e descrição -->
      <div class="row g-3 mb-4">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Nome da plataforma *</label>
          <input type="text" name="app_nome" class="form-control" required maxlength="80"
                 value="<?= htmlspecialchars($config['app_nome'] ?? 'Condux') ?>"
                 placeholder="Ex: Condomínio Sol Nascente">
          <div class="form-text">Exibido no cabeçalho e e-mails.</div>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Nome curto</label>
          <input type="text" name="app_nome_curto" class="form-control" maxlength="20"
                 value="<?= htmlspecialchars($config['app_nome_curto'] ?? 'Condux') ?>"
                 placeholder="Ex: Sol Nasc.">
          <div class="form-text">Usado no PWA e título do browser.</div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Descrição</label>
          <input type="text" name="app_descricao" class="form-control" maxlength="150"
                 value="<?= htmlspecialchars($config['app_descricao'] ?? '') ?>"
                 placeholder="Ex: Gestão — Cond. Sol Nascente">
          <div class="form-text">Aparece no manifesto PWA e na tela de login.</div>
        </div>
      </div>

      <hr class="my-4">

      <!-- Imagens -->
      <div class="row g-4">

        <!-- Logo header/sidebar -->
        <div class="col-md-6">
          <p class="fw-semibold mb-3" style="font-size:.85rem; text-transform:uppercase; letter-spacing:.06em; color:var(--bs-body-secondary);">
            <i class="bi bi-image"></i> Logo — Header e Sidebar
          </p>
          <div class="d-flex align-items-start gap-3">
            <!-- Preview -->
            <div id="logo-preview-wrap"
                 class="flex-shrink-0 rounded-2 bg-body-tertiary d-flex align-items-center justify-content-center"
                 style="width:140px; height:72px; overflow:hidden;">
              <?php if ($logoUrl): ?>
                <img id="logo-preview-img" src="<?= $logoUrl ?>" alt="Logo"
                     style="max-width:128px; max-height:56px; object-fit:contain;">
              <?php else: ?>
                <i class="bi bi-image opacity-25 fs-4"></i>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1">
              <input type="file" name="logo" class="form-control form-control-sm mb-2"
                     accept="image/png,image/jpeg,image/svg+xml,image/webp"
                     id="logo-input">
              <div class="form-text">PNG, JPG, SVG ou WebP · <strong>400×120 px</strong> · máx. 2 MB · fundo transparente recomendado.</div>
              <?php if ($logoUrl): ?>
                <label class="d-flex align-items-center gap-2 mt-2" style="cursor:pointer; font-size:.82rem;">
                  <input type="checkbox" name="remover_logo" class="form-check-input mt-0">
                  <span class="text-danger">Remover logo atual</span>
                </label>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Ícone PWA -->
        <?php
        $iconePwa = RAIZ . '/public/icons/icon-512.png';
        $iconeUrl = file_exists($iconePwa) ? url('icons/icon-512.png') . '?v=' . filemtime($iconePwa) : null;
        ?>
        <div class="col-md-6">
          <p class="fw-semibold mb-3" style="font-size:.85rem; text-transform:uppercase; letter-spacing:.06em; color:var(--bs-body-secondary);">
            <i class="bi bi-phone"></i> Ícone do App — Tela inicial (PWA)
          </p>
          <div class="d-flex align-items-start gap-3">
            <!-- Preview -->
            <div id="icone-pwa-preview-wrap"
                 class="flex-shrink-0 rounded-3 bg-body-tertiary d-flex align-items-center justify-content-center"
                 style="width:72px; height:72px; overflow:hidden;">
              <?php if ($iconeUrl): ?>
                <img id="icone-pwa-preview-img" src="<?= $iconeUrl ?>" alt="Ícone PWA"
                     style="width:72px; height:72px; object-fit:cover; border-radius:14px;">
              <?php else: ?>
                <i class="bi bi-app opacity-25 fs-4"></i>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1">
              <input type="file" name="icone_pwa" class="form-control form-control-sm mb-2"
                     accept="image/png,image/jpeg,image/webp"
                     id="icone-pwa-input">
              <div class="form-text">PNG, JPG ou WebP · <strong>quadrado</strong> (512×512 px ideal) · máx. 2 MB.<br>Gera icon-192 e icon-512 automaticamente.</div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- ── Aparência ─────────────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold py-3">
      <i class="bi bi-palette"></i> Aparência
    </div>
    <div class="card-body p-4">
      <p class="text-body-secondary mb-3" style="font-size:.85rem;">
        As cores são aplicadas ao menu lateral e elementos de destaque da interface.
      </p>

      <div class="row g-4 align-items-start">
        <div class="col-sm-4">
          <label class="form-label fw-semibold">Cor primária</label>
          <div class="d-flex align-items-center gap-2">
            <input type="color" name="cor_primaria" class="form-control form-control-color"
                   value="<?= htmlspecialchars($config['cor_primaria'] ?? '#1a3c5e') ?>"
                   id="cor-primaria" oninput="previewCores()">
            <input type="text" class="form-control form-control-sm font-monospace"
                   id="cor-primaria-txt"
                   value="<?= htmlspecialchars($config['cor_primaria'] ?? '#1a3c5e') ?>"
                   maxlength="7" oninput="sincronizarCor(this,'cor-primaria')">
          </div>
          <div class="form-text">Fundo do menu lateral.</div>
        </div>

        <div class="col-sm-4">
          <label class="form-label fw-semibold">Cor escura</label>
          <div class="d-flex align-items-center gap-2">
            <input type="color" name="cor_escura" class="form-control form-control-color"
                   value="<?= htmlspecialchars($config['cor_escura'] ?? '#0f2540') ?>"
                   id="cor-escura" oninput="previewCores()">
            <input type="text" class="form-control form-control-sm font-monospace"
                   id="cor-escura-txt"
                   value="<?= htmlspecialchars($config['cor_escura'] ?? '#0f2540') ?>"
                   maxlength="7" oninput="sincronizarCor(this,'cor-escura')">
          </div>
          <div class="form-text">Gradiente inferior do menu.</div>
        </div>

        <div class="col-sm-4">
          <label class="form-label fw-semibold">Cor de acento</label>
          <div class="d-flex align-items-center gap-2">
            <input type="color" name="cor_acento" class="form-control form-control-color"
                   value="<?= htmlspecialchars($config['cor_acento'] ?? '#f0a500') ?>"
                   id="cor-acento" oninput="previewCores()">
            <input type="text" class="form-control form-control-sm font-monospace"
                   id="cor-acento-txt"
                   value="<?= htmlspecialchars($config['cor_acento'] ?? '#f0a500') ?>"
                   maxlength="7" oninput="sincronizarCor(this,'cor-acento')">
          </div>
          <div class="form-text">Destaque, links ativos e badges.</div>
        </div>
      </div>

      <!-- Preview ao vivo da sidebar -->
      <div class="mt-4">
        <div class="form-label fw-semibold mb-2">Preview</div>
        <div id="sidebar-preview"
             style="width:200px; border-radius:.75rem; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,.2);">
          <div id="prev-header"
               style="padding:1rem 1.25rem; font-weight:800; font-size:1rem; color:#fff; border-bottom:1px solid rgba(255,255,255,.1);">
            <span id="prev-nome"><?= htmlspecialchars($config['app_nome'] ?? 'Condux') ?></span>
          </div>
          <div id="prev-body" style="padding:.5rem 0 .75rem;">
            <div style="font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
                        color:rgba(255,255,255,.3); padding:.75rem 1.25rem .25rem;">Menu</div>
            <div id="prev-item-ativo"
                 style="padding:.4rem 1rem .4rem 1.25rem; font-size:.8rem; color:#fff;
                        border-left:3px solid; display:flex; align-items:center; gap:.5rem;">
              <i class="bi bi-speedometer2"></i> Painel
            </div>
            <div style="padding:.4rem 1rem .4rem 1.25rem; font-size:.8rem;
                        color:rgba(255,255,255,.55); display:flex; align-items:center; gap:.5rem;">
              <i class="bi bi-buildings"></i> Unidades
            </div>
          </div>
        </div>
      </div>

      <!-- Paletas prontas -->
      <div class="mt-3">
        <div class="form-label fw-semibold mb-2" style="font-size:.82rem;">Paletas prontas</div>
        <div class="d-flex gap-2 flex-wrap">
          <?php foreach ([
            ['Padrão',   '#1a3c5e', '#0f2540', '#f0a500'],
            ['Escuro',   '#1a2236', '#0d1525', '#f59e0b'],
            ['Verde',    '#14532d', '#052e16', '#22c55e'],
            ['Roxo',     '#3b1f6e', '#1e0a3c', '#a78bfa'],
            ['Vinho',    '#7f1d1d', '#450a0a', '#fb7185'],
            ['Cinza',    '#1f2937', '#111827', '#60a5fa'],
          ] as [$label, $p, $e, $a]): ?>
          <button type="button"
                  onclick="aplicarPaleta('<?= $p ?>','<?= $e ?>','<?= $a ?>')"
                  class="btn btn-sm d-flex align-items-center gap-2 border"
                  style="font-size:.75rem; padding:.25rem .6rem;">
            <span style="display:inline-flex; gap:2px;">
              <span style="width:12px;height:12px;border-radius:2px;background:<?= $p ?>;display:inline-block;"></span>
              <span style="width:12px;height:12px;border-radius:2px;background:<?= $a ?>;display:inline-block;"></span>
            </span>
            <?= $label ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-floppy"></i> Salvar configurações
    </button>
  </div>

</form>

<script>
// Sincroniza input[type=color] com input[type=text]
function sincronizarCor(txt, id) {
  var v = txt.value;
  if (/^#[0-9a-fA-F]{6}$/.test(v)) {
    document.getElementById(id).value = v;
    previewCores();
  }
}

function previewCores() {
  var p = document.getElementById('cor-primaria').value;
  var e = document.getElementById('cor-escura').value;
  var a = document.getElementById('cor-acento').value;
  // Atualiza textos
  document.getElementById('cor-primaria-txt').value = p;
  document.getElementById('cor-escura-txt').value   = e;
  document.getElementById('cor-acento-txt').value   = a;
  // Preview
  var prev = document.getElementById('sidebar-preview');
  prev.style.background = 'linear-gradient(180deg, ' + p + ' 0%, ' + e + ' 100%)';
  document.getElementById('prev-header').style.background = p;
  document.getElementById('prev-item-ativo').style.borderLeftColor = a;
  document.getElementById('prev-item-ativo').style.background = 'rgba(255,255,255,.08)';
  // Atualiza nome no preview
  var nome = document.querySelector('[name="app_nome"]')?.value || 'Condux';
  document.getElementById('prev-nome').textContent = nome;
}

function aplicarPaleta(p, e, a) {
  document.getElementById('cor-primaria').value = p;
  document.getElementById('cor-escura').value   = e;
  document.getElementById('cor-acento').value   = a;
  previewCores();
}

// Atualiza preview ao vivo do nome
document.querySelector('[name="app_nome"]')?.addEventListener('input', function() {
  document.getElementById('prev-nome').textContent = this.value || 'Condux';
});

// Init
previewCores();

// Preview de logo
document.getElementById('logo-input')?.addEventListener('change', function() {
  var f = this.files[0];
  if (!f) return;
  var objectUrl = URL.createObjectURL(f);
  var wrap = document.getElementById('logo-preview-wrap');
  wrap.innerHTML = '<img src="' + objectUrl + '" style="max-width:128px; max-height:56px; object-fit:contain;">';
});

// Preview ícone PWA
document.getElementById('icone-pwa-input')?.addEventListener('change', function() {
  var f = this.files[0];
  if (!f) return;
  var objectUrl = URL.createObjectURL(f);
  var wrap = document.getElementById('icone-pwa-preview-wrap');
  wrap.innerHTML = '<img src="' + objectUrl + '" style="width:72px; height:72px; object-fit:cover; border-radius:14px;">';
});
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
