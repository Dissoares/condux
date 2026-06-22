<?php
/** @var array $resumoFinanceiro @var int $totalInadimplentes @var Projeto[] $projetosRecentes */
$tituloPagina = 'Painel';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina">Painel — <?= date('m/Y') ?></h1>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:1.75rem;" class="grade-resumo-mobile">
  <div class="card-resumo">
    <div class="icone-resumo verde"><i class="bi bi-check-circle-fill"></i></div>
    <div>
      <div class="valor-resumo"><?= (int)($resumoFinanceiro['total_pagas'] ?? 0) ?></div>
      <div class="rotulo-resumo">Taxas pagas no mês</div>
    </div>
  </div>
  <div class="card-resumo">
    <div class="icone-resumo amarelo"><i class="bi bi-clock-fill"></i></div>
    <div>
      <div class="valor-resumo"><?= (int)($resumoFinanceiro['total_pendentes'] ?? 0) ?></div>
      <div class="rotulo-resumo">Pendentes / vencidas</div>
    </div>
  </div>
  <div class="card-resumo">
    <div class="icone-resumo vermelho"><i class="bi bi-exclamation-triangle-fill"></i></div>
    <div>
      <div class="valor-resumo"><?= $totalInadimplentes ?></div>
      <div class="rotulo-resumo">Unidades inadimplentes</div>
    </div>
  </div>
  <div class="card-resumo">
    <div class="icone-resumo azul"><i class="bi bi-cash"></i></div>
    <div>
      <div class="valor-resumo"><?= dinheiro((float)($resumoFinanceiro['valor_arrecadado'] ?? 0)) ?></div>
      <div class="rotulo-resumo">Arrecadado no mês</div>
    </div>
  </div>
</div>

<div class="card-conteudo">
  <div style="display:flex; align-items:center; justify-content:space-between;" class="titulo-card">
    <span>Projetos recentes</span>
    <a href="<?= url('projetos') ?>" class="botao-secundario" style="font-size:.8rem; padding:.3rem .75rem;">Ver todos</a>
  </div>

  <?php if (empty($projetosRecentes)): ?>
    <p style="color:#6b7280; font-size:.9rem;">Nenhum projeto cadastrado.</p>
  <?php else: ?>
    <div class="tabela-responsiva">
    <table class="tabela-condux">
      <thead>
        <tr><th>Projeto</th><th>Responsável</th><th>Valor estimado</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($projetosRecentes as $projeto): ?>
        <tr>
          <td><?= htmlspecialchars($projeto->nome) ?></td>
          <td><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td>
          <td><?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?></td>
          <td><span class="badge-status <?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span></td>
          <td>
            <a href="<?= url("projetos/{$projeto->id}") ?>" class="botao-secundario" style="font-size:.78rem; padding:.25rem .6rem;">Ver</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
