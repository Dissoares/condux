<?php
/** @var Unidade|null $unidade @var TaxaCondominial|null $taxaMesAtual @var TaxaCondominial[] $taxasPendentes */
$tituloPagina = 'Meu Painel';
require_once RAIZ . '/views/layouts/cabecalho.php';
$primeiroNome = explode(' ', Sessao::usuarioAtual()['nome'] ?? 'Morador')[0];
?>

<div class="mb-4">
  <h4 class="fw-semibold mb-0">Olá, <?= htmlspecialchars($primeiroNome) ?> 👋</h4>
  <p class="text-body-secondary mb-0">Bem-vindo ao seu painel do condomínio.</p>
</div>

<?php if (!$unidade): ?>
  <div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
    Sua conta ainda não está vinculada a nenhuma unidade. Aguarde o síndico concluir o cadastro.
  </div>
<?php else: ?>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary" style="width:48px;height:48px;font-size:1.3rem;">
            <i class="bi bi-building"></i>
          </div>
          <div>
            <div class="fw-bold lh-1"><?= htmlspecialchars($unidade->identificacao()) ?></div>
            <div class="text-body-secondary" style="font-size:.8rem;">Sua unidade</div>
          </div>
        </div>
      </div>
    </div>
    <?php if ($taxaMesAtual): ?>
    <?php $statusEfAtual = $taxaMesAtual->estaVencido() ? 'vencido' : $taxaMesAtual->status; ?>
    <?php $corIcone = match($statusEfAtual) { 'pago' => 'success', 'vencido' => 'danger', default => 'warning' }; ?>
    <div class="col-sm-6 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-<?= $corIcone ?> bg-opacity-10 text-<?= $corIcone ?>" style="width:48px;height:48px;font-size:1.3rem;">
            <i class="bi bi-<?= $taxaMesAtual->estaPago() ? 'check-circle-fill' : 'clock-fill' ?>"></i>
          </div>
          <div>
            <div class="fw-bold lh-1"><?= dinheiro($taxaMesAtual->valor) ?></div>
            <div class="text-body-secondary" style="font-size:.8rem;">
              Taxa <?= htmlspecialchars($taxaMesAtual->competenciaFormatada()) ?>
              — <span class="badge rounded-pill badge-<?= $statusEfAtual ?>"><?= ['pago'=>'Pago','vencido'=>'Atrasado','isento'=>'Isento'][$statusEfAtual] ?? 'Pendente' ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($taxaMesAtual && !$taxaMesAtual->estaPago()): ?>
  <div class="card border-0 shadow-sm mb-4" style="max-width:520px;">
    <div class="card-header bg-transparent fw-semibold py-3"><i class="bi bi-upload"></i> Enviar comprovante</div>
    <div class="card-body">
      <?php if ($taxaMesAtual->comprovante): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-0">
          <i class="bi bi-hourglass-split flex-shrink-0"></i> Comprovante enviado. Aguardando aprovação do síndico.
        </div>
      <?php else: ?>
        <form action="<?= url('minhas-taxas/comprovante') ?>" method="POST" enctype="multipart/form-data">
          <?php $taxaId = (int)$taxaMesAtual->id; ?>
          <input type="hidden" name="taxa_id" value="<?= $taxaId ?>">
          <div class="mb-3">
            <label for="arquivo-comprovante" class="form-label">Comprovante (PDF, JPG ou PNG)</label>
            <input type="file" id="arquivo-comprovante" name="comprovante" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>
          <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Enviar comprovante</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($taxasPendentes)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent fw-semibold py-3">
      <i class="bi bi-exclamation-triangle text-warning me-1"></i> Taxas pendentes
    </div>

    <!-- Mobile: itens em lista -->
    <div class="d-md-none">
      <?php foreach ($taxasPendentes as $i => $taxa):
        $statusEf = $taxa->estaVencido() ? 'vencido' : $taxa->status;
        $badgeClass = match($statusEf) {
          'vencido'    => 'bg-danger text-white',
          'aguardando' => 'badge-aguardando',
          default      => 'badge-pendente',
        };
        $rotulo = match($statusEf) {
          'vencido'    => 'Atrasado',
          'aguardando' => 'Enviado',
          default      => 'Pendente',
        };
      ?>
      <div class="d-flex align-items-center justify-content-between px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?>">
        <div>
          <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($taxa->competenciaFormatada()) ?></div>
          <div class="text-body-secondary" style="font-size:.78rem;">Venc. <?= dataBR($taxa->vencimento) ?></div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="fw-semibold" style="font-size:.9rem;"><?= dinheiro($taxa->valor) ?></span>
          <span class="badge rounded-pill <?= $badgeClass ?>"><?= $rotulo ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="px-3 pb-3 pt-1">
        <a href="<?= url('minhas-taxas') ?>" class="btn btn-primary btn-sm w-100">
          <i class="bi bi-send me-1"></i>Pagar / enviar comprovante
        </a>
      </div>
    </div>

    <!-- Desktop: tabela -->
    <div class="d-none d-md-block table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Competência</th><th>Valor</th><th>Vencimento</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($taxasPendentes as $taxa):
            $statusEf = $taxa->estaVencido() ? 'vencido' : $taxa->status;
            $badgeClass = match($statusEf) {
              'vencido'    => 'bg-danger text-white',
              'aguardando' => 'badge-aguardando',
              default      => 'badge-pendente',
            };
            $rotulo = match($statusEf) { 'vencido' => 'Atrasado', 'aguardando' => 'Enviado', default => 'Pendente' };
          ?>
          <tr>
            <td><?= htmlspecialchars($taxa->competenciaFormatada()) ?></td>
            <td><?= dinheiro($taxa->valor) ?></td>
            <td><?= dataBR($taxa->vencimento) ?></td>
            <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= $rotulo ?></span></td>
            <td class="text-end">
              <?php if ($statusEf !== 'aguardando'): ?>
              <a href="<?= url('minhas-taxas') ?>" class="btn btn-primary btn-sm py-0 px-2">
                <i class="bi bi-send me-1"></i>Pagar
              </a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

<?php endif; ?>

<!-- Comunicados -->
<?php if (!empty($comunicados)): ?>
<div class="mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-semibold mb-0"><i class="bi bi-megaphone"></i> Comunicados</h5>
    <a href="<?= url('comunicados') ?>" class="btn btn-outline-secondary btn-sm">Ver todos</a>
  </div>
  <div class="d-flex flex-column gap-2">
    <?php foreach ($comunicados as $c): ?>
    <?php $corCss = $c->cor() === 'purple' ? 'primary' : $c->cor(); ?>
    <div class="card border-0 shadow-sm" style="border-left:3px solid var(--bs-<?= $corCss ?>)!important;">
      <div class="card-body py-2 px-3 d-flex align-items-start gap-3">
        <i class="bi <?= $c->icone() ?> text-<?= $corCss ?> mt-1 flex-shrink-0"></i>
        <div class="flex-grow-1 min-w-0">
          <div class="fw-semibold" style="font-size:.875rem;"><?= htmlspecialchars($c->titulo) ?></div>
          <div class="text-body-secondary text-truncate" style="font-size:.78rem;"><?= htmlspecialchars(mb_substr($c->conteudo, 0, 100)) ?><?= mb_strlen($c->conteudo) > 100 ? '…' : '' ?></div>
        </div>
        <span class="text-body-secondary flex-shrink-0" style="font-size:.72rem;white-space:nowrap;">
          <?= date('d/m', strtotime($c->dataPublicacao)) ?>
        </span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
