<?php
/** @var Prestadora|null $prestadora */
$editando     = $prestadora !== null;
$tituloPagina = $editando ? 'Editar Prestadora' : 'Nova Prestadora';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0">
    <i class="bi bi-building"></i> <?= $editando ? 'Editar Empresa' : 'Nova Empresa' ?>
  </h4>
  <a href="<?= url('prestadoras') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div class="card border-0 shadow-sm" style="max-width:560px;">
  <div class="card-body p-4">
    <form action="<?= url('prestadoras/salvar') ?>" method="POST">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= (int)$prestadora->id ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Nome da empresa *</label>
        <input type="text" name="nome" class="form-control" required
               placeholder="Ex: Construtora Silva Ltda."
               value="<?= htmlspecialchars($prestadora?->nome ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">CNPJ</label>
        <input type="text" name="cnpj" class="form-control"
               placeholder="00.000.000/0000-00"
               value="<?= htmlspecialchars($prestadora?->cnpj ?? '') ?>">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label">Pessoa de contato</label>
          <input type="text" name="contato" class="form-control"
                 placeholder="Nome do responsável"
                 value="<?= htmlspecialchars($prestadora?->contato ?? '') ?>">
        </div>
        <div class="col-6">
          <label class="form-label">Telefone</label>
          <input type="text" name="telefone" class="form-control"
                 placeholder="(00) 00000-0000"
                 value="<?= htmlspecialchars($prestadora?->telefone ?? '') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-control"
               placeholder="contato@empresa.com.br"
               value="<?= htmlspecialchars($prestadora?->email ?? '') ?>">
      </div>

      <?php if ($editando): ?>
      <div class="mb-4 form-check form-switch">
        <input type="hidden" name="ativo" value="0">
        <input class="form-check-input" type="checkbox" id="chk-ativo" name="ativo" value="1"
               <?= ($prestadora?->ativo ?? true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="chk-ativo">Empresa ativa</label>
      </div>
      <?php else: ?>
        <input type="hidden" name="ativo" value="1">
      <?php endif; ?>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
        <?= $editando ? 'Salvar alterações' : 'Cadastrar empresa' ?>
      </button>
    </form>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
