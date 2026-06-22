<?php
/** @var Projeto[] $projetos @var string|null $mensagem */
$tituloPagina = 'Gerar Taxa Extra';
require_once RAIZ . '/views/layouts/cabecalho.php';

$msgErro = match($_GET['msg'] ?? '') {
    'erro'             => 'Preencha todos os campos obrigatórios.',
    'projeto_invalido' => 'Projeto não encontrado.',
    'sem_unidades'     => 'Nenhuma unidade ativa cadastrada.',
    default            => null,
};
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-lightning-fill text-primary"></i> Gerar Taxa Extra</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      Cobre um projeto em andamento em parcelas para todas as unidades.
    </p>
  </div>
  <a href="<?= url('taxas-extra') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<?php if ($msgErro): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= $msgErro ?>
  </div>
<?php endif; ?>

<form action="<?= url('taxas-extra/gerar') ?>" method="POST">

  <div class="row g-4">

    <!-- Coluna principal -->
    <div class="col-lg-7">

      <!-- Projeto -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-kanban"></i></span>
          <span class="fw-semibold">Projeto vinculado</span>
        </div>
        <div class="card-body">
          <?php if (empty($projetos)): ?>
            <div class="alert alert-warning mb-0">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              Nenhum projeto em andamento. <a href="<?= url('projetos') ?>">Veja os projetos</a>.
            </div>
          <?php else: ?>
            <label for="campo-projeto" class="form-label">Projeto em andamento <span class="text-danger">*</span></label>
            <select id="campo-projeto" name="projeto_id" class="form-select" required>
              <option value="">— Selecione um projeto —</option>
              <?php foreach ($projetos as $p): ?>
                <option value="<?= $p->id ?>">
                  <?= htmlspecialchars($p->nome) ?>
                  <?php if ($p->valorEstimado): ?> — <?= dinheiro($p->valorEstimado) ?><?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Somente projetos com status "Em andamento" aparecem aqui.</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Valores -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-success bg-opacity-10 text-success"><i class="bi bi-cash-coin"></i></span>
          <span class="fw-semibold">Valores</span>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="campo-valor-total" class="form-label">Total do projeto (R$)</label>
              <input type="text" id="campo-valor-total" name="valor_total" class="form-control"
                     placeholder="Ex: 36.000,00"
                     pattern="[\d.]+([,][\d]{1,2})?"
                     title="Use vírgula como decimal">
              <div class="form-text">Opcional — apenas para referência.</div>
            </div>
            <div class="col-sm-6">
              <label for="campo-valor-parcela" class="form-label">Valor por parcela (R$) <span class="text-danger">*</span></label>
              <input type="text" id="campo-valor-parcela" name="valor_parcela" class="form-control"
                     placeholder="Ex: 150,00" required
                     pattern="[\d.]+([,][\d]{1,2})?"
                     title="Use vírgula como decimal">
              <div class="form-text">Cobrado de cada unidade por mês.</div>
            </div>
          </div>
          <div id="calculo-total" class="p-3 rounded-3 mb-1" style="background:var(--bs-tertiary-bg); display:none;">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-body-secondary" style="font-size:.85rem;">Arrecadação total estimada</span>
              <strong id="valor-arrecadacao" class="text-success" style="font-size:1rem;">—</strong>
            </div>
            <div class="text-body-secondary mt-1" style="font-size:.75rem;" id="desc-calculo"></div>
          </div>
        </div>
      </div>

      <!-- Parcelamento -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-calendar-range"></i></span>
          <span class="fw-semibold">Parcelamento</span>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-sm-5">
              <label for="campo-parcelas" class="form-label">Quantidade de parcelas <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" id="campo-parcelas" name="total_parcelas" class="form-control"
                       min="1" max="120" value="12" required>
                <span class="input-group-text">×</span>
              </div>
            </div>
            <div class="col-sm-7">
              <label for="campo-vencimento" class="form-label">1º vencimento <span class="text-danger">*</span></label>
              <input type="date" id="campo-vencimento" name="primeiro_vencimento" class="form-control"
                     value="<?= date('Y-m-d', strtotime('first day of next month')) ?>" required>
              <div class="form-text">Demais parcelas vencem no mesmo dia dos meses seguintes.</div>
            </div>
          </div>

          <div class="mb-3">
            <label for="campo-descricao" class="form-label">Descrição / justificativa</label>
            <textarea id="campo-descricao" name="descricao" class="form-control" rows="2"
                      placeholder="Ex: Rateio da reforma da garagem aprovada em assembleia..."></textarea>
          </div>
        </div>
      </div>

    </div>

    <!-- Resumo lateral -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm" style="position:sticky; top:calc(56px + 1.5rem);">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-receipt"></i></span>
          <span class="fw-semibold">Resumo</span>
        </div>
        <div class="card-body">
          <div id="resumo-vazio" class="text-body-secondary text-center py-3" style="font-size:.875rem;">
            <i class="bi bi-arrow-left-circle mb-2 d-block" style="font-size:1.5rem; opacity:.4;"></i>
            Preencha os campos ao lado para ver o resumo.
          </div>
          <div id="resumo-conteudo" style="display:none;">
            <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.875rem;">
              <span class="text-body-secondary">Parcelas</span>
              <strong id="res-parcelas">—</strong>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.875rem;">
              <span class="text-body-secondary">Valor / unidade / parcela</span>
              <strong id="res-valor-parc">—</strong>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.875rem;">
              <span class="text-body-secondary">1º vencimento</span>
              <strong id="res-venc1">—</strong>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.875rem;">
              <span class="text-body-secondary">Último vencimento</span>
              <strong id="res-vencN">—</strong>
            </div>
            <div class="d-flex justify-content-between py-2" style="font-size:.875rem;">
              <span class="text-body-secondary">Total arrecadado / unidade</span>
              <strong id="res-total-und" class="text-success">—</strong>
            </div>
          </div>

          <div class="alert alert-info d-flex align-items-start gap-2 mt-3 py-2 mb-0" style="font-size:.8rem;">
            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
            <div>As cobranças serão geradas para <strong>todas as unidades ativas</strong> no momento da geração.</div>
          </div>
        </div>
        <div class="card-footer bg-transparent">
          <button type="submit" class="btn btn-primary w-100" <?= empty($projetos) ? 'disabled' : '' ?>>
            <i class="bi bi-lightning-fill"></i> Gerar cobranças em lote
          </button>
        </div>
      </div>
    </div>

  </div>
</form>

<script>
(function () {
  var fParcelas = document.getElementById('campo-parcelas');
  var fValor    = document.getElementById('campo-valor-parcela');
  var fVenc     = document.getElementById('campo-vencimento');

  var elResVazio    = document.getElementById('resumo-vazio');
  var elResConteudo = document.getElementById('resumo-conteudo');
  var elResParcelas = document.getElementById('res-parcelas');
  var elResValParc  = document.getElementById('res-valor-parc');
  var elResVenc1    = document.getElementById('res-venc1');
  var elResVencN    = document.getElementById('res-vencN');
  var elResTotalUnd = document.getElementById('res-total-und');

  function fmt(n) {
    return 'R$ ' + n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function fmtData(s) {
    if (!s) return '—';
    var p = s.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
  }
  function addMeses(s, m) {
    var d = new Date(s + 'T12:00:00');
    d.setMonth(d.getMonth() + m);
    return d.toISOString().slice(0, 10);
  }

  function atualizar() {
    var n     = parseInt(fParcelas.value, 10) || 0;
    var v     = parseFloat((fValor.value || '0').replace(',', '.')) || 0;
    var venc1 = fVenc.value;

    if (n < 1 || v <= 0 || !venc1) {
      elResVazio.style.display    = '';
      elResConteudo.style.display = 'none';
      return;
    }

    elResVazio.style.display    = 'none';
    elResConteudo.style.display = '';

    elResParcelas.textContent = n + 'x';
    elResValParc.textContent  = fmt(v);
    elResVenc1.textContent    = fmtData(venc1);
    elResVencN.textContent    = fmtData(addMeses(venc1, n - 1));
    elResTotalUnd.textContent = fmt(n * v);
  }

  [fParcelas, fValor, fVenc].forEach(function (el) {
    el.addEventListener('input', atualizar);
  });
  atualizar();
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
