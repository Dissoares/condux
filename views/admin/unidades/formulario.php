<?php
/** @var Unidade|null $unidade */
$editando     = $unidade !== null;
$tituloPagina = $editando ? 'Editar Unidade' : 'Nova Unidade';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina">
    <i class="bi bi-building"></i> <?= $editando ? 'Editar Unidade' : 'Nova Unidade' ?>
  </h1>
  <a href="<?= url('unidades') ?>" class="botao-secundario">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div class="card-conteudo" style="max-width:620px;">
  <form action="<?= url('unidades/salvar') ?>" method="POST" id="form-unidade">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?= $unidade->id ?>">
    <?php endif; ?>

    <!-- Identificação -->
    <p class="rotulo-secao">Identificação</p>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;" class="grade-formulario">
      <div class="campo-formulario">
        <label for="campo-numero">Número *</label>
        <input type="text" id="campo-numero" name="numero" required
               placeholder="101" maxlength="20"
               value="<?= htmlspecialchars($unidade->numero ?? '') ?>">
      </div>
      <div class="campo-formulario">
        <label for="campo-bloco">Bloco</label>
        <input type="text" id="campo-bloco" name="bloco"
               placeholder="A" maxlength="10"
               value="<?= htmlspecialchars($unidade->bloco ?? '') ?>">
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; margin-bottom:1.25rem;" class="grade-formulario">
      <div class="campo-formulario">
        <label for="campo-andar">Andar</label>
        <input type="number" id="campo-andar" name="andar" min="0" max="99"
               placeholder="1"
               value="<?= htmlspecialchars((string)($unidade->andar ?? '')) ?>">
      </div>
      <div class="campo-formulario">
        <label for="campo-descricao">Observações</label>
        <input type="text" id="campo-descricao" name="descricao"
               placeholder="Informações adicionais"
               value="<?= htmlspecialchars($unidade->descricao ?? '') ?>">
      </div>
    </div>

    <!-- Tipo de ocupação -->
    <p class="rotulo-secao">Ocupação</p>

    <div class="campo-formulario" style="margin-bottom:1.25rem;">
      <label for="campo-tipo-ocupacao">Situação da unidade</label>
      <select id="campo-tipo-ocupacao" name="tipo_ocupacao">
        <option value="proprio"  <?= ($unidade->tipoOcupacao ?? 'proprio') === 'proprio'  ? 'selected' : '' ?>>Próprio — ocupado pelo proprietário</option>
        <option value="alugado"  <?= ($unidade->tipoOcupacao ?? '') === 'alugado'          ? 'selected' : '' ?>>Alugado — possui inquilino</option>
      </select>
    </div>

    <!-- Proprietário -->
    <p class="rotulo-secao">Proprietário</p>

    <div class="campo-formulario" style="margin-bottom:1rem;">
      <label for="campo-nome-proprietario">Nome</label>
      <input type="text" id="campo-nome-proprietario" name="nome_proprietario"
             placeholder="Nome completo do proprietário"
             value="<?= htmlspecialchars($unidade->nomeProprietario ?? '') ?>">
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;" class="grade-formulario">
      <div class="campo-formulario">
        <label for="campo-telefone-proprietario">Telefone</label>
        <input type="tel" id="campo-telefone-proprietario" name="telefone_proprietario"
               placeholder="(11) 99999-9999"
               value="<?= htmlspecialchars($unidade->telefoneProprietario ?? '') ?>">
      </div>
      <div class="campo-formulario">
        <label for="campo-email-proprietario">E-mail</label>
        <input type="email" id="campo-email-proprietario" name="email_proprietario"
               placeholder="proprietario@email.com"
               value="<?= htmlspecialchars($unidade->emailProprietario ?? '') ?>">
      </div>
    </div>

    <!-- Inquilino (visível apenas quando alugado) -->
    <div id="secao-inquilino" style="display:none;">
      <p class="rotulo-secao">Inquilino</p>

      <div class="campo-formulario" style="margin-bottom:1rem;">
        <label for="campo-nome-inquilino">Nome</label>
        <input type="text" id="campo-nome-inquilino" name="nome_inquilino"
               placeholder="Nome completo do inquilino"
               value="<?= htmlspecialchars($unidade->nomeInquilino ?? '') ?>">
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;" class="grade-formulario">
        <div class="campo-formulario">
          <label for="campo-telefone-inquilino">Telefone</label>
          <input type="tel" id="campo-telefone-inquilino" name="telefone_inquilino"
                 placeholder="(11) 99999-9999"
                 value="<?= htmlspecialchars($unidade->telefoneInquilino ?? '') ?>">
        </div>
        <div class="campo-formulario">
          <label for="campo-email-inquilino">E-mail</label>
          <input type="email" id="campo-email-inquilino" name="email_inquilino"
                 placeholder="inquilino@email.com"
                 value="<?= htmlspecialchars($unidade->emailInquilino ?? '') ?>">
        </div>
      </div>
    </div>

    <button type="submit" class="botao-primario">
      <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
      <?= $editando ? 'Salvar alterações' : 'Cadastrar unidade' ?>
    </button>
  </form>
</div>

<script>
(function () {
  const select  = document.getElementById('campo-tipo-ocupacao');
  const secao   = document.getElementById('secao-inquilino');

  function atualizar() {
    secao.style.display = select.value === 'alugado' ? 'block' : 'none';
  }

  select.addEventListener('change', atualizar);
  atualizar(); // aplica ao carregar (edição)
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
