            <div class="position-absolute top-0 start-0 ms-2 mt-2">
              <div class="dropdown">
                <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-3 rounded dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  view option
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item <?php if(basename($_SERVER['PHP_SELF']) == 'private_view.php') echo 'active' ?>" href="private_view.php?artworkid=<?php echo $image['id']; ?>&mode=<?php echo $_GET['mode']; ?>"><?php if(basename($_SERVER['PHP_SELF']) == 'private_view.php') echo '<i class="bi bi-chevron-right text-stroke"></i>' ?> normal view</a></li>
                  <li><a class="dropdown-item <?php if(basename($_SERVER['PHP_SELF']) == 'private_full_view.php') echo 'active' ?>" href="private_full_view.php?artworkid=<?php echo $image['id']; ?>&mode=<?php echo $_GET['mode']; ?>"><?php if(basename($_SERVER['PHP_SELF']) == 'private_full_view.php') echo '<i class="bi bi-chevron-right text-stroke"></i>' ?> full view</a></li>
                  <li><a class="dropdown-item <?php if(basename($_SERVER['PHP_SELF']) == 'private_simple_view.php') echo 'active' ?>" href="private_simple_view.php?artworkid=<?php echo $image['id']; ?>&mode=<?php echo $_GET['mode']; ?>"><?php if(basename($_SERVER['PHP_SELF']) == 'private_simple_view.php') echo '<i class="bi bi-chevron-right text-stroke"></i>' ?> simple view</a></li>
                  <li><a class="dropdown-item <?php if(basename($_SERVER['PHP_SELF']) == 'private_simplest_view.php') echo 'active' ?>" href="private_simplest_view.php?artworkid=<?php echo $image['id']; ?>&mode=<?php echo $_GET['mode']; ?>"><?php if(basename($_SERVER['PHP_SELF']) == 'private_simplest_view.php') echo '<i class="bi bi-chevron-right text-stroke"></i>' ?> simplest view</a></li>
                </ul>
              </div>
            </div>