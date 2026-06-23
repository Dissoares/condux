<?php
/** @var Funcionario|null $funcionario */
$editando     = $funcionario !== null;
$tituloPagina = $editando ? 'Editar Funcionário' : 'Novo Funcionário';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0">
    <i class="bi bi-person-badge"></i> <?= $editando ? 'Editar Funcionário' : 'Novo Funcionário' ?>
  </h4>
  <a href="<?= url('funcionarios') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<div class="card border-0 shadow-sm" style="max-width:640px;">
  <div class="card-body p-4">
    <form action="<?= url('funcionarios/salvar') ?>" method="POST">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= (int)$funcionario->id ?>">
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <div class="col-sm-8">
          <label class="form-label">Nome *</label>
          <input type="text" name="nome" class="form-control" required
                 placeholder="Nome completo"
                 value="<?= htmlspecialchars($funcionario?->nome ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label">CPF</label>
          <input type="text" name="cpf" class="form-control"
                 placeholder="000.000.000-00"
                 value="<?= htmlspecialchars($funcionario?->cpf ?? '') ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label">Cargo *</label>
          <input type="text" name="cargo" class="form-control" required
                 placeholder="Ex: Zelador, Porteiro..."
                 value="<?= htmlspecialchars($funcionario?->cargo ?? '') ?>">
        </div>
        <div class="col-sm-6">
          <label class="form-label">Departamento</label>
          <input type="text" name="departamento" class="form-control"
                 placeholder="Ex: Manutenção, Segurança..."
                 value="<?= htmlspecialchars($funcionario?->departamento ?? '') ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-5">
          <label class="form-label">Telefone</label>
          <input type="text" name="telefone" class="form-control"
                 placeholder="(00) 00000-0000"
                 value="<?= htmlspecialchars($funcionario?->telefone ?? '') ?>">
        </div>
        <div class="col-sm-7">
          <label class="form-label">E-mail</label>
          <input type="email" name="email" class="form-control"
                 placeholder="funcionario@email.com"
                 value="<?= htmlspecialchars($funcionario?->email ?? '') ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-4">
          <label class="form-label">Salário (R$)</label>
          <input type="text" name="salario" class="form-control"
                 placeholder="0,00"
                 value="<?= $funcionario?->salario !== null ? number_format($funcionario->salario, 2, ',', '.') : '' ?>">
        </div>
        <div class="col-sm-3">
          <label class="form-label">Dia de pagamento</label>
          <input type="number" name="dia_pagamento" class="form-control" min="1" max="31"
                 placeholder="Ex: 5"
                 value="<?= $funcionario?->diaPagamento ?? '' ?>">
        </div>
        <div class="col-sm-5">
          <label class="form-label">Data de admissão</label>
          <input type="date" name="data_admissao" class="form-control"
                 value="<?= htmlspecialchars($funcionario?->dataAdmissao ?? '') ?>">
        </div>
        <div class="col-sm-4">
          <label class="form-label">Data de demissão</label>
          <input type="date" name="data_demissao" class="form-control"
                 value="<?= htmlspecialchars($funcionario?->dataDemissao ?? '') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Observações</label>
        <textarea name="observacoes" class="form-control" rows="3"
                  placeholder="Informações adicionais..."><?= htmlspecialchars($funcionario?->observacoes ?? '') ?></textarea>
      </div>

      <?php if ($editando): ?>
      <div class="mb-4 form-check form-switch">
        <input type="hidden" name="ativo" value="0">
        <input class="form-check-input" type="checkbox" id="chk-ativo" name="ativo" value="1"
               <?= ($funcionario?->ativo ?? true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="chk-ativo">Funcionário ativo</label>
      </div>
      <?php else: ?>
        <input type="hidden" name="ativo" value="1">
      <?php endif; ?>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-<?= $editando ? 'floppy' : 'plus-lg' ?>"></i>
        <?= $editando ? 'Salvar alterações' : 'Cadastrar funcionário' ?>
      </button>
    </form>
  </div>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
