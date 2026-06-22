<?php
/** @var Unidade[] $unidades @var string|null $mensagem @var string|null $erroMensagem */
$tituloPagina = 'Unidades';
require_once RAIZ . '/views/layouts/cabecalho.php';
?>

<div class="cabecalho-pagina">
  <h1 class="titulo-pagina"><i class="bi bi-building"></i> Unidades</h1>
  <a href="<?= $urlBase ?>/index.php?pagina=unidades&acao=formulario" class="botao-primario">
    <i class="bi bi-plus-lg"></i> Nova unidade
  </a>
</div>

<?php if ($mensagem): ?>
  <div class="alerta-flash sucesso"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erroMensagem): ?>
  <div class="alerta-flash erro"><i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($erroMensagem) ?></div>
<?php endif; ?>

<div class="card-conteudo" style="padding:0; overflow:hidden;">
  <table class="tabela-condux">
    <thead>
      <tr>
        <th>Unidade</th>
        <th>Responsável</th>
        <th>Status mês atual</th>
        <th style="width:120px;"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($unidades)): ?>
        <tr>
          <td colspan="4" style="text-align:center; color:#6b7280; padding:2rem;">
            Nenhuma unidade cadastrada.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($unidades as $unidade): ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($unidade->identificacao()) ?></strong>
            <?php if ($unidade->andar): ?>
              <br><small style="color:#6b7280;"><?= $unidade->andar ?>º andar</small>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($unidade->nomeResponsavel ?? '—') ?></td>
          <td>
            <?php $status = $unidade->statusTaxaAtual ?? 'sem_taxa'; ?>
            <span class="badge-status <?= $status ?>">
              <?= match($status) {
                'pago'    => 'Pago',
                'vencido' => 'Vencido',
                'isento'  => 'Isento',
                'pendente'=> 'Pendente',
                default   => 'Sem taxa',
              } ?>
            </span>
          </td>
          <td>
            <a href="<?= $urlBase ?>/index.php?pagina=unidades&acao=ver&id=<?= $unidade->id ?>"
               class="botao-secundario">
              <i class="bi bi-eye"></i> Ver
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once RAIZ . '/views/layouts/rodape.php'; ?>
