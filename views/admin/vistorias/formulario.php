<?php
/** @var Vistoria|null $vistoria @var array[] $responsaveis @var Unidade[] $unidades @var array[] $prestadoras */
$editando     = $vistoria !== null;
$tituloPagina = $editando ? 'Editar Vistoria' : 'Nova Vistoria';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0">
      <i class="bi bi-clipboard-check text-primary"></i>
      <?= $editando ? 'Editar Vistoria' : 'Nova Vistoria' ?>
    </h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      Agende ou registre uma inspeção, laudo ou visita técnica.
    </p>
  </div>
  <a href="<?= $editando ? url("vistorias/{$vistoria->id}") : url('vistorias') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<form action="<?= url('vistorias/salvar') ?>" method="POST">
  <?php if ($editando): ?>
    <?php $vid = (int)$vistoria->id; ?>
    <input type="hidden" name="id" value="<?= $vid ?>">
  <?php endif; ?>

  <div class="row g-4">

    <!-- Coluna principal -->
    <div class="col-lg-7">

      <!-- Tipo e categoria -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-tags"></i></span>
          <span class="fw-semibold">Tipo de vistoria</span>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Categoria <span class="text-danger">*</span></label>
            <div class="row g-2" id="grid-tipos">
              <?php foreach (Vistoria::$tiposRotulo as $k => $r): ?>
              <?php $sel = ($vistoria?->tipo ?? 'predial') === $k; ?>
              <div class="col-6 col-sm-4">
                <input type="radio" class="btn-check" name="tipo" id="tipo-<?= $k ?>" value="<?= $k ?>"
                       <?= $sel ? 'checked' : '' ?> required>
                <label class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-1 py-3"
                       for="tipo-<?= $k ?>" style="font-size:.8rem; font-weight:600;">
                  <i class="bi <?= Vistoria::$tiposIcone[$k] ?>" style="font-size:1.4rem;"></i>
                  <?= $r ?>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div>
            <label for="campo-categoria" class="form-label">Especificação</label>
            <input type="text" id="campo-categoria" name="categoria" class="form-control"
                   placeholder="Ex: AVCB, Laudo estrutural, Manutenção preventiva..."
                   value="<?= htmlspecialchars($vistoria?->categoria ?? '') ?>">
            <div class="form-text">Detalhe o tipo de documento ou visita.</div>
          </div>
        </div>
      </div>

      <!-- Data e status -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-calendar-event"></i></span>
          <span class="fw-semibold">Datas e status</span>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="campo-data" class="form-label">Data da vistoria <span class="text-danger">*</span></label>
              <input type="date" id="campo-data" name="data_vistoria" class="form-control"
                     value="<?= htmlspecialchars($vistoria?->dataVistoria ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="col-sm-6">
              <label for="campo-status" class="form-label">Status</label>
              <select id="campo-status" name="status" class="form-select">
                <option value="agendada"  <?= ($vistoria?->status ?? 'agendada') === 'agendada'  ? 'selected' : '' ?>>Agendada</option>
                <option value="realizada" <?= ($vistoria?->status ?? '') === 'realizada' ? 'selected' : '' ?>>Realizada</option>
                <option value="cancelada" <?= ($vistoria?->status ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
              </select>
            </div>
          </div>

          <div id="secao-resultado" style="display:<?= ($vistoria?->status ?? '') === 'realizada' ? '' : 'none' ?>;">
            <div class="row g-3">
              <div class="col-sm-6">
                <label for="campo-resultado" class="form-label">Resultado</label>
                <select id="campo-resultado" name="resultado" class="form-select">
                  <option value="">— Selecione —</option>
                  <?php foreach (Vistoria::$resultadosRotulo as $k => $r): ?>
                    <option value="<?= $k ?>" <?= ($vistoria?->resultado ?? '') === $k ? 'selected' : '' ?>><?= $r ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-6">
                <label for="campo-validade" class="form-label">Validade do documento</label>
                <input type="date" id="campo-validade" name="validade" class="form-control"
                       value="<?= htmlspecialchars($vistoria?->validade ?? '') ?>">
                <div class="form-text">Quando o laudo/alvará vence.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Observações -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-chat-left-text"></i></span>
          <span class="fw-semibold">Observações</span>
        </div>
        <div class="card-body">
          <textarea name="descricao" class="form-control" rows="4"
                    placeholder="Descreva o escopo, pendências encontradas, ações necessárias..."><?= htmlspecialchars($vistoria?->descricao ?? '') ?></textarea>
        </div>
      </div>

    </div>

    <!-- Coluna lateral -->
    <div class="col-lg-5">

      <!-- Responsáveis -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-success bg-opacity-10 text-success"><i class="bi bi-people"></i></span>
          <span class="fw-semibold">Responsáveis</span>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="campo-responsavel" class="form-label">Responsável interno</label>
            <select id="campo-responsavel" name="responsavel_id" class="form-select">
              <option value="">— Nenhum —</option>
              <?php foreach ($responsaveis as $r): ?>
                <option value="<?= $r->id ?>" <?= ($vistoria?->responsavelId ?? null) == $r->id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($r->nome) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (!empty($prestadoras)): ?>
          <div>
            <label for="campo-prestadora" class="form-label">Empresa / Prestadora</label>
            <select id="campo-prestadora" name="prestadora_id" class="form-select">
              <option value="">— Nenhuma —</option>
              <?php foreach ($prestadoras as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($vistoria?->prestadoraId ?? null) == $p['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Documento -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-indigo bg-opacity-10" style="color:#6366f1;background:rgba(99,102,241,.1);">
            <i class="bi bi-file-earmark-text"></i>
          </span>
          <span class="fw-semibold">Documento</span>
        </div>
        <div class="card-body">
          <label for="campo-num-doc" class="form-label">Nº do documento / alvará</label>
          <input type="text" id="campo-num-doc" name="numero_documento" class="form-control"
                 placeholder="Ex: AVCB 2024/001234"
                 value="<?= htmlspecialchars($vistoria?->numeroDocumento ?? '') ?>">
        </div>
      </div>

      <!-- Unidade (opcional) -->
      <?php if (!empty($unidades)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-info bg-opacity-10 text-info"><i class="bi bi-building"></i></span>
          <span class="fw-semibold">Unidade específica</span>
        </div>
        <div class="card-body">
          <label for="campo-unidade" class="form-label">Unidade</label>
          <select id="campo-unidade" name="unidade_id" class="form-select">
            <option value="">— Todo o condomínio —</option>
            <?php foreach ($unidades as $u): ?>
              <option value="<?= $u->id ?>" <?= ($vistoria?->unidadeId ?? null) == $u->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($u->identificacao()) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Deixe em branco para vistorias gerais do prédio.</div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary px-4">
      <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
      <?= $editando ? 'Salvar alterações' : 'Agendar vistoria' ?>
    </button>
    <a href="<?= $editando ? url("vistorias/{$vistoria->id}") : url('vistorias') ?>"
       class="btn btn-outline-secondary">Cancelar</a>
  </div>
</form>

<script>
(function () {
  document.getElementById('campo-status').addEventListener('change', function () {
    document.getElementById('secao-resultado').style.display =
      this.value === 'realizada' ? '' : 'none';
  });
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
