<?php
/** @var Projeto $projeto @var bool $ehAdmin @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = $projeto->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina" style="font-size:1.15rem;"><?= htmlspecialchars($projeto->nome) ?></h1>
  <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
    <a href="<?= url("projetos/{$projeto->id}/editar") ?>" class="botao-secundario">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('projetos') ?>" class="botao-secundario">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<?php if ($mensagem): ?>
  <div class="alerta-flash sucesso"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alerta-flash erro"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erroMensagem) ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; margin-bottom:1.25rem;" class="grade-formulario">

  <!-- Informações principais -->
  <div class="card-conteudo">
    <h2 class="titulo-card">Informações do projeto</h2>
    <table style="width:100%; font-size:.9rem; border-collapse:collapse;">
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280; width:140px;">Status</td>
        <td style="padding:.5rem;">
          <span class="badge-status <?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span>
        </td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Idealizador</td>
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
        <td style="padding:.5rem; color:#6b7280;">Valor estimado</td>
        <td style="padding:.5rem;"><?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Valor realizado</td>
        <td style="padding:.5rem;"><?= $projeto->valorRealizado ? dinheiro($projeto->valorRealizado) : '—' ?></td>
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

    <?php if ($projeto->descricao): ?>
      <div style="margin-top:1rem; padding:1rem; background:#f8fafc; border-radius:6px; font-size:.9rem; line-height:1.6;">
        <?= nl2br(htmlspecialchars($projeto->descricao)) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Atualizar status -->
  <div class="card-conteudo">
    <h2 class="titulo-card">Atualizar status</h2>
    <form action="<?= url("projetos/{$projeto->id}/status") ?>" method="POST">
      <div class="campo-formulario" style="margin-bottom:1rem;">
        <label for="campo-novo-status">Novo status</label>
        <select id="campo-novo-status" name="status">
          <?php foreach (Projeto::$rotulosStatus as $chave => $rotulo): ?>
            <option value="<?= $chave ?>" <?= $projeto->status === $chave ? 'selected' : '' ?>>
              <?= htmlspecialchars($rotulo) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="botao-primario" style="width:100%; justify-content:center;">
        <i class="bi bi-arrow-repeat"></i> Atualizar
      </button>
    </form>
  </div>

</div>

<!-- Anexos -->
<div class="card-conteudo">
  <div style="display:flex; justify-content:space-between; align-items:center;" class="titulo-card">
    <span><i class="bi bi-paperclip"></i> Anexos</span>
  </div>

  <!-- Formulário de upload -->
  <form action="<?= url("projetos/{$projeto->id}/anexos") ?>" method="POST"
        enctype="multipart/form-data"
        style="display:flex; gap:.75rem; align-items:flex-end; margin-bottom:1.25rem; flex-wrap:wrap;">
    <div class="campo-formulario" style="margin:0; flex:1; min-width:160px;">
      <label for="campo-tipo-anexo">Tipo</label>
      <select id="campo-tipo-anexo" name="tipo" required>
        <option value="foto">Foto</option>
        <option value="video">Vídeo</option>
        <option value="nota_fiscal">Nota fiscal</option>
        <option value="documento">Documento</option>
      </select>
    </div>
    <div class="campo-formulario" style="margin:0; flex:2; min-width:200px;">
      <label for="campo-arquivo-anexo">Arquivo</label>
      <input type="file" id="campo-arquivo-anexo" name="arquivo" required>
    </div>
    <button type="submit" class="botao-secundario" style="white-space:nowrap;">
      <i class="bi bi-upload"></i> Enviar
    </button>
  </form>

  <!-- Lista de anexos -->
  <?php if (empty($projeto->anexos)): ?>
    <p style="color:#6b7280; font-size:.9rem;">Nenhum anexo adicionado.</p>
  <?php else: ?>
    <div class="grade-anexos">
      <?php foreach ($projeto->anexos as $anexo): ?>
      <div class="item-anexo">
        <?php if ($anexo['tipo'] === 'foto'): ?>
          <a href="<?= url('uploads/' . $anexo['caminho']) ?>" target="_blank">
            <img src="<?= url('uploads/' . $anexo['caminho']) ?>"
                 alt="<?= htmlspecialchars($anexo['nome_original']) ?>">
          </a>
        <?php else: ?>
          <div style="height:100px; display:flex; align-items:center; justify-content:center; background:#f8fafc;">
            <i class="bi bi-<?= match($anexo['tipo']) {
              'video'       => 'play-circle',
              'nota_fiscal' => 'receipt',
              default       => 'file-earmark-text',
            } ?>" style="font-size:2rem; color:#9ca3af;"></i>
          </div>
          <a href="<?= url('uploads/' . $anexo['caminho']) ?>" target="_blank"
             style="display:block; padding:.4rem .5rem; font-size:.75rem; color:var(--cor-secundaria); text-align:center;">
            Abrir arquivo
          </a>
        <?php endif; ?>
        <div class="legenda-anexo">
          <?= htmlspecialchars($anexo['nome_original']) ?>
          <br>
          <a href="<?= url("projetos/{$projeto->id}/anexos/{$anexo['id']}/remover") ?>"
             onclick="return confirm('Remover este anexo?')"
             style="color:var(--cor-perigo); font-size:.7rem;">
            <i class="bi bi-trash"></i> Remover
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
