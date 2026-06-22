<?php
/** @var TaxaCondominial[] $taxas @var TaxaCondominial|null $taxaAtual */
$tituloPagina = 'Minhas Taxas';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-receipt"></i> Minhas Taxas</h1>
</div>

<?php if ($mensagem): ?>
  <div class="alerta-flash sucesso"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alerta-flash erro"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erroMensagem) ?></div>
<?php endif; ?>

<!-- Envio de comprovante para qualquer taxa pendente -->
<?php if ($taxaAtual && !$taxaAtual->estaPago() && !$taxaAtual->comprovante): ?>
<div class="card-conteudo" style="max-width:520px; margin-bottom:1.5rem;">
  <h2 class="titulo-card">Enviar comprovante — <?= htmlspecialchars($taxaAtual->competenciaFormatada()) ?></h2>
  <form action="<?= $urlBase ?>/index.php?pagina=minhas-taxas&acao=enviarComprovante" method="POST"
        enctype="multipart/form-data">
    <input type="hidden" name="taxa_id" value="<?= $taxaAtual->id ?>">
    <div class="campo-formulario" style="margin-bottom:1rem;">
      <label for="arquivo-comprovante-lista">Arquivo (PDF, JPG ou PNG)</label>
      <input type="file" id="arquivo-comprovante-lista" name="comprovante"
             accept=".pdf,.jpg,.jpeg,.png" required>
    </div>
    <button type="submit" class="botao-primario">
      <i class="bi bi-send"></i> Enviar
    </button>
  </form>
</div>
<?php endif; ?>

<div class="card-conteudo" style="padding:0; overflow:hidden;">
  <table class="tabela-condux">
    <thead>
      <tr>
        <th>Competência</th>
        <th>Valor</th>
        <th>Vencimento</th>
        <th>Status</th>
        <th>Pagamento</th>
        <th>Comprovante</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($taxas)): ?>
        <tr><td colspan="6" style="text-align:center; color:#6b7280; padding:2rem;">Nenhuma taxa encontrada.</td></tr>
      <?php else: ?>
        <?php foreach ($taxas as $taxa): ?>
        <tr>
          <td><?= htmlspecialchars($taxa->competenciaFormatada()) ?></td>
          <td>R$ <?= number_format($taxa->valor, 2, ',', '.') ?></td>
          <td><?= date('d/m/Y', strtotime($taxa->vencimento)) ?></td>
          <td><span class="badge-status <?= $taxa->status ?>"><?= ucfirst($taxa->status) ?></span></td>
          <td>
            <?= $taxa->dataPagamento
              ? date('d/m/Y', strtotime($taxa->dataPagamento))
              : '—' ?>
          </td>
          <td>
            <?php if ($taxa->comprovante): ?>
              <a href="<?= $urlBase ?>/uploads/<?= htmlspecialchars($taxa->comprovante) ?>"
                 target="_blank" class="botao-secundario" style="font-size:.78rem; padding:.25rem .6rem;">
                <i class="bi bi-paperclip"></i> Ver
              </a>
            <?php else: ?>
              <span style="color:#9ca3af;">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
