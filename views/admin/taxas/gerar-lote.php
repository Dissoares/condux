<?php
$tituloPagina = 'Gerar Taxas em Lote';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-lightning-fill"></i> Gerar Taxas em Lote</h1>
  <a href="<?= url('taxas') ?>" class="botao-secundario"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card-conteudo" style="max-width:480px;">
  <h2 class="titulo-card">Parâmetros de geração</h2>

  <form action="<?= url('taxas/gerar-lote') ?>" method="POST">
    <div class="campo-formulario" style="margin-bottom:1rem;">
      <label for="campo-competencia">Competência</label>
      <input type="month" id="campo-competencia" name="competencia" value="<?= date('Y-m') ?>" required>
      <small style="color:#6b7280;">Mês/ano de referência da cobrança.</small>
    </div>
    <div class="campo-formulario" style="margin-bottom:1rem;">
      <label for="campo-valor">Valor (R$)</label>
      <input type="text" id="campo-valor" name="valor" placeholder="350,00" required
             pattern="[\d]+([,.][\d]{1,2})?">
      <small style="color:#6b7280;">Use vírgula ou ponto como separador decimal.</small>
    </div>
    <div class="campo-formulario" style="margin-bottom:1.5rem;">
      <label for="campo-vencimento">Data de vencimento</label>
      <input type="date" id="campo-vencimento" name="vencimento" required>
    </div>
    <button type="submit" class="botao-primario">
      <i class="bi bi-lightning-fill"></i> Gerar taxas para todas as unidades
    </button>
  </form>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
