<?php
/**
 * @var Unidade[]         $unidades
 * @var array[]           $todosCondominios
 * @var array<int,Morador[]> $moradoresPorUnidade
 * @var int               $abrirModalId
 */
$tituloPagina = 'Unidades';
require_once RAIZ . '/views/layouts/cabecalho.php';
require_once RAIZ . '/views/partials/picker.php';

// Agrupar por bloco
$porBloco = [];
foreach ($unidades as $u) {
    $chave = $u->bloco ? 'Bloco ' . strtoupper($u->bloco) : 'Sem bloco';
    $porBloco[$chave][] = $u;
}
ksort($porBloco);

function rotuloBadgeStatus(string $status): string {
    return match($status) {
        'pago'    => 'Pago',
        'vencido' => 'Vencido',
        'isento'  => 'Isento',
        'pendente'=> 'Pendente',
        default   => 'Sem taxa',
    };
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-semibold mb-0"><i class="bi bi-building text-primary"></i> Unidades</h4>
    <p class="text-body-secondary mb-0 mt-1" style="font-size:.85rem;">
      <?= count($unidades) ?> unidade<?= count($unidades) !== 1 ? 's' : '' ?> cadastrada<?= count($unidades) !== 1 ? 's' : '' ?>
    </p>
  </div>
  <a href="<?= url('unidades/nova') ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Nova unidade
  </a>
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

<?php if (empty($unidades)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5 text-center">
      <i class="bi bi-building text-body-secondary mb-2" style="font-size:2.5rem;opacity:.35;"></i>
      <p class="text-body-secondary mb-3">Nenhuma unidade cadastrada ainda.</p>
      <a href="<?= url('unidades/nova') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Cadastrar primeira unidade
      </a>
    </div>
  </div>

<?php else: ?>

<?php foreach ($porBloco as $nomeBloco => $unidadesBloco): ?>
<div class="mb-5">
  <h6 class="text-body-secondary text-uppercase fw-semibold mb-3 d-flex align-items-center gap-2"
      style="font-size:.72rem;letter-spacing:.08em;">
    <i class="bi bi-grid-3x3-gap"></i>
    <?= htmlspecialchars($nomeBloco) ?>
    <span class="badge bg-secondary bg-opacity-10 text-body fw-normal" style="font-size:.75rem;text-transform:none;letter-spacing:0;">
      <?= count($unidadesBloco) ?> unidade<?= count($unidadesBloco) !== 1 ? 's' : '' ?>
    </span>
  </h6>

  <div class="row g-3">
    <?php foreach ($unidadesBloco as $u): ?>
    <?php
      $uid    = (int) $u->id;
      $status = $u->statusTaxaAtual ?? 'sem_taxa';
      $qtdMoradores = count($moradoresPorUnidade[$uid] ?? []);
      $nomeProprietario = $u->exibirProprietario();
    ?>
    <div class="col-sm-6 col-md-4 col-xl-3">
      <button type="button"
              class="card border-0 shadow-sm w-100 text-start btn-unidade p-0"
              data-bs-toggle="modal"
              data-bs-target="#modal-unidade-<?= $uid ?>">
        <div class="card-body p-3">

          <!-- Cabeçalho do card -->
          <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
            <div class="fw-bold" style="font-size:1rem; line-height:1.2;">
              <?php if ($u->bloco): ?>
                <span class="text-body-secondary fw-normal" style="font-size:.8rem;">Apto</span>
              <?php endif; ?>
              <?= htmlspecialchars($u->numero) ?>
            </div>
            <span class="badge rounded-pill badge-<?= $status ?> flex-shrink-0" style="font-size:.68rem;">
              <?= rotuloBadgeStatus($status) ?>
            </span>
          </div>

          <!-- Andar -->
          <?php if ($u->andar): ?>
            <div class="text-body-secondary mb-2" style="font-size:.78rem;">
              <i class="bi bi-layers me-1"></i><?= $u->andar ?>º andar
            </div>
          <?php endif; ?>

          <!-- Tipo de ocupação -->
          <div class="mb-2" style="font-size:.78rem;">
            <?php if ($u->estaAlugada()): ?>
              <span class="text-warning-emphasis"><i class="bi bi-key me-1"></i>Alugado</span>
            <?php else: ?>
              <span class="text-success-emphasis"><i class="bi bi-house-check me-1"></i>Próprio</span>
            <?php endif; ?>
          </div>

          <!-- Proprietário -->
          <div class="text-body-secondary" style="font-size:.78rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <i class="bi bi-person me-1"></i>
            <?= $nomeProprietario ? htmlspecialchars($nomeProprietario) : '<span class="opacity-50">Sem proprietário</span>' ?>
          </div>

          <!-- Moradores -->
          <?php if ($qtdMoradores > 0): ?>
          <div class="text-body-secondary mt-1" style="font-size:.75rem;">
            <i class="bi bi-people me-1"></i><?= $qtdMoradores ?> morador<?= $qtdMoradores !== 1 ? 'es' : '' ?>
          </div>
          <?php endif; ?>

        </div>
      </button>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     MODAIS — um por unidade
══════════════════════════════════════════════════════ -->
<?php foreach ($unidades as $u): ?>
<?php
  $uid     = (int) $u->id;
  $status  = $u->statusTaxaAtual ?? 'sem_taxa';
  $moradores = $moradoresPorUnidade[$uid] ?? [];
  $nomeProprietario = $u->exibirProprietario();
  $nomeInquilino    = $u->exibirInquilino();
?>
<div class="modal fade" id="modal-unidade-<?= $uid ?>" tabindex="-1"
     aria-labelledby="label-modal-<?= $uid ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <!-- Header -->
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title fw-bold mb-0" id="label-modal-<?= $uid ?>">
            <?= htmlspecialchars($u->identificacao()) ?>
          </h5>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge rounded-pill badge-<?= $status ?>"><?= rotuloBadgeStatus($status) ?></span>
            <?php if ($u->andar): ?>
              <span class="text-body-secondary" style="font-size:.8rem;"><?= $u->andar ?>º andar</span>
            <?php endif; ?>
            <?php if ($u->descricao): ?>
              <span class="text-body-secondary" style="font-size:.8rem;"><?= htmlspecialchars($u->descricao) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Tabs -->
      <div class="modal-body pt-2">
        <ul class="nav nav-tabs mb-4" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab"
                    data-bs-target="#tab-ocupacao-<?= $uid ?>" type="button">
              <i class="bi bi-house-door me-1"></i>Ocupação
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#tab-moradores-<?= $uid ?>" type="button">
              <i class="bi bi-people me-1"></i>Moradores
              <?php if ($qtdMoradores = count($moradores)): ?>
                <span class="badge bg-secondary bg-opacity-10 text-body ms-1"><?= $qtdMoradores ?></span>
              <?php endif; ?>
            </button>
          </li>
        </ul>

        <div class="tab-content">

          <!-- ── Tab Ocupação ── -->
          <div class="tab-pane fade show active" id="tab-ocupacao-<?= $uid ?>" role="tabpanel">
            <form action="<?= url('unidades/salvar') ?>" method="POST">
              <input type="hidden" name="id"         value="<?= $uid ?>">
              <input type="hidden" name="numero"     value="<?= htmlspecialchars($u->numero) ?>">
              <input type="hidden" name="bloco"      value="<?= htmlspecialchars($u->bloco ?? '') ?>">
              <input type="hidden" name="andar"      value="<?= $u->andar ?? '' ?>">
              <input type="hidden" name="descricao"  value="<?= htmlspecialchars($u->descricao ?? '') ?>">
              <input type="hidden" name="_retornar"  value="lista">

              <div class="mb-4">
                <label class="form-label fw-semibold">Situação da unidade</label>
                <div class="d-flex gap-2">
                  <?php foreach (['proprio' => ['Próprio', 'house-check', 'success'], 'alugado' => ['Alugado', 'key', 'warning']] as $val => [$rot, $ico, $cor]): ?>
                  <div class="flex-fill">
                    <input type="radio" class="btn-check" name="tipo_ocupacao"
                           id="oc-<?= $val ?>-<?= $uid ?>" value="<?= $val ?>"
                           <?= $u->tipoOcupacao === $val ? 'checked' : '' ?>>
                    <label class="btn btn-outline-<?= $cor ?> w-100 d-flex flex-column align-items-center gap-1 py-2"
                           for="oc-<?= $val ?>-<?= $uid ?>" style="font-size:.82rem;">
                      <i class="bi bi-<?= $ico ?>" style="font-size:1.2rem;"></i><?= $rot ?>
                    </label>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Proprietário -->
              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-person-badge text-warning me-1"></i>Proprietário
                </label>
                <?php if (empty($todosCondominios)): ?>
                  <p class="text-body-secondary mb-0" style="font-size:.85rem;">
                    <a href="<?= url('condominios/novo') ?>">Cadastre um condômino</a> para vincular.
                  </p>
                <?php else: ?>
                  <?php renderPicker('proprietario_id', $todosCondominios, $u->proprietarioId, 'Buscar proprietário pelo nome...', '— Sem proprietário —') ?>
                <?php endif; ?>
              </div>

              <!-- Inquilino -->
              <div id="bloco-inquilino-<?= $uid ?>" class="mb-4" style="display:<?= $u->estaAlugada() ? '' : 'none' ?>;">
                <label class="form-label fw-semibold">
                  <i class="bi bi-person-check text-info me-1"></i>Inquilino
                </label>
                <?php if (!empty($todosCondominios)): ?>
                  <?php renderPicker('inquilino_id', $todosCondominios, $u->inquilinoId, 'Buscar inquilino pelo nome...', '— Sem inquilino —') ?>
                <?php endif; ?>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                  <i class="bi bi-floppy"></i> Salvar ocupação
                </button>
                <a href="<?= url("unidades/{$uid}/editar") ?>" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-pencil"></i> Editar completo
                </a>
              </div>
            </form>
          </div><!-- /tab-ocupacao -->

          <!-- ── Tab Moradores ── -->
          <div class="tab-pane fade" id="tab-moradores-<?= $uid ?>" role="tabpanel">

            <!-- Lista de moradores -->
            <?php if (empty($moradores)): ?>
              <p class="text-body-secondary mb-4" style="font-size:.88rem;">
                <i class="bi bi-person-slash me-1"></i>Nenhum morador vinculado.
              </p>
            <?php else: ?>
              <div class="d-flex flex-column gap-2 mb-4">
                <?php foreach ($moradores as $m): ?>
                <div class="d-flex align-items-center gap-3 p-3 rounded-2" style="background:var(--bs-tertiary-bg);">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary"
                       style="width:36px;height:36px;font-size:.9rem;font-weight:700;">
                    <?= mb_strtoupper(mb_substr($m->nomeUsuario ?? 'M', 0, 1)) ?>
                  </div>
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($m->nomeUsuario ?? '—') ?></div>
                    <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($m->emailUsuario ?? '') ?></div>
                  </div>
                  <?php if ($m->responsavel): ?>
                    <span class="badge badge-pago flex-shrink-0" style="font-size:.68rem;">Responsável</span>
                  <?php endif; ?>
                  <a href="<?= url("unidades/{$uid}/desvincular-morador/{$m->id}?retornar=lista") ?>"
                     class="btn btn-outline-danger btn-sm flex-shrink-0"
                     onclick="return confirm('Desvincular <?= htmlspecialchars(addslashes($m->nomeUsuario ?? '')) ?>?')">
                    <i class="bi bi-x-lg"></i>
                  </a>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Adicionar morador -->
            <div class="border-top pt-4">
              <p class="fw-semibold mb-3" style="font-size:.88rem;">
                <i class="bi bi-person-plus text-success me-1"></i>Adicionar morador
              </p>
              <?php if (empty($todosCondominios)): ?>
                <p class="text-body-secondary" style="font-size:.85rem;">
                  <a href="<?= url('condominios/novo') ?>">Cadastre condôminos</a> para vincular.
                </p>
              <?php else: ?>
                <?php $idsJaVinculados = array_map(fn($m) => $m->usuarioId, $moradores); ?>
                <form action="<?= url("unidades/{$uid}/vincular-existente") ?>" method="POST">
                  <input type="hidden" name="unidade_id"  value="<?= $uid ?>">
                  <input type="hidden" name="data_entrada" value="<?= date('Y-m-d') ?>">
                  <input type="hidden" name="_retornar"   value="lista">

                  <div class="mb-3">
                    <?php renderPicker('usuario_id', $todosCondominios, null, 'Buscar condômino pelo nome...', '— Selecione o morador —', $idsJaVinculados) ?>
                  </div>

                  <div class="d-flex align-items-center gap-3">
                    <?php if ($u->inquilinoId): ?>
                      <span class="text-body-secondary" style="font-size:.8rem;">
                        <i class="bi bi-info-circle me-1"></i>Adicionado como morador simples — o inquilino já é o responsável.
                      </span>
                    <?php else: ?>
                      <div class="form-check mb-0">
                        <input type="checkbox" name="responsavel" value="1"
                               class="form-check-input" id="resp-<?= $uid ?>">
                        <label class="form-check-label" for="resp-<?= $uid ?>" style="font-size:.85rem;">
                          Responsável financeiro
                        </label>
                      </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-success btn-sm ms-auto">
                      <i class="bi bi-person-plus"></i> Adicionar
                    </button>
                    <a href="<?= url("condominios/novo") ?>" class="btn btn-outline-secondary btn-sm">
                      <i class="bi bi-person-plus-fill"></i> Novo
                    </a>
                  </div>
                </form>
              <?php endif; ?>
            </div>

          </div><!-- /tab-moradores -->

        </div><!-- /tab-content -->
      </div><!-- /modal-body -->

    </div>
  </div>
</div>
<?php endforeach; ?>

<style>
.btn-unidade {
  cursor: pointer;
  transition: transform .12s ease, box-shadow .12s ease;
  border-radius: .5rem !important;
  text-align: left;
}
.btn-unidade:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0,0,0,.1) !important;
}
</style>

<script>
(function () {
  /* Mostrar/ocultar campo de inquilino conforme tipo_ocupacao */
  document.querySelectorAll('[name="tipo_ocupacao"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
      var uid   = this.id.replace(/^oc-(?:proprio|alugado)-/, '');
      var bloco = document.getElementById('bloco-inquilino-' + uid);
      if (bloco) bloco.style.display = this.value === 'alugado' ? '' : 'none';
    });
  });

  /* Reabrir modal após redirect com ?abrir={id} */
  var params = new URLSearchParams(window.location.search);
  var abrirId = params.get('abrir');
  if (abrirId) {
    var el = document.getElementById('modal-unidade-' + abrirId);
    if (el) {
      var modal = new bootstrap.Modal(el);
      modal.show();
      // Limpar o param da URL sem recarregar
      params.delete('abrir');
      history.replaceState(null, '', window.location.pathname + (params.toString() ? '?' + params : ''));
    }
  }
}());
</script>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
