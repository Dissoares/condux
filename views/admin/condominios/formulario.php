<?php
/**
 * @var Usuario|null  $usuario
 * @var int           $retornarUnidade
 * @var Unidade[]     $todasUnidades
 * @var int[]         $unidadesVinculadas
 * @var string|null   $mensagem
 * @var string|null   $erroMensagem
 */
$editando     = $usuario !== null;
$tituloPagina = $editando ? 'Editar condômino' : 'Novo condômino';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0">
      <i class="bi bi-person-<?= $editando ? 'gear' : 'plus' ?> text-primary"></i>
      <?= $editando ? 'Editar condômino' : 'Novo condômino' ?>
    </h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      <?= $editando ? 'Atualize os dados e as unidades vinculadas.' : 'Preencha os dados de contato e vincule às unidades.' ?>
    </p>
  </div>
  <a href="<?= $retornarUnidade ? url("unidades/{$retornarUnidade}") : url('condominios') ?>"
     class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
</div>

<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<form action="<?= url('condominios/salvar') ?>" method="POST">

  <?php if ($editando): ?>
    <?php $uid = (int)$usuario->id; ?>
    <input type="hidden" name="id" value="<?= $uid ?>">
  <?php endif; ?>
  <input type="hidden" name="retornar_unidade" value="<?= $retornarUnidade ?>">

  <div class="row g-4">

    <!-- Dados pessoais e acesso -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary flex-shrink-0"
                style="width:32px;height:32px;font-size:.9rem;"><i class="bi bi-person"></i></span>
          <span class="fw-semibold">Dados pessoais</span>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label for="campo-nome" class="form-label">Nome completo *</label>
            <input type="text" id="campo-nome" name="nome" class="form-control" required
                   value="<?= htmlspecialchars($usuario->nome ?? '') ?>" placeholder="João da Silva">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-7">
              <label for="campo-telefone" class="form-label">Telefone</label>
              <input type="tel" id="campo-telefone" name="telefone" class="form-control"
                     value="<?= htmlspecialchars($usuario->telefone ?? '') ?>" placeholder="(11) 99999-9999">
            </div>
            <div class="col-5">
              <label for="campo-nascimento" class="form-label">Nascimento</label>
              <input type="date" id="campo-nascimento" name="data_nascimento" class="form-control"
                     value="<?= htmlspecialchars($usuario->dataNascimento ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label for="campo-cpf" class="form-label">CPF</label>
            <input type="text" id="campo-cpf" name="cpf" class="form-control"
                   value="<?= htmlspecialchars($usuario->cpf ?? '') ?>" placeholder="000.000.000-00" maxlength="14">
          </div>

          <div class="mb-3">
            <label for="campo-observacoes" class="form-label">Observações</label>
            <textarea id="campo-observacoes" name="observacoes" class="form-control" rows="2"
                      placeholder="Informações adicionais"><?= htmlspecialchars($usuario->observacoes ?? '') ?></textarea>
          </div>

        </div>
      </div>
    </div>

    <!-- Acesso ao sistema -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="d-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning flex-shrink-0"
                style="width:32px;height:32px;font-size:.9rem;"><i class="bi bi-key"></i></span>
          <span class="fw-semibold">Acesso ao sistema</span>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label for="campo-email" class="form-label">E-mail (login) *</label>
            <input type="email" id="campo-email" name="email" class="form-control" required
                   value="<?= htmlspecialchars($usuario->email ?? '') ?>" placeholder="joao@email.com">
          </div>

          <div class="mb-3">
            <label for="campo-senha" class="form-label">
              Senha <?= $editando ? '<span class="text-body-secondary fw-normal">(em branco = manter atual)</span>' : '*' ?>
            </label>
            <input type="password" id="campo-senha" name="senha" class="form-control"
                   <?= !$editando ? 'required' : '' ?> placeholder="Mínimo 6 caracteres" autocomplete="new-password">
          </div>

          <?php if ($editando): ?>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="campo-ativo" name="ativo" value="1"
                   <?= $usuario->ativo ? 'checked' : '' ?>>
            <label class="form-check-label" for="campo-ativo">Conta ativa</label>
          </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Unidades vinculadas -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
          <span class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 text-secondary flex-shrink-0"
                style="width:32px;height:32px;font-size:.9rem;"><i class="bi bi-building"></i></span>
          <span class="fw-semibold">Unidades vinculadas</span>
        </div>
        <div class="card-body">
          <?php if (empty($todasUnidades)): ?>
            <p class="text-body-secondary mb-0" style="font-size:.875rem;">Nenhuma unidade cadastrada.</p>
          <?php else: ?>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($todasUnidades as $unidade): ?>
              <?php $marcado = in_array($unidade->id, $unidadesVinculadas, true); ?>
              <label class="d-flex align-items-center gap-2 p-2 rounded border
                            <?= $marcado ? 'border-primary bg-primary bg-opacity-10' : 'border-0 bg-body-secondary' ?>"
                     for="unidade-<?= $unidade->id ?>" style="cursor:pointer;">
                <input type="checkbox" class="form-check-input flex-shrink-0 m-0"
                       id="unidade-<?= $unidade->id ?>"
                       name="unidades[]"
                       value="<?= $unidade->id ?>"
                       <?= $marcado ? 'checked' : '' ?>
                       onchange="this.closest('label').classList.toggle('border-primary', this.checked);
                                 this.closest('label').classList.toggle('bg-primary', this.checked);
                                 this.closest('label').classList.toggle('bg-opacity-10', this.checked);
                                 this.closest('label').classList.toggle('border-0', !this.checked);
                                 this.closest('label').classList.toggle('bg-body-secondary', !this.checked);">
                <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($unidade->identificacao()) ?></div>
              </label>
              <?php endforeach; ?>
            </div>
            <div class="form-text mt-2">Marque as unidades onde este condômino mora.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div><!-- row -->

  <?php if ($retornarUnidade > 0 && !$editando): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mt-4 py-2" style="font-size:.875rem;">
      <i class="bi bi-info-circle-fill flex-shrink-0"></i>
      Após cadastrar, você retornará para a unidade de origem.
    </div>
  <?php endif; ?>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-check-lg"></i> <?= $editando ? 'Salvar alterações' : 'Cadastrar' ?>
    </button>
    <a href="<?= $retornarUnidade ? url("unidades/{$retornarUnidade}") : url('condominios') ?>"
       class="btn btn-outline-secondary">Cancelar</a>
  </div>

</form>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
