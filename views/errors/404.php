<?php
$tituloPagina = 'Página não encontrada';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex flex-column align-items-center justify-content-center text-center py-5" style="min-height:55vh;">
  <div class="mb-4" style="font-size:5rem; line-height:1; font-weight:800; color:var(--condux-primaria); opacity:.15; letter-spacing:-.05em;">
    404
  </div>
  <div class="rounded-circle d-flex align-items-center justify-content-center mb-4 bg-primary bg-opacity-10"
       style="width:72px;height:72px;font-size:2rem;">
    <i class="bi bi-compass text-primary"></i>
  </div>
  <h4 class="fw-bold mb-2">Página não encontrada</h4>
  <p class="text-body-secondary mb-4" style="max-width:380px;">
    O endereço que você acessou não existe ou foi removido.
    Verifique o link ou volte para o início.
  </p>
  <div class="d-flex gap-2">
    <a href="<?= url('painel') ?>" class="btn btn-primary">
      <i class="bi bi-house-fill me-1"></i> Ir para o painel
    </a>
    <button onclick="history.back()" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i> Voltar
    </button>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
