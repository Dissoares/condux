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

<div class="card-conteudo" style="max-width:520px;">
  <form action="<?= url('unidades/salvar') ?>" method="POST">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?= $unidade->id ?>">
    <?php endif; ?>

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

    <div class="campo-formulario" style="margin-bottom:1rem;">
      <label for="campo-andar">Andar</label>
      <input type="number" id="campo-andar" name="andar" min="0" max="99"
             placeholder="1"
             value="<?= htmlspecialchars((string)($unidade->andar ?? '')) ?>">
    </div>

    <div class="campo-formulario" style="margin-bottom:1.5rem;">
      <label for="campo-descricao">Descrição</label>
      <textarea id="campo-descricao" name="descricao" rows="3"
                placeholder="Informações adicionais..."><?= htmlspecialchars($unidade->descricao ?? '') ?></textarea>
    </div>

    <button type="submit" class="botao-primario">
      <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
      <?= $editando ? 'Salvar alterações' : 'Cadastrar unidade' ?>
    </button>
  </form>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
