<?php
/**
 * @var Funcionario            $funcionario
 * @var FuncionarioPagamento[] $pagamentos
 * @var FuncionarioOcorrencia[] $ocorrencias
 * @var string|null            $mensagem
 * @var string|null            $erroMensagem
 */
$tituloPagina = $funcionario->nome;
require_once RAIZ . '/views/layouts/cabecalho.php';

$fid     = $funcionario->id;
$fmtData = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$fmtVal  = fn(?float $v)  => $v !== null ? 'R$ ' . number_format($v, 2, ',', '.') : '—';

// Aba ativa via query string
$aba = $_GET['aba'] ?? 'info';

// Competência padrão = mês atual
$compAtual = date('Y-m');
?>

<style>
.nav-tabs .nav-link { font-size:.85rem; }
.ocor-badge { font-size:.7rem; padding:.2em .5em; }
.info-row { display:flex; padding:.5rem 0; border-bottom:1px solid var(--bs-border-color-translucent); font-size:.875rem; }
.info-row dt { min-width:130px; color:var(--bs-secondary-color); font-weight:normal; }
.info-row dd { margin:0; font-weight:600; }
</style>

<!-- Cabeçalho -->
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex align-items-center gap-3">
    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                <?= $funcionario->ativo ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-secondary' ?>"
         style="width:52px;height:52px;font-size:1.3rem;font-weight:700;">
      <?= mb_strtoupper(mb_substr($funcionario->nome, 0, 1)) ?>
    </div>
    <div>
      <h4 class="fw-bold mb-0"><?= htmlspecialchars($funcionario->nome) ?></h4>
      <div class="text-body-secondary" style="font-size:.85rem;">
        <?= htmlspecialchars($funcionario->cargo) ?>
        <?php if ($funcionario->departamento): ?> · <?= htmlspecialchars($funcionario->departamento) ?><?php endif; ?>
        <?php if (!$funcionario->ativo): ?>
          <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">Inativo</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url("funcionarios/{$fid}/editar") ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('funcionarios') ?>" class="btn btn-outline-secondary btn-sm">
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

<!-- Abas -->
<ul class="nav nav-tabs mb-3" id="tabs-func">
  <?php foreach ([
    'info'        => ['bi-person-lines-fill', 'Informações'],
    'pagamentos'  => ['bi-cash-stack',        'Pagamentos'  . (count($pagamentos) ? ' <span class="badge bg-secondary rounded-pill">' . count($pagamentos) . '</span>' : '')],
    'ocorrencias' => ['bi-calendar-event',    'Ocorrências' . (count($ocorrencias) ? ' <span class="badge bg-secondary rounded-pill">' . count($ocorrencias) . '</span>' : '')],
  ] as $chave => [$ico, $label]): ?>
  <li class="nav-item">
    <a class="nav-link <?= $aba === $chave ? 'active' : '' ?>"
       href="<?= url("funcionarios/{$fid}?aba={$chave}") ?>">
      <i class="bi bi-<?= $ico ?> me-1"></i><?= $label ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<!-- ── ABA: INFORMAÇÕES ── -->
<?php if ($aba === 'info'): ?>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3">Dados pessoais</div>
      <div class="card-body p-3">
        <dl class="mb-0">
          <?php foreach ([
            ['CPF',         $funcionario->cpf          ? htmlspecialchars($funcionario->cpf)          : '—'],
            ['Telefone',    $funcionario->telefone      ? '<a href="tel:'.htmlspecialchars($funcionario->telefone).'">'.htmlspecialchars($funcionario->telefone).'</a>' : '—'],
            ['E-mail',      $funcionario->email         ? '<a href="mailto:'.htmlspecialchars($funcionario->email).'">'.htmlspecialchars($funcionario->email).'</a>' : '—'],
            ['Admissão',    $fmtData($funcionario->dataAdmissao)],
            ['Demissão',    $fmtData($funcionario->dataDemissao)],
          ] as [$label, $valor]): ?>
          <div class="info-row">
            <dt><?= $label ?></dt>
            <dd><?= $valor ?></dd>
          </div>
          <?php endforeach; ?>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3">Remuneração</div>
      <div class="card-body p-3">
        <dl class="mb-0">
          <?php foreach ([
            ['Salário',         $fmtVal($funcionario->salario)],
            ['Dia de pagamento', $funcionario->diaPagamento ? 'Todo dia ' . $funcionario->diaPagamento : '—'],
          ] as [$label, $valor]): ?>
          <div class="info-row">
            <dt><?= $label ?></dt>
            <dd><?= $valor ?></dd>
          </div>
          <?php endforeach; ?>
        </dl>

        <?php
        // Verificar situação do salário do mês atual
        $pagMes = null;
        foreach ($pagamentos as $pg) {
            if ($pg->competencia === $compAtual) { $pagMes = $pg; break; }
        }
        $hoje       = date('Y-m-d');
        $diaVenc    = $funcionario->diaPagamento;
        $dataVenc   = $diaVenc ? date('Y-m-') . str_pad((string)$diaVenc, 2, '0', STR_PAD_LEFT) : null;
        $atrasado   = $dataVenc && $hoje > $dataVenc && (!$pagMes || $pagMes->status === 'pendente');
        ?>
        <?php if ($diaVenc): ?>
        <div class="mt-3 p-2 rounded-2 <?= $atrasado ? 'bg-danger bg-opacity-10' : ($pagMes && $pagMes->status === 'pago' ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10') ?>">
          <div class="d-flex align-items-center gap-2" style="font-size:.82rem;">
            <i class="bi bi-<?= $atrasado ? 'exclamation-triangle-fill text-danger' : ($pagMes && $pagMes->status === 'pago' ? 'check-circle-fill text-success' : 'clock-fill text-warning') ?>"></i>
            <span class="fw-semibold">
              <?php if ($pagMes && $pagMes->status === 'pago'): ?>
                Salário pago em <?= $fmtData($pagMes->dataPagamento) ?>
              <?php elseif ($atrasado): ?>
                Salário atrasado — venceu dia <?= $diaVenc ?>
              <?php else: ?>
                Salário pendente — vence dia <?= $diaVenc ?>
              <?php endif; ?>
            </span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($funcionario->observacoes): ?>
  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold py-3">Observações</div>
      <div class="card-body p-3" style="font-size:.875rem;line-height:1.6;">
        <?= nl2br(htmlspecialchars($funcionario->observacoes)) ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ── ABA: PAGAMENTOS ── -->
<?php elseif ($aba === 'pagamentos'): ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3">Histórico de pagamentos</div>
      <?php if (empty($pagamentos)): ?>
        <div class="card-body text-center py-5 text-body-secondary">
          <i class="bi bi-cash-stack fs-1 opacity-25 d-block mb-2"></i>
          Nenhum pagamento registrado.
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead class="table-light">
            <tr>
              <th>Competência</th>
              <th>Valor</th>
              <th>Previsto</th>
              <th>Pago em</th>
              <th>Status</th>
              <th style="width:40px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pagamentos as $pg): ?>
            <?php $pgAtrasado = $pg->estaAtrasado(); ?>
            <tr>
              <td class="fw-semibold"><?= $pg->competenciaBR() ?></td>
              <td><?= $fmtVal($pg->valor) ?></td>
              <td><?= $fmtData($pg->dataPrevista) ?></td>
              <td><?= $fmtData($pg->dataPagamento) ?></td>
              <td>
                <?php if ($pg->status === 'pago'): ?>
                  <span class="badge bg-success-subtle text-success-emphasis">Pago</span>
                <?php elseif ($pgAtrasado): ?>
                  <span class="badge bg-danger-subtle text-danger-emphasis">Atrasado</span>
                <?php else: ?>
                  <span class="badge bg-warning-subtle text-warning-emphasis">Pendente</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= url("funcionarios/{$fid}/pagamentos/{$pg->id}/excluir") ?>"
                   onclick="return confirm('Remover este pagamento?')"
                   class="btn btn-outline-danger btn-sm py-0 px-1">
                  <i class="bi bi-trash"></i>
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

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3">Registrar pagamento</div>
      <div class="card-body p-3">
        <form action="<?= url("funcionarios/{$fid}/pagamentos") ?>" method="POST">
          <div class="mb-3">
            <label class="form-label">Competência *</label>
            <input type="month" name="competencia" class="form-control" required
                   value="<?= $compAtual ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Valor (R$) *</label>
            <input type="text" name="valor" class="form-control" required
                   placeholder="0,00"
                   value="<?= $funcionario->salario !== null ? number_format($funcionario->salario, 2, ',', '.') : '' ?>">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Data prevista</label>
              <input type="date" name="data_prevista" class="form-control"
                     value="<?= $funcionario->diaPagamento ? date('Y-m-') . str_pad((string)$funcionario->diaPagamento, 2, '0', STR_PAD_LEFT) : '' ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Data do pagamento</label>
              <input type="date" name="data_pagamento" class="form-control">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Observações</label>
            <input type="text" name="observacoes" class="form-control" placeholder="Opcional...">
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check2"></i> Registrar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ── ABA: OCORRÊNCIAS ── -->
<?php elseif ($aba === 'ocorrencias'): ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3">Ocorrências registradas</div>
      <?php if (empty($ocorrencias)): ?>
        <div class="card-body text-center py-5 text-body-secondary">
          <i class="bi bi-calendar-x fs-1 opacity-25 d-block mb-2"></i>
          Nenhuma ocorrência registrada.
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead class="table-light">
            <tr>
              <th>Tipo</th>
              <th>Período</th>
              <th class="d-none d-md-table-cell">Justificativa</th>
              <th>Status</th>
              <th style="width:80px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ocorrencias as $oc): ?>
            <tr>
              <td>
                <span class="badge bg-<?= $oc->cor() ?>-subtle text-<?= $oc->cor() ?>-emphasis ocor-badge">
                  <?= $oc->rotuloTipo() ?>
                </span>
                <?php if ($oc->tipo === 'adiantamento' && $oc->valor !== null): ?>
                  <div class="text-body-secondary" style="font-size:.7rem;"><?= $fmtVal($oc->valor) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <div><?= $fmtData($oc->dataInicio) ?></div>
                <?php if ($oc->dataFim && $oc->dataFim !== $oc->dataInicio): ?>
                  <div class="text-body-secondary" style="font-size:.75rem;">
                    até <?= $fmtData($oc->dataFim) ?>
                    <?php $dias = $oc->diasDuracao(); if ($dias): ?>(<?= $dias ?> dias)<?php endif; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td class="d-none d-md-table-cell text-body-secondary">
                <?= $oc->justificativa ? mb_strimwidth(htmlspecialchars($oc->justificativa), 0, 50, '…') : '—' ?>
                <?php if ($oc->anexo): ?>
                  <a href="<?= url('uploads/' . $oc->anexo) ?>" target="_blank" class="ms-1 text-primary" title="Ver anexo">
                    <i class="bi bi-paperclip"></i>
                  </a>
                <?php endif; ?>
              </td>
              <td>
                <?php $corStatus = match($oc->status) {
                  'aprovado'  => 'success',
                  'reprovado' => 'danger',
                  default     => 'warning',
                }; ?>
                <span class="badge bg-<?= $corStatus ?>-subtle text-<?= $corStatus ?>-emphasis ocor-badge">
                  <?= ucfirst($oc->status) ?>
                </span>
              </td>
              <td>
                <a href="<?= url("funcionarios/{$fid}/ocorrencias/{$oc->id}/excluir") ?>"
                   onclick="return confirm('Remover ocorrência?')"
                   class="btn btn-outline-danger btn-sm py-0 px-2">
                  <i class="bi bi-trash"></i>
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

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold py-3">Nova ocorrência</div>
      <div class="card-body p-3">
        <form action="<?= url("funcionarios/{$fid}/ocorrencias") ?>" method="POST"
              enctype="multipart/form-data" id="form-ocorrencia">

          <div class="mb-3">
            <label class="form-label">Tipo *</label>
            <select name="tipo" class="form-select" id="sel-tipo" required onchange="atualizarCampos()">
              <option value="">— Selecione —</option>
              <?php foreach (FuncionarioOcorrencia::$rotulosTipo as $val => $rot): ?>
                <option value="<?= $val ?>"><?= $rot ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label" id="lbl-data-inicio">Data início *</label>
              <input type="date" name="data_inicio" class="form-control" required>
            </div>
            <div class="col-6" id="campo-data-fim">
              <label class="form-label">Data fim</label>
              <input type="date" name="data_fim" class="form-control">
            </div>
          </div>

          <div class="mb-3 d-none" id="campo-valor">
            <label class="form-label">Valor do adiantamento (R$)</label>
            <input type="text" name="valor" class="form-control" placeholder="0,00">
          </div>

          <div class="mb-3">
            <label class="form-label">Justificativa</label>
            <textarea name="justificativa" class="form-control" rows="2"
                      placeholder="Descreva o motivo..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Anexo <span class="text-body-secondary fw-normal" style="font-size:.8rem;">(atestado, doc...)</span></label>
            <input type="file" name="anexo" class="form-control"
                   accept="image/*,.pdf,.doc,.docx">
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="aprovado">Aprovado</option>
              <option value="pendente">Pendente</option>
              <option value="reprovado">Reprovado</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg"></i> Registrar ocorrência
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function atualizarCampos() {
  const tipo = document.getElementById('sel-tipo').value;
  document.getElementById('campo-valor').classList.toggle('d-none', tipo !== 'adiantamento');
  const lblInicio = document.getElementById('lbl-data-inicio');
  const campoFim  = document.getElementById('campo-data-fim');
  if (tipo === 'falta' || tipo === 'atestado') {
    lblInicio.textContent = tipo === 'falta' ? 'Data da falta *' : 'Data do atestado *';
    campoFim.classList.remove('d-none');
  } else if (tipo === 'adiantamento') {
    lblInicio.textContent = 'Data *';
    campoFim.classList.add('d-none');
  } else {
    lblInicio.textContent = 'Data início *';
    campoFim.classList.remove('d-none');
  }
}
</script>
<?php endif; ?>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
