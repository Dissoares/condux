<?php
/** @var Gestao $gestao @var array[] $projetos */
$tituloPagina = $gestao->descricao;
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
  <div>
    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
      <h4 class="fw-semibold mb-0"><?= htmlspecialchars($gestao->descricao) ?></h4>
      <?php if ($gestao->ativa()): ?>
        <span class="badge bg-success bg-opacity-10 text-success fw-semibold">Ativa</span>
      <?php else: ?>
        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold">Encerrada</span>
      <?php endif; ?>
    </div>
    <p class="text-body-secondary mb-0" style="font-size:.85rem;">
      <i class="bi bi-calendar3 me-1"></i><?= $gestao->periodo() ?>
      · <?= count($gestao->membros) ?> membro<?= count($gestao->membros) !== 1 ? 's' : '' ?>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url("gestoes/{$gestao->id}/editar") ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('gestoes') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<div class="row g-4">

  <!-- Membros -->
  <div class="col-lg-7">

    <?php
    $grupos = [
      'sindico'    => ['label' => 'Síndico',    'cor' => 'primary',   'icone' => 'bi-person-badge'],
      'subsindico' => ['label' => 'Subsíndico', 'cor' => 'secondary', 'icone' => 'bi-person-check'],
      'conselheiro'=> ['label' => 'Conselheiros','cor' => 'indigo',   'icone' => 'bi-people'],
      'suplente'   => ['label' => 'Suplentes',  'cor' => 'warning',   'icone' => 'bi-person-dash'],
    ];
    ?>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
        <span class="icone-secao bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></span>
        <span class="fw-semibold">Composição da gestão</span>
      </div>
      <div class="card-body">
        <?php if (empty($gestao->membros)): ?>
          <p class="text-body-secondary mb-0">Nenhum membro cadastrado.</p>
        <?php else: ?>
          <?php foreach ($grupos as $cargo => $cfg): ?>
          <?php $membrosGrupo = array_filter($gestao->membros, fn($m) => $m['cargo'] === $cargo); ?>
          <?php if (!empty($membrosGrupo)): ?>
          <div class="mb-4">
            <h6 class="text-body-secondary fw-semibold mb-2" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;">
              <i class="bi <?= $cfg['icone'] ?> me-1"></i><?= $cfg['label'] ?>
            </h6>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($membrosGrupo as $m): ?>
              <div class="d-flex align-items-center gap-3 p-3 rounded-2"
                   style="background:var(--bs-tertiary-bg);">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                            bg-<?= $cfg['cor'] === 'indigo' ? 'primary' : $cfg['cor'] ?> bg-opacity-10
                            text-<?= $cfg['cor'] === 'indigo' ? 'primary' : $cfg['cor'] ?>"
                     style="width:38px;height:38px;font-size:1rem;">
                  <i class="bi <?= $cfg['icone'] ?>"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold"><?= htmlspecialchars($m['nome']) ?></div>
                  <div class="text-body-secondary" style="font-size:.8rem;"><?= htmlspecialchars($m['email']) ?></div>
                </div>
                <span class="badge bg-secondary bg-opacity-10 text-body" style="font-size:.72rem;">
                  <?= Gestao::$cargosRotulo[$cargo] ?>
                </span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Projetos do período -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
        <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-kanban"></i></span>
        <span class="fw-semibold">Projetos deste período</span>
        <span class="badge bg-secondary bg-opacity-10 text-body ms-auto"><?= count($projetos) ?></span>
      </div>
      <?php if (empty($projetos)): ?>
        <div class="card-body">
          <p class="text-body-secondary mb-0" style="font-size:.88rem;">
            Nenhum projeto iniciado entre <?= dataBR($gestao->inicio) ?> e
            <?= $gestao->fim ? dataBR($gestao->fim) : 'hoje' ?>.
          </p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Projeto</th>
                <th class="d-none d-sm-table-cell">Status</th>
                <th class="d-none d-md-table-cell">Início</th>
                <th style="width:60px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($projetos as $p): ?>
              <?php
                $corP = match($p['status']) {
                  'concluido'   => 'success',
                  'em_andamento'=> 'primary',
                  'cancelado'   => 'secondary',
                  default       => 'warning',
                };
                $rotuloP = match($p['status']) {
                  'concluido'   => 'Concluído',
                  'em_andamento'=> 'Em andamento',
                  'cancelado'   => 'Cancelado',
                  'planejamento'=> 'Planejamento',
                  default       => ucfirst($p['status']),
                };
              ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($p['nome']) ?></td>
                <td class="d-none d-sm-table-cell">
                  <span class="badge bg-<?= $corP ?> bg-opacity-10 text-<?= $corP ?>"><?= $rotuloP ?></span>
                </td>
                <td class="d-none d-md-table-cell text-body-secondary" style="font-size:.82rem;">
                  <?= dataBR($p['data_inicio']) ?>
                </td>
                <td>
                  <a href="<?= url("projetos/{$p['id']}") ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-eye"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- Lateral: ações + observações -->
  <div class="col-lg-5">

    <?php if ($gestao->observacoes): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
        <span class="icone-secao bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-chat-left-text"></i></span>
        <span class="fw-semibold">Observações</span>
      </div>
      <div class="card-body" style="white-space:pre-line;font-size:.9rem;">
        <?= htmlspecialchars($gestao->observacoes) ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex align-items-center gap-2 py-3">
        <span class="icone-secao bg-warning bg-opacity-10 text-warning"><i class="bi bi-lightning"></i></span>
        <span class="fw-semibold">Ações</span>
      </div>
      <div class="card-body d-flex flex-column gap-2">
        <a href="<?= url("gestoes/{$gestao->id}/editar") ?>" class="btn btn-outline-secondary w-100">
          <i class="bi bi-pencil"></i> Editar dados e membros
        </a>
        <?php if ($gestao->ativa()): ?>
          <a href="<?= url("gestoes/{$gestao->id}/encerrar") ?>" class="btn btn-outline-warning w-100"
             onclick="return confirm('Encerrar esta gestão? A data de término será registrada como hoje caso não preenchida.')">
            <i class="bi bi-check2-circle"></i> Encerrar gestão
          </a>
        <?php endif; ?>
        <a href="<?= url("gestoes/{$gestao->id}/excluir") ?>" class="btn btn-outline-danger w-100"
           onclick="return confirm('Excluir permanentemente esta gestão e todos os membros?')">
          <i class="bi bi-trash3"></i> Excluir
        </a>
      </div>
    </div>

  </div>

</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
