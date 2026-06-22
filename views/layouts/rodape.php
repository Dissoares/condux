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

  /* ── Picker pesquisável ── */
  (function () {
    function initPicker(picker) {
      var input    = picker.querySelector('.condux-picker-input');
      var hidden   = picker.querySelector('.condux-picker-value');
      var dropdown = picker.querySelector('.condux-picker-dropdown');
      if (!input || !hidden || !dropdown) return;

      var opts   = Array.from(dropdown.querySelectorAll('.condux-picker-opt'));
      var vazio  = dropdown.querySelector('.condux-picker-vazio');

      function abrir() { dropdown.classList.add('aberto'); }
      function fechar() { dropdown.classList.remove('aberto'); }

      function filtrar(termo) {
        var t = termo.toLowerCase().trim();
        var algum = false;
        opts.forEach(function (o, i) {
          if (i === 0) { o.hidden = false; return; }  // opção vazia sempre visível
          var vis = t === '' || o.textContent.toLowerCase().includes(t);
          o.hidden = !vis;
          if (vis) algum = true;
        });
        if (vazio) vazio.classList.toggle('visivel', t !== '' && !algum);
      }

      input.addEventListener('focus', abrir);
      input.addEventListener('input', function () { filtrar(this.value); abrir(); });

      opts.forEach(function (opt) {
        opt.addEventListener('mousedown', function (e) {
          e.preventDefault();
          var val = this.dataset.value;
          hidden.value = val;
          // Mostrar texto sem HTML (remover tag email)
          input.value = val ? this.firstChild ? this.firstChild.textContent.trim() : this.textContent.trim() : '';
          opts.forEach(function (o) { o.classList.remove('selecionado'); });
          this.classList.add('selecionado');
          filtrar('');
          fechar();
        });
      });

      document.addEventListener('click', function (e) {
        if (!picker.contains(e.target)) fechar();
      });
    }

    function initTodosPickers() {
      document.querySelectorAll('.condux-picker:not([data-picker-init])').forEach(function (el) {
        el.setAttribute('data-picker-init', '1');
        initPicker(el);
      });
    }

    // Init ao carregar e também quando modais abrirem (pickers dentro de modais)
    document.addEventListener('DOMContentLoaded', initTodosPickers);
    document.addEventListener('show.bs.modal', function () {
      setTimeout(initTodosPickers, 50);
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
