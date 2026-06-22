<?php
/** @var Unidade|null $unidade @var array[] $todosCondominios */
$editando     = $unidade !== null;
$tituloPagina = $editando ? 'Editar Unidade' : 'Nova Unidade';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0">
      <i class="bi bi-building text-primary"></i>
      <?= $editando ? 'Editar Unidade' : 'Nova Unidade' ?>
    </h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      <?= $editando ? 'Atualize os dados da unidade.' : 'Preencha os dados para cadastrar uma nova unidade.' ?>
    </p>
  </div>
  <a href="<?= url('unidades') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<form action="<?= url('unidades/salvar') ?>" method="POST">
  <?php if ($editando): ?>
    <input type="hidden" name="id" value="<?= (int)$unidade->id ?>">
  <?php endif; ?>

  <div class="row g-4">

    <!-- Coluna esquerda: Identificação + Ocupação -->
    <div class="col-lg-5">

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-hash"></i></span>
          <span class="fw-semibold">Identificação</span>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label for="campo-numero" class="form-label">Número <span class="text-danger">*</span></label>
              <input type="text" id="campo-numero" name="numero" class="form-control" required
                     placeholder="101" maxlength="20"
                     value="<?= htmlspecialchars($unidade?->numero ?? '') ?>">
            </div>
            <div class="col-6">
              <label for="campo-bloco" class="form-label">Bloco</label>
              <input type="text" id="campo-bloco" name="bloco" class="form-control"
                     placeholder="A" maxlength="10"
                     value="<?= htmlspecialchars($unidade?->bloco ?? '') ?>">
            </div>
          </div>
          <div class="row g-3">
            <div class="col-4">
              <label for="campo-andar" class="form-label">Andar</label>
              <input type="number" id="campo-andar" name="andar" class="form-control"
                     min="0" max="99" placeholder="1"
                     value="<?= htmlspecialchars((string)($unidade?->andar ?? '')) ?>">
            </div>
            <div class="col-8">
              <label for="campo-descricao" class="form-label">Observações</label>
              <input type="text" id="campo-descricao" name="descricao" class="form-control"
                     placeholder="Informações adicionais"
                     value="<?= htmlspecialchars($unidade?->descricao ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-success bg-opacity-10 text-success"><i class="bi bi-house-door"></i></span>
          <span class="fw-semibold">Ocupação</span>
        </div>
        <div class="card-body">
          <label for="campo-tipo-ocupacao" class="form-label">Situação da unidade</label>
          <select id="campo-tipo-ocupacao" name="tipo_ocupacao" class="form-select">
            <option value="proprio" <?= ($unidade?->tipoOcupacao ?? 'proprio') === 'proprio' ? 'selected' : '' ?>>
              Próprio — ocupado pelo proprietário
            </option>
            <option value="alugado" <?= ($unidade?->tipoOcupacao ?? '') === 'alugado' ? 'selected' : '' ?>>
              Alugado — possui inquilino
            </option>
          </select>
        </div>
      </div>

    </div>

    <!-- Coluna direita: Proprietário + Inquilino -->
    <div class="col-lg-7">

      <!-- Proprietário -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-person-badge"></i></span>
          <span class="fw-semibold">Proprietário</span>
        </div>
        <div class="card-body">
          <?php if (empty($todosCondominios)): ?>
            <p class="text-body-secondary mb-1" style="font-size:.875rem;">
              Nenhum condômino cadastrado.
              <a href="<?= url('condominios/novo') ?>">Cadastre o primeiro</a> e volte aqui.
            </p>
          <?php else: ?>
            <div class="condux-select-search mb-1">
              <input type="text" class="form-control form-control-sm condux-search-input mb-2"
                     placeholder="Buscar condômino pelo nome..." autocomplete="off">
              <select name="proprietario_id" class="form-select condux-searchable-select" size="5">
                <option value="">— Nenhum proprietário vinculado —</option>
                <?php foreach ($todosCondominios as $c): ?>
                  <option value="<?= $c['id'] ?>"
                    <?= ($unidade?->proprietarioId ?? null) == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nome']) ?>
                    <?php if (!empty($c['email'])): ?>— <?= htmlspecialchars($c['email']) ?><?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-text">
              Selecione o proprietário da unidade. Para cadastrar um novo condômino,
              <a href="<?= url('condominios/novo') ?>">clique aqui</a>.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Inquilino (visível apenas quando alugado) -->
      <div id="secao-inquilino" class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="icone-secao bg-info bg-opacity-10 text-info"><i class="bi bi-person-check"></i></span>
          <span class="fw-semibold">Inquilino</span>
        </div>
        <div class="card-body">
          <?php if (empty($todosCondominios)): ?>
            <p class="text-body-secondary mb-0" style="font-size:.875rem;">Nenhum condômino cadastrado.</p>
          <?php else: ?>
            <div class="condux-select-search mb-1">
              <input type="text" class="form-control form-control-sm condux-search-input mb-2"
                     placeholder="Buscar condômino pelo nome..." autocomplete="off">
              <select name="inquilino_id" class="form-select condux-searchable-select" size="5">
                <option value="">— Nenhum inquilino vinculado —</option>
                <?php foreach ($todosCondominios as $c): ?>
                  <option value="<?= $c['id'] ?>"
                    <?= ($unidade?->inquilinoId ?? null) == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nome']) ?>
                    <?php if (!empty($c['email'])): ?>— <?= htmlspecialchars($c['email']) ?><?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-text">O inquilino deve ser um condômino já cadastrado no sistema.</div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary px-4">
      <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
      <?= $editando ? 'Salvar alterações' : 'Cadastrar unidade' ?>
    </button>
    <a href="<?= url('unidades') ?>" class="btn btn-outline-secondary">Cancelar</a>
  </div>

</form>

<style>
.condux-searchable-select {
  max-height: 200px;
  border-radius: .375rem;
}
.condux-searchable-select option {
  padding: .4rem .6rem;
  border-radius: .25rem;
}
</style>

<script>
(function () {
  /* Mostrar/ocultar inquilino */
  var selectOcupacao = document.getElementById('campo-tipo-ocupacao');
  var secaoInquilino = document.getElementById('secao-inquilino');
  function atualizarInquilino() {
    secaoInquilino.style.display = selectOcupacao.value === 'alugado' ? '' : 'none';
  }
  selectOcupacao.addEventListener('change', atualizarInquilino);
  atualizarInquilino();

  /* Filtro de busca para cada select pesquisável */
  document.querySelectorAll('.condux-select-search').forEach(function (bloco) {
    var input  = bloco.querySelector('.condux-search-input');
    var select = bloco.querySelector('.condux-searchable-select');
    var opcoes = Array.from(select.options);

    input.addEventListener('input', function () {
      var termo = this.value.toLowerCase().trim();
      opcoes.forEach(function (opt) {
        var texto = opt.textContent.toLowerCase();
        opt.hidden = termo !== '' && !texto.includes(termo);
      });
      /* Garante que a opção vazia só fica oculta se há filtro digitado */
      opcoes[0].hidden = false;
    });

    /* Clicar na opção limpa o filtro */
    select.addEventListener('change', function () {
      if (this.value) input.value = '';
    });
  });
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
