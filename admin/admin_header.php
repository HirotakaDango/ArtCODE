    <ul class="me-2 ms-2 mt-2 nav nav-pills nav-fill justify-content-center">
      <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="../admin/index.php"><i class="bi bi-house-fill"></i></a></li>
      <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'edit_users.php') echo 'active' ?>" href="../admin/edit_users.php"><i class="bi bi-person-fill-gear"></i></a></li>
      <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'update_news.php') echo 'active' ?>" href="../admin/update_news.php"><i class="bi bi-newspaper"></i></a></li>
      <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'remove_images.php') echo 'active' ?>" href="../admin/remove_images.php"><i class="bi bi-images"></i></a></li> 
      <li class="nav-item"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'remove_all.php') echo 'active' ?>" href="../admin/remove_all.php"><i class="bi bi-person-fill-exclamation"></i></a></li>
    </ul>