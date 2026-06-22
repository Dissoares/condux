<?php
/**
 * Picker pesquisável reutilizável.
 *
 * @param string   $name         Nome do campo (input hidden)
 * @param array[]  $opcoes       Array de ['id' => int, 'nome' => string, 'email' => ?string]
 * @param int|null $selecionado  ID pré-selecionado
 * @param string   $placeholder  Texto do input de busca
 * @param string   $labelVazia   Texto da opção "nenhum"
 * @param int[]    $excluir      IDs a omitir das opções
 */
function renderPicker(
    string  $name,
    array   $opcoes,
    ?int    $selecionado  = null,
    string  $placeholder  = 'Buscar...',
    string  $labelVazia   = '— Nenhum —',
    array   $excluir      = [],
): void {
    $textoSelecionado = '';
    if ($selecionado) {
        foreach ($opcoes as $o) {
            if ((int)$o['id'] === $selecionado) {
                $textoSelecionado = $o['nome'];
                if (!empty($o['email'])) $textoSelecionado .= ' — ' . $o['email'];
                break;
            }
        }
    }
    $uid = 'pk-' . $name . '-' . substr(md5($name . rand()), 0, 6);
?>
<div class="condux-picker" id="<?= $uid ?>">
  <input type="text"
         class="form-control condux-picker-input"
         placeholder="<?= htmlspecialchars($placeholder) ?>"
         autocomplete="off"
         value="<?= htmlspecialchars($textoSelecionado) ?>"
         data-picker="<?= $uid ?>">
  <input type="hidden" name="<?= htmlspecialchars($name) ?>" class="condux-picker-value"
         value="<?= htmlspecialchars((string)($selecionado ?? '')) ?>">
  <div class="condux-picker-dropdown">
    <div class="condux-picker-opt" data-value=""><?= htmlspecialchars($labelVazia) ?></div>
    <?php foreach ($opcoes as $o): ?>
      <?php if (in_array((int)$o['id'], $excluir, true)) continue; ?>
      <?php
        $label = htmlspecialchars($o['nome']);
        if (!empty($o['email'])) $label .= ' <span style="opacity:.6;font-size:.82em;">— ' . htmlspecialchars($o['email']) . '</span>';
        $sel   = (int)$o['id'] === $selecionado;
      ?>
      <div class="condux-picker-opt <?= $sel ? 'selecionado' : '' ?>" data-value="<?= (int)$o['id'] ?>"><?= $label ?></div>
    <?php endforeach; ?>
    <div class="condux-picker-vazio">Nenhum resultado encontrado.</div>
  </div>
</div>
<?php } ?>
