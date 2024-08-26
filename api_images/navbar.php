    <nav class="navbar navbar-expand-lg bg-body-tertiary shadow">
      <div class="container-fluid gap-2 justify-content-end">
        <a class="navbar-brand me-auto fw-bold" href="redirect.php?back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/index.php'); ?>">ArtCODE - API</a>
        <button id="themeToggle" class="btn px-0 border-0">
          <i id="themeIcon" class="bi bi-sun-fill"></i>
        </button>
      </div>
    </nav>