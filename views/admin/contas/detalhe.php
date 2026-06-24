<?php
/** @var Conta $conta @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = $conta->descricao;
require_once RAIZ . '/views/layouts/cabecalho.php';

$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;
$fmtVal  = fn(float $v)   => 'R$ ' . number_format($v, 2, ',', '.');
$atrasada = $conta->estaAtrasada();
$hoje     = date('Y-m-d');

[$mesN, $anoN] = explode('-', $conta->competencia);
$nomesMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$nomeComp = ($nomesMeses[(int)$mesN] ?? $mesN) . '/' . $anoN;

if ($conta->status === 'pago')     { $badgeClass = 'badge-pago';    $badgeLabel = 'Pago'; }
elseif ($atrasada)                 { $badgeClass = 'badge-vencido'; $badgeLabel = 'Atrasada'; }
else                               { $badgeClass = 'badge-pendente';$badgeLabel = 'Pendente'; }

$isImg = $conta->anexo && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $conta->anexo);
$isPdf = $conta->anexo && preg_match('/\.pdf$/i', $conta->anexo);
?>

<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
  <a href="<?= url('contas?comp=' . $conta->competencia) ?>" class="btn btn-outline-secondary btn-sm py-1 px-2">
    <i class="bi bi-chevron-left"></i>
  </a>
  <div class="flex-grow-1">
    <h4 class="fw-semibold mb-0"><?= htmlspecialchars($conta->descricao) ?></h4>
    <span class="text-body-secondary" style="font-size:.82rem;"><?= $nomeComp ?></span>
  </div>
  <span class="badge rounded-pill <?= $badgeClass ?>"><?= $badgeLabel ?></span>
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

<div class="row g-4">

  <!-- ── Coluna esquerda: dados + ações ── -->
  <div class="col-lg-5">

    <!-- Card de dados -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center gap-2">
        <div class="cat-icon bg-<?= $conta->corCategoria() ?>-subtle text-<?= $conta->corCategoria() ?>-emphasis">
          <i class="bi <?= $conta->iconeCategoria() ?>"></i>
        </div>
        <?= $conta->rotuloCategoria() ?>
      </div>
      <div class="card-body p-0">
        <?php
        $linhas = [
          ['Valor',        '<span class="fw-bold fs-5">' . $fmtVal($conta->valor) . '</span>'],
          ['Competência',  $nomeComp],
          ['Fornecedor',   $conta->fornecedor ? htmlspecialchars($conta->fornecedor) : '<span class="text-body-secondary">—</span>'],
          ['Vencimento',   $conta->dataVencimento
              ? '<span class="' . ($atrasada ? 'text-danger fw-semibold' : '') . '">' . $fmtData($conta->dataVencimento) . ($atrasada ? ' <i class="bi bi-exclamation-triangle-fill ms-1"></i>' : '') . '</span>'
              : '<span class="text-body-secondary">—</span>'],
          ['Pagamento',    $conta->dataPagamento
              ? '<span class="text-success"><i class="bi bi-check2 me-1"></i>' . $fmtData($conta->dataPagamento) . '</span>'
              : '<span class="text-body-secondary">—</span>'],
          ['Observações',  $conta->observacoes ? htmlspecialchars($conta->observacoes) : '<span class="text-body-secondary">—</span>'],
          ['Registrada em', $conta->criadoEm ? $fmtData($conta->criadoEm) : '<span class="text-body-secondary">—</span>'],
        ];
        ?>
        <?php foreach ($linhas as [$label, $valor]): ?>
        <div class="d-flex border-bottom px-3 py-2" style="font-size:.875rem;">
          <dt class="fw-normal text-body-secondary me-3" style="min-width:110px;"><?= $label ?></dt>
          <dd class="mb-0"><?= $valor ?></dd>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Ações -->
    <div class="d-flex flex-column gap-2">
      <?php if ($conta->status !== 'pago'): ?>
      <form action="<?= url('contas/pagar') ?>" method="POST">
        <input type="hidden" name="id"             value="<?= $conta->id ?>">
        <input type="hidden" name="comp"           value="<?= htmlspecialchars($conta->competencia) ?>">
        <input type="hidden" name="data_pagamento" value="<?= $hoje ?>">
        <button type="submit" class="btn btn-success w-100"
                onclick="return confirm('Marcar como pago hoje?')">
          <i class="bi bi-check2-circle me-1"></i> Confirmar pagamento
        </button>
      </form>
      <?php endif; ?>

      <?php if (!$conta->anexo): ?>
      <form action="<?= url('contas/salvar') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id"          value="<?= $conta->id ?>">
        <input type="hidden" name="descricao"   value="<?= htmlspecialchars($conta->descricao) ?>">
        <input type="hidden" name="categoria"   value="<?= htmlspecialchars($conta->categoria) ?>">
        <input type="hidden" name="competencia" value="<?= htmlspecialchars($conta->competencia) ?>">
        <input type="hidden" name="fornecedor"  value="<?= htmlspecialchars($conta->fornecedor ?? '') ?>">
        <input type="hidden" name="valor"       value="<?= number_format($conta->valor, 2, ',', '.') ?>">
        <input type="hidden" name="data_vencimento" value="<?= $conta->dataVencimento ?? '' ?>">
        <input type="hidden" name="data_pagamento"  value="<?= $conta->dataPagamento ?? '' ?>">
        <input type="hidden" name="observacoes" value="<?= htmlspecialchars($conta->observacoes ?? '') ?>">
        <div class="input-group">
          <input type="file" name="anexo" class="form-control form-control-sm"
                 accept="image/*,.pdf" required>
          <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-paperclip"></i> Anexar
          </button>
        </div>
      </form>
      <?php endif; ?>

      <a href="<?= url('contas/' . $conta->id . '/excluir?comp=' . urlencode($conta->competencia)) ?>"
         onclick="return confirm('Remover esta conta permanentemente?')"
         class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash me-1"></i> Remover conta
      </a>
    </div>
  </div>

  <!-- ── Coluna direita: anexo ── -->
  <div class="col-lg-7">
    <?php if ($conta->anexo): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center justify-content-between">
        <span><i class="bi bi-paperclip me-1"></i> Comprovante</span>
        <a href="<?= url('uploads/' . $conta->anexo) ?>" target="_blank"
           class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-box-arrow-up-right me-1"></i> Abrir
        </a>
      </div>
      <div class="card-body p-0">
        <?php if ($isImg): ?>
          <img src="<?= url('uploads/' . $conta->anexo) ?>" alt="Comprovante"
               class="w-100 rounded-bottom" style="max-height:520px; object-fit:contain; background:#f8f9fa;">
        <?php elseif ($isPdf): ?>
          <iframe src="<?= url('uploads/' . $conta->anexo) ?>"
                  class="w-100 rounded-bottom border-0"
                  style="height:520px;"></iframe>
        <?php else: ?>
          <div class="p-4 text-center text-body-secondary">
            <i class="bi bi-file-earmark fs-1 opacity-25 d-block mb-2"></i>
            <a href="<?= url('uploads/' . $conta->anexo) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-download me-1"></i>
              <?= htmlspecialchars($conta->nomeOriginal ?? 'Baixar arquivo') ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5 text-body-secondary">
        <i class="bi bi-paperclip fs-1 opacity-25 d-block mb-2"></i>
        <p class="mb-0">Nenhum comprovante anexado.</p>
        <p class="mb-0" style="font-size:.82rem;">Use o campo ao lado para adicionar.</p>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>

<style>
.cat-icon { width:32px; height:32px; border-radius:.5rem; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
</style>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
