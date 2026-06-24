<?php
/** @var Conta $conta @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = $conta->descricao;
require_once RAIZ . '/views/layouts/cabecalho.php';

$fmtData  = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;
$fmtVal   = fn(float $v)   => 'R$ ' . number_format($v, 2, ',', '.');
$atrasada = $conta->estaAtrasada();
$hoje     = date('Y-m-d');

[$mesN, $anoN] = explode('-', $conta->competencia);
$nomesMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$nomeComp = ($nomesMeses[(int)$mesN] ?? $mesN) . '/' . $anoN;

if ($conta->status === 'pago')     { $badgeClass = 'badge-pago';    $badgeLabel = 'Pago'; }
elseif ($atrasada)                 { $badgeClass = 'badge-vencido'; $badgeLabel = 'Atrasada'; }
else                               { $badgeClass = 'badge-pendente';$badgeLabel = 'Pendente'; }

$isImg    = $conta->anexo && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $conta->anexo);
$isPdf    = $conta->anexo && preg_match('/\.pdf$/i', $conta->anexo);
$temAnexo = (bool) $conta->anexo;

$valorFormatado = number_format($conta->valor, 2, ',', '.');
?>

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
  <a href="<?= url('contas?comp=' . $conta->competencia) ?>" class="btn btn-outline-secondary btn-sm py-1 px-2">
    <i class="bi bi-chevron-left"></i>
  </a>
  <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
    <div class="cat-icon bg-<?= $conta->corCategoria() ?>-subtle text-<?= $conta->corCategoria() ?>-emphasis flex-shrink-0">
      <i class="bi <?= $conta->iconeCategoria() ?>"></i>
    </div>
    <div class="min-w-0">
      <h4 class="fw-semibold mb-0 text-truncate"><?= htmlspecialchars($conta->descricao) ?></h4>
      <span class="text-body-secondary" style="font-size:.82rem;"><?= $nomeComp ?></span>
    </div>
  </div>
  <span class="badge rounded-pill <?= $badgeClass ?> flex-shrink-0"><?= $badgeLabel ?></span>
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

  <!-- ── Coluna do formulário ── -->
  <div class="<?= $temAnexo ? 'col-lg-5' : 'col-md-8 col-xl-6' ?>">

    <form action="<?= url('contas/salvar') ?>" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $conta->id ?>">

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-column gap-3 py-3">

          <div>
            <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Descrição</label>
            <input type="text" name="descricao" class="form-control" required
                   value="<?= htmlspecialchars($conta->descricao) ?>">
          </div>

          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Categoria</label>
              <select name="categoria" class="form-select">
                <?php foreach (Conta::$categorias as $val => [$rot]): ?>
                  <option value="<?= $val ?>" <?= $conta->categoria === $val ? 'selected' : '' ?>>
                    <?= $rot ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Competência</label>
              <input type="month" name="competencia" class="form-control"
                     value="<?= htmlspecialchars($conta->competencia) ?>">
            </div>
          </div>

          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Valor (R$)</label>
              <input type="text" name="valor" class="form-control" required
                     value="<?= $valorFormatado ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Fornecedor</label>
              <input type="text" name="fornecedor" class="form-control"
                     value="<?= htmlspecialchars($conta->fornecedor ?? '') ?>"
                     placeholder="Opcional">
            </div>
          </div>

          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Vencimento</label>
              <input type="date" name="data_vencimento" class="form-control"
                     value="<?= $conta->dataVencimento ?? '' ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Data de pagamento</label>
              <input type="date" name="data_pagamento" class="form-control"
                     value="<?= $conta->dataPagamento ?? '' ?>">
            </div>
          </div>

          <div>
            <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Observações</label>
            <input type="text" name="observacoes" class="form-control"
                   value="<?= htmlspecialchars($conta->observacoes ?? '') ?>"
                   placeholder="Opcional">
          </div>

          <?php if (!$temAnexo): ?>
          <div>
            <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Comprovante</label>
            <input type="file" name="anexo" class="form-control form-control-sm" accept="image/*,.pdf">
          </div>
          <?php endif; ?>

        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">
          <i class="bi bi-floppy me-1"></i> Salvar alterações
        </button>
        <a href="<?= url('contas/' . $conta->id . '/excluir?comp=' . urlencode($conta->competencia)) ?>"
           onclick="return confirm('Remover esta conta permanentemente?')"
           class="btn btn-outline-danger">
          <i class="bi bi-trash"></i>
        </a>
      </div>
    </form>

  </div>

  <!-- ── Coluna do anexo ── -->
  <?php if ($temAnexo): ?>
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3 d-flex align-items-center justify-content-between">
        <span><i class="bi bi-paperclip me-1"></i> Comprovante</span>
        <div class="d-flex gap-2">
          <a href="<?= url('uploads/' . $conta->anexo) ?>" target="_blank"
             class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Abrir
          </a>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if ($isImg): ?>
          <img src="<?= url('uploads/' . $conta->anexo) ?>" alt="Comprovante"
               class="w-100 rounded-bottom" style="max-height:520px; object-fit:contain; background:#f8f9fa;">
        <?php elseif ($isPdf): ?>
          <iframe src="<?= url('uploads/' . $conta->anexo) ?>"
                  class="w-100 rounded-bottom border-0" style="height:520px;"></iframe>
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
  </div>
  <?php endif; ?>

</div>

<style>
.cat-icon { width:32px; height:32px; border-radius:.5rem; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
</style>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
