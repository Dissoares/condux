<?php
/** @var Projeto $projeto */
$tituloPagina = $projeto->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina" style="font-size:1.15rem;"><?= htmlspecialchars($projeto->nome) ?></h1>
  <a href="<?= url('transparencia') ?>" class="botao-secundario">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; margin-bottom:1.25rem;" class="grade-formulario">

  <div class="card-conteudo">
    <h2 class="titulo-card">Sobre o projeto</h2>

    <?php if ($projeto->descricao): ?>
      <p style="font-size:.95rem; line-height:1.7; color:#374151; margin-bottom:1.25rem;">
        <?= nl2br(htmlspecialchars($projeto->descricao)) ?>
      </p>
    <?php endif; ?>

    <table style="width:100%; font-size:.875rem; border-collapse:collapse;">
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280; width:130px;">Idealizador</td>
        <td style="padding:.5rem;"><?= htmlspecialchars($projeto->idealizador ?? '—') ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Responsável</td>
        <td style="padding:.5rem;"><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Prestadora</td>
        <td style="padding:.5rem;"><?= htmlspecialchars($projeto->nomePrestadora ?? '—') ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Início</td>
        <td style="padding:.5rem;"><?= dataBR($projeto->dataInicio) ?></td>
      </tr>
      <tr>
        <td style="padding:.5rem; color:#6b7280;">Conclusão</td>
        <td style="padding:.5rem;"><?= dataBR($projeto->dataConclusao) ?></td>
      </tr>
    </table>
  </div>

  <div>
    <!-- Status e valores -->
    <div class="card-conteudo" style="margin-bottom:1rem;">
      <h2 class="titulo-card">Status</h2>
      <span class="badge-status <?= $projeto->status ?>" style="font-size:.9rem; padding:.4rem 1rem;">
        <?= htmlspecialchars($projeto->rotuloStatus()) ?>
      </span>
    </div>

    <div class="card-conteudo">
      <h2 class="titulo-card">Financeiro</h2>
      <div style="margin-bottom:.5rem;">
        <div style="font-size:.78rem; color:#6b7280;">Valor estimado</div>
        <div style="font-size:1.1rem; font-weight:600;">
          <?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?>
        </div>
      </div>
      <div>
        <div style="font-size:.78rem; color:#6b7280;">Valor realizado</div>
        <div style="font-size:1.1rem; font-weight:600;">
          <?= $projeto->valorRealizado ? dinheiro($projeto->valorRealizado) : '—' ?>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Fotos e documentos públicos -->
<?php
$fotos      = array_filter($projeto->anexos, fn($a) => $a['tipo'] === 'foto');
$documentos = array_filter($projeto->anexos, fn($a) => in_array($a['tipo'], ['nota_fiscal', 'documento']));
?>

<?php if (!empty($fotos)): ?>
<div class="card-conteudo" style="margin-bottom:1.25rem;">
  <h2 class="titulo-card"><i class="bi bi-images"></i> Fotos</h2>
  <div class="grade-anexos">
    <?php foreach ($fotos as $foto): ?>
    <div class="item-anexo">
      <a href="<?= url('uploads/' . $foto['caminho']) ?>" target="_blank">
        <img src="<?= url('uploads/' . $foto['caminho']) ?>"
             alt="<?= htmlspecialchars($foto['nome_original']) ?>">
      </a>
      <div class="legenda-anexo"><?= htmlspecialchars($foto['nome_original']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($documentos)): ?>
<div class="card-conteudo">
  <h2 class="titulo-card"><i class="bi bi-file-earmark-text"></i> Documentos e Notas Fiscais</h2>
  <div style="display:flex; flex-direction:column; gap:.5rem;">
    <?php foreach ($documentos as $doc): ?>
    <a href="<?= url('uploads/' . $doc['caminho']) ?>" target="_blank"
       class="botao-secundario" style="justify-content:flex-start;">
      <i class="bi bi-<?= $doc['tipo'] === 'nota_fiscal' ? 'receipt' : 'file-earmark-text' ?>"></i>
      <?= htmlspecialchars($doc['nome_original']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
