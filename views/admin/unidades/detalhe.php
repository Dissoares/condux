<?php
/** @var Unidade $unidade @var Morador[] $moradores @var array[]|null $resultadosBusca @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Unidade ' . $unidade->identificacao();
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-semibold mb-0"><i class="bi bi-building"></i> <?= htmlspecialchars($unidade->identificacao()) ?></h4>
  <div class="d-flex gap-2">
    <a href="<?= url("unidades/{$unidade->id}/editar") ?>" class="btn btn-outline-secondary">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('unidades') ?>" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($erroMensagem) ?>
  </div>
<?php endif; ?>

<div class="row g-4 mb-4">

  <!-- Informações da unidade -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-semibold border-bottom pb-2 mb-3">Dados da unidade</h6>

        <p class="fw-semibold text-body-secondary mb-2" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">Identificação</p>
        <table class="w-100 mb-3" style="font-size:.9rem; border-collapse:collapse;">
          <tr class="border-bottom"><td class="py-2 text-body-secondary" style="width:110px;">Número</td><td class="py-2 fw-semibold"><?= htmlspecialchars($unidade->numero) ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Bloco</td><td class="py-2"><?= htmlspecialchars($unidade->bloco ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Andar</td><td class="py-2"><?= $unidade->andar ? $unidade->andar . 'º' : '—' ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Ocupação</td><td class="py-2">
            <?php if ($unidade->estaAlugada()): ?>
              <span class="badge rounded-pill badge-pendente">Alugado</span>
            <?php else: ?>
              <span class="badge rounded-pill badge-pago">Próprio</span>
            <?php endif; ?>
          </td></tr>
          <tr><td class="py-2 text-body-secondary">Observações</td><td class="py-2"><?= htmlspecialchars($unidade->descricao ?? '—') ?></td></tr>
        </table>

        <p class="fw-semibold text-body-secondary mb-2" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">Proprietário</p>
        <table class="w-100 mb-3" style="font-size:.9rem; border-collapse:collapse;">
          <tr class="border-bottom"><td class="py-2 text-body-secondary" style="width:110px;">Nome</td><td class="py-2"><?= htmlspecialchars($unidade->nomeProprietario ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Telefone</td><td class="py-2">
            <?php if ($unidade->telefoneProprietario): ?>
              <a href="tel:<?= htmlspecialchars($unidade->telefoneProprietario) ?>"><?= htmlspecialchars($unidade->telefoneProprietario) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
          <tr><td class="py-2 text-body-secondary">E-mail</td><td class="py-2">
            <?php if ($unidade->emailProprietario): ?>
              <a href="mailto:<?= htmlspecialchars($unidade->emailProprietario) ?>"><?= htmlspecialchars($unidade->emailProprietario) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
        </table>

        <?php if ($unidade->estaAlugada()): ?>
        <p class="fw-semibold text-body-secondary mb-2" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">Inquilino</p>
        <table class="w-100" style="font-size:.9rem; border-collapse:collapse;">
          <tr class="border-bottom"><td class="py-2 text-body-secondary" style="width:110px;">Nome</td><td class="py-2"><?= htmlspecialchars($unidade->nomeInquilino ?? '—') ?></td></tr>
          <tr class="border-bottom"><td class="py-2 text-body-secondary">Telefone</td><td class="py-2">
            <?php if ($unidade->telefoneInquilino): ?>
              <a href="tel:<?= htmlspecialchars($unidade->telefoneInquilino) ?>"><?= htmlspecialchars($unidade->telefoneInquilino) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
          <tr><td class="py-2 text-body-secondary">E-mail</td><td class="py-2">
            <?php if ($unidade->emailInquilino): ?>
              <a href="mailto:<?= htmlspecialchars($unidade->emailInquilino) ?>"><?= htmlspecialchars($unidade->emailInquilino) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td></tr>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Buscar e vincular condômino -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bi bi-person-plus"></i> Vincular condômino</h6>

        <!-- Campo de busca -->
        <?php $unidadeIdAtual = (int)$unidade->id; ?>
        <form method="GET" action="<?= url("unidades/{$unidadeIdAtual}") ?>" class="mb-3">
          <label for="campo-buscar-condomino" class="form-label">Buscar por nome ou e-mail</label>
          <div class="input-group">
            <input type="text" id="campo-buscar-condomino" name="buscar"
                   class="form-control" placeholder="Nome ou e-mail..."
                   value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
            <button type="submit" class="btn btn-outline-secondary">
              <i class="bi bi-search"></i>
            </button>
          </div>
        </form>

        <!-- Resultados da busca -->
        <?php if ($resultadosBusca !== null): ?>
          <?php if (empty($resultadosBusca)): ?>
            <div class="alert alert-warning py-2 d-flex align-items-center gap-2 mb-3" style="font-size:.875rem;">
              <i class="bi bi-person-x flex-shrink-0"></i>
              Nenhum condômino encontrado com esse nome ou e-mail.
            </div>
          <?php else: ?>
            <ul class="list-group list-group-flush mb-3">
              <?php foreach ($resultadosBusca as $c): ?>
              <li class="list-group-item px-0">
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div>
                    <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($c['nome']) ?></div>
                    <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($c['email']) ?></div>
                    <?php if ($c['identificacao_unidade']): ?>
                      <div class="text-warning-emphasis" style="font-size:.75rem;">
                        <i class="bi bi-building"></i> Já vinculado: <?= htmlspecialchars($c['identificacao_unidade']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <?php $cId = (int)$c['id']; ?>
                  <form action="<?= url("unidades/{$unidadeIdAtual}/vincular-existente") ?>" method="POST" class="flex-shrink-0">
                    <input type="hidden" name="usuario_id" value="<?= $cId ?>">
                    <input type="hidden" name="data_entrada" value="<?= date('Y-m-d') ?>">
                    <button type="submit" class="btn btn-primary btn-sm">
                      <i class="bi bi-link-45deg"></i> Vincular
                    </button>
                  </form>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-2">
          <span class="text-body-secondary" style="font-size:.82rem;">Não encontrou?</span>
          <a href="<?= url("condominios/novo?retornar_unidade={$unidadeIdAtual}") ?>"
             class="btn btn-outline-primary btn-sm">
            <i class="bi bi-person-plus"></i> Cadastrar novo
          </a>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- Moradores vinculados -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold py-3">
    <i class="bi bi-people"></i> Moradores vinculados
  </div>
  <?php if (empty($moradores)): ?>
    <div class="card-body text-body-secondary">Nenhum morador vinculado.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Nome</th><th>E-mail</th><th>Entrada</th><th>Responsável</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($moradores as $morador): ?>
          <tr>
            <td><?= htmlspecialchars($morador->nomeUsuario ?? '—') ?></td>
            <td><?= htmlspecialchars($morador->emailUsuario ?? '—') ?></td>
            <td><?= dataBR($morador->dataEntrada) ?></td>
            <td>
              <?php if ($morador->responsavel): ?>
                <span class="badge rounded-pill badge-pago">Responsável</span>
              <?php else: ?>
                <span class="text-body-tertiary">—</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= url("unidades/{$unidade->id}/desvincular-morador/{$morador->id}") ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Desvincular este morador?')">
                <i class="bi bi-x-lg"></i> Desvincular
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
