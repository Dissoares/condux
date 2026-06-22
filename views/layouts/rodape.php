</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const botaoMenu    = document.getElementById('botaoToggleMenu');
    const barraLateral = document.getElementById('barraLateral');
    const overlay      = document.getElementById('overlayMenu');

    function abrirMenu() {
      barraLateral.classList.add('aberta');
      overlay.classList.add('visivel');
    }

    function fecharMenu() {
      barraLateral.classList.remove('aberta');
      overlay.classList.remove('visivel');
    }

    if (botaoMenu) {
      botaoMenu.addEventListener('click', function () {
        barraLateral.classList.contains('aberta') ? fecharMenu() : abrirMenu();
      });
    }

    if (overlay) {
      overlay.addEventListener('click', fecharMenu);
    }

    // Fecha menu ao navegar (links internos)
    barraLateral.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', fecharMenu);
    });
  });
</script>
</body>
</html>
