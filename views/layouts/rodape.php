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

  (function () {
    var tema = localStorage.getItem('condux-tema') || 'light';
    document.querySelectorAll('.condux-tema-icone').forEach(function (el) {
      el.className = 'bi ' + (tema === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill') + ' condux-tema-icone';
    });
  }());

  /* ── Drawer (sidebar) mobile ── */
  document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('barraLateral');
    var overlay = document.getElementById('conduxOverlay');
    var maisBtn = document.getElementById('conduxMaisBtn');

    function abrirDrawer()  {
      sidebar.classList.add('aberta');
      overlay.classList.add('visivel');
      if (maisBtn) maisBtn.classList.add('ativo');
    }
    function fecharDrawer() {
      sidebar.classList.remove('aberta');
      overlay.classList.remove('visivel');
      if (maisBtn) maisBtn.classList.remove('ativo');
    }

    if (maisBtn)  maisBtn.addEventListener('click', function () {
      sidebar.classList.contains('aberta') ? fecharDrawer() : abrirDrawer();
    });
    if (overlay)  overlay.addEventListener('click', fecharDrawer);
    if (sidebar)  sidebar.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', fecharDrawer);
    });
  });
</script>
</body>
</html>
