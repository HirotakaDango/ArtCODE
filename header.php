<?php
// Connect to the SQLite database
$dbPath = $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite';
$db1 = new SQLite3($dbPath);

// Get the artist name from the database
$email1 = $_SESSION['email'];
$stmt1 = $db1->prepare("SELECT id, artist, pic FROM users WHERE email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$pic1 = $row1['pic'];
$artist1 = $row1['artist'];
$user_id1 = $row1['id'];

// Count the number of followers
$stmt1 = $db1->prepare("SELECT COUNT(*) AS num_followers FROM following WHERE following_email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$num_followers1 = $row1['num_followers'];

// Count the number of following
$stmt1 = $db1->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$num_following1 = $row1['num_following'];

// Get all of the images uploaded by the current user
$stmt1 = $db1->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();

// Count the number of images uploaded by the current user
$count1 = 0;
while ($image1 = $result1->fetchArray()) {
  $count1++;
}
  
$fav_result1 = $db1->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}'");
$fav_count1 = $fav_result1->fetchArray()[0];
?>

    <!-- Navbar -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <nav class="navbar fixed-top navbar-expand-md navbar-expand-lg navbar-light bg-body-tertiary">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="text-secondary navbar-brand fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
          ArtCODE
        </a>
        <div class="dropdown nav-right">
          <a class="btn btn-sm bg-body-secondary rounded-pill fw-bold dropdown-toggle d-none d-md-block d-lg-block p-1 pe-2 border border-light-subtle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
            <img class="rounded-circle border-0 object-fit-cover border border-1 m-0" width="24" height="24" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture" style="margin-top: -2px;">
            <span><?php echo $artist1; ?></span>
          </a>
          <a class="nav-link px-2 text-secondary dropdown-toggle d-md-none d-lg-none" type="button" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
            <img class="rounded-circle object-fit-cover border border-1" width="32" height="32" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture" style="margin-top: -2px;">
          </a>
          <ul class="dropdown-menu dropdown-menu-end rounded-4 shadow border-0" style="width: 300px;">
            <div class="text-center mb-2">
              <a class="d-block pt-2" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/settings/profile_picture.php"><img class="rounded-circle object-fit-cover border border-5" width="150" height="150" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture"></a>
              <h5 class="fw-bold mt-2 "><?php echo $artist1; ?></h5>
              <p class="fw-medium" style="margin-top: -10px;"><small><?php echo $email1; ?></small></p>
            </div>
            <div class="btn-group mt-2 mb-1 w-100 container" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-dark rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'follower.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/follower.php?id=<?php echo $user_id1; ?>"><i class="bi bi-people-fill"></i> <?php echo $num_followers1 ?> <small>followers</small></a>
              <a class="btn btn-sm btn-outline-dark ms-1 rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'following.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/following.php?id=<?php echo $user_id1; ?>"><i class="bi bi-person-fill"></i> <?php echo $num_following1 ?> <small>following</small></a>
            </div>
            <div class="btn-group mb-3 w-100 container" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-dark rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'myworks.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/myworks.php"><i class="bi bi-images"></i> <?php echo $count1; ?> <small>images</small></a>
              <a class="btn btn-sm btn-outline-dark ms-1 rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'favorite.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/favorite.php"><i class="bi bi-heart-fill"></i> <?php echo $fav_count1;?> <small>favorites</small></a> 
            </div>
            <div class="container">
              <div class="btn-group w-100 gap-2 mb-1">
                <a class="text-center dropdown-item w-50 hover-effect fw-bold mb-1 rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/notification/') !== false) || (strpos($_SERVER['PHP_SELF'], '/profile/') !== false) ? 'text-white bg-darker rounded' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'text-white bg-darker rounded' : 'text-s'); ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/profile.php">
                  Profile
                </a>
                <a class="text-center dropdown-item w-50 hover-effect fw-bold mb-1 rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/favorites/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/favorites/') !== false) ? 'text-white bg-darker rounded' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'text-white bg-darker rounded' : 'text-s'); ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/favorite.php">
                  Favorites
                </a>
              </div>
              <div class="btn-group w-100 gap-2 mb-1">
                <a class="text-center dropdown-item w-50 hover-effect fw-bold mb-1 rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'album.php') ? 'text-white bg-darker rounded' : 'text-s'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/album.php">
                  My Albums
                </a>
                <a class="text-center dropdown-item w-50 hover-effect fw-bold mb-1 rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'myworks.php') ? 'text-white bg-darker rounded' : 'text-s'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/myworks.php">
                  My Works
                </a>
              </div>
              <div class="btn-group w-100 gap-2">
                <a class="text-center dropdown-item w-50 hover-effect fw-bold mb-1 rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'setting.php') ? 'text-white bg-darker rounded' : 'text-s'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/setting.php">
                  Settings
                </a>
                <a class="text-center dropdown-item w-50 hover-effect fw-bold mb-1 rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/history/') !== false) || (strpos($_SERVER['PHP_SELF'], '/feeds/history/') !== false) ? 'text-white bg-darker rounded' : ((basename($_SERVER['PHP_SELF']) == 'profile') ? 'text-white bg-darker rounded' : 'text-s'); ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/history.php">
                  History
                </a>
              </div>
            </div>
            <div class="mx-1">
              <hr class="border-3 rounded">
              <div class="container-fluid mb-2">
              <?php if(isset($_SESSION['email']) && isset($_COOKIE['token'])): ?>
                <li>
                  <a class="btn btn-danger fw-bold w-100 rounded-3" href="#" data-bs-toggle="modal" data-bs-target="#logOut">
                    <i class="bi bi-door-open-fill"></i> Logout
                  </a>
                </li>
              <?php else: ?>
                <li>
                  <a class="btn btn-primary fw-bold w-100 <?php echo (basename($_SERVER['PHP_SELF']) == 'session.php'); ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/session.php">
                    Signin
                  </a>
                </li>
              <?php endif; ?> 
              </div>
            </div>
          </ul>
        </div> 
        <div class="offcanvas offcanvas-start" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <a class="text-decoration-none link-body-emphasis link-light" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>"><h5 class="offcanvas-title fw-bold" id="navbarLabel">ArtCODE</h5></a>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <!-- Mobile -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold d-md-none d-lg-none">
              <form action="/search.php" method="GET" class="mb-3">
                <div class="input-group">
                  <input type="text" name="search" class="form-control text-lowercase fw-bold rounded-end-0 rounded-4 border-0 bg-body-tertiary" placeholder="Search tags or title (e.g: white, sky)" required onkeyup="debouncedShowSuggestions(this, 'suggestions1')" />
                  <button type="submit" class="btn bg-body-tertiary link-body-emphasis border-0 rounded-start-0 rounded-4"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
                </div>
                <div id="suggestions1"></div>
              </form>
              <div class="btn-group gap-2 w-100">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'home/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/">
                  <i class="bi bi-house-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Home</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/forum.php">
                  <i class="bi bi-chat-left-dots-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Forum</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'upload/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/upload/">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Uploads</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/notification/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notification/">
                  <i class="bi bi-bell-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Notification</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/notes/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseNotes" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-journal-text fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Notes</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/novel/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseNovel" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-book-half fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Novel</span>
                </a>
              </div>
              <div class="collapse-content">
                <div class="collapse mt-2" id="collapseNotes">
                  <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                    <div class="btn-group-vertical gap-2">
                      <h6 class="fw-bold text-start">Notes</h6>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/notes">Home</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/notes/upload.php">Upload</a>
                    </div>
                  </div>
                </div>
                <div class="collapse mt-2" id="collapseNovel">
                  <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                    <div class="btn-group-vertical gap-2">
                      <h6 class="fw-bold text-start">Novel</h6>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel">Home</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel/upload.php">Upload</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel/favorite.php">Favorites</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel/profile.php">Profile</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/music/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseMusic" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-vinyl-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Music</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/minutes/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseMinutes" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-person-video2 fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Minutes</span>
                </a>
              </div>
              <div class="collapse-content">
                <div class="collapse mt-2" id="collapseMusic">
                  <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                    <div class="btn-group-vertical gap-2">
                      <h6 class="fw-bold text-start">Music</h6>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music">Home</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/upload.php">Upload</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/favorite.php">Favorites</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_artist.php">Artists</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_album.php">Albums</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/profile.php">Profile</a>
                    </div>
                  </div>
                </div>
                <div class="collapse mt-2" id="collapseMinutes">
                  <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                    <div class="btn-group-vertical gap-2">
                      <h6 class="fw-bold text-start">Minutes</h6>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes">Home</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes/upload.php">Upload</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes/favorite.php">Favorites</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes/profile.php">Profile</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/status.php">
                  <i class="bi bi-card-text fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Status</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/tags.php">
                  <i class="bi bi-tags-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Tags</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/explores/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/explores/">
                  <i class="bi bi-compass-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Explore</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'users.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/users.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Users</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'news.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/news.php">
                  <i class="bi bi-newspaper fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">News</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'support.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/support.php">
                  <i class="bi bi-headset fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Support</span>
                </a>
              </div>
            </ul>
            <!-- end -->
            
            <!-- Desktop -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold d-none-md-lg">
              <li class="nav-item">
                <a class="fw-medium nav-center btn btn-sm btn-outline-dark rounded-pill text-nowrap py-0 <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'upload/index.php') !== false) echo 'active'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/upload/">
                  <h6><i class="bi bi-cloud-arrow-up-fill fs-5"></i> uploads</h6>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center py-1 <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'home/') !== false) echo 'active border-bottom border-dark border-3'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                  Home
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center py-1 <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'active border-bottom border-dark border-3' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/forum.php">
                  Forum
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center py-1 <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/notification/') !== false) echo 'active border-bottom border-dark border-3'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notification/">
                  Notification
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center py-1 <?php if(basename($_SERVER['PHP_SELF']) == 'search.php') echo 'active border-bottom border-dark border-3' ?>" href="#" data-bs-toggle="modal" data-bs-target="#searchTerm">
                  Search
                </a>
              </li>
              <li class="nav-item">
                <div class="dropdown-center">
                  <a class="btn btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-grid-3x3-gap-fill fs-5 text-secondary"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end rounded-4 shadow border-0" style="width: 500px;">
                    <div class="row p-3">
                      <div class="col-6">
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/tags.php">
                            <i class="bi bi-tags-fill fs-5"></i>
                            <span class="d-lg-inline ms-2">Tags</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/notes/') !== false) echo 'active'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseNotes">
                            <i class="bi bi-journal-text fs-5"></i>
                            <span class="d-lg-inline ms-2">Notes</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/music/') !== false) echo 'active'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseMusic">
                            <i class="bi bi-vinyl-fill fs-5"></i>
                            <span class="d-lg-inline ms-2">Music</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/status.php">
                            <i class="bi bi-card-text fs-5"></i>
                            <span class="d-lg-inline ms-2">Status</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'news.php') echo 'active' ?>" href="news.php">
                            <i class="bi bi-newspaper fs-5"></i>
                            <span class="d-lg-inline ms-2">News</span>
                          </a>
                        </li>
                      </div>
                      <div class="col-6">
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'users.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/users.php">
                            <i class="bi bi-people-fill fs-5"></i>
                            <span class="d-lg-inline ms-2">Users</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/novel/') !== false) echo 'active'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseNovel">
                            <i class="bi bi-book-half fs-5"></i>
                            <span class="d-lg-inline ms-2">Novel</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/minutes/') !== false) echo 'active'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseMinutes">
                            <i class="bi bi-person-video2 fs-5"></i>
                            <span class="d-lg-inline ms-2">Minutes</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/explores/') !== false) echo 'active'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/explores/">
                            <i class="bi bi-compass-fill fs-5"></i>
                            <span class="d-lg-inline ms-2">Explore</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'support.php') echo 'active' ?>" href="support.php">
                            <i class="bi bi-headset fs-5"></i>
                            <span class="d-lg-inline ms-2">Support</span>
                          </a>
                        </li>
                      </div>
                    </div>
                  </ul>
                </div>
              </li>
            </ul>
            <!-- end -->
          </div>
        </div>
      </div>
    </nav>
    <br><br>

    <div class="modal fade" id="modalCollapseNotes" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Notes</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <div class="btn-group-vertical gap-2">
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/notes">Home</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/notes/upload.php">Upload</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalCollapseNovel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Novel</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <div class="btn-group-vertical gap-2">
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel">Home</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel/upload.php">Upload</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel/favorite.php">Favorites</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/novel/profile.php">Profile</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalCollapseMusic" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Music</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <div class="btn-group-vertical gap-2">
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music">Home</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/upload.php">Upload</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/favorite.php">Favorites</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_artist.php">Artists</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_album.php">Albums</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/profile.php">Profile</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalCollapseMinutes" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Minutes</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <div class="btn-group-vertical gap-2">
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes">Home</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes/upload.php">Upload</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes/favorite.php">Favorites</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/minutes/profile.php">Profile</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="logOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-body p-4 text-center fw-medium">
            <h5 class="mb-0">Do you want to end the session?</h5>
            <p class="mb-0 mt-2">You can always comeback whenever you want later.</p>
          </div>
          <div class="modal-footer flex-nowrap p-0">
            <a class="btn btn-lg btn-link text-danger fs-6 text-decoration-none col-6 py-3 m-0 rounded-0 border-end" href="logout.php"><strong>Yes, end the session!</strong></a>
            <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel, keep it!</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="searchTerm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Search</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="/search.php" method="GET" class="mb-3">
              <div class="input-group">
                <input type="text" name="search" class="form-control text-lowercase fw-bold rounded-end-0 rounded-3" placeholder="Search tags or title" required onkeyup="debouncedShowSuggestions(this, 'suggestions2')" />
                <button type="submit" class="btn btn-primary rounded-start-0 rounded-3"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
              </div>
              <div id="suggestions2"></div>
            </form>
            <h5 class="fw-bold text-center">Search Tips</h5>
            <p class="fw-semibold text-center">"You can search multi tags or title using comma to get multiple result!"</p>
            <p class="fw-semibold">example:</p>
            <input class="form-control text-dark fw-bold rounded-3" placeholder="tags, title (e.g: white, sky)" readonly>
          </div>
        </div>
      </div>
    </div>
    <button id="scrollButton" class="btn fw-bold btn-dark rounded-pill <?= (basename($_SERVER['PHP_SELF']) === 'album.php' || basename($_SERVER['PHP_SELF']) === 'list_favorite.php' || basename($_SERVER['PHP_SELF']) === 'forum.php') ? 'd-none' : ''; ?> d-md-none d-lg-none position-fixed bottom-0 end-0 m-3 z-3" data-bs-toggle="modal" data-bs-target="#navModal">
      <i class="fa-solid fa-bars small"></i> menu
    </button>
    <!-- Nav Modal -->
    <div class="modal fade" id="navModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fw-bold fs-5" id="exampleModalLabel">Start with your creativity</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="container">
              <div class="row">
                <div class="col-4">
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/imgupload.php" class="btn btn-outline-dark border-2 d-block feature-icon mb-3 py-3 border-3">
                    <i class="bi bi-cloud-arrow-up" style="font-size: 30px; -webkit-text-stroke: 1px;"></i>
                  </a>
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/imgupload.php" class="fw-bold text-center text-decoration-none text-dark d-block">Upload</a>
                </div>
                <div class="col-4">
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/favorite.php" class="btn btn-outline-dark border-2 d-block feature-icon mb-3 py-3 border-3">
                    <i class="bi bi-heart" style="font-size: 30px; -webkit-text-stroke: 1px;"></i>
                  </a>
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/favorite.php" class="fw-bold text-center text-decoration-none text-dark d-block">Favorite</a>
                </div>
                <div class="col-4">
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/album.php" class="btn btn-outline-dark border-2 d-block feature-icon mb-3 py-3 border-3">
                    <i class="bi bi-columns" style="font-size: 30px; -webkit-text-stroke: 1px;"></i>
                  </a>
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/album.php" class="fw-bold text-center text-decoration-none text-dark d-block">Album</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <style>
      .fade-in-out {
        opacity: 1;
        transition: opacity 0.5s ease-in-out;
      }
      
      .hidden-button {
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
      }

      .hover-effect:hover {
        color: white;
        background-color: #28242c;
        border-radius: 5px;
      }
      
      .text-s {
        color: #28242c;
      }
      
      .bg-darker {
        background-color: #28242c;
      }
      
      @media (min-width: 768px) {
        .navbar-nav {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
      
        .nav-center {
          margin-left: 15px;
          margin-right: 15px;
        }
      
        .nav-right {
          position: absolute;
          right: 10px;
          top: 10;
          align-items: center;
        }
        
        .d-none-sm {
          display: none;
        }
      }
      
      @media (max-width: 767px) {
        .d-none-md-lg {
          display: none;
        }
        .navbar-brand {
          position: static;
          display: block;
          text-align: center;
          margin: auto;
          transform: none;
        }

        .navbar-brand {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 18px;
        }
      }
      
      .btn-smaller {
        padding: 2px 4px;
      }
      
      .navbar {
        height: 45px;
      }
      
      .navbar-brand {
        font-size: 18px;
      }

      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-top: -2px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      } 
    </style>
    <script>
      let lastScrollPos = 0;
      const scrollButton = document.getElementById("scrollButton");
      window.addEventListener("scroll", () => {
        const currentScrollPos = window.pageYOffset;
        if (currentScrollPos > lastScrollPos) {
          // Scrolling down
          scrollButton.classList.add("hidden-button");
          scrollButton.classList.remove("fade-in-out");
          scrollButton.style.pointerEvents = "none"; // Disable interactions
        } else {
          // Scrolling up
          scrollButton.classList.remove("hidden-button");
          scrollButton.classList.add("fade-in-out");
          scrollButton.style.pointerEvents = "auto"; // Enable interactions
        }
    
        lastScrollPos = currentScrollPos;
      });
    </script>
    <script>
      var suggestedTags = {};

      function debounce(func, wait) {
        let timeout;
        return function (...args) {
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            func.apply(this, args);
          }, wait);
        };
      }

      function showSuggestions(input, suggestionsId) {
        // Get the suggestions element
        var suggestionsElement = document.getElementById(suggestionsId);

        // Clear previous suggestions
        suggestionsElement.innerHTML = "";

        // If the input is empty, hide the suggestions
        var inputValue = input.value.trim();
        if (inputValue === "") {
          return;
        }

        // Fetch suggestions from the server using AJAX
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
          if (this.readyState === 4 && this.status === 200) {
            var suggestions = JSON.parse(this.responseText);

            // Create a dropdown for suggestions using Bootstrap classes
            var dropdownDiv = document.createElement("div");
            dropdownDiv.classList.add("card", "dropdown-menu", "show", "w-100");

            // Clear the suggestedTags array before adding new suggestions
            suggestedTags[suggestionsId] = [];

            suggestions.forEach(function (suggestion) {
              // Check if the suggestion is not already in the suggestedTags array
              if (!suggestedTags[suggestionsId].includes(suggestion)) {
                suggestedTags[suggestionsId].push(suggestion);

                var a = document.createElement("a");
                a.classList.add("dropdown-item", "text-lowercase");
                a.href = "#";
                a.textContent = suggestion;
                a.onclick = function () {
                  addTag(input, suggestionsId, suggestion);
                };
                dropdownDiv.appendChild(a);
              }
            });
      
            // Append the dropdown to the suggestions element
            suggestionsElement.appendChild(dropdownDiv);
          }
        };

        // Adjusted PHP part
        var protocol = window.location.protocol === 'https:' ? 'https' : 'http';
        var serverUrl = protocol + '://' + window.location.host;
        xhttp.open("GET", serverUrl + "/get_suggestions.php?q=" + inputValue, true);
        xhttp.send();
      }

      var debouncedShowSuggestions = debounce(showSuggestions, 300);
  
      function addTag(input, suggestionsId, tag) {
        // Get the current input value
        var currentValue = input.value.trim();

        // If the current input value is empty, set the clicked suggestion as the input value
        if (currentValue === "") {
          input.value = tag;
        } else {
          // Otherwise, add the clicked suggestion as a new tag
          var tags = currentValue.split(",").map(function (item) {
            return item.trim();
          });

          // Check if the tag is not already in the tags list
          if (!tags.includes(tag)) {
            // Check if the tag starts with the current input prefix
            var prefix = tags[tags.length - 1];
            if (tag.toLowerCase().startsWith(prefix.toLowerCase())) {
              // Remove the prefix from the new tag to avoid duplication
              var newTag = tag.slice(prefix.length).trim();

              // If there is a comma at the end of the prefix, remove it
              if (tags[tags.length - 1].endsWith(",")) {
                tags[tags.length - 1] = tags[tags.length - 1].slice(0, -1).trim();
              }

              // Add the new tag to the list without any whitespace
              tags[tags.length - 1] = tags[tags.length - 1] + newTag;
            } else {
              tags.push(tag);
            }

            input.value = tags.join(", ");
          }
        }

        // Clear the suggestions
        var suggestionsElement = document.getElementById(suggestionsId);
        suggestionsElement.innerHTML = "";
      }
    </script>