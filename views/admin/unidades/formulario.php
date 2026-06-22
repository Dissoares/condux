<?php
/** @var Unidade|null $unidade @var array[] $todosCondominios @var int[] $condominosSelecionados */
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
      <?= $editando ? 'Atualize os dados da unidade e salve.' : 'Preencha os dados para cadastrar uma nova unidade.' ?>
    </p>
  </div>
  <a href="<?= url('unidades') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<form action="<?= url('unidades/salvar') ?>" method="POST" id="form-unidade">
  <?php if ($editando): ?>
    <?php $uid = (int)$unidade->id; ?>
    <input type="hidden" name="id" value="<?= $uid ?>">
  <?php endif; ?>

  <div class="row g-4 mb-4">

    <!-- Coluna esquerda: Identificação + Ocupação -->
    <div class="col-lg-6">

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary flex-shrink-0"
                style="width:32px;height:32px;font-size:.9rem;">
            <i class="bi bi-hash"></i>
          </span>
          <span class="fw-semibold">Identificação</span>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label for="campo-numero" class="form-label">Número <span class="text-danger">*</span></label>
              <input type="text" id="campo-numero" name="numero" class="form-control"
                     required placeholder="101" maxlength="20"
                     value="<?= htmlspecialchars($unidade->numero ?? '') ?>">
            </div>
            <div class="col-6">
              <label for="campo-bloco" class="form-label">Bloco</label>
              <input type="text" id="campo-bloco" name="bloco" class="form-control"
                     placeholder="A" maxlength="10"
                     value="<?= htmlspecialchars($unidade->bloco ?? '') ?>">
            </div>
          </div>
          <div class="row g-3">
            <div class="col-4">
              <label for="campo-andar" class="form-label">Andar</label>
              <input type="number" id="campo-andar" name="andar" class="form-control"
                     min="0" max="99" placeholder="1"
                     value="<?= htmlspecialchars((string)($unidade->andar ?? '')) ?>">
            </div>
            <div class="col-8">
              <label for="campo-descricao" class="form-label">Observações</label>
              <input type="text" id="campo-descricao" name="descricao" class="form-control"
                     placeholder="Informações adicionais"
                     value="<?= htmlspecialchars($unidade->descricao ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success flex-shrink-0"
                style="width:32px;height:32px;font-size:.9rem;">
            <i class="bi bi-house-door"></i>
          </span>
          <span class="fw-semibold">Ocupação</span>
        </div>
        <div class="card-body">
          <label for="campo-tipo-ocupacao" class="form-label">Situação da unidade</label>
          <select id="campo-tipo-ocupacao" name="tipo_ocupacao" class="form-select">
            <option value="proprio" <?= ($unidade->tipoOcupacao ?? 'proprio') === 'proprio' ? 'selected' : '' ?>>
              Próprio — ocupado pelo proprietário
            </option>
            <option value="alugado" <?= ($unidade->tipoOcupacao ?? '') === 'alugado' ? 'selected' : '' ?>>
              Alugado — possui inquilino
            </option>
          </select>
        </div>
      </div>

    </div>

    <!-- Coluna direita: Proprietário + Inquilino -->
    <div class="col-lg-6">

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="d-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning flex-shrink-0"
                style="width:32px;height:32px;font-size:.9rem;">
            <i class="bi bi-person"></i>
          </span>
          <span class="fw-semibold">Proprietário</span>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="campo-nome-proprietario" class="form-label">Nome</label>
            <input type="text" id="campo-nome-proprietario" name="nome_proprietario" class="form-control"
                   placeholder="Nome completo"
                   value="<?= htmlspecialchars($unidade->nomeProprietario ?? '') ?>">
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label for="campo-telefone-proprietario" class="form-label">Telefone</label>
              <input type="tel" id="campo-telefone-proprietario" name="telefone_proprietario" class="form-control"
                     placeholder="(11) 99999-9999"
                     value="<?= htmlspecialchars($unidade->telefoneProprietario ?? '') ?>">
            </div>
            <div class="col-6">
              <label for="campo-email-proprietario" class="form-label">E-mail</label>
              <input type="email" id="campo-email-proprietario" name="email_proprietario" class="form-control"
                     placeholder="email@exemplo.com"
                     value="<?= htmlspecialchars($unidade->emailProprietario ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <div id="secao-inquilino" class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="d-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info flex-shrink-0"
                style="width:32px;height:32px;font-size:.9rem;">
            <i class="bi bi-person-badge"></i>
          </span>
          <span class="fw-semibold">Inquilino</span>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="campo-nome-inquilino" class="form-label">Nome</label>
            <input type="text" id="campo-nome-inquilino" name="nome_inquilino" class="form-control"
                   placeholder="Nome completo"
                   value="<?= htmlspecialchars($unidade->nomeInquilino ?? '') ?>">
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label for="campo-telefone-inquilino" class="form-label">Telefone</label>
              <input type="tel" id="campo-telefone-inquilino" name="telefone_inquilino" class="form-control"
                     placeholder="(11) 99999-9999"
                     value="<?= htmlspecialchars($unidade->telefoneInquilino ?? '') ?>">
            </div>
            <div class="col-6">
              <label for="campo-email-inquilino" class="form-label">E-mail</label>
              <input type="email" id="campo-email-inquilino" name="email_inquilino" class="form-control"
                     placeholder="email@exemplo.com"
                     value="<?= htmlspecialchars($unidade->emailInquilino ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

    </div>
  </div><!-- /row principal -->

  <!-- Condôminos vinculados -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
      <div class="d-flex align-items-center gap-2">
        <span class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 text-secondary flex-shrink-0"
              style="width:32px;height:32px;font-size:.9rem;">
          <i class="bi bi-people"></i>
        </span>
        <span class="fw-semibold">Condôminos vinculados</span>
      </div>
      <a href="<?= url('condominios/novo') ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-person-plus"></i> Novo condômino
      </a>
    </div>
    <div class="card-body">
      <?php if (empty($todosCondominios)): ?>
        <p class="text-body-secondary mb-0" style="font-size:.875rem;">
          Nenhum condômino cadastrado ainda.
          <a href="<?= url('condominios/novo') ?>">Cadastre o primeiro</a> e volte aqui para vincular.
        </p>
      <?php else: ?>
        <div class="row g-2">
          <?php foreach ($todosCondominios as $c): ?>
          <div class="col-sm-6 col-md-4">
            <label class="d-flex align-items-center gap-2 p-2 rounded border cursor-pointer
                          <?= in_array((int)$c['id'], $condominosSelecionados, true) ? 'border-primary bg-primary bg-opacity-10' : 'border-0 bg-body-secondary' ?>"
                   for="cond-<?= $c['id'] ?>" style="cursor:pointer;">
              <input type="checkbox" class="form-check-input flex-shrink-0 m-0"
                     id="cond-<?= $c['id'] ?>"
                     name="condominos[]"
                     value="<?= $c['id'] ?>"
                     <?= in_array((int)$c['id'], $condominosSelecionados, true) ? 'checked' : '' ?>
                     onchange="this.closest('label').classList.toggle('border-primary', this.checked);
                               this.closest('label').classList.toggle('bg-primary', this.checked);
                               this.closest('label').classList.toggle('bg-opacity-10', this.checked);
                               this.closest('label').classList.toggle('border-0', !this.checked);
                               this.closest('label').classList.toggle('bg-body-secondary', !this.checked);">
              <div>
                <div class="fw-semibold lh-1" style="font-size:.875rem;"><?= htmlspecialchars($c['nome']) ?></div>
                <?php if ($c['telefone']): ?>
                  <div class="text-body-secondary" style="font-size:.75rem;"><?= htmlspecialchars($c['telefone']) ?></div>
                <?php endif; ?>
              </div>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary px-4">
      <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
      <?= $editando ? 'Salvar alterações' : 'Cadastrar unidade' ?>
    </button>
    <a href="<?= url('unidades') ?>" class="btn btn-outline-secondary">Cancelar</a>
  </div>

</form>

<script>
(function () {
  var select = document.getElementById('campo-tipo-ocupacao');
  var secao  = document.getElementById('secao-inquilino');
  function atualizar() { secao.style.display = select.value === 'alugado' ? '' : 'none'; }
  select.addEventListener('change', atualizar);
  atualizar();
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
