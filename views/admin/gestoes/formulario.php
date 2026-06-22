<?php
/** @var Gestao|null $gestao @var array[] $usuarios */
$editando     = $gestao !== null;
$tituloPagina = $editando ? 'Editar Gestão' : 'Nova Gestão';
require_once RAIZ . '/views/layouts/cabecalho.php';

$cargosOrdem = ['sindico', 'subsindico', 'conselheiro', 'suplente'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0">
      <i class="bi bi-person-badge text-primary"></i>
      <?= $editando ? 'Editar Gestão' : 'Nova Gestão' ?>
    </h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      Defina o período e os membros desta administração.
    </p>
  </div>
  <a href="<?= $editando ? url("gestoes/{$gestao->id}") : url('gestoes') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<form action="<?= url('gestoes/salvar') ?>" method="POST" id="form-gestao">
  <?php if ($editando): ?>
    <input type="hidden" name="id" value="<?= (int)$gestao->id ?>">
  <?php endif; ?>

  <div class="row g-4">

    <!-- Coluna principal: dados gerais -->
    <div class="col-lg-5">

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-info-circle"></i></span>
          <span class="fw-semibold">Dados da gestão</span>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="campo-descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
            <input type="text" id="campo-descricao" name="descricao" class="form-control" required
                   placeholder="Ex: Gestão 2024–2026, Mandato triênio 2023..."
                   value="<?= htmlspecialchars($gestao?->descricao ?? '') ?>">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="campo-inicio" class="form-label">Início <span class="text-danger">*</span></label>
              <input type="date" id="campo-inicio" name="inicio" class="form-control" required
                     value="<?= htmlspecialchars($gestao?->inicio ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label for="campo-fim" class="form-label">Término</label>
              <input type="date" id="campo-fim" name="fim" class="form-control"
                     value="<?= htmlspecialchars($gestao?->fim ?? '') ?>">
              <div class="form-text">Deixe em branco se ainda vigente.</div>
            </div>
          </div>

          <?php if ($editando): ?>
          <div class="mb-3">
            <label for="campo-status" class="form-label">Status</label>
            <select id="campo-status" name="status" class="form-select">
              <option value="ativa"     <?= $gestao->status === 'ativa'     ? 'selected' : '' ?>>Ativa</option>
              <option value="encerrada" <?= $gestao->status === 'encerrada' ? 'selected' : '' ?>>Encerrada</option>
            </select>
          </div>
          <?php else: ?>
            <input type="hidden" name="status" value="ativa">
          <?php endif; ?>

          <div>
            <label for="campo-obs" class="form-label">Observações</label>
            <textarea id="campo-obs" name="observacoes" class="form-control" rows="3"
                      placeholder="Ata de eleição, observações do mandato..."><?= htmlspecialchars($gestao?->observacoes ?? '') ?></textarea>
          </div>
        </div>
      </div>

    </div>

    <!-- Coluna membros -->
    <div class="col-lg-7">

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-success bg-opacity-10 text-success"><i class="bi bi-people"></i></span>
          <span class="fw-semibold">Membros da gestão</span>
          <button type="button" class="btn btn-outline-primary btn-sm ms-auto" id="btn-add-membro">
            <i class="bi bi-plus-lg"></i> Adicionar membro
          </button>
        </div>
        <div class="card-body">

          <div id="lista-membros" class="d-flex flex-column gap-2">
            <?php
            $membrosExistentes = $editando ? $gestao->membros : [];
            $indiceMembro = 0;
            ?>
            <?php if (!empty($membrosExistentes)): ?>
              <?php foreach ($membrosExistentes as $m): ?>
              <div class="linha-membro d-flex align-items-center gap-2">
                <select name="membro_usuario[]" class="form-select select-membro" required>
                  <option value="">— Selecione —</option>
                  <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u->id ?>" <?= $u->id == $m['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($u->nome) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <select name="membro_cargo[]" class="form-select select-cargo" style="width:160px;min-width:160px;">
                  <?php foreach ($cargosOrdem as $c): ?>
                    <option value="<?= $c ?>" <?= $m['cargo'] === $c ? 'selected' : '' ?>>
                      <?= Gestao::$cargosRotulo[$c] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-danger btn-sm btn-remover flex-shrink-0">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Linha vazia inicial -->
              <div class="linha-membro d-flex align-items-center gap-2">
                <select name="membro_usuario[]" class="form-select select-membro">
                  <option value="">— Selecione o membro —</option>
                  <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u->id ?>"><?= htmlspecialchars($u->nome) ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="membro_cargo[]" class="form-select select-cargo" style="width:160px;min-width:160px;">
                  <?php foreach ($cargosOrdem as $c): ?>
                    <option value="<?= $c ?>"><?= Gestao::$cargosRotulo[$c] ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-danger btn-sm btn-remover flex-shrink-0">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            <?php endif; ?>
          </div>

          <p class="text-body-secondary mb-0 mt-3" style="font-size:.8rem;">
            <i class="bi bi-info-circle me-1"></i>
            Adicione um membro por linha. O mesmo usuário não pode aparecer duas vezes na mesma gestão.
          </p>

        </div>
      </div>

    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary px-4">
      <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
      <?= $editando ? 'Salvar alterações' : 'Criar gestão' ?>
    </button>
    <a href="<?= $editando ? url("gestoes/{$gestao->id}") : url('gestoes') ?>"
       class="btn btn-outline-secondary">Cancelar</a>
  </div>
</form>

<!-- Template de linha de membro (hidden) -->
<template id="tpl-membro">
  <div class="linha-membro d-flex align-items-center gap-2">
    <select name="membro_usuario[]" class="form-select select-membro">
      <option value="">— Selecione o membro —</option>
      <?php foreach ($usuarios as $u): ?>
        <option value="<?= $u->id ?>"><?= htmlspecialchars($u->nome) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="membro_cargo[]" class="form-select select-cargo" style="width:160px;min-width:160px;">
      <?php foreach ($cargosOrdem as $c): ?>
        <option value="<?= $c ?>"><?= Gestao::$cargosRotulo[$c] ?></option>
      <?php endforeach; ?>
    </select>
    <button type="button" class="btn btn-outline-danger btn-sm btn-remover flex-shrink-0">
      <i class="bi bi-trash3"></i>
    </button>
  </div>
</template>

<script>
(function () {
  const lista  = document.getElementById('lista-membros');
  const tpl    = document.getElementById('tpl-membro');
  const btnAdd = document.getElementById('btn-add-membro');

  function bindRemover(linha) {
    linha.querySelector('.btn-remover').addEventListener('click', () => linha.remove());
  }

  lista.querySelectorAll('.linha-membro').forEach(bindRemover);

  btnAdd.addEventListener('click', () => {
    const clone = tpl.content.cloneNode(true);
    const div   = clone.querySelector('.linha-membro');
    bindRemover(div);
    lista.appendChild(div);
    div.querySelector('select').focus();
  });
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
