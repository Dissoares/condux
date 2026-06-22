<?php
/** @var int $diaVencimento @var string|null $valorMensal */
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

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label for="campo-dia-vencimento" class="form-label">Dia de vencimento</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill" id="ico-lock-dia"></i></span>
            <input type="number" id="campo-dia-vencimento" name="dia_vencimento"
                   class="form-control bg-body-secondary" min="1" max="31"
                   value="<?= $diaVencimento ?>" readonly required>
            <button type="button" class="btn btn-outline-secondary btn-desbloq" data-alvo="campo-dia-vencimento" data-ico="ico-lock-dia" title="Editar">
              <i class="bi bi-pencil"></i>
            </button>
          </div>
        </div>
        <div class="col-6">
          <label for="campo-valor" class="form-label">Valor mensal (R$)</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill" id="ico-lock-valor"></i></span>
            <input type="text" id="campo-valor" name="valor" class="form-control bg-body-secondary"
                   placeholder="500,00"
                   value="<?= $valorMensal ? number_format((float)$valorMensal, 2, ',', '.') : '' ?>"
                   readonly required>
            <button type="button" class="btn btn-outline-secondary btn-desbloq" data-alvo="campo-valor" data-ico="ico-lock-valor" title="Editar">
              <i class="bi bi-pencil"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <label for="campo-competencia" class="form-label">Competência</label>
        <input type="month" id="campo-competencia" name="competencia" class="form-control"
               value="<?= date('Y-m') ?>" required>
        <div class="form-text">Mês/ano de referência da cobrança.</div>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-lightning-fill"></i> Gerar taxas para todas as unidades
      </button>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.btn-desbloq').forEach(btn => {
  btn.addEventListener('click', function () {
    const campo = document.getElementById(this.dataset.alvo);
    const ico   = document.getElementById(this.dataset.ico);
    const bloq  = campo.hasAttribute('readonly');

    if (bloq) {
      campo.removeAttribute('readonly');
      campo.classList.remove('bg-body-secondary');
      campo.focus();
      ico.className  = 'bi bi-unlock-fill text-warning';
      this.innerHTML = '<i class="bi bi-lock"></i>';
      this.title     = 'Bloquear';
    } else {
      campo.setAttribute('readonly', '');
      campo.classList.add('bg-body-secondary');
      ico.className  = 'bi bi-lock-fill';
      this.innerHTML = '<i class="bi bi-pencil"></i>';
      this.title     = 'Editar';
    }
  });
});
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
