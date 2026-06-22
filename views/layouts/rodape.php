</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  /* ── Tema claro/escuro ── */
  function conduxToggleTema() {
    var atual = document.documentElement.getAttribute('data-bs-theme');
    var novo  = atual === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', novo);
    localStorage.setItem('condux-tema', novo);
    document.querySelectorAll('.condux-tema-icone').forEach(function (el) {
      el.className = 'bi ' + (novo === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill') + ' condux-tema-icone';
    });
  }

  /* Aplica ícone correto ao carregar */
  (function () {
    var tema = localStorage.getItem('condux-tema') || 'light';
    document.querySelectorAll('.condux-tema-icone').forEach(function (el) {
      el.className = 'bi ' + (tema === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill') + ' condux-tema-icone';
    });
  }());

  /* ── Sidebar mobile ── */
  document.addEventListener('DOMContentLoaded', function () {
    var botao   = document.getElementById('botaoToggleMenu');
    var sidebar = document.getElementById('barraLateral');
    var overlay = document.getElementById('conduxOverlay');

    function abrir()  { sidebar.classList.add('aberta');    overlay.classList.add('visivel'); }
    function fechar() { sidebar.classList.remove('aberta'); overlay.classList.remove('visivel'); }

    if (botao)   botao.addEventListener('click', function () { sidebar.classList.contains('aberta') ? fechar() : abrir(); });
    if (overlay) overlay.addEventListener('click', fechar);
    if (sidebar) sidebar.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', fechar); });
  });
</script>
</body>
</html>
