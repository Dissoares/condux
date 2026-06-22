<?php
/** @var Projeto|null $projeto @var Usuario[] $responsaveis */
$editando     = $projeto !== null;
$tituloPagina = $editando ? 'Editar Projeto' : 'Novo Projeto';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina">
    <i class="bi bi-kanban"></i> <?= $editando ? 'Editar Projeto' : 'Novo Projeto' ?>
  </h1>
  <a href="<?= url('projetos') ?>" class="botao-secundario">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div class="card-conteudo" style="max-width:680px;">
  <form action="<?= url('projetos/salvar') ?>" method="POST">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?= $projeto->id ?>">
    <?php endif; ?>

    <div class="campo-formulario" style="margin-bottom:1rem;">
      <label for="campo-nome-projeto">Nome do projeto *</label>
      <input type="text" id="campo-nome-projeto" name="nome" required
             placeholder="Ex: Reforma da garagem"
             value="<?= htmlspecialchars($projeto->nome ?? '') ?>">
    </div>

    <div class="campo-formulario" style="margin-bottom:1rem;">
      <label for="campo-descricao-projeto">Descrição</label>
      <textarea id="campo-descricao-projeto" name="descricao" rows="4"
                placeholder="Descreva o projeto, objetivos e escopo..."><?= htmlspecialchars($projeto->descricao ?? '') ?></textarea>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;" class="grade-formulario">
      <div class="campo-formulario">
        <label for="campo-idealizador">Idealizador</label>
        <input type="text" id="campo-idealizador" name="idealizador"
               placeholder="Nome do morador ou conselho"
               value="<?= htmlspecialchars($projeto->idealizador ?? '') ?>">
      </div>
      <div class="campo-formulario">
        <label for="campo-responsavel">Responsável</label>
        <select id="campo-responsavel" name="responsavel_id">
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

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;" class="grade-formulario">
      <div class="campo-formulario">
        <label for="campo-valor-estimado">Valor estimado (R$)</label>
        <input type="text" id="campo-valor-estimado" name="valor_estimado"
               placeholder="0,00"
               value="<?= $projeto->valorEstimado ? number_format($projeto->valorEstimado, 2, ',', '.') : '' ?>">
      </div>
      <div class="campo-formulario">
        <label for="campo-valor-realizado">Valor realizado (R$)</label>
        <input type="text" id="campo-valor-realizado" name="valor_realizado"
               placeholder="0,00"
               value="<?= $projeto->valorRealizado ? number_format($projeto->valorRealizado, 2, ',', '.') : '' ?>">
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;" class="grade-formulario">
      <div class="campo-formulario">
        <label for="campo-data-inicio">Data de início</label>
        <input type="date" id="campo-data-inicio" name="data_inicio"
               value="<?= htmlspecialchars($projeto->dataInicio ?? '') ?>">
      </div>
      <div class="campo-formulario">
        <label for="campo-data-conclusao">Previsão de conclusão</label>
        <input type="date" id="campo-data-conclusao" name="data_conclusao"
               value="<?= htmlspecialchars($projeto->dataConclusao ?? '') ?>">
      </div>
    </div>

    <div class="campo-formulario" style="margin-bottom:1.5rem;">
      <label for="campo-status-projeto">Status</label>
      <select id="campo-status-projeto" name="status">
        <?php foreach (Projeto::$rotulosStatus as $chave => $rotulo): ?>
          <option value="<?= $chave ?>"
            <?= ($projeto->status ?? 'pendente') === $chave ? 'selected' : '' ?>>
            <?= htmlspecialchars($rotulo) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="botao-primario">
      <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
      <?= $editando ? 'Salvar alterações' : 'Criar projeto' ?>
    </button>
  </form>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
