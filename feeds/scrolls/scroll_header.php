          <div class="d-md-none dropdown">
            <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-images"></i> sort by
            </button>
            <ul class="dropdown-menu">
              <li><a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/newest/" class="dropdown-item fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/scrolls/newest/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/newest/') !== false) ? 'active' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'active' : 'text-s'); ?>">newest</a></li>
              <li><a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/oldest/" class="dropdown-item fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/scrolls/oldest/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/oldest/') !== false) ? 'active' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'active' : 'text-s'); ?>">oldest</a></li>
              <li><a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/newest/" class="dropdown-item fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/scrolls/popular/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/popular/') !== false) ? 'active' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'active' : 'text-s'); ?>">popular</a></li>
            </ul> 
          </div> 
          <div class="d-none d-md-block">
            <div class="d-flex justify-content-center align-items-center vh-100">
              <div class="container position-fixed" style="max-width: 300px;">
                <div class="card border-0 shadow rounded-4 p-4 px-2">
                  <div class="container d-flex justify-content-center">
                    <div class="btn-group-vertical w-100">
                      <a class="p-1 px-3 btn rounded fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/scrolls/newest/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/newest/') !== false) ? 'active' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'active' : 'text-s'); ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/newest/">
                        Newest
                      </a>
                      <a class="p-1 px-3 btn rounded fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/scrolls/oldest/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/oldest/') !== false) ? 'active' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'active' : 'text-s'); ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/oldest/">
                        Oldest
                      </a>
                      <a class="p-1 px-3 btn rounded fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/scrolls/popular/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/popular/') !== false) ? 'active' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'active' : 'text-s'); ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/popular/">
                        Popular
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>