<?php
/** @var Unidade|null $unidade @var array[] $todosCondominios @var int[] $condominosSelecionados */
$editando     = $unidade !== null;
$tituloPagina = $editando ? 'Editar Unidade' : 'Nova Unidade';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0">
    <i class="bi bi-building"></i> <?= $editando ? 'Editar Unidade' : 'Nova Unidade' ?>
  </h4>
  <a href="<?= url('unidades') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div class="card border-0 shadow-sm mb-4" style="max-width:620px;">
  <div class="card-body">
    <form action="<?= url('unidades/salvar') ?>" method="POST" id="form-unidade">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $unidade->id ?>">
      <?php endif; ?>

      <p class="fw-semibold text-body-secondary mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em;">Identificação</p>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label for="campo-numero" class="form-label">Número *</label>
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

      <div class="row g-3 mb-4">
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

      <p class="fw-semibold text-body-secondary mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em;">Ocupação</p>

      <div class="mb-4">
        <label for="campo-tipo-ocupacao" class="form-label">Situação da unidade</label>
        <select id="campo-tipo-ocupacao" name="tipo_ocupacao" class="form-select">
          <option value="proprio" <?= ($unidade->tipoOcupacao ?? 'proprio') === 'proprio' ? 'selected' : '' ?>>Próprio — ocupado pelo proprietário</option>
          <option value="alugado" <?= ($unidade->tipoOcupacao ?? '') === 'alugado'         ? 'selected' : '' ?>>Alugado — possui inquilino</option>
        </select>
      </div>

      <p class="fw-semibold text-body-secondary mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em;">Proprietário</p>

      <div class="mb-3">
        <label for="campo-nome-proprietario" class="form-label">Nome</label>
        <input type="text" id="campo-nome-proprietario" name="nome_proprietario" class="form-control"
               placeholder="Nome completo do proprietário"
               value="<?= htmlspecialchars($unidade->nomeProprietario ?? '') ?>">
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6">
          <label for="campo-telefone-proprietario" class="form-label">Telefone</label>
          <input type="tel" id="campo-telefone-proprietario" name="telefone_proprietario" class="form-control"
                 placeholder="(11) 99999-9999"
                 value="<?= htmlspecialchars($unidade->telefoneProprietario ?? '') ?>">
        </div>
        <div class="col-6">
          <label for="campo-email-proprietario" class="form-label">E-mail</label>
          <input type="email" id="campo-email-proprietario" name="email_proprietario" class="form-control"
                 placeholder="proprietario@email.com"
                 value="<?= htmlspecialchars($unidade->emailProprietario ?? '') ?>">
        </div>
      </div>

      <div id="secao-inquilino" style="display:none;">
        <p class="fw-semibold text-body-secondary mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em;">Inquilino</p>

        <div class="mb-3">
          <label for="campo-nome-inquilino" class="form-label">Nome</label>
          <input type="text" id="campo-nome-inquilino" name="nome_inquilino" class="form-control"
                 placeholder="Nome completo do inquilino"
                 value="<?= htmlspecialchars($unidade->nomeInquilino ?? '') ?>">
        </div>
        <div class="row g-3 mb-4">
          <div class="col-6">
            <label for="campo-telefone-inquilino" class="form-label">Telefone</label>
            <input type="tel" id="campo-telefone-inquilino" name="telefone_inquilino" class="form-control"
                   placeholder="(11) 99999-9999"
                   value="<?= htmlspecialchars($unidade->telefoneInquilino ?? '') ?>">
          </div>
          <div class="col-6">
            <label for="campo-email-inquilino" class="form-label">E-mail</label>
            <input type="email" id="campo-email-inquilino" name="email_inquilino" class="form-control"
                   placeholder="inquilino@email.com"
                   value="<?= htmlspecialchars($unidade->emailInquilino ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Condôminos vinculados -->
      <hr class="my-4">
      <p class="fw-semibold text-body-secondary mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em;">
        Condôminos
      </p>

      <?php if (empty($todosCondominios)): ?>
        <p class="text-body-secondary mb-3" style="font-size:.875rem;">
          Nenhum condômino cadastrado.
          <a href="<?= url('condominios/novo') ?>">Cadastrar agora</a>
        </p>
      <?php else: ?>
        <div class="mb-3" style="max-height:200px; overflow-y:auto; border:1px solid var(--bs-border-color); border-radius:.375rem; padding:.75rem;">
          <?php foreach ($todosCondominios as $c): ?>
          <div class="form-check mb-1">
            <input type="checkbox" class="form-check-input"
                   id="cond-<?= $c['id'] ?>"
                   name="condominos[]"
                   value="<?= $c['id'] ?>"
                   <?= in_array((int)$c['id'], $condominosSelecionados, true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="cond-<?= $c['id'] ?>" style="font-size:.9rem;">
              <?= htmlspecialchars($c['nome']) ?>
              <?php if ($c['telefone']): ?>
                <span class="text-body-secondary" style="font-size:.78rem;">— <?= htmlspecialchars($c['telefone']) ?></span>
              <?php endif; ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="form-text mb-4">
          Marque quem mora nesta unidade. Para cadastrar um novo condômino,
          <a href="<?= url('condominios/novo') ?>">clique aqui</a>.
        </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
        <?= $editando ? 'Salvar alterações' : 'Cadastrar unidade' ?>
      </button>
    </form>
  </div>
</div>

<script>
(function () {
  var select = document.getElementById('campo-tipo-ocupacao');
  var secao  = document.getElementById('secao-inquilino');
  function atualizar() { secao.style.display = select.value === 'alugado' ? 'block' : 'none'; }
  select.addEventListener('change', atualizar);
  atualizar();
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
