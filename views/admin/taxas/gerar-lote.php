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

<!-- Modal de confirmação -->
<div class="modal fade" id="modalConfirmarAlteracao" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-semibold">
          <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Confirmar alteração
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modal-confirmar-texto"></div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="modal-confirmar-btn">Sim, alterar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const modal      = new bootstrap.Modal(document.getElementById('modalConfirmarAlteracao'));
  const modalTexto = document.getElementById('modal-confirmar-texto');
  const modalBtn   = document.getElementById('modal-confirmar-btn');

  function desbloquear(campo, ico, btn) {
    campo.removeAttribute('readonly');
    campo.classList.remove('bg-body-secondary');
    campo.focus();
    ico.className = 'bi bi-unlock-fill text-warning';
    btn.innerHTML = '<i class="bi bi-lock"></i>';
    btn.title     = 'Bloquear';
  }

  function bloquear(campo, ico, btn) {
    campo.setAttribute('readonly', '');
    campo.classList.add('bg-body-secondary');
    ico.className = 'bi bi-lock-fill';
    btn.innerHTML = '<i class="bi bi-pencil"></i>';
    btn.title     = 'Editar';
  }

  document.querySelectorAll('.btn-desbloq').forEach(btn => {
    btn.addEventListener('click', function () {
      const campo = document.getElementById(this.dataset.alvo);
      const ico   = document.getElementById(this.dataset.ico);

      if (!campo.hasAttribute('readonly')) {
        bloquear(campo, ico, this);
        return;
      }

      const isDia    = this.dataset.alvo === 'campo-dia-vencimento';
      const diaAtual = document.getElementById('campo-dia-vencimento').value;
      const valAtual = document.getElementById('campo-valor').value;

      modalTexto.innerHTML = isDia
        ? `O dia de vencimento padrão é <strong>dia ${diaAtual}</strong>. Tem certeza que quer alterar?`
        : `O valor padrão da taxa condominial é de <strong>R$ ${valAtual}</strong>. Tem certeza que quer alterar?`;

      const btnRef  = this;
      const handler = () => {
        desbloquear(campo, ico, btnRef);
        modal.hide();
        modalBtn.removeEventListener('click', handler);
      };
      modalBtn.addEventListener('click', handler);
      modal.show();
    });
  });
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
