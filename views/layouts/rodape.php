</main>

<script>
  // Alterna menu em telas pequenas
  document.addEventListener('DOMContentLoaded', function () {
    const botaoMenu  = document.getElementById('botaoToggleMenu');
    const barraLateral = document.getElementById('barraLateral');
    if (botaoMenu && barraLateral) {
      botaoMenu.addEventListener('click', function () {
        barraLateral.classList.toggle('aberta');
      });
    }
  });
</script>
</body>
</html>
