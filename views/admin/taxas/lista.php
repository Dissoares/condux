<?php
/** @var TaxaCondominial[] $taxas @var array $resumo @var string $competencia */
$tituloPagina = 'Taxas Mensais';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-cash-stack"></i> Taxas Mensais</h1>
  <a href="<?= url('taxas/gerar-lote') ?>" class="botao-primario">
    <i class="bi bi-lightning-fill"></i> Gerar em lote
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alerta-flash sucesso"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alerta-flash erro"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erroMensagem) ?></div>
<?php endif; ?>

<form method="GET" action="<?= url('taxas') ?>" style="margin-bottom:1.25rem; display:flex; gap:.75rem; align-items:flex-end;">
  <div class="campo-formulario" style="margin:0;">
    <label for="filtro-competencia">Competência</label>
    <input type="month" id="filtro-competencia" name="competencia"
           value="<?= htmlspecialchars($competencia) ?>" style="width:auto;">
  </div>
  <button type="submit" class="botao-secundario"><i class="bi bi-search"></i> Filtrar</button>
</form>

<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.25rem;" class="grade-resumo-mobile">
  <div class="card-resumo">
    <div class="icone-resumo verde"><i class="bi bi-check-circle-fill"></i></div>
    <div><div class="valor-resumo"><?= $resumo['total_pagas'] ?? 0 ?></div><div class="rotulo-resumo">Pagas</div></div>
  </div>
  <div class="card-resumo">
    <div class="icone-resumo amarelo"><i class="bi bi-clock-fill"></i></div>
    <div><div class="valor-resumo"><?= $resumo['total_pendentes'] ?? 0 ?></div><div class="rotulo-resumo">Pendentes/Vencidas</div></div>
  </div>
  <div class="card-resumo">
    <div class="icone-resumo azul"><i class="bi bi-cash"></i></div>
    <div><div class="valor-resumo"><?= dinheiro((float)($resumo['valor_arrecadado'] ?? 0)) ?></div><div class="rotulo-resumo">Arrecadado</div></div>
  </div>
</div>

<div class="tabela-responsiva">
<table class="tabela-condux">
  <thead>
    <tr>
      <th>Unidade</th><th>Competência</th><th>Valor</th>
      <th>Vencimento</th><th>Status</th><th>Comprovante</th><th></th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($taxas)): ?>
      <tr><td colspan="7" style="text-align:center; color:#6b7280; padding:2rem;">Nenhuma taxa encontrada.</td></tr>
    <?php else: ?>
      <?php foreach ($taxas as $taxa): ?>
      <tr>
        <td><?= htmlspecialchars($taxa->identificacaoUnidade ?? '—') ?></td>
        <td><?= htmlspecialchars($taxa->competenciaFormatada()) ?></td>
        <td><?= dinheiro($taxa->valor) ?></td>
        <td><?= dataBR($taxa->vencimento) ?></td>
        <td><span class="badge-status <?= $taxa->status ?>"><?= ucfirst($taxa->status) ?></span></td>
        <td>
          <?php if ($taxa->comprovante): ?>
            <a href="<?= url('uploads/' . $taxa->comprovante) ?>" target="_blank"
               class="botao-secundario" style="font-size:.78rem; padding:.25rem .6rem;">
              <i class="bi bi-paperclip"></i> Ver
            </a>
          <?php else: ?>
            <span style="color:#9ca3af;">—</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($taxa->comprovante && $taxa->status !== 'pago'): ?>
            <a href="<?= url("taxas/{$taxa->id}/aprovar?competencia={$competencia}") ?>"
               class="botao-primario" style="font-size:.78rem; padding:.3rem .65rem;"
               onclick="return confirm('Aprovar este pagamento?')">
              <i class="bi bi-check-lg"></i> Aprovar
            </a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
