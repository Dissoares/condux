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

      function abrir() {
        var rect = input.getBoundingClientRect();
        dropdown.style.position = 'fixed';
        dropdown.style.top      = (rect.bottom + 3) + 'px';
        dropdown.style.left     = rect.left + 'px';
        dropdown.style.width    = rect.width + 'px';
        dropdown.style.right    = 'auto';
        dropdown.classList.add('aberto');
      }
      function fechar() {
        dropdown.classList.remove('aberto');
        dropdown.style.cssText = '';
      }

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

  /* ── PWA: Service Worker + Push Notifications ── */
  (function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    var SW_URL     = '<?= url('sw.js') ?>';
    var VAPID_URL  = '<?= url('push/vapid-public-key') ?>';
    var SUB_URL    = '<?= url('push/subscribe') ?>';
    var UNSUB_URL  = '<?= url('push/unsubscribe') ?>';
    var btn        = document.getElementById('condux-push-btn');

    function urlBase64ToUint8Array(base64String) {
      var padding = '='.repeat((4 - base64String.length % 4) % 4);
      var base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
      var raw     = atob(base64);
      var arr     = new Uint8Array(raw.length);
      for (var i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
      return arr;
    }

    function atualizarBtn(inscrito) {
      if (!btn) return;
      if (inscrito) {
        btn.innerHTML = '<i class="bi bi-bell-slash"></i>';
        btn.title     = 'Desativar notificações';
        btn.classList.remove('condux-push-off');
        btn.classList.add('condux-push-on');
      } else {
        btn.innerHTML = '<i class="bi bi-bell"></i>';
        btn.title     = 'Ativar notificações';
        btn.classList.add('condux-push-off');
        btn.classList.remove('condux-push-on');
      }
    }

    navigator.serviceWorker.register(SW_URL).then(function (reg) {
      reg.pushManager.getSubscription().then(function (sub) {
        atualizarBtn(!!sub);
      });

      if (!btn) return;
      btn.addEventListener('click', function () {
        reg.pushManager.getSubscription().then(function (sub) {
          if (sub) {
            // Desinscrever
            sub.unsubscribe().then(function () {
              fetch(UNSUB_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({endpoint: sub.endpoint}),
              });
              atualizarBtn(false);
            });
          } else {
            // Inscrever
            fetch(VAPID_URL).then(function (r) { return r.json(); }).then(function (data) {
              return reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(data.publicKey),
              });
            }).then(function (newSub) {
              var json = newSub.toJSON();
              fetch(SUB_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                  endpoint: json.endpoint,
                  keys: { p256dh: json.keys.p256dh, auth: json.keys.auth },
                }),
              });
              atualizarBtn(true);
            }).catch(function (err) {
              console.warn('Push subscription failed:', err);
            });
          }
        });
      });
    });
  }());

  /* ── PWA Install prompt ── */
  (function () {
    var deferred = null;
    var btn = document.getElementById('condux-install-btn');
    if (!btn) return;

    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      deferred = e;
      btn.style.display = 'flex';
    });

    btn.addEventListener('click', function () {
      if (!deferred) return;
      deferred.prompt();
      deferred.userChoice.then(function () {
        btn.style.display = 'none';
        deferred = null;
      });
    });

    window.addEventListener('appinstalled', function () {
      btn.style.display = 'none';
    });
  }());

  /* ── Drawer (sidebar) mobile ── */
  document.addEventListener('DOMContentLoaded', function () {
    var sidebar        = document.getElementById('barraLateral');
    var overlay        = document.getElementById('conduxOverlay');
    var maisBtn        = document.getElementById('conduxMaisBtn');
    var hamburger      = document.getElementById('conduxHamburger');
    var sidebarFechar  = document.getElementById('conduxSidebarFechar');
    var userBtn        = document.getElementById('conduxUserBtn');
    var userDrop       = document.getElementById('conduxUserDrop');

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

    if (maisBtn)   maisBtn.addEventListener('click', function () {
      sidebar.classList.contains('aberta') ? fecharDrawer() : abrirDrawer();
    });
    if (hamburger) hamburger.addEventListener('click', function () {
      sidebar.classList.contains('aberta') ? fecharDrawer() : abrirDrawer();
    });
    if (sidebarFechar) sidebarFechar.addEventListener('click', fecharDrawer);
    if (overlay)       overlay.addEventListener('click', fecharDrawer);
    if (sidebar)   sidebar.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', fecharDrawer);
    });

    /* Dropdown usuário */
    if (userBtn && userDrop) {
      userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        userDrop.classList.toggle('aberto');
      });
      document.addEventListener('click', function () {
        userDrop.classList.remove('aberto');
      });
      userDrop.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    }
  });
</script>
</body>
</html>
