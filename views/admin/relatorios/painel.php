<?php
/**
 * @var int    $ano
 * @var int[]  $anos
 * @var array  $mensalidade      — linhas: competencia, total_cobrado, total_pago, total_unidades, total_pagas, total_inadimplentes
 * @var array  $inadimplentes    — linhas: unidade, responsavel, valor, vencimento, dias_atraso
 * @var string $competenciaAtual
 */
$tituloPagina = 'Relatórios';
require_once RAIZ . '/views/layouts/cabecalho.php';

$totalArrecadado = array_sum(array_column($mensalidade, 'total_pago'));
$totalCobrado    = array_sum(array_column($mensalidade, 'total_cobrado'));
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-bar-chart-line"></i> Relatórios</h1>

  <!-- Filtro de ano -->
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

<!-- Cards de totais -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:1rem; margin-bottom:1.5rem;">
  <div class="card-resumo">
    <div class="rotulo-resumo">Cobrado em <?= $ano ?></div>
    <div class="valor-resumo"><?= dinheiro($totalCobrado) ?></div>
  </div>
  <div class="card-resumo">
    <div class="rotulo-resumo">Arrecadado em <?= $ano ?></div>
    <div class="valor-resumo" style="color:var(--cor-sucesso);"><?= dinheiro($totalArrecadado) ?></div>
  </div>
  <div class="card-resumo">
    <div class="rotulo-resumo">Em aberto em <?= $ano ?></div>
    <div class="valor-resumo" style="color:var(--cor-perigo);"><?= dinheiro($totalCobrado - $totalArrecadado) ?></div>
  </div>
  <div class="card-resumo">
    <div class="rotulo-resumo">Taxa de adimplência</div>
    <div class="valor-resumo">
      <?= $totalCobrado > 0 ? number_format(($totalArrecadado / $totalCobrado) * 100, 1) : '0,0' ?>%
    </div>
  </div>
</div>

<!-- Arrecadação mensal -->
<div class="card-conteudo" style="margin-bottom:1.5rem;">
  <h2 class="titulo-card"><i class="bi bi-calendar3"></i> Arrecadação mensal — <?= $ano ?></h2>

  <?php if (empty($mensalidade)): ?>
    <p style="color:#6b7280; font-size:.9rem;">Nenhuma taxa cadastrada para <?= $ano ?>.</p>
  <?php else: ?>
    <div class="tabela-responsiva">
    <table class="tabela-condux">
      <thead>
        <tr>
          <th>Competência</th>
          <th>Unidades</th>
          <th>Pagas</th>
          <th>Inadimplentes</th>
          <th>Cobrado</th>
          <th>Arrecadado</th>
          <th>%</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mensalidade as $linha): ?>
        <?php
          $pct = $linha['total_cobrado'] > 0
              ? round(($linha['total_pago'] / $linha['total_cobrado']) * 100)
              : 0;
          [$anoC, $mesC] = explode('-', $linha['competencia']);
          $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
          $rotulo = ($meses[(int)$mesC] ?? $mesC) . '/' . $anoC;
        ?>
        <tr>
          <td><?= $rotulo ?></td>
          <td><?= $linha['total_unidades'] ?></td>
          <td><span class="badge-status pago"><?= $linha['total_pagas'] ?></span></td>
          <td>
            <?php if ($linha['total_inadimplentes'] > 0): ?>
              <span class="badge-status vencido"><?= $linha['total_inadimplentes'] ?></span>
            <?php else: ?>
              <span class="badge-status pago">0</span>
            <?php endif; ?>
          </td>
          <td><?= dinheiro((float)$linha['total_cobrado']) ?></td>
          <td><?= dinheiro((float)$linha['total_pago']) ?></td>
          <td>
            <div style="display:flex; align-items:center; gap:.4rem;">
              <div style="flex:1; height:6px; background:#e5e7eb; border-radius:3px; min-width:50px;">
                <div style="width:<?= $pct ?>%; height:100%; background:var(--cor-sucesso); border-radius:3px;"></div>
              </div>
              <span style="font-size:.8rem; color:#6b7280; white-space:nowrap;"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<!-- Inadimplentes do mês atual -->
<div class="card-conteudo">
  <h2 class="titulo-card"><i class="bi bi-exclamation-triangle"></i> Inadimplentes — competência atual</h2>

  <?php if (empty($inadimplentes)): ?>
    <p style="color:var(--cor-sucesso); font-size:.9rem;">
      <i class="bi bi-check-circle-fill"></i> Nenhuma inadimplência na competência atual.
    </p>
  <?php else: ?>
    <div class="tabela-responsiva">
    <table class="tabela-condux">
      <thead>
        <tr><th>Unidade</th><th>Responsável</th><th>Valor</th><th>Vencimento</th><th>Atraso</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($inadimplentes as $linha): ?>
        <tr>
          <td><?= htmlspecialchars($linha['unidade']) ?></td>
          <td><?= htmlspecialchars($linha['responsavel'] ?? '—') ?></td>
          <td><?= dinheiro((float)$linha['valor']) ?></td>
          <td><?= dataBR($linha['vencimento']) ?></td>
          <td>
            <?php if ($linha['dias_atraso'] > 0): ?>
              <span style="color:var(--cor-perigo); font-size:.85rem;"><?= $linha['dias_atraso'] ?> dias</span>
            <?php else: ?>
              <span style="color:#6b7280; font-size:.85rem;">No prazo</span>
            <?php endif; ?>
          </td>
          <td><span class="badge-status <?= $linha['status'] ?>"><?= ucfirst($linha['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
