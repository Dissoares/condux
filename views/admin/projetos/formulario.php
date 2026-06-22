<?php
/** @var Projeto|null $projeto @var Usuario[] $responsaveis */
$editando     = $projeto !== null;
$tituloPagina = $editando ? 'Editar Projeto' : 'Novo Projeto';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0">
    <i class="bi bi-kanban"></i> <?= $editando ? 'Editar Projeto' : 'Novo Projeto' ?>
  </h4>
  <a href="<?= url('projetos') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div class="card border-0 shadow-sm mb-4" style="max-width:680px;">
  <div class="card-body">
    <form action="<?= url('projetos/salvar') ?>" method="POST">
      <?php if ($editando): ?>
        <?php $pid = (int)$projeto->id; ?>
        <input type="hidden" name="id" value="<?= $pid ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label for="campo-nome-projeto" class="form-label">Nome do projeto *</label>
        <input type="text" id="campo-nome-projeto" name="nome" class="form-control" required
               placeholder="Ex: Reforma da garagem"
               value="<?= htmlspecialchars($projeto->nome ?? '') ?>">
      </div>

      <div class="mb-3">
        <label for="campo-descricao-projeto" class="form-label">Descrição</label>
        <textarea id="campo-descricao-projeto" name="descricao" class="form-control" rows="4"
                  placeholder="Descreva o projeto, objetivos e escopo..."><?= htmlspecialchars($projeto->descricao ?? '') ?></textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label for="campo-idealizador" class="form-label">Idealizador</label>
          <input type="text" id="campo-idealizador" name="idealizador" class="form-control"
                 placeholder="Nome do morador ou conselho"
                 value="<?= htmlspecialchars($projeto->idealizador ?? '') ?>">
        </div>
        <div class="col-6">
          <label for="campo-responsavel" class="form-label">Responsável</label>
          <select id="campo-responsavel" name="responsavel_id" class="form-select">
            <option value="">— Selecione —</option>
            <?php foreach ($responsaveis as $resp): ?>
              <option value="<?= $resp->id ?>"
                <?= ($projeto->responsavelId ?? null) == $resp->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($resp->nome) ?> (<?= $resp->perfil ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label for="campo-valor-estimado" class="form-label">Valor estimado (R$)</label>
          <input type="text" id="campo-valor-estimado" name="valor_estimado" class="form-control"
                 placeholder="0,00"
                 value="<?= $projeto->valorEstimado ? number_format($projeto->valorEstimado, 2, ',', '.') : '' ?>">
        </div>
        <div class="col-6">
          <label for="campo-valor-realizado" class="form-label">Valor realizado (R$)</label>
          <input type="text" id="campo-valor-realizado" name="valor_realizado" class="form-control"
                 placeholder="0,00"
                 value="<?= $projeto->valorRealizado ? number_format($projeto->valorRealizado, 2, ',', '.') : '' ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label for="campo-data-inicio" class="form-label">Data de início</label>
          <input type="date" id="campo-data-inicio" name="data_inicio" class="form-control"
                 value="<?= htmlspecialchars($projeto->dataInicio ?? '') ?>">
        </div>
        <div class="col-6">
          <label for="campo-data-conclusao" class="form-label">Previsão de conclusão</label>
          <input type="date" id="campo-data-conclusao" name="data_conclusao" class="form-control"
                 value="<?= htmlspecialchars($projeto->dataConclusao ?? '') ?>">
        </div>
      </div>

      <div class="mb-4">
        <label for="campo-status-projeto" class="form-label">Status</label>
        <select id="campo-status-projeto" name="status" class="form-select">
          <?php foreach (Projeto::$rotulosStatus as $chave => $rotulo): ?>
            <option value="<?= $chave ?>"
              <?= ($projeto->status ?? 'pendente') === $chave ? 'selected' : '' ?>>
              <?= htmlspecialchars($rotulo) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
        <?= $editando ? 'Salvar alterações' : 'Criar projeto' ?>
      </button>
    </form>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
