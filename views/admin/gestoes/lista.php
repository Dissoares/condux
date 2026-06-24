<?php
/** @var Gestao[] $gestoes */
$tituloPagina = 'Gestões';
require_once RAIZ . '/views/layouts/cabecalho.php';

$cargoCor = [
    'sindico'     => ['border' => 'var(--bs-primary)',  'bg' => 'rgba(var(--bs-primary-rgb),.07)',  'label' => 'primary'],
    'subsindico'  => ['border' => 'var(--bs-secondary)','bg' => 'rgba(var(--bs-secondary-rgb),.07)','label' => 'secondary'],
    'conselheiro' => ['border' => '#6366f1',             'bg' => 'rgba(99,102,241,.07)',             'label' => 'info'],
    'suplente'    => ['border' => 'var(--bs-warning)',   'bg' => 'rgba(var(--bs-warning-rgb),.07)', 'label' => 'warning'],
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-person-badge text-primary"></i> Gestões do condomínio</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      Histórico de administrações — síndicos, subsíndicos e conselho fiscal.
    </p>
  </div>
  <a href="<?= url('gestoes/nova') ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Nova gestão
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i> <?= htmlspecialchars($mensagem) ?>
  </div>
<?php endif; ?>

<?php if (empty($gestoes)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5 text-center">
      <i class="bi bi-person-badge text-body-secondary mb-2" style="font-size:2.5rem;opacity:.35;"></i>
      <p class="text-body-secondary mb-3">Nenhuma gestão cadastrada ainda.</p>
      <a href="<?= url('gestoes/nova') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Cadastrar primeira gestão
      </a>
    </div>
  </div>
<?php else: ?>

<?php
$ativas     = array_filter($gestoes, fn($g) => $g->ativa());
$encerradas = array_filter($gestoes, fn($g) => !$g->ativa());
?>

<?php if (!empty($ativas)): ?>
<h6 class="text-body-secondary text-uppercase fw-semibold mb-3" style="font-size:.72rem;letter-spacing:.08em;">
  Gestão atual
</h6>
<?php foreach ($ativas as $g): ?>
<div class="card border-0 shadow-sm mb-4" style="border-left:4px solid var(--condux-acento) !important;">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-bold fs-6"><?= htmlspecialchars($g->descricao) ?></span>
        <span class="badge bg-success bg-opacity-10 text-success fw-semibold" style="font-size:.7rem;">Ativa</span>
      </div>
      <a href="<?= url("gestoes/{$g->id}") ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil"></i> Editar
      </a>
    </div>
    <p class="text-body-secondary mb-4" style="font-size:.82rem;">
      <i class="bi bi-calendar3 me-1"></i><?= $g->periodo() ?>
    </p>

    <div class="gestao-grid">
      <?php foreach ($g->membros as $m):
        $c = $cargoCor[$m['cargo']] ?? $cargoCor['conselheiro'];
        $rotulo = Gestao::$cargosRotulo[$m['cargo']] ?? $m['cargo'];
        $inicial = strtoupper(mb_substr($m['nome'], 0, 1));
      ?>
      <div class="gestao-card" style="border-top:3px solid <?= $c['border'] ?>; background:<?= $c['bg'] ?>;">
        <div class="gestao-avatar" style="border-color:<?= $c['border'] ?>; color:<?= $c['border'] ?>;">
          <?= $inicial ?>
        </div>
        <div class="gestao-cargo" style="color:<?= $c['border'] ?>;"><?= $rotulo ?></div>
        <div class="gestao-nome"><?= htmlspecialchars($m['nome']) ?></div>
        <div class="gestao-email"><?= htmlspecialchars($m['email']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($encerradas)): ?>
<h6 class="text-body-secondary text-uppercase fw-semibold mt-2 mb-3" style="font-size:.72rem;letter-spacing:.08em;">
  Gestões anteriores
</h6>
<div class="d-flex flex-column gap-3">
  <?php foreach ($encerradas as $g):
    $sindico = $g->sindico();
  ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body py-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 bg-secondary bg-opacity-10 text-secondary"
             style="width:44px;height:44px;font-size:1.2rem;">
          <i class="bi bi-archive"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="fw-semibold"><?= htmlspecialchars($g->descricao) ?></div>
          <div class="text-body-secondary" style="font-size:.8rem;">
            <i class="bi bi-calendar3 me-1"></i><?= $g->periodo() ?>
            <?php if ($sindico): ?>
              · <i class="bi bi-person me-1"></i><?= htmlspecialchars($sindico['nome']) ?>
            <?php endif; ?>
            · <?= count($g->membros) ?> membro<?= count($g->membros) !== 1 ? 's' : '' ?>
          </div>
        </div>
        <a href="<?= url("gestoes/{$g->id}") ?>" class="btn btn-outline-secondary btn-sm flex-shrink-0">
          <i class="bi bi-eye"></i>
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<style>
.gestao-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 16px;
}
.gestao-card {
  border-radius: 12px;
  padding: 20px 16px;
  text-align: center;
  border: 1px solid var(--bs-border-color);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}
.gestao-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: 2px solid;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 700;
  background: var(--bs-body-bg);
  margin-bottom: 4px;
}
.gestao-cargo {
  font-size: .62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
}
.gestao-nome  { font-weight: 600; font-size: .88rem; line-height: 1.3; color: var(--bs-body-color); }
.gestao-email { font-size: .72rem; color: var(--bs-secondary-color); }
</style>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
