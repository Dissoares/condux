<?php
/** @var Unidade|null $unidade @var TaxaCondominial|null $taxaMesAtual @var TaxaCondominial[] $taxasPendentes */
$tituloPagina = 'Meu Painel';
require_once RAIZ . '/views/layouts/cabecalho.php';
$primeiroNome = explode(' ', Sessao::usuarioAtual()['nome'] ?? 'Morador')[0];
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina">Olá, <?= htmlspecialchars($primeiroNome) ?>!</h1>
</div>

<?php if ($unidade === null): ?>
  <div class="alerta-flash aviso">
    <i class="bi bi-info-circle-fill"></i>
    Você ainda não está vinculado a nenhuma unidade. Fale com o síndico.
  </div>
<?php else: ?>

  <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; margin-bottom:1.5rem;" class="grade-resumo-mobile">
    <div class="card-resumo">
      <div class="icone-resumo azul"><i class="bi bi-building"></i></div>
      <div>
        <div class="valor-resumo" style="font-size:1.2rem;"><?= htmlspecialchars($unidade->identificacao()) ?></div>
        <div class="rotulo-resumo">Sua unidade</div>
      </div>
    </div>

    <?php if ($taxaMesAtual): ?>
    <div class="card-resumo">
      <div class="icone-resumo <?= match($taxaMesAtual->status) { 'pago' => 'verde', 'vencido' => 'vermelho', default => 'amarelo' } ?>">
        <i class="bi bi-<?= $taxaMesAtual->estaPago() ? 'check-circle-fill' : 'clock-fill' ?>"></i>
      </div>
      <div>
        <div class="valor-resumo"><?= dinheiro($taxaMesAtual->valor) ?></div>
        <div class="rotulo-resumo">
          Taxa <?= htmlspecialchars($taxaMesAtual->competenciaFormatada()) ?> —
          <span class="badge-status <?= $taxaMesAtual->status ?>"><?= ucfirst($taxaMesAtual->status) ?></span>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($taxaMesAtual && !$taxaMesAtual->estaPago()): ?>
  <div class="card-conteudo" style="max-width:520px; margin-bottom:1.5rem;">
    <h2 class="titulo-card"><i class="bi bi-upload"></i> Enviar comprovante</h2>

    <?php if ($taxaMesAtual->comprovante): ?>
      <div class="alerta-flash aviso">
        <i class="bi bi-hourglass-split"></i> Comprovante enviado. Aguardando aprovação do síndico.
      </div>
    <?php else: ?>
      <form action="<?= url('minhas-taxas/comprovante') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="taxa_id" value="<?= $taxaMesAtual->id ?>">
        <div class="campo-formulario" style="margin-bottom:1rem;">
          <label for="arquivo-comprovante">Comprovante (PDF, JPG ou PNG)</label>
          <input type="file" id="arquivo-comprovante" name="comprovante" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        <button type="submit" class="botao-primario"><i class="bi bi-send"></i> Enviar comprovante</button>
      </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($taxasPendentes)): ?>
  <div class="card-conteudo">
    <h2 class="titulo-card"><i class="bi bi-exclamation-triangle"></i> Taxas pendentes</h2>
    <div class="tabela-responsiva">
    <table class="tabela-condux">
      <thead>
        <tr><th>Competência</th><th>Valor</th><th>Vencimento</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($taxasPendentes as $taxa): ?>
        <tr>
          <td><?= htmlspecialchars($taxa->competenciaFormatada()) ?></td>
          <td><?= dinheiro($taxa->valor) ?></td>
          <td><?= dataBR($taxa->vencimento) ?></td>
          <td><span class="badge-status <?= $taxa->status ?>"><?= ucfirst($taxa->status) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
  <?php endif; ?>

<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
