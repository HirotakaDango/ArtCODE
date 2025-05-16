<?php
// Connect to the database using PDO
$db = new PDO('sqlite:../database.sqlite');

// Get the filename from the query string
$artworkId = $_GET['artworkid'];
$toUrl = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid ");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();

// Check if the image exists in the database
if (!$image) {
  header("Location: error.php");
  exit; // Stop further execution
}

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];
$email = $image['email'];

// Get the previous image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id < :id AND email = :email ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $email);
$stmt->execute();
$prev_image = $stmt->fetch();

// Get the next image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id > :id AND email = :email ORDER BY id ASC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $email);
$stmt->execute();
$next_image = $stmt->fetch();

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();
$image_id = $image['id'];

// Check if the user is logged in and get their email
$email = '';
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];
}

// Get the email of the selected user
$user_email = $image['email'];

// Get the selected user's information from the database
$query = $db->prepare('SELECT * FROM users WHERE email = :email');
$query->bindParam(':email', $user_email);
$query->execute();
$user = $query->fetch();

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
$query->bindParam(':follower_email', $email);
$query->bindParam(':following_email', $user_email);
$query->execute();
$is_following = $query->fetchColumn();

// Increment the view count for the image
$stmt = $db->prepare("UPDATE images SET view_count = view_count + 1 WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();

// Get the updated image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();

// Retrieve the updated view count from the image information
$viewCount = $image['view_count'];

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count the total number of images from "images" table for the specific artworkid
$stmt = $db->prepare("SELECT COUNT(*) as total_images FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_images = $stmt->fetch()['total_images'];

// Count the total number of images from "image_child" table for the specific artworkid
$stmt = $db->prepare("SELECT COUNT(*) as total_child_images FROM image_child WHERE image_id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_child_images = $stmt->fetch()['total_child_images'];

// Calculate the combined total
$total_all_images = $total_images + $total_child_images;

// Get image size of the original image in megabytes
$original_image_size = round(filesize('../images/' . $image['filename']) / (1024 * 1024), 2);

// Get image size of the thumbnail in megabytes
$thumbnail_image_size = round(filesize('../thumbnails/' . $image['filename']) / (1024 * 1024), 2);

// Calculate the percentage of reduction
$reduction_percentage = ((($original_image_size - $thumbnail_image_size) / $original_image_size) * 100);

// Get image dimensions
list($width, $height) = getimagesize('../images/' . $image['filename']);

// Get the current date
$currentDate = date('Y-m-d');

// Check if there's already a record for today in the daily table
$stmt = $db->prepare("SELECT * FROM daily WHERE image_id = :image_id AND date = :date");
$stmt->bindParam(':image_id', $image['id']);
$stmt->bindParam(':date', $currentDate);
$stmt->execute();
$daily_view = $stmt->fetch();

if ($daily_view) {
  // If there's already a record for today, increment the view count
  $stmt = $db->prepare("UPDATE daily SET views = views + 1 WHERE id = :id");
  $stmt->bindParam(':id', $daily_view['id']);
  $stmt->execute();
} else {
  // If there's no record for today, insert a new record
  $stmt = $db->prepare("INSERT INTO daily (image_id, views, date) VALUES (:image_id, 1, :date)");
  $stmt->bindParam(':image_id', $image['id']);
  $stmt->bindParam(':date', $currentDate);
  $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <script type="module" src="/swup/swup.js"></script>
    <style>
      html.is-changing .transition-main {
        transition: opacity 2ms ease-in-out;
      }
    </style>
  </head>
  <body>
    <?php include('header_preview.php'); ?>
    <?php include('terms.php'); ?>
    <main id="swup" class="transition-main">
    <div>
      <div class="container-fluid mb-2 d-flex d-md-none d-lg-none">
        <?php
          $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
          $stmt->bindParam(':id', $id);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="d-flex">
          <a class="text-decoration-none text-dark fw-bold rounded-pill" href="#" data-bs-toggle="modal" data-bs-target="#userModal">
            <?php if (!empty($user['pic'])): ?>
              <img class="object-fit-cover border border-1 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
            <?php else: ?>
              <img class="object-fit-cover border border-1 rounded-circle" src="icon/profile.svg" style="width: 32px; height: 32px;">
            <?php endif; ?>
            <?php echo (mb_strlen($user['artist']) > 10) ? mb_substr($user['artist'], 0, 10) . '...' : $user['artist']; ?> <small class="badge rounded-pill bg-dark"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
          </a>
        </div>
      </div>
      <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header border-bottom-0">
              <h5 class="modal-title fw-bold fs-5" id="exampleModalLabel"><?php echo $user['artist']; ?> <small class="badge rounded-pill bg-dark"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row featurette">
                <div class="col-5 order-1">
                  <a class="text-decoration-none d-flex justify-content-center text-dark fw-bold rounded-pill" href="../artist.php?by=newest&id=<?= $user['id'] ?>">
                    <?php if (!empty($user['pic'])): ?>
                      <img class="object-fit-cover border border-3 rounded-circle" src="../<?php echo $user['pic']; ?>" style="width: 103px; height: 103px;">
                    <?php else: ?>
                      <img class="object-fit-cover border border-3 rounded-circle" src="../icon/profile.svg" style="width: 103px; height: 103px;">
                    <?php endif; ?>
                  </a>
                </div>
                <div class="col-7 order-2">
                  <div class="btn-group w-100 mb-1 gap-1" role="group" aria-label="Basic example">
                    <a class="btn btn-sm btn-outline-dark w-50 rounded fw-bold" href="../follower.php?id=<?php echo $user['id']; ?>"><small>followers</small></a>
                    <a class="btn btn-sm btn-outline-dark w-50 rounded fw-bold" href="../following.php?id=<?php echo $user['id']; ?>"><small>following</small></a>
                  </div>
                  <div class="btn-group w-100 mb-1 gap-1" role="group" aria-label="Basic example">
                    <a class="btn btn-sm btn-outline-dark w-50 rounded fw-bold" href="../artist.php?by=newest&id=<?php echo $user['id']; ?>"><small>images</small></a>
                    <a class="btn btn-sm btn-outline-dark w-50 rounded fw-bold" href="../list_favorite.php?id=<?php echo $user['id']; ?>"><small>favorites</small></a> 
                  </div>
                  <a class="btn btn-sm btn-outline-dark w-100 rounded fw-bold" href="../artist.php?by=newest&id=<?php echo $user['id']; ?>"><small>view profile</small></a>
                </div>
              </div>
              <div class="input-group my-1">
                <?php
                  $domain = $_SERVER['HTTP_HOST'];
                  $user_id_url = $user['id'];
                  $url = "http://$domain/artist.php?by=newest&id=$user_id_url";
                ?>
                <input type="text" id="urlInput" value="<?php echo $url; ?>" class="form-control border-2 fw-bold" readonly>
                <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard()">
                  <i class="bi bi-clipboard-fill"></i>
                </button>
                <button class="btn btn-sm btn-secondary rounded-3 rounded-start-0 fw-bold opacity-50" onclick="shareArtist(<?php echo $user_id_url; ?>)">
                  <i class="bi bi-share-fill"></i> <small>share</small>
                </button>
              </div>
              <a class="btn btn-dark w-100 fw-bold mt-1" data-bs-toggle="collapse" href="#collapseBio" role="button" aria-expanded="false" aria-controls="collapseExample">
                <small>view description</small>
              </a>
              <div class="collapse mt-1" id="collapseBio">
                <div class="card fw-bold card-body">
                  <small>
                    <?php
                      $messageText = $user['desc'];
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a target="_blank" href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $formattedTextWithLineBreaks = nl2br($formattedText);
                      echo $formattedTextWithLineBreaks;
                    ?>
                  </small>
                </div>
              </div> 
            </div>
          </div>
        </div>
      </div>
      <div class="roow">
        <div class="cool-6">
          <div class="bg-body-tertiary d-flex justify-content-center d-md-none d-lg-none">
            <?php if ($next_image): ?>
              <button class="img-pointer btn me-auto border-0" onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
                <i class="bi bi-chevron-left text-stroke-2"></i>
              </button>
            <?php else: ?>
              <button class="img-pointer btn me-auto border-0" onclick="location.href='../artist.php?by=newest&id=<?php echo $user['id']; ?>'">
                <i class="bi bi-box-arrow-in-up-left text-stroke"></i>
              </button>
            <?php endif; ?>
            <h6 class="mx-auto img-pointer user-select-none text-center fw-bold scrollable-title mt-2" style="overflow-x: auto; white-space: nowrap; margin: 0 auto;">
              <?php echo $image['title']; ?>
            </h6>
            <?php if ($prev_image): ?>
              <button class="img-pointer btn ms-auto border-0" onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
                <i class="bi bi-chevron-right text-stroke-2"></i>
              </button>
            <?php else: ?>
              <button class="img-pointer btn ms-auto border-0" onclick="location.href='../artist.php?by=newest&id=<?php echo $user['id']; ?>'">
                <i class="bi bi-box-arrow-in-up-right text-stroke"></i>
              </button>
            <?php endif; ?>
          </div>
          <div class="caard position-relative">
            <?php
              $id    = (int)$image['id'];
              $ratio = $width > 0 ? ($height/$width)*100 : 56.25;
            ?>
            <div id="iframeContainer<?= $id ?>" class="ratio" style="--bs-aspect-ratio: <?= $ratio ?>%;">
              <a href="#" id="originalImageLink" data-bs-toggle="modal" data-bs-target="#originalImageModal" data-original-src="/images/<?php echo $image['filename']; ?>">
                <iframe src="iframe_image_view.php?artworkid=<?= $id ?>" class="ratio-item border-0 img-pointer shadow-lg rounded-r h-100 w-100" allowfullscreen scrolling="no" frameborder="0"></iframe>
              </a>
            </div>
            <!-- Original Image Modal -->

            <div class="modal fade" id="signinModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                  <div class="modal-header p-5 pb-4 border-bottom-0">
                    <h1 class="fw-bold mb-0 fs-2">Sign in to continue</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-5 pt-0">
                    <form class="" action="session_code.php" method="post">
                      <input type="hidden" name="tourl" value="<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/index.php'); ?>">
                      <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-3" name="email" id="floatingInput" placeholder="name@example.com" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" required>
                        <label class="fw-bold" for="floatingInput">Email address</label>
                      </div>
                      <div class="form-floating mb-3">
                        <input type="password" class="form-control rounded-3" name="password" id="floatingPassword" placeholder="Password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" required>
                        <label class="fw-bold" for="floatingPassword">Password</label>
                      </div>
                      <button name="login" class="w-100 mb-2 btn btn-lg rounded-3 btn-dark fw-bold" type="submit">Sign in</button>
                    </form>
                    <p class="fw-medium fw-bold">Don't have an account? <button data-bs-target="#signupModal" data-bs-toggle="modal" class="text-decoration-none text-white btn btn-dark btn-sm text-white fw-bold rounded-pill white-75">Signup</button></p>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal fade" id="signupModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                  <div class="modal-header p-5 pb-4 border-bottom-0">
                    <h1 class="fw-bold mb-0 fs-2">Sign up for free</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-5 pt-0">
                    <form class="" action="session_code.php" method="post">
                      <input type="hidden" name="tourl" value="<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/index.php'); ?>">
                      <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-3" name="artist" id="floatingInput" placeholder="name" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" required>
                        <label class="fw-bold" for="floatingInput">Name</label>
                      </div>
                      <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-3" name="email" id="floatingInput" placeholder="name@example.com" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" required>
                        <label class="fw-bold" for="floatingInput">Email address</label>
                      </div>
                      <div class="form-floating mb-3">
                        <input type="password" class="form-control rounded-3" name="password" id="floatingPassword" placeholder="Password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" required>
                        <label class="fw-bold" for="floatingPassword">Password</label>
                      </div>
                      <button name="register" class="w-100 mb-2 btn btn-lg rounded-3 btn-dark fw-bold" type="submit">Sign up</button>
                    </form>
                    <p class="fw-bold"><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" required> By clicking this, you'll agree with the <a class="" href="#" data-bs-target="#terms" data-bs-toggle="modal">terms of service</a>.</p>
                    <p class="fw-bold">Already have an account? <button data-bs-target="#signinModal" data-bs-toggle="modal" class="text-decoration-none btn btn-dark btn-sm text-white fw-bold rounded-pill opacity-75">Signin</button></p>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($next_image): ?>
              <div class="d-md-none d-lg-none">
                <a class="btn btn-sm opacity-75 rounded fw-bold position-absolute start-0 top-50 translate-middle-y rounded-start-0" href="?<?php echo http_build_query(array_merge($_GET, ['artworkid' => $next_image['id']])); ?>">
                  <i class="bi bi-chevron-left display-f" style="-webkit-text-stroke: 4px;"></i>
                </a>
              </div>
            <?php endif; ?> 
            <?php if ($prev_image): ?>
              <div class="d-md-none d-lg-none">
                <a class="btn btn-sm opacity-75 rounded fw-bold position-absolute end-0 top-50 translate-middle-y rounded-end-0" href="?<?php echo http_build_query(array_merge($_GET, ['artworkid' => $prev_image['id']])); ?>">
                  <i class="bi bi-chevron-right display-f" style="-webkit-text-stroke: 4px;"></i>
                </a>
              </div>
            <?php endif; ?>  
            <script>
              document.addEventListener('keydown', function(e) {
                // Skip if focused on an input or textarea
                if (['input', 'textarea'].includes(e.target.tagName.toLowerCase())) return;
                if (e.key === 'ArrowRight') {
                  const prevLink = document.getElementById('prevPageLink');
                  if (prevLink) {
                    prevLink.click();
                  }
                } else if (e.key === 'ArrowLeft') {
                  const nextLink = document.getElementById('nextPageLink');
                  if (nextLink) {
                    nextLink.click();
                  }
                }
              });

              function adjustMode() {
                const isMobile = window.innerWidth <= 767;
            
                // Extract current mode from the URL
                const urlParams = new URLSearchParams(window.location.search);
                const currentMode = urlParams.get('mode');
            
                // Build new URL for redirection (remove search/hash, use current path)
                function updateUrlModeParam(value) {
                  const params = new URLSearchParams();
                  for (const [key, val] of urlParams.entries()) {
                    if (key !== 'mode') params.append(key, val);
                  }
                  params.set('mode', value);
                  return window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                }
            
                if (isMobile && currentMode !== 'mobile') {
                  // Redirect to mobile mode if not already on mobile mode
                  window.location.href = updateUrlModeParam('mobile');
                } else if (!isMobile && currentMode !== 'desktop') {
                  // Redirect to desktop mode if not already on desktop mode
                  window.location.href = updateUrlModeParam('desktop');
                }
              }
            
              // Run after page load
              window.addEventListener('DOMContentLoaded', adjustMode);
              window.addEventListener('resize', adjustMode);
            </script>
            <button id="showProgressBtn" class="fw-bold btn btn-sm btn-dark position-absolute top-50 start-50 translate-middle text-nowrap rounded-pill opacity-75" style="display: none;">
              progress
            </button>
            <?php
              $image_id = $image['id'];
              $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
              $fav_count = $stmt->fetchColumn();
              if ($fav_count >= 1000000000) {
                $fav_count = round($fav_count / 1000000000, 1) . 'b';
              } elseif ($fav_count >= 1000000) {
                $fav_count = round($fav_count / 1000000, 1) . 'm';
              } elseif ($fav_count >= 1000) {
                $fav_count = round($fav_count / 1000, 1) . 'k';
              }
              $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
              $stmt->bindParam(':email', $email);
              $stmt->bindParam(':image_id', $image_id);
              $stmt->execute();
              $is_favorited = $stmt->fetchColumn();
              if ($is_favorited);
            ?>
          </div>
          <div class="d-none d-md-block d-lg-block">
            <div class="collapse" id="collapseCompression1">
              <div class="alert alert-warning fw-bold rounded-4">
                <small><p>first original image have been compressed to <?php echo round($reduction_percentage, 2); ?>%</p> (<a class="text-decoration-none" href="../images/<?php echo $image['filename']; ?>">click to view original image</a>)</small>
              </div>
            </div>
          </div>
        </div>
        <?php include('image_information_preview.php'); ?>
      </div>
    </div>
    </main>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>