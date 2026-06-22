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
      $uid              = (int) $u->id;
      $status           = $u->statusTaxaAtual ?? 'sem_taxa';
      $qtdMoradores     = count($moradoresPorUnidade[$uid] ?? []);
      $nomeProprietario = $u->exibirProprietario();
      $nomeInquilino    = $u->estaAlugada() ? $u->exibirInquilino() : null;
      $alugada          = $u->estaAlugada();
      $inicialProp      = $nomeProprietario ? mb_strtoupper(mb_substr(explode(' ', $nomeProprietario)[0], 0, 1)) : '?';
      $inicialInq       = $nomeInquilino    ? mb_strtoupper(mb_substr(explode(' ', $nomeInquilino)[0],    0, 1)) : '?';
    ?>
    <div class="col-sm-6 col-md-4 col-xl-3">
      <button type="button"
              class="card border-0 shadow-sm w-100 text-start btn-unidade p-0"
              data-bs-toggle="modal"
              data-bs-target="#modal-unidade-<?= $uid ?>">

        <!-- Faixa colorida no topo -->
        <div class="card-ocupacao-faixa <?= $alugada ? 'faixa-alugada' : 'faixa-propria' ?>"></div>

        <div class="card-body p-3 d-flex flex-column gap-0">

          <!-- Número + metadados -->
          <div class="mb-3">
            <div class="d-flex align-items-baseline gap-2">
              <span class="fw-black" style="font-size:1.45rem; line-height:1;"><?= htmlspecialchars($u->numero) ?></span>
              <?php if ($alugada): ?>
                <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:.65rem;">
                  <i class="bi bi-key"></i> Alugado
                </span>
              <?php else: ?>
                <span class="badge bg-success-subtle text-success-emphasis" style="font-size:.65rem;">
                  <i class="bi bi-house-check"></i> Próprio
                </span>
              <?php endif; ?>
            </div>
            <?php $meta = array_filter([$u->andar ? $u->andar.'º andar' : null, $u->descricao]); ?>
            <?php if ($meta): ?>
              <div class="text-body-secondary mt-1" style="font-size:.75rem;">
                <?= htmlspecialchars(implode(' · ', $meta)) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Pessoas -->
          <div class="d-flex flex-column gap-2 mb-3">
            <!-- Proprietário -->
            <div class="d-flex align-items-center gap-2" style="min-width:0;">
              <div class="avatar-unidade avatar-prop flex-shrink-0"><?= $inicialProp ?></div>
              <div style="min-width:0;">
                <div class="text-body-secondary" style="font-size:.68rem; line-height:1;">Proprietário</div>
                <div class="fw-semibold text-truncate" style="font-size:.82rem;">
                  <?= $nomeProprietario ? htmlspecialchars($nomeProprietario) : '<span class="opacity-40">Não definido</span>' ?>
                </div>
              </div>
            </div>

            <?php if ($alugada): ?>
            <!-- Inquilino -->
            <div class="d-flex align-items-center gap-2" style="min-width:0;">
              <div class="avatar-unidade avatar-inq flex-shrink-0"><?= $inicialInq ?></div>
              <div style="min-width:0;">
                <div class="text-body-secondary" style="font-size:.68rem; line-height:1;">Inquilino</div>
                <div class="fw-semibold text-truncate" style="font-size:.82rem;">
                  <?= $nomeInquilino ? htmlspecialchars($nomeInquilino) : '<span class="opacity-40">Não definido</span>' ?>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Rodapé: moradores + status financeiro -->
          <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
            <span class="text-body-secondary" style="font-size:.73rem;">
              <i class="bi bi-people me-1"></i>
              <?= $qtdMoradores ?> morador<?= $qtdMoradores !== 1 ? 'es' : '' ?>
            </span>
            <span class="badge rounded-pill badge-<?= $status ?>" style="font-size:.65rem;">
              <?= rotuloBadgeStatus($status) ?>
            </span>
          </div>

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
        <?php
          $taxasCond   = $taxasCondPorUnidade[$uid]   ?? [];
          $taxasExtras = $taxasExtrasPorUnidade[$uid]  ?? [];
          $pendCond    = count(array_filter($taxasCond,   fn($t) => in_array($t->status, ['pendente','vencido'])));
          $pendExtra   = count(array_filter($taxasExtras, fn($t) => in_array($t['status'] ?? 'pendente', ['pendente','vencido'])));
        ?>
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
              <?php if (count($moradores)): ?>
                <span class="badge bg-secondary bg-opacity-10 text-body ms-1"><?= count($moradores) ?></span>
              <?php endif; ?>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#tab-taxas-<?= $uid ?>" type="button">
              <i class="bi bi-receipt me-1"></i>Taxas
              <?php if ($pendCond): ?>
                <span class="badge bg-danger bg-opacity-75 ms-1"><?= $pendCond ?></span>
              <?php endif; ?>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#tab-extras-<?= $uid ?>" type="button">
              <i class="bi bi-plus-square me-1"></i>Extras
              <?php if ($pendExtra): ?>
                <span class="badge bg-danger bg-opacity-75 ms-1"><?= $pendExtra ?></span>
              <?php endif; ?>
            </button>
          </li>
        </ul>

        <div class="tab-content">

          <!-- ── Tab Ocupação ── -->
          <div class="tab-pane fade show active" id="tab-ocupacao-<?= $uid ?>" role="tabpanel">
            <form action="<?= url('unidades/salvar') ?>" method="POST">
              <input type="hidden" name="id"        value="<?= $uid ?>">
              <input type="hidden" name="numero"    value="<?= htmlspecialchars($u->numero) ?>">
              <input type="hidden" name="bloco"     value="<?= htmlspecialchars($u->bloco ?? '') ?>">
              <input type="hidden" name="andar"     value="<?= $u->andar ?? '' ?>">
              <input type="hidden" name="descricao" value="<?= htmlspecialchars($u->descricao ?? '') ?>">
              <input type="hidden" name="_retornar" value="lista">

              <!-- Toggle situação — segmented control -->
              <div class="d-flex rounded-2 overflow-hidden mb-4 border" style="height:42px;">
                <input type="radio" class="btn-check" name="tipo_ocupacao"
                       id="oc-proprio-<?= $uid ?>" value="proprio"
                       <?= $u->tipoOcupacao === 'proprio' ? 'checked' : '' ?>>
                <label class="btn btn-outline-success border-0 flex-fill rounded-0 d-flex align-items-center justify-content-center gap-2"
                       for="oc-proprio-<?= $uid ?>" style="font-size:.85rem;">
                  <i class="bi bi-house-check"></i> Próprio
                </label>
                <div class="vr"></div>
                <input type="radio" class="btn-check" name="tipo_ocupacao"
                       id="oc-alugado-<?= $uid ?>" value="alugado"
                       <?= $u->tipoOcupacao === 'alugado' ? 'checked' : '' ?>>
                <label class="btn btn-outline-warning border-0 flex-fill rounded-0 d-flex align-items-center justify-content-center gap-2"
                       for="oc-alugado-<?= $uid ?>" style="font-size:.85rem;">
                  <i class="bi bi-key"></i> Alugado
                </label>
              </div>

              <!-- Proprietário -->
              <div class="mb-4">
                <p class="fw-semibold mb-2" style="font-size:.88rem;">
                  <i class="bi bi-person-badge text-warning me-1"></i>Proprietário
                </p>
                <?php if (empty($todosCondominios)): ?>
                  <p class="text-body-secondary mb-0" style="font-size:.85rem;">
                    <a href="<?= url('condominios/novo') ?>">Cadastre um condômino</a> para vincular.
                  </p>
                <?php else: ?>
                  <?php renderPicker('proprietario_id', $todosCondominios, $u->proprietarioId,
                    'Buscar proprietário...', '— Sem proprietário —', [], true) ?>
                  <?php if ($u->proprietarioId && $u->nomeProprietarioVinc): ?>
                  <div class="d-flex align-items-center gap-3 p-3 rounded-2 mt-2" style="background:var(--bs-tertiary-bg);">
                    <div class="avatar-unidade avatar-prop flex-shrink-0">
                      <?= mb_strtoupper(mb_substr($u->nomeProprietarioVinc, 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                      <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($u->nomeProprietarioVinc) ?></div>
                      <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($u->emailProprietarioVinc ?? '') ?></div>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis flex-shrink-0" style="font-size:.68rem;">
                      <i class="bi bi-person-badge me-1"></i>Proprietário
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm flex-shrink-0 btn-limpar-picker"
                            data-target="proprietario_id">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>

              <!-- Inquilino (visível só quando alugado) -->
              <div id="bloco-inquilino-<?= $uid ?>" class="mb-4" style="display:<?= $u->estaAlugada() ? '' : 'none' ?>;">
                <p class="fw-semibold mb-2" style="font-size:.88rem;">
                  <i class="bi bi-key text-warning-emphasis me-1"></i>Inquilino
                </p>
                <?php if (!empty($todosCondominios)): ?>
                  <?php renderPicker('inquilino_id', $todosCondominios, $u->inquilinoId,
                    'Buscar inquilino...', '— Sem inquilino —', [], true) ?>
                  <?php if ($u->inquilinoId && $u->nomeInquilinoVinc): ?>
                  <div class="d-flex align-items-center gap-3 p-3 rounded-2 mt-2" style="background:var(--bs-tertiary-bg);">
                    <div class="avatar-unidade avatar-inq flex-shrink-0">
                      <?= mb_strtoupper(mb_substr($u->nomeInquilinoVinc, 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                      <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($u->nomeInquilinoVinc) ?></div>
                      <div class="text-body-secondary" style="font-size:.78rem;"><?= htmlspecialchars($u->emailInquilinoVinc ?? '') ?></div>
                    </div>
                    <span class="badge bg-info-subtle text-info-emphasis flex-shrink-0" style="font-size:.68rem;">
                      <i class="bi bi-key me-1"></i>Inquilino
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm flex-shrink-0 btn-limpar-picker"
                            data-target="inquilino_id">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>

              <!-- Rodapé de ações -->
              <div class="d-flex align-items-center gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-floppy me-1"></i>Salvar
                </button>
                <a href="<?= url("unidades/{$uid}/editar") ?>" class="btn btn-outline-secondary ms-auto">
                  Editar completo <i class="bi bi-arrow-right ms-1"></i>
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
                  <?php if ($m->usuarioId === $u->inquilinoId): ?>
                    <span class="badge bg-info-subtle text-info-emphasis flex-shrink-0" style="font-size:.68rem;">
                      <i class="bi bi-key me-1"></i>Inquilino
                    </span>
                  <?php elseif ($m->responsavel): ?>
                    <span class="badge badge-pago flex-shrink-0" style="font-size:.68rem;">Responsável</span>
                  <?php else: ?>
                    <span class="badge bg-secondary bg-opacity-25 text-body flex-shrink-0" style="font-size:.68rem;">
                      <i class="bi bi-person me-1"></i>Morador
                    </span>
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

          <!-- ── Tab Taxas Condominiais ── -->
          <div class="tab-pane fade" id="tab-taxas-<?= $uid ?>" role="tabpanel">
            <?php if (empty($taxasCond)): ?>
              <p class="text-body-secondary" style="font-size:.88rem;">
                <i class="bi bi-receipt me-1"></i>Nenhuma taxa condominial registrada.
              </p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
                  <thead class="table-light">
                    <tr>
                      <th>Competência</th>
                      <th class="text-end">Valor</th>
                      <th>Vencimento</th>
                      <th>Status</th>
                      <th>Pagamento</th>
                      <th>Anexo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($taxasCond as $t): ?>
                    <?php
                      $badgeClass = match($t->status) {
                        'pago'    => 'badge-pago',
                        'vencido' => 'bg-danger text-white',
                        'isento'  => 'bg-secondary-subtle text-secondary-emphasis',
                        default   => 'bg-warning-subtle text-warning-emphasis',
                      };
                      $rotulo = match($t->status) {
                        'pago'    => 'Pago',
                        'vencido' => 'Vencido',
                        'isento'  => 'Isento',
                        default   => 'Pendente',
                      };
                    ?>
                    <tr>
                      <td class="fw-semibold"><?= htmlspecialchars($t->competenciaFormatada()) ?></td>
                      <td class="text-end">R$ <?= number_format($t->valor, 2, ',', '.') ?></td>
                      <td><?= date('d/m/Y', strtotime($t->vencimento)) ?></td>
                      <td><span class="badge <?= $badgeClass ?>"><?= $rotulo ?></span></td>
                      <td class="text-body-secondary">
                        <?= $t->dataPagamento ? date('d/m/Y', strtotime($t->dataPagamento)) : '—' ?>
                      </td>
                      <td>
                        <?php if ($t->comprovante): ?>
                          <a href="/<?= htmlspecialchars($t->comprovante) ?>" target="_blank"
                             class="btn btn-outline-secondary btn-sm py-0 px-1" title="Ver comprovante">
                            <i class="bi bi-paperclip"></i>
                          </a>
                        <?php else: ?>
                          <span class="text-body-tertiary">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div><!-- /tab-taxas -->

          <!-- ── Tab Taxas Extras ── -->
          <div class="tab-pane fade" id="tab-extras-<?= $uid ?>" role="tabpanel">
            <?php if (empty($taxasExtras)): ?>
              <p class="text-body-secondary" style="font-size:.88rem;">
                <i class="bi bi-plus-square me-1"></i>Nenhuma taxa extra atribuída.
              </p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
                  <thead class="table-light">
                    <tr>
                      <th>Descrição</th>
                      <th class="text-end">Valor</th>
                      <th>Vencimento</th>
                      <th>Status</th>
                      <th>Pagamento</th>
                      <th>Anexo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($taxasExtras as $te): ?>
                    <?php
                      $stExtra = $te['status'] ?? 'pendente';
                      $badgeExtra = match($stExtra) {
                        'pago'    => 'badge-pago',
                        'vencido' => 'bg-danger text-white',
                        'isento'  => 'bg-secondary-subtle text-secondary-emphasis',
                        default   => 'bg-warning-subtle text-warning-emphasis',
                      };
                      $rotuloExtra = match($stExtra) {
                        'pago'    => 'Pago',
                        'vencido' => 'Vencido',
                        'isento'  => 'Isento',
                        default   => 'Pendente',
                      };
                      $parcela = ($te['parcela'] && $te['total_parcelas'])
                        ? " ({$te['parcela']}/{$te['total_parcelas']})" : '';
                    ?>
                    <tr>
                      <td>
                        <div class="fw-semibold"><?= htmlspecialchars($te['nome'] . $parcela) ?></div>
                        <?php if ($te['nome_projeto']): ?>
                          <div class="text-body-secondary" style="font-size:.75rem;">
                            <i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($te['nome_projeto']) ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">R$ <?= number_format((float)($te['valor'] ?? $te['valor_original']), 2, ',', '.') ?></td>
                      <td><?= date('d/m/Y', strtotime($te['vencimento'])) ?></td>
                      <td><span class="badge <?= $badgeExtra ?>"><?= $rotuloExtra ?></span></td>
                      <td class="text-body-secondary">
                        <?= !empty($te['data_pagamento']) ? date('d/m/Y', strtotime($te['data_pagamento'])) : '—' ?>
                      </td>
                      <td>
                        <?php if (!empty($te['comprovante'])): ?>
                          <a href="/<?= htmlspecialchars($te['comprovante']) ?>" target="_blank"
                             class="btn btn-outline-secondary btn-sm py-0 px-1" title="Ver comprovante">
                            <i class="bi bi-paperclip"></i>
                          </a>
                        <?php else: ?>
                          <span class="text-body-tertiary">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div><!-- /tab-extras -->

        </div><!-- /tab-content -->
      </div><!-- /modal-body -->

    </div>
  </div>
</div>
<?php endforeach; ?>

<style>
.btn-unidade {
  cursor: pointer;
  transition: transform .13s ease, box-shadow .13s ease;
  border-radius: .6rem !important;
  text-align: left;
  overflow: hidden;
}
.btn-unidade:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,.13) !important;
}
.card-ocupacao-faixa {
  height: 5px;
  width: 100%;
}
.faixa-propria { background: linear-gradient(90deg, var(--bs-success), #34d399); }
.faixa-alugada { background: linear-gradient(90deg, var(--bs-warning), #fcd34d); }

/* Avatares de pessoa no card */
.avatar-unidade {
  width: 30px; height: 30px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; font-weight: 700; line-height: 1;
}
.avatar-prop { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
.avatar-inq  { background: rgba(var(--bs-warning-rgb), .18); color: var(--bs-warning-emphasis); }
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

  /* Botão X nos cards de proprietário/inquilino — limpa o picker correspondente */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-limpar-picker');
    if (!btn) return;
    var fieldName = btn.dataset.target;
    // Encontrar o picker pelo input hidden name dentro do mesmo form
    var form = btn.closest('form');
    if (!form) return;
    var hidden = form.querySelector('input[type=hidden][name="' + fieldName + '"]');
    var picker = hidden ? hidden.closest('.condux-picker') : null;
    if (hidden) hidden.value = '';
    if (picker) {
      var textInput = picker.querySelector('.condux-picker-input');
      if (textInput) textInput.value = '';
      picker.querySelectorAll('.condux-picker-opt').forEach(function (o) {
        o.classList.remove('selecionado');
      });
    }
    // Esconder o card visual
    btn.closest('.d-flex.align-items-center.gap-3').style.display = 'none';
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
