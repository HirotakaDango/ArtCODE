    <div class="btn-group w-100 gap-2 my-3 container-fluid">
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'follower/') !== false) echo 'opacity-75 shadow'; ?>" href="/follower.php?id=<?php echo $userId; ?>"><i class="bi bi-people-fill"></i> Followers</a>
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'following/') !== false) echo 'opacity-75 shadow'; ?>" href="/following.php?id=<?php echo $userId; ?>"><i class="bi bi-people-fill"></i> Followings</a>
    </div> 
