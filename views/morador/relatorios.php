<?php
/**
 * @var int    $ano
 * @var int[]  $anos
 * @var array  $extrato   — linhas: competencia, valor, vencimento, status, data_pagamento, observacao
 */
$tituloPagina = 'Meu Extrato';
require_once RAIZ . '/views/layouts/cabecalho.php';

$totalPago    = array_sum(array_column(array_filter($extrato, fn($r) => $r['status'] === 'pago'), 'valor'));
$totalAberto  = array_sum(array_column(array_filter($extrato, fn($r) => in_array($r['status'], ['pendente','vencido'])), 'valor'));
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-receipt"></i> Meu extrato</h1>

  <form method="GET" action="<?= url('relatorios') ?>" style="display:flex; gap:.5rem; align-items:center;">
    <select name="ano" class="campo-select-inline" onchange="this.form.submit()">
      <?php if (empty($anos)): ?>
        <option value="<?= $ano ?>"><?= $ano ?></option>
      <?php else: ?>
        <?php foreach ($anos as $anoOpcao): ?>
          <option value="<?= $anoOpcao ?>" <?= $anoOpcao === $ano ? 'selected' : '' ?>><?= $anoOpcao ?></option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
  </form>
</div>

<!-- Resumo -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem;">
  <div class="card-resumo">
    <div class="rotulo-resumo">Total pago em <?= $ano ?></div>
    <div class="valor-resumo" style="color:var(--cor-sucesso);"><?= dinheiro($totalPago) ?></div>
  </div>
  <div class="card-resumo">
    <div class="rotulo-resumo">Em aberto em <?= $ano ?></div>
    <div class="valor-resumo" style="color:var(--cor-perigo);"><?= dinheiro($totalAberto) ?></div>
  </div>
</div>

<!-- Extrato -->
<div class="card-conteudo">
  <h2 class="titulo-card">Extrato — <?= $ano ?></h2>

  <?php if (empty($extrato)): ?>
    <p style="color:#6b7280; font-size:.9rem;">Nenhuma taxa encontrada para <?= $ano ?>.</p>
  <?php else: ?>
    <div class="tabela-responsiva">
    <table class="tabela-condux">
      <thead>
        <tr><th>Competência</th><th>Vencimento</th><th>Valor</th><th>Pago em</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($extrato as $linha): ?>
        <?php
          [$anoC, $mesC] = explode('-', $linha['competencia']);
          $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
          $rotulo = ($meses[(int)$mesC] ?? $mesC) . '/' . $anoC;
        ?>
        <tr>
          <td><?= $rotulo ?></td>
          <td><?= dataBR($linha['vencimento']) ?></td>
          <td><?= dinheiro((float)$linha['valor']) ?></td>
          <td><?= dataBR($linha['data_pagamento']) ?></td>
          <td><span class="badge-status <?= $linha['status'] ?>"><?= ucfirst($linha['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
