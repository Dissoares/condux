<?php
/** @var Unidade $unidade @var Morador[] $moradores @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Unidade ' . $unidade->identificacao();
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-building"></i> <?= htmlspecialchars($unidade->identificacao()) ?></h1>
  <div style="display:flex; gap:.5rem;">
    <a href="<?= url("unidades/{$unidade->id}/editar") ?>" class="botao-secundario">
      <i class="bi bi-pencil"></i> Editar
    </a>
    <a href="<?= url('unidades') ?>" class="botao-secundario">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<?php if ($mensagem): ?>
  <div class="alerta-flash sucesso"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alerta-flash erro"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erroMensagem) ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;" class="grade-formulario">

  <!-- Informações da unidade -->
  <div class="card-conteudo">
    <h2 class="titulo-card">Dados da unidade</h2>

    <p class="rotulo-secao">Identificação</p>
    <table style="width:100%; font-size:.9rem; border-collapse:collapse; margin-bottom:1.25rem;">
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280; width:110px;">Número</td>
        <td style="padding:.5rem;"><strong><?= htmlspecialchars($unidade->numero) ?></strong></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Bloco</td>
        <td style="padding:.5rem;"><?= htmlspecialchars($unidade->bloco ?? '—') ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Andar</td>
        <td style="padding:.5rem;"><?= $unidade->andar ? $unidade->andar . 'º' : '—' ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Ocupação</td>
        <td style="padding:.5rem;">
          <?php if ($unidade->estaAlugada()): ?>
            <span class="badge-status pendente">Alugado</span>
          <?php else: ?>
            <span class="badge-status pago">Próprio</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td style="padding:.5rem; color:#6b7280;">Observações</td>
        <td style="padding:.5rem;"><?= htmlspecialchars($unidade->descricao ?? '—') ?></td>
      </tr>
    </table>

    <p class="rotulo-secao">Proprietário</p>
    <table style="width:100%; font-size:.9rem; border-collapse:collapse; margin-bottom:<?= $unidade->estaAlugada() ? '1.25rem' : '0' ?>;">
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280; width:110px;">Nome</td>
        <td style="padding:.5rem;"><?= htmlspecialchars($unidade->nomeProprietario ?? '—') ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Telefone</td>
        <td style="padding:.5rem;">
          <?php if ($unidade->telefoneProprietario): ?>
            <a href="tel:<?= htmlspecialchars($unidade->telefoneProprietario) ?>"><?= htmlspecialchars($unidade->telefoneProprietario) ?></a>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
      <tr>
        <td style="padding:.5rem; color:#6b7280;">E-mail</td>
        <td style="padding:.5rem;">
          <?php if ($unidade->emailProprietario): ?>
            <a href="mailto:<?= htmlspecialchars($unidade->emailProprietario) ?>"><?= htmlspecialchars($unidade->emailProprietario) ?></a>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
    </table>

    <?php if ($unidade->estaAlugada()): ?>
    <p class="rotulo-secao">Inquilino</p>
    <table style="width:100%; font-size:.9rem; border-collapse:collapse;">
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280; width:110px;">Nome</td>
        <td style="padding:.5rem;"><?= htmlspecialchars($unidade->nomeInquilino ?? '—') ?></td>
      </tr>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <td style="padding:.5rem; color:#6b7280;">Telefone</td>
        <td style="padding:.5rem;">
          <?php if ($unidade->telefoneInquilino): ?>
            <a href="tel:<?= htmlspecialchars($unidade->telefoneInquilino) ?>"><?= htmlspecialchars($unidade->telefoneInquilino) ?></a>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
      <tr>
        <td style="padding:.5rem; color:#6b7280;">E-mail</td>
        <td style="padding:.5rem;">
          <?php if ($unidade->emailInquilino): ?>
            <a href="mailto:<?= htmlspecialchars($unidade->emailInquilino) ?>"><?= htmlspecialchars($unidade->emailInquilino) ?></a>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
    </table>
    <?php endif; ?>
  </div>

  <!-- Vincular novo morador -->
  <div class="card-conteudo">
    <h2 class="titulo-card"><i class="bi bi-person-plus"></i> Vincular morador</h2>
    <form action="<?= url("unidades/{$unidade->id}/vincular-morador") ?>" method="POST">
      <div class="campo-formulario" style="margin-bottom:.75rem;">
        <label for="campo-nome-morador">Nome *</label>
        <input type="text" id="campo-nome-morador" name="nome" required placeholder="Nome completo">
      </div>
      <div class="campo-formulario" style="margin-bottom:.75rem;">
        <label for="campo-email-morador">E-mail *</label>
        <input type="email" id="campo-email-morador" name="email" required placeholder="morador@email.com">
        <small style="color:#6b7280;">Se o e-mail já existir, o usuário é vinculado sem criar novo.</small>
      </div>
      <div class="campo-formulario" style="margin-bottom:.75rem;">
        <label for="campo-senha-morador">Senha (apenas para novos usuários)</label>
        <input type="password" id="campo-senha-morador" name="senha" placeholder="Senha de acesso">
      </div>
      <div class="campo-formulario" style="margin-bottom:.75rem;">
        <label for="campo-entrada">Data de entrada</label>
        <input type="date" id="campo-entrada" name="data_entrada" value="<?= date('Y-m-d') ?>">
      </div>
      <div style="margin-bottom:1rem; display:flex; align-items:center; gap:.5rem;">
        <input type="checkbox" id="campo-responsavel" name="responsavel" value="1" style="width:auto;">
        <label for="campo-responsavel" style="margin:0; font-weight:normal;">Responsável financeiro</label>
      </div>
      <button type="submit" class="botao-primario" style="font-size:.875rem;">
        <i class="bi bi-person-plus"></i> Vincular
      </button>
    </form>
  </div>

</div>

<!-- Moradores vinculados -->
<div class="card-conteudo" style="margin-top:1.25rem;">
  <h2 class="titulo-card"><i class="bi bi-people"></i> Moradores vinculados</h2>

  <?php if (empty($moradores)): ?>
    <p style="color:#6b7280; font-size:.9rem;">Nenhum morador vinculado.</p>
  <?php else: ?>
    <div class="tabela-responsiva">
    <table class="tabela-condux">
      <thead>
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
              <span class="badge-status pago">Responsável</span>
            <?php else: ?>
              <span style="color:#9ca3af;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= url("unidades/{$unidade->id}/desvincular-morador/{$morador->id}") ?>"
               class="botao-perigo" style="font-size:.78rem; padding:.25rem .6rem;"
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
