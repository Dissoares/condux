<?php
$tituloPagina = 'Gerar Taxas em Lote';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-lightning-fill"></i> Gerar Taxas em Lote</h4>
  <a href="<?= url('taxas') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div class="card border-0 shadow-sm" style="max-width:480px;">
  <div class="card-header bg-transparent fw-semibold py-3">Parâmetros de geração</div>
  <div class="card-body">
    <form action="<?= url('taxas/gerar-lote') ?>" method="POST">
      <div class="mb-3">
        <label for="campo-competencia" class="form-label">Competência</label>
        <input type="month" id="campo-competencia" name="competencia" class="form-control"
               value="<?= date('Y-m') ?>" required>
        <div class="form-text">Mês/ano de referência da cobrança.</div>
      </div>
      <div class="mb-3">
        <label for="campo-valor" class="form-label">Valor (R$)</label>
        <input type="text" id="campo-valor" name="valor" class="form-control"
               placeholder="350,00" required pattern="[\d]+([,.][\d]{1,2})?">
        <div class="form-text">Use vírgula ou ponto como separador decimal.</div>
      </div>
      <div class="mb-4">
        <label for="campo-vencimento" class="form-label">Data de vencimento</label>
        <input type="date" id="campo-vencimento" name="vencimento" class="form-control" required>
        <div class="form-text">Preenchido automaticamente com o dia 10 da competência selecionada.</div>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-lightning-fill"></i> Gerar taxas para todas as unidades
      </button>
    </form>
  </div>
</div>

<script>
(function () {
  const comp = document.getElementById('campo-competencia');
  const venc = document.getElementById('campo-vencimento');

  function sugerirVencimento() {
    if (comp.value) {
      venc.value = comp.value + '-10';
    }
  }

  comp.addEventListener('change', sugerirVencimento);
  sugerirVencimento();
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
