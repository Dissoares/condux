<?php
/** @var Projeto $projeto @var bool $ehAdmin @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = $projeto->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><?= htmlspecialchars($projeto->nome) ?></h4>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url("projetos/{$projeto->id}/editar") ?>" class="btn btn-outline-secondary">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('projetos') ?>" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
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

<div class="row g-4 mb-4">

  <!-- Informações principais -->
  <div class="col-md-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-semibold border-bottom pb-2 mb-3">Informações do projeto</h6>
        <table class="w-100" style="font-size:.9rem; border-collapse:collapse;">
          <tr class="border-bottom"><td class="py-2 text-body-secondary" style="width:140px;">Status</td>
            <td class="py-2"><span class="badge rounded-pill badge-<?= $projeto->status ?>"><?= htmlspecialchars($projeto->rotuloStatus()) ?></span></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Idealizador</td>
            <td class="py-2"><?= htmlspecialchars($projeto->idealizador ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Responsável</td>
            <td class="py-2"><?= htmlspecialchars($projeto->nomeResponsavel ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Prestadora</td>
            <td class="py-2"><?= htmlspecialchars($projeto->nomePrestadora ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Valor estimado</td>
            <td class="py-2"><?= $projeto->valorEstimado ? dinheiro($projeto->valorEstimado) : '—' ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Valor realizado</td>
            <td class="py-2"><?= $projeto->valorRealizado ? dinheiro($projeto->valorRealizado) : '—' ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Início</td>
            <td class="py-2"><?= dataBR($projeto->dataInicio) ?></td></tr>
          <tr><td class="py-2 text-body-secondary">Conclusão</td>
            <td class="py-2"><?= dataBR($projeto->dataConclusao) ?></td></tr>
        </table>

        <?php if ($projeto->descricao): ?>
          <div class="mt-3 p-3 bg-body-tertiary rounded" style="font-size:.9rem; line-height:1.6;">
            <?= nl2br(htmlspecialchars($projeto->descricao)) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Atualizar status -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-semibold border-bottom pb-2 mb-3">Atualizar status</h6>
        <form action="<?= url("projetos/{$projeto->id}/status") ?>" method="POST">
          <div class="mb-3">
            <label for="campo-novo-status" class="form-label">Novo status</label>
            <select id="campo-novo-status" name="status" class="form-select">
              <?php foreach (Projeto::$rotulosStatus as $chave => $rotulo): ?>
                <option value="<?= $chave ?>" <?= $projeto->status === $chave ? 'selected' : '' ?>>
                  <?= htmlspecialchars($rotulo) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-arrow-repeat"></i> Atualizar
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

<!-- Anexos -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-paperclip"></i> Anexos
  </div>
  <div class="card-body">
    <form action="<?= url("projetos/{$projeto->id}/anexos") ?>" method="POST"
          enctype="multipart/form-data"
          class="d-flex gap-3 align-items-end mb-4 flex-wrap">
      <div>
        <label for="campo-tipo-anexo" class="form-label">Tipo</label>
        <select id="campo-tipo-anexo" name="tipo" class="form-select" style="width:auto;" required>
          <option value="foto">Foto</option>
          <option value="video">Vídeo</option>
          <option value="nota_fiscal">Nota fiscal</option>
          <option value="documento">Documento</option>
        </select>
      </div>
      <div class="flex-grow-1">
        <label for="campo-arquivo-anexo" class="form-label">Arquivo</label>
        <input type="file" id="campo-arquivo-anexo" name="arquivo" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-outline-secondary">
        <i class="bi bi-upload"></i> Enviar
      </button>
    </form>

    <?php if (empty($projeto->anexos)): ?>
      <p class="text-body-secondary mb-0" style="font-size:.9rem;">Nenhum anexo adicionado.</p>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($projeto->anexos as $anexo): ?>
        <div class="col-6 col-md-3">
          <div class="card border h-100">
            <?php if ($anexo['tipo'] === 'foto'): ?>
              <a href="<?= url('uploads/' . $anexo['caminho']) ?>" target="_blank">
                <img src="<?= url('uploads/' . $anexo['caminho']) ?>"
                     class="card-img-top" style="height:110px; object-fit:cover;"
                     alt="<?= htmlspecialchars($anexo['nome_original']) ?>">
              </a>
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center bg-body-secondary" style="height:110px;">
                <i class="bi bi-<?= match($anexo['tipo']) {
                  'video'       => 'play-circle',
                  'nota_fiscal' => 'receipt',
                  default       => 'file-earmark-text',
                } ?>" style="font-size:2rem;"></i>
              </div>
            <?php endif; ?>
            <div class="card-body p-2">
              <div class="text-body-secondary" style="font-size:.75rem; word-break:break-all;">
                <?= htmlspecialchars($anexo['nome_original']) ?>
              </div>
              <div class="d-flex gap-2 mt-1">
                <a href="<?= url('uploads/' . $anexo['caminho']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" style="font-size:.7rem;">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="<?= url("projetos/{$projeto->id}/anexos/{$anexo['id']}/remover") ?>"
                   onclick="return confirm('Remover este anexo?')"
                   class="btn btn-outline-danger btn-sm" style="font-size:.7rem;">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
