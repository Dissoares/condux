<?php
/** @var Vistoria $vistoria */
$tituloPagina = $vistoria->rotuloTipo();
require_once RAIZ . '/views/layouts/cabecalho.php';

$corStatus = match($vistoria->status) {
    'realizada' => 'success', 'cancelada' => 'secondary', default => 'primary'
};
$alertaValidade = $vistoria->validadeVencida() ? 'danger' : ($vistoria->validadeProxima() ? 'warning' : null);
?>

<div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
  <div>
    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
      <h4 class="fw-semibold mb-0"><?= htmlspecialchars($vistoria->rotuloTipo()) ?></h4>
      <?php if ($vistoria->categoria): ?>
        <span class="text-body-secondary fw-normal">— <?= htmlspecialchars($vistoria->categoria) ?></span>
      <?php endif; ?>
      <span class="badge rounded-pill bg-<?= $corStatus ?> bg-opacity-10 text-<?= $corStatus ?> fw-semibold">
        <?= $vistoria->rotuloStatus() ?>
      </span>
      <?php if ($vistoria->resultado): ?>
        <span class="badge rounded-pill badge-<?= $vistoria->resultado === 'aprovado' ? 'aprovado' : ($vistoria->resultado === 'reprovado' ? 'vencido' : 'pendente') ?>">
          <?= $vistoria->rotuloResultado() ?>
        </span>
      <?php endif; ?>
    </div>
    <p class="text-body-secondary mb-0" style="font-size:.85rem;">
      <i class="bi bi-calendar3 me-1"></i><?= dataBR($vistoria->dataVistoria) ?>
      <?php if ($alertaValidade): ?>
        · <span class="text-<?= $alertaValidade ?> fw-semibold">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Validade <?= $vistoria->validadeVencida() ? 'vencida' : 'vencendo' ?>: <?= dataBR($vistoria->validade) ?>
          </span>
      <?php elseif ($vistoria->validade): ?>
        · <i class="bi bi-clock me-1"></i>Válido até <?= dataBR($vistoria->validade) ?>
      <?php endif; ?>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url("vistorias/{$vistoria->id}/editar") ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('vistorias') ?>" class="btn btn-outline-secondary btn-sm">
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

<div class="row g-4">

  <!-- Coluna principal -->
  <div class="col-lg-7">

    <!-- Detalhes -->
    <div class="card border-0 shadow-sm mb-4 <?= $alertaValidade ? "card-acento-{$alertaValidade}" : '' ?>">
      <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
        <span class="icone-secao bg-primary bg-opacity-10 text-primary">
          <i class="bi <?= $vistoria->icone() ?>"></i>
        </span>
        <span class="fw-semibold">Detalhes da vistoria</span>
      </div>
      <div class="card-body">
        <dl class="row mb-0" style="font-size:.9rem;">
          <dt class="col-sm-4 text-body-secondary fw-normal">Tipo</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($vistoria->rotuloTipo()) ?></dd>

          <?php if ($vistoria->categoria): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Especificação</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($vistoria->categoria) ?></dd>
          <?php endif; ?>

          <dt class="col-sm-4 text-body-secondary fw-normal">Data</dt>
          <dd class="col-sm-8"><?= dataBR($vistoria->dataVistoria) ?></dd>

          <?php if ($vistoria->nomeResponsavel): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Responsável</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($vistoria->nomeResponsavel) ?></dd>
          <?php endif; ?>

          <?php if ($vistoria->nomePrestadora): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Empresa</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($vistoria->nomePrestadora) ?></dd>
          <?php endif; ?>

          <?php if ($vistoria->identificacaoUnidade): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Unidade</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($vistoria->identificacaoUnidade) ?></dd>
          <?php endif; ?>

          <?php if ($vistoria->numeroDocumento): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Nº documento</dt>
          <dd class="col-sm-8"><code><?= htmlspecialchars($vistoria->numeroDocumento) ?></code></dd>
          <?php endif; ?>

          <?php if ($vistoria->validade): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Validade</dt>
          <dd class="col-sm-8 <?= $alertaValidade ? "text-{$alertaValidade} fw-semibold" : '' ?>">
            <?= dataBR($vistoria->validade) ?>
            <?php if ($vistoria->validadeVencida()): ?>
              <span class="badge bg-danger ms-1">Vencida</span>
            <?php elseif ($vistoria->validadeProxima()): ?>
              <span class="badge bg-warning text-dark ms-1">Vencendo</span>
            <?php endif; ?>
          </dd>
          <?php endif; ?>

          <?php if ($vistoria->resultado): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Resultado</dt>
          <dd class="col-sm-8">
            <span class="badge rounded-pill badge-<?= $vistoria->resultado === 'aprovado' ? 'aprovado' : ($vistoria->resultado === 'reprovado' ? 'vencido' : 'pendente') ?>">
              <?= $vistoria->rotuloResultado() ?>
            </span>
          </dd>
          <?php endif; ?>

          <?php if ($vistoria->descricao): ?>
          <dt class="col-sm-4 text-body-secondary fw-normal">Observações</dt>
          <dd class="col-sm-8" style="white-space:pre-line;"><?= htmlspecialchars($vistoria->descricao) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <!-- Anexos -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
        <span class="icone-secao bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-paperclip"></i></span>
        <span class="fw-semibold">Documentos e anexos</span>
        <span class="badge bg-secondary bg-opacity-10 text-body ms-auto"><?= count($vistoria->anexos) ?></span>
      </div>

      <?php if (!empty($vistoria->anexos)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Arquivo</th>
              <th class="d-none d-sm-table-cell">Tipo</th>
              <th class="d-none d-md-table-cell">Enviado</th>
              <th style="width:80px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($vistoria->anexos as $a): ?>
            <tr>
              <td>
                <a href="<?= url('uploads/' . $a['caminho']) ?>" target="_blank"
                   class="fw-semibold text-decoration-none">
                  <i class="bi bi-file-earmark me-1"></i><?= htmlspecialchars($a['nome_original']) ?>
                </a>
              </td>
              <td class="d-none d-sm-table-cell">
                <span class="badge bg-secondary bg-opacity-10 text-body"><?= ucfirst($a['tipo']) ?></span>
              </td>
              <td class="d-none d-md-table-cell text-body-secondary" style="font-size:.82rem;">
                <?= dataBR(substr($a['enviado_em'], 0, 10)) ?>
              </td>
              <td>
                <?php $vid = (int)$vistoria->id; $aid = (int)$a['id']; ?>
                <a href="<?= url("vistorias/{$vid}/anexos/{$aid}/remover") ?>"
                   class="btn btn-outline-danger btn-sm"
                   onclick="return confirm('Remover este anexo?')">
                  <i class="bi bi-trash3"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <div class="card-body <?= !empty($vistoria->anexos) ? 'border-top' : '' ?>">
        <form action="<?= url("vistorias/{$vistoria->id}/anexos") ?>" method="POST" enctype="multipart/form-data">
          <?php $vid = (int)$vistoria->id; ?>
          <input type="hidden" name="vistoria_id" value="<?= $vid ?>">
          <div class="row g-2 align-items-end">
            <div class="col-sm-4">
              <label class="form-label" style="font-size:.8rem;">Tipo do documento</label>
              <select name="tipo_anexo" class="form-select form-select-sm">
                <option value="laudo">Laudo técnico</option>
                <option value="relatorio">Relatório</option>
                <option value="orcamento">Orçamento</option>
                <option value="foto">Foto</option>
                <option value="documento">Outro documento</option>
              </select>
            </div>
            <div class="col-sm-5">
              <label class="form-label" style="font-size:.8rem;">Arquivo</label>
              <input type="file" name="arquivo" class="form-control form-control-sm"
                     accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="col-sm-3">
              <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-upload"></i> Enviar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>

  <!-- Coluna lateral: ações rápidas -->
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
        <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-lightning"></i></span>
        <span class="fw-semibold">Ações rápidas</span>
      </div>
      <div class="card-body d-flex flex-column gap-2">
        <?php if ($vistoria->status === 'agendada'): ?>
          <form method="POST" action="<?= url('vistorias/salvar') ?>">
            <input type="hidden" name="id" value="<?= (int)$vistoria->id ?>">
            <input type="hidden" name="data_vistoria"   value="<?= $vistoria->dataVistoria ?>">
            <input type="hidden" name="tipo"            value="<?= $vistoria->tipo ?>">
            <input type="hidden" name="categoria"       value="<?= htmlspecialchars($vistoria->categoria ?? '') ?>">
            <input type="hidden" name="descricao"       value="<?= htmlspecialchars($vistoria->descricao ?? '') ?>">
            <input type="hidden" name="unidade_id"      value="<?= $vistoria->unidadeId ?? '' ?>">
            <input type="hidden" name="responsavel_id"  value="<?= $vistoria->responsavelId ?? '' ?>">
            <input type="hidden" name="prestadora_id"   value="<?= $vistoria->prestadoraId ?? '' ?>">
            <input type="hidden" name="numero_documento"value="<?= htmlspecialchars($vistoria->numeroDocumento ?? '') ?>">
            <input type="hidden" name="validade"        value="<?= $vistoria->validade ?? '' ?>">
            <input type="hidden" name="resultado"       value="<?= $vistoria->resultado ?? '' ?>">
            <input type="hidden" name="status"          value="realizada">
            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Marcar como realizada?')">
              <i class="bi bi-check-circle"></i> Marcar como realizada
            </button>
          </form>
          <form method="POST" action="<?= url('vistorias/salvar') ?>">
            <input type="hidden" name="id" value="<?= (int)$vistoria->id ?>">
            <input type="hidden" name="data_vistoria"   value="<?= $vistoria->dataVistoria ?>">
            <input type="hidden" name="tipo"            value="<?= $vistoria->tipo ?>">
            <input type="hidden" name="categoria"       value="<?= htmlspecialchars($vistoria->categoria ?? '') ?>">
            <input type="hidden" name="descricao"       value="<?= htmlspecialchars($vistoria->descricao ?? '') ?>">
            <input type="hidden" name="unidade_id"      value="<?= $vistoria->unidadeId ?? '' ?>">
            <input type="hidden" name="responsavel_id"  value="<?= $vistoria->responsavelId ?? '' ?>">
            <input type="hidden" name="prestadora_id"   value="<?= $vistoria->prestadoraId ?? '' ?>">
            <input type="hidden" name="numero_documento"value="<?= htmlspecialchars($vistoria->numeroDocumento ?? '') ?>">
            <input type="hidden" name="validade"        value="<?= $vistoria->validade ?? '' ?>">
            <input type="hidden" name="resultado"       value="<?= $vistoria->resultado ?? '' ?>">
            <input type="hidden" name="status"          value="cancelada">
            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Cancelar esta vistoria?')">
              <i class="bi bi-x-circle"></i> Cancelar vistoria
            </button>
          </form>
        <?php endif; ?>
        <a href="<?= url("vistorias/{$vistoria->id}/editar") ?>" class="btn btn-outline-secondary w-100">
          <i class="bi bi-pencil"></i> Editar dados
        </a>
        <a href="<?= url("vistorias/{$vistoria->id}/excluir") ?>" class="btn btn-outline-danger w-100"
           onclick="return confirm('Excluir permanentemente esta vistoria?')">
          <i class="bi bi-trash3"></i> Excluir
        </a>
      </div>
    </div>
  </div>

</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
