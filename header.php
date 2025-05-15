<?php
// Connect to the SQLite database
$dbPath = $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite';
$db1 = new SQLite3($dbPath);

// Get the artist name from the database
$email1 = $_SESSION['email'];
$stmt1 = $db1->prepare("SELECT id, artist, bgpic, pic FROM users WHERE email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$pic1 = $row1['pic'];
$bgpic1 = $row1['bgpic'];
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
        <a class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> navbar-brand fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
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
            <div class="text-center mb-2 rounded-top-4 position-relative">
              <div class="rounded-top-4" style="background-image: url('<?php echo !empty($bgpic1) ? $bgpic1 : "icon/bg.png"; ?>'); background-size: cover; background-position: center; height: 100%; width: 100%; padding: 1em; margin-top: -8px;">
                <a class="d-block pt-3" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/settings/profile_picture.php"><img class="rounded-circle object-fit-cover border border-5" width="150" height="150" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture"></a>
                <h5 class="fw-bold mt-2 text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);"><?php echo $artist1; ?></h5>
                <p class="fw-medium text-white" style="margin-top: -10px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);"><small><?php echo $email1; ?></small></p>
                <a class="btn border-0 position-absolute end-0 bottom-0" href="/easter-egg/" style="opacity:0;" target="_blank">click me!</a>
              </div>
            </div>
            <div class="btn-group my-2 w-100 container gap-2" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'follower.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/follower.php?id=<?php echo $user_id1; ?>"><?php echo $num_followers1 ?> <small>followers</small></a>
              <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'following.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/following.php?id=<?php echo $user_id1; ?>"><?php echo $num_following1 ?> <small>following</small></a>
            </div>
            <div class="btn-group mb-3 w-100 container gap-2" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'myworks.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/myworks.php"><?php echo $count1; ?> <small>images</small></a>
              <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'favorite.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/favorite.php"><?php echo $fav_count1;?> <small>favorites</small></a> 
            </div>
            <div class="w-100 container mt-2">
              <a class="w-100 btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/quicknote/') !== false) echo 'active'; ?>" href="/quicknote/">
                <i class="bi bi-journal-text"></i> Quicknote
              </a>
            </div>
            <div class="w-100">
              <div class="btn-group mt-2 mb-2 w-100 container gap-2" role="group" aria-label="Basic example">
                <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/profile/') !== false) echo 'active'; ?>" href="/profile.php">
                  Profile
                </a>
                <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/favorites/') !== false) echo 'active'; ?>" href="/favorite.php">
                  Favorites
                </a>
              </div>
              <div class="btn-group mb-2 w-100 container gap-2" role="group" aria-label="Basic example">
                <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/albums/') !== false) echo 'active'; ?>" href="/album.php">
                  My Albums
                </a>
                <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'myworks.php') ? 'active' : ''; ?>" href="/myworks.php">
                  My Works
                </a> 
              </div>
              <div class="btn-group mb-1 w-100 container gap-2" role="group" aria-label="Basic example">
                <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if (basename($_SERVER['PHP_SELF']) == 'all.php' && strpos($_SERVER['PHP_SELF'], '/settings/') !== false) echo 'active'; ?>" href="/setting.php">
                  Settings
                </a>
                <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded w-50 fw-bold <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/history/') !== false) echo 'active'; ?>" href="/history.php">
                  History
                </a> 
              </div>
            </div>
            <div class="mx-1">
              <div class="container-fluid mb-2">
                <hr class="border-3 rounded">
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
        <div class="offcanvas offcanvas-start w-100" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
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

              <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-100 mt-2 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/home/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/">
                <i class="bi bi-house-fill fs-5"></i>
                <span class="d-md-none d-lg-inline d-lg-none">Home</span>
              </a>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/upload/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/upload/">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Upload</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis position-relative rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/inboxes/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/inboxes/">
                  <div class="position-relative">
                    <i class="bi bi-envelope-fill fs-5"></i>
                    <div class="position-absolute top-0 start-100 mt-1 translate-middle">
                      <span class="badge rounded-pill bg-danger" id="unread-count"></span>
                      <span class="visually-hidden">unread messages</span>
                    </div>
                    <script>
                      function updateUnreadCount() {
                        fetch('/inboxes.php')  // Ensure the correct path to inboxes.php
                          .then(response => response.json())
                          .then(data => {
                            const unreadElement = document.getElementById('unread-count');
                            if (unreadElement) {
                              unreadElement.textContent = data.unread_count > 0 ? 
                                (data.unread_count >= 1e12 ? (data.unread_count / 1e12).toFixed(1) + 't' : 
                                data.unread_count >= 1e9 ? (data.unread_count / 1e9).toFixed(1) + 'b' : 
                                data.unread_count >= 1e6 ? (data.unread_count / 1e6).toFixed(1) + 'm' : 
                                data.unread_count >= 1e3 ? (data.unread_count / 1e3).toFixed(1) + 'k' : data.unread_count) : '';
                            }
                          })
                          .catch(error => console.error('Error fetching unread count:', error));
                      }
                
                      // Fetch unread count every 10 seconds
                      setInterval(updateUnreadCount, 10000);  // Fetch every 10 seconds
                
                      // Fetch the count immediately when the page loads
                      document.addEventListener('DOMContentLoaded', updateUnreadCount);
                    </script>
                  </div>
                  <span class="d-md-none d-lg-inline d-lg-none">Inboxes</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/advance_search/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/advance_search/">
                  <i class="bi bi-filter-left fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Advance Search</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/similar_image_search/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/similar_image_search/">
                  <i class="bi bi-search fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Similar Search</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/gallerium/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/gallerium/">
                  <i class="bi bi-collection-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Gallerium</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/">
                  <i class="bi bi-distribute-vertical fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Scrolls</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if ((basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == 'favorite.php') && strpos($_SERVER['PHP_SELF'], '/text/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseText" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-pencil-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Text</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if ((basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == 'favorite.php') && strpos($_SERVER['PHP_SELF'], '/manga/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseManga" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-journals fs-5"></i>
                  <span class="d-lg-inline">Manga</span>
                </a>
              </div>
              <div class="collapse-content">
                <div class="collapse mt-2" id="collapseText">
                  <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                    <div class="btn-group-vertical gap-2">
                      <h6 class="fw-bold text-start">Text</h6>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/text/">Home</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/text/favorite.php">Favorites</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/text/?uid=<?php echo $user_id1; ?>">Profile</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="collapse-content">
                <div class="collapse mt-2" id="collapseManga">
                  <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                    <div class="btn-group-vertical gap-2">
                      <h6 class="fw-bold text-start">Manga</h6>
                      <form action="/feeds/manga/" method="GET" class="my-3 w-100">
                        <div class="input-group">
                          <input type="text" name="search" class="form-control text-lowercase fw-bold rounded-end-0 rounded-4 border-0 bg-body-secondary" placeholder="Search">
                          <button type="submit" class="btn bg-body-secondary link-body-emphasis border-0 rounded-start-0 rounded-4"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
                        </div>
                      </form>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga">Home</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/artists.php">Artists</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/tags.php">Tags</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/groups.php">Groups</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/parodies.php">Parodies</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/characters.php">Characters</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/messages/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/messages/">
                  <i class="bi bi-chat-fill fs-5"></i>
                  <span class="d-lg-inline">Messages</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/notification/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notification/">
                  <i class="bi bi-bell-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Notification</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/notes/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseNotes" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-journal-text fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Notes</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/novel/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseNovel" role="button" aria-expanded="false" aria-controls="collapseExample">
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
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/music/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseMusic" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-vinyl-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Music</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/minutes/') !== false) echo 'opacity-75 shadow'; ?>" data-bs-toggle="collapse" href="#collapseMinutes" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-person-video2 fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Minutes</span>
                </a>
              </div>
              <div class="collapse-content">
                <div class="collapse mt-2" id="collapseMusic">
                  <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                    <div class="btn-group-vertical gap-2">
                      <h6 class="fw-bold text-start">Music</h6>
                      <form action="/feeds/music/" method="GET" class="my-3 w-100">
                        <div class="input-group">
                          <input type="text" name="q" class="form-control text-lowercase fw-bold rounded-end-0 rounded-4 border-0 bg-body-secondary" placeholder="Search title and artist">
                          <button type="submit" class="btn bg-body-secondary link-body-emphasis border-0 rounded-start-0 rounded-4"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
                        </div>
                      </form>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/?mode=lists&by=newest_lists">Home</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/upload.php?mode=lists">Upload</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/favorite.php?mode=lists">Favorites</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_artist.php?mode=lists&by=desc_lists">Artists</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_album.php?mode=lists&by=desc_lists">Albums</a>
                      <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/profile.php?mode=lists&by=newest_lists">Profile</a>
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
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/users/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/users.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Users</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/tags/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/tags.php">
                  <i class="bi bi-tags-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Tags</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/characters/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/characters.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-lg-inline">Characters</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/parodies/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/parodies.php">
                  <i class="bi bi-journals fs-5"></i>
                  <span class="d-lg-inline">Parodies</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/groups/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/groups.php">
                  <i class="bi bi-person-fill fs-5"></i>
                  <span class="d-lg-inline">Groups</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/forum.php">
                  <i class="bi bi-chat-left-dots-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Forum</span>
                </a>
              </div>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/status.php">
                  <i class="bi bi-card-text fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Status</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/explores/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/explores/">
                  <i class="bi bi-compass-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Explore</span>
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
            <ul class="position-absolute top-50 start-50 translate-middle navbar-nav mb-lg-0 fw-bold d-none-md-lg" style="margin-top: 0.1em;">
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/home/') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                  Home
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/scrolls/') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/">
                  Scrolls
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/gallerium/') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/gallerium/">
                  Gallerium
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/upload/index.php') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/upload/">
                  Upload
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'bg-dark-subtle rounded-pill' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/forum.php">
                  Forum
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if ((basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == 'favorite.php') && strpos($_SERVER['PHP_SELF'], '/text/') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseText">
                  Text
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/notification/') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notification/">
                  Notification
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/search/') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#searchTerm">
                  Search
                </a>
              </li>
              <li class="nav-item mx-1">
                <a class="btn border-0 fw-bold text-decoration-none mb-1 text-nowrap text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/advance_search/') !== false) echo 'bg-dark-subtle rounded-pill'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/advance_search/">
                  Advance Search
                </a>
              </li>
              <li class="nav-item mx-1">
                <div class="dropdown-center">
                  <a class="btn border-0 fw-bold text-decoration-none mb-1 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> link-body-emphasis dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="false">
                    More
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end rounded-4 shadow border-0" style="width: 450px;">
                    <div class="container-fluid px-3 overflow-auto">
                      <div class="btn-group gap-2 w-100 mt-2">
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/similar_image_search/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/similar_image_search/">
                          <i class="bi bi-search fs-5"></i>
                          <span class="d-lg-inline">Similar Image Search</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/inboxes/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/inboxes/">
                          <div class="position-relative">
                            <i class="bi bi-envelope-fill fs-5"></i>
                            <div class="position-absolute top-0 start-100 mt-1 translate-middle">
                              <span class="badge rounded-pill bg-danger" id="unread-count-Desktop"></span>
                              <span class="visually-hidden">unread messages</span>
                            </div>
                            <script>
                              function updateUnreadCountDesktop() {
                                fetch('/inboxes.php')  // Ensure the correct path to inboxes.php
                                  .then(response => response.json())
                                  .then(dataDesktop => {
                                    const unreadElementDesktop = document.getElementById('unread-count-Desktop');
                                    if (unreadElementDesktop) {
                                      unreadElementDesktop.textContent = dataDesktop.unread_count > 0 ? 
                                        (dataDesktop.unread_count >= 1e12 ? (dataDesktop.unread_count / 1e12).toFixed(1) + 't' : 
                                        dataDesktop.unread_count >= 1e9 ? (dataDesktop.unread_count / 1e9).toFixed(1) + 'b' : 
                                        dataDesktop.unread_count >= 1e6 ? (dataDesktop.unread_count / 1e6).toFixed(1) + 'm' : 
                                        dataDesktop.unread_count >= 1e3 ? (dataDesktop.unread_count / 1e3).toFixed(1) + 'k' : dataDesktop.unread_count) : '';
                                    }
                                  })
                                  .catch(errorDesktop => console.error('Error fetching unread count:', errorDesktop));
                              }
                        
                              // Fetch unread count every 10 seconds
                              setInterval(updateUnreadCountDesktop, 10000);  // Fetch every 10 seconds
                        
                              // Fetch the count immediately when the page loads
                              document.addEventListener('DOMContentLoaded', updateUnreadCountDesktop);
                            </script>
                          </div>
                          <span class="d-lg-inline">Inboxes</span>
                        </a>
                      </div>
                      <div class="btn-group gap-2 w-100 mt-2">
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/notes/') !== false) echo 'opacity-75 shadow'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseNotes">
                          <i class="bi bi-journal-text fs-5"></i>
                          <span class="d-lg-inline">Notes</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/novel/') !== false) echo 'opacity-75 shadow'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseNovel">
                          <i class="bi bi-book-half fs-5"></i>
                          <span class="d-lg-inline">Novel</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/manga/') !== false) echo 'opacity-75 shadow'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseManga">
                          <i class="bi bi-journals fs-5"></i>
                          <span class="d-lg-inline">Manga</span>
                        </a>
                      </div>
                      <div class="btn-group gap-2 w-100 mt-2">
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/music/') !== false) echo 'opacity-75 shadow'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseMusic">
                          <i class="bi bi-vinyl-fill fs-5"></i>
                          <span class="d-lg-inline">Music</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/minutes/') !== false) echo 'opacity-75 shadow'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#modalCollapseMinutes">
                          <i class="bi bi-person-video2 fs-5"></i>
                          <span class="d-lg-inline">Minutes</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/messages_desktop/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/messages_desktop/">
                          <i class="bi bi-chat-fill fs-5"></i>
                          <span class="d-lg-inline">Messages</span>
                        </a>
                      </div>
                      <div class="btn-group gap-2 w-100 mt-2">
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/tags/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/tags.php">
                          <i class="bi bi-tags-fill fs-5"></i>
                          <span class="d-lg-inline">Tags</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/explores/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/explores/">
                          <i class="bi bi-compass-fill fs-5"></i>
                          <span class="d-lg-inline">Explore</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/users/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/users.php">
                          <i class="bi bi-person-circle fs-5"></i>
                          <span class="d-lg-inline">Users</span>
                        </a>
                      </div>
                      <div class="btn-group gap-2 w-100 mt-2">
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/characters/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/characters.php">
                          <i class="bi bi-people-fill fs-5"></i>
                          <span class="d-lg-inline">Characters</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/parodies/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/parodies.php">
                          <i class="bi bi-journals fs-5"></i>
                          <span class="d-lg-inline">Parodies</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/groups/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/groups.php">
                          <i class="bi bi-person-fill fs-5"></i>
                          <span class="d-lg-inline">Groups</span>
                        </a>
                      </div>
                      <div class="btn-group gap-2 w-100 my-2">
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/status.php">
                          <i class="bi bi-card-text fs-5"></i>
                          <span class="d-lg-inline">Status</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'news.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/news.php">
                          <i class="bi bi-newspaper fs-5"></i>
                          <span class="d-lg-inline">News</span>
                        </a>
                        <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-3 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if(basename($_SERVER['PHP_SELF']) == 'support.php') echo 'opacity-75 shadow' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/support.php">
                          <i class="bi bi-headset fs-5"></i>
                          <span class="d-lg-inline">Support</span>
                        </a>
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
          <div class="d-flex position-relative">
            <h6 class="fw-bold text-start me-auto ms-3 mt-3">Notes</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto me-1" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
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
          <div class="d-flex position-relative">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Notes</h6>
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

    <div class="modal fade" id="modalCollapseManga" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex position-relative">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Manga</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <form action="/feeds/manga/" method="GET" class="mb-3">
                <div class="input-group">
                  <input type="text" name="search" class="form-control text-lowercase fw-bold rounded-end-0 rounded-4 border-0 bg-body-tertiary" placeholder="Search">
                  <button type="submit" class="btn bg-body-tertiary link-body-emphasis border-0 rounded-start-0 rounded-4"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
                </div>
              </form>
              <div class="btn-group-vertical gap-2">
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga">Home</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/artists.php">Artists</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/tags.php">Tags</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/groups.php">Groups</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/parodies.php">Parodies</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/manga/characters.php">Characters</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalCollapseText" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex position-relative">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Text</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <div class="btn-group-vertical gap-2">
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/text/">Home</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/text/favorite.php">Favorites</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/text/?uid=<?php echo $user_id1; ?>">Profile</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalCollapseMusic" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex position-relative">
            <h6 class="fw-bold text-start me-auto ms-3 mt-2">Music</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <form action="/feeds/music/" method="GET" class="mb-3">
                <div class="input-group">
                  <input type="text" name="q" class="form-control text-lowercase fw-bold rounded-end-0 rounded-4 border-0 bg-body-tertiary" placeholder="Search title and artist">
                  <button type="submit" class="btn bg-body-tertiary link-body-emphasis border-0 rounded-start-0 rounded-4"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
                </div>
              </form>
              <div class="btn-group-vertical gap-2">
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/?mode=lists&by=newest_lists">Home</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/upload.php?mode=lists">Upload</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/favorite.php?mode=lists">Favorites</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_artist.php?mode=lists&by=desc_lists">Artists</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/all_album.php?mode=lists&by=desc_lists">Albums</a>
                <a class="text-start btn link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="/feeds/music/profile.php?mode=lists&by=newest_lists">Profile</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalCollapseMinutes" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="d-flex position-relative">
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
    <button id="scrollButton" class="text-view-none btn fw-bold btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill <?= (basename($_SERVER['PHP_SELF']) === 'album.php' || basename($_SERVER['PHP_SELF']) === 'list_favorite.php' || basename($_SERVER['PHP_SELF']) === 'forum.php') ? 'd-none' : ''; ?> d-md-none d-lg-none position-fixed bottom-0 end-0 m-3 z-3" data-bs-toggle="modal" data-bs-target="#navModal">
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
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/imgupload.php" class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-2 d-block feature-icon mb-3 py-3 border-3">
                    <i class="bi bi-cloud-arrow-up" style="font-size: 30px; -webkit-text-stroke: 1px;"></i>
                  </a>
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/imgupload.php" class="fw-bold text-center text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> d-block">Upload</a>
                </div>
                <div class="col-4">
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/favorite.php" class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-2 d-block feature-icon mb-3 py-3 border-3">
                    <i class="bi bi-heart" style="font-size: 30px; -webkit-text-stroke: 1px;"></i>
                  </a>
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/favorite.php" class="fw-bold text-center text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> d-block">Favorite</a>
                </div>
                <div class="col-4">
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/album.php" class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-2 d-block feature-icon mb-3 py-3 border-3">
                    <i class="bi bi-columns" style="font-size: 30px; -webkit-text-stroke: 1px;"></i>
                  </a>
                  <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/album.php" class="fw-bold text-center text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> d-block">Album</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mb-1"></div>
    <style>
      .fade-in-out {
        opacity: 1;
        transition: opacity 0.5s ease-in-out;
      }
      
      .hidden-button {
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
      }
      
      @media (min-width: 768px) {
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
        background-color: <?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>;
        border: none;
        font-size: 8px;
        margin-top: -3px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
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