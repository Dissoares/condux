<?php
$tituloPagina = 'Acesso não autorizado';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex flex-column align-items-center justify-content-center text-center py-5" style="min-height:55vh;">
  <div class="mb-4" style="font-size:5rem; line-height:1; font-weight:800; color:var(--bs-danger); opacity:.12; letter-spacing:-.05em;">
    403
  </div>
  <div class="rounded-circle d-flex align-items-center justify-content-center mb-4 bg-danger bg-opacity-10"
       style="width:72px;height:72px;font-size:2rem;">
    <i class="bi bi-lock-fill text-danger"></i>
  </div>
  <h4 class="fw-bold mb-2">Acesso não autorizado</h4>
  <p class="text-body-secondary mb-4" style="max-width:380px;">
    Você não tem permissão para acessar esta página.
    Se acredita que isso é um erro, entre em contato com o administrador.
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
