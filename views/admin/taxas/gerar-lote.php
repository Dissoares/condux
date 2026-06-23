<?php
/**
 * @var int         $diaVencimento
 * @var string|null $valorMensal
 * @var string      $competencia
 * @var array       $unidadesComTaxa
 * @var string|null $mensagem
 * @var string|null $erroMensagem
 */
$tituloPagina = 'Gerar Taxas em Lote';
require_once RAIZ . '/views/layouts/cabecalho.php';

$statusLabel = [
    'pago'       => ['text' => 'Pago',       'class' => 'badge-pago'],
    'pendente'   => ['text' => 'Pendente',   'class' => 'badge-pendente'],
    'vencido'    => ['text' => 'Vencido',    'class' => 'badge-vencido'],
    'aguardando' => ['text' => 'Aguardando', 'class' => 'badge-aguardando'],
    'isento'     => ['text' => 'Isento',     'class' => 'badge-pago'],
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-lightning-fill"></i> Gerar Taxas em Lote</h4>
  <a href="<?= url('taxas') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<!-- Filtro de competência + form de geração -->
<div class="row g-4 mb-4">

  <!-- Seletor de competência (GET — só filtra a lista) -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold py-3">Visualizar competência</div>
      <div class="card-body">
        <form method="GET" action="<?= url('taxas/gerar-lote') ?>" class="d-flex gap-2">
          <input type="month" name="competencia" class="form-control"
                 value="<?= htmlspecialchars($competencia) ?>" required>
          <button type="submit" class="btn btn-outline-primary text-nowrap">
            <i class="bi bi-funnel"></i> Filtrar
          </button>
        </form>
        <div class="form-text mt-2">Filtra a lista de unidades abaixo.</div>
      </div>
    </div>
  </div>

  <!-- Form de geração (POST) -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold py-3">Parâmetros de geração</div>
      <div class="card-body">
        <form action="<?= url('taxas/gerar-lote') ?>" method="POST">
          <div class="row g-3 mb-3">
            <div class="col-sm-4">
              <label for="campo-dia-vencimento" class="form-label">Dia de vencimento</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill" id="ico-lock-dia"></i></span>
                <input type="number" id="campo-dia-vencimento" name="dia_vencimento"
                       class="form-control bg-body-secondary" min="1" max="31"
                       value="<?= $diaVencimento ?>" readonly required>
                <button type="button" class="btn btn-outline-secondary btn-desbloq"
                        data-alvo="campo-dia-vencimento" data-ico="ico-lock-dia" title="Editar">
                  <i class="bi bi-pencil"></i>
                </button>
              </div>
            </div>
            <div class="col-sm-4">
              <label for="campo-valor" class="form-label">Valor mensal (R$)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill" id="ico-lock-valor"></i></span>
                <input type="text" id="campo-valor" name="valor" class="form-control bg-body-secondary"
                       placeholder="500,00"
                       value="<?= $valorMensal ? number_format((float)$valorMensal, 2, ',', '.') : '' ?>"
                       readonly required>
                <button type="button" class="btn btn-outline-secondary btn-desbloq"
                        data-alvo="campo-valor" data-ico="ico-lock-valor" title="Editar">
                  <i class="bi bi-pencil"></i>
                </button>
              </div>
            </div>
            <div class="col-sm-4">
              <label for="campo-competencia" class="form-label">Competência</label>
              <input type="month" id="campo-competencia" name="competencia"
                     class="form-control" value="<?= htmlspecialchars($competencia) ?>" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-lightning-fill"></i> Gerar / atualizar taxas
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

<!-- Lista de unidades por competência -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between">
    <span class="fw-semibold">
      <i class="bi bi-buildings"></i> Unidades —
      <?= date('m/Y', strtotime($competencia . '-01')) ?>
    </span>
    <span class="text-muted" style="font-size:.82rem;">
      <?= count($unidadesComTaxa) ?> unidade(s) ativa(s)
    </span>
  </div>

  <?php if (empty($unidadesComTaxa)): ?>
    <div class="card-body text-muted">Nenhuma unidade ativa cadastrada.</div>
  <?php else: ?>
  <div class="accordion accordion-flush" id="acordUnidades">
    <?php foreach ($unidadesComTaxa as $i => $u):
      $temTaxa   = $u['taxa_id'] !== null;
      $status    = $u['status'] ?? null;
      $info      = $status ? ($statusLabel[$status] ?? null) : null;
      $collapseId = 'colapso-' . $i;
    ?>
    <div class="accordion-item border-0 border-bottom">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed py-3" type="button"
                data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
          <div class="d-flex align-items-center gap-3 w-100 me-3">
            <i class="bi bi-building text-muted"></i>
            <span class="fw-semibold"><?= htmlspecialchars($u['identificacao']) ?></span>
            <span class="ms-auto d-flex align-items-center gap-2">
              <?php if ($temTaxa && $info): ?>
                <span class="badge rounded-pill <?= $info['class'] ?>"><?= $info['text'] ?></span>
                <span class="text-muted" style="font-size:.85rem;">
                  R$ <?= number_format((float) $u['valor'], 2, ',', '.') ?>
                </span>
              <?php else: ?>
                <span class="badge rounded-pill text-bg-secondary">Sem taxa</span>
              <?php endif; ?>
            </span>
          </div>
        </button>
      </h2>
      <div id="<?= $collapseId ?>" class="accordion-collapse collapse"
           data-bs-parent="#acordUnidades">
        <div class="accordion-body pt-0 pb-3 ps-5">
          <?php if (!$temTaxa): ?>
            <p class="text-muted mb-2" style="font-size:.875rem;">
              Nenhuma taxa gerada para <strong><?= date('m/Y', strtotime($competencia . '-01')) ?></strong>.
              Use o formulário acima para gerar.
            </p>
          <?php else: ?>
            <table class="table table-sm table-borderless mb-2" style="font-size:.875rem; max-width:420px;">
              <tr>
                <td class="text-muted ps-0" style="width:140px;">Competência</td>
                <td><?= date('m/Y', strtotime($competencia . '-01')) ?></td>
              </tr>
              <tr>
                <td class="text-muted ps-0">Vencimento</td>
                <td><?= $u['vencimento'] ? date('d/m/Y', strtotime($u['vencimento'])) : '—' ?></td>
              </tr>
              <tr>
                <td class="text-muted ps-0">Valor</td>
                <td>R$ <?= number_format((float) $u['valor'], 2, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="text-muted ps-0">Status</td>
                <td>
                  <?php if ($info): ?>
                    <span class="badge rounded-pill <?= $info['class'] ?>"><?= $info['text'] ?></span>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
              </tr>
              <?php if ($u['data_pagamento']): ?>
              <tr>
                <td class="text-muted ps-0">Pago em</td>
                <td><?= date('d/m/Y', strtotime($u['data_pagamento'])) ?></td>
              </tr>
              <?php endif; ?>
            </table>
            <a href="<?= url('taxas/unidade/' . $u['unidade_id'] . '?competencia=' . $competencia) ?>"
               class="btn btn-sm btn-outline-primary">
              <i class="bi bi-eye"></i> Ver detalhes
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal de confirmação de alteração de parâmetros -->
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
  let modal        = null;
  const modalTexto = document.getElementById('modal-confirmar-texto');
  const modalBtn   = document.getElementById('modal-confirmar-btn');

  function getModal() {
    if (!modal) modal = new bootstrap.Modal(document.getElementById('modalConfirmarAlteracao'));
    return modal;
  }

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

      const m       = getModal();
      const btnRef  = this;
      const handler = () => {
        desbloquear(campo, ico, btnRef);
        m.hide();
        modalBtn.removeEventListener('click', handler);
      };
      modalBtn.addEventListener('click', handler);
      m.show();
    });
  });
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
