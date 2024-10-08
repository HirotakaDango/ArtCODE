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
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header_preview.php'); ?>
    <?php include('terms.php'); ?>
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
            <a href="#" id="originalImageLink" data-bs-toggle="modal" data-bs-target="#signupModal" data-original-src="../images/<?php echo $image['filename']; ?>">
              <img class="img-pointer shadow-lg rounded-r h-100 w-100" src="../thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
            </a>
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

            <div class="position-absolute bottom-0 end-0 me-2 mb-2">
              <div class="btn-group">
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark opacity-75 rounded-3 rounded-end-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#signupModal">
                        <i class="bi bi-distribute-vertical"></i> full gallery view
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#signupModal">
                        <i class="bi bi-distribute-horizontal"></i> full carousel view
                      </a>
                    </li>
                  </ul>
                </div>
                <button class="btn btn-sm btn-dark fw-bold opacity-75 rounded-0" id="loadOriginalBtn">Load Original Image</button>
                <a class="btn btn-sm btn-dark fw-bold opacity-75 rounded-3 rounded-start-0" data-bs-toggle="modal" data-bs-target="#signupModal">
                  <i class="bi bi-cloud-arrow-down-fill"></i>
                </a>
              </div>
            </div>
            <?php if ($next_image): ?>
              <div class="d-md-none d-lg-none">
                <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute start-0 top-50 translate-middle-y rounded-start-0"  onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
                  <i class="bi bi-chevron-left display-f" style="-webkit-text-stroke: 4px;"></i>
                </button>
              </div>
            <?php endif; ?> 
            <?php if ($prev_image): ?>
              <div class="d-md-none d-lg-none">
                <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute end-0 top-50 translate-middle-y rounded-end-0"  onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
                  <i class="bi bi-chevron-right display-f" style="-webkit-text-stroke: 4px;"></i>
                </button>
              </div>
            <?php endif; ?>  
            <button id="showProgressBtn" class="fw-bold btn btn-sm btn-dark position-absolute top-50 start-50 translate-middle text-nowrap rounded-pill opacity-75" style="display: none;">
              progress
            </button>
            <div class="position-absolute bottom-0 start-0 ms-2 mb-2">
              <div class="btn-group">
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
                <button class="btn btn-sm btn-dark opacity-75 rounded-3 rounded-end-0" data-bs-toggle="modal" data-bs-target="#shareLink">
                  <i class="bi bi-share-fill"></i>
                </button>
                <button class="btn btn-sm btn-dark opacity-75 rounded-3 rounded-start-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompression1" aria-expanded="false" aria-controls="collapseExample1" id="toggleButton1">
                  <i class="bi bi-caret-down-fill"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="d-none d-md-block d-lg-block">
            <div class="collapse" id="collapseCompression1">
              <div class="alert alert-warning fw-bold rounded-4">
                <small><p>first original image have been compressed to <?php echo round($reduction_percentage, 2); ?>%</p> (<a class="text-decoration-none" href="../images/<?php echo $image['filename']; ?>">click to view original image</a>)</small>
              </div>
            </div>
          </div>
        </div>
        <div class="cool-6">
          <div class="container d-md-none d-lg-none">
            <div class="collapse" id="collapseCompression1">
              <div class="alert alert-warning fw-bold rounded-3">
                <small>first original image have been compressed to <?php echo round($reduction_percentage, 2); ?>% (<a class="text-decoration-none" href="../images/<?php echo $image['filename']; ?>">click to view original image</a>)</small>
              </div>
            </div>
          </div>
          <div class="caard border-md-lg">
            <div class="container-fluid mb-4 d-none d-md-flex d-lg-flex">
              <?php
                $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.region, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
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
                  <?php echo (mb_strlen($user['artist']) > 20) ? mb_substr($user['artist'], 0, 20) . '...' : $user['artist']; ?> <small class="badge rounded-pill bg-dark"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
                </a>
              </div>
            </div>
            <div class="me-2 ms-2 rounded fw-bold">
              <div class="d-flex d-md-none d-lg-none gap-2">
                <?php if ($next_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $next_image['id'] ?>">
                    <div class="position-relative">
                      <div class="ratio ratio-1x1">
                        <img class="img-blur object-fit-cover rounded opacity-75" src="../thumbnails/<?php echo $next_image['filename']; ?>" alt="<?php echo $next_image['title']; ?>">
                      </div>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-arrow-left-circle text-stroke"></i> Next
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="/artist.php?by=newest&id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                        </div>
                      <?php else: ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="icon/profile.svg">
                        </div>
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                      </h6>
                    </div>
                  </a>
                <?php endif; ?>
                <a class="image-containerA shadow rounded" href="?artworkid=<?= $image['id'] ?>">
                  <div class="ratio ratio-1x1">
                    <img class="object-fit-cover opacity-50 rounded" src="../thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
                  </div>
                </a>
                <?php if ($prev_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $prev_image['id'] ?>">
                    <div class="position-relative">
                      <div class="ratio ratio-1x1">
                        <img class="img-blur object-fit-cover rounded opacity-75" src="../thumbnails/<?php echo $prev_image['filename']; ?>" alt="<?php echo $prev_image['title']; ?>">
                      </div>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        Prev <i class="bi bi-arrow-right-circle text-stroke"></i>
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="/artist.php?by=newest&id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                        </div>
                      <?php else: ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="icon/profile.svg">
                        </div>
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                      </h6>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
              <h5 class="text-dark fw-bold text-center mt-3"><?php echo $image['title']; ?></h5>
              <div style="word-break: break-word;" data-lazyload>
                <p class="text-dark small fw-medium" style="word-break: break-word;">
                  <?php
                    if (!empty($image['imgdesc'])) {
                      $messageText = $image['imgdesc'];
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $charLimit = 400; // Set your character limit

                      if (strlen($formattedText) > $charLimit) {
                        $limitedText = substr($formattedText, 0, $charLimit);
                        echo '<span id="limitedText1">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                        echo '<span id="more1" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                        echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction1()" id="myBtn1"><small>read more</small></button>';
                      } else {
                        // If the text is within the character limit, just display it with line breaks.
                        echo nl2br($formattedText);
                      }
                    } else {
                      echo "User description is empty.";
                    }
                  ?>
                  <script>
                    function myFunction1() {
                      var dots1 = document.getElementById("limitedText1");
                      var moreText1 = document.getElementById("more1");
                      var btnText1 = document.getElementById("myBtn1");

                      if (moreText1.style.display === "none") {
                        dots1.style.display = "none";
                        moreText1.style.display = "inline";
                        btnText1.innerHTML = "read less";
                      } else {
                        dots1.style.display = "inline";
                        moreText1.style.display = "none";
                        btnText1.innerHTML = "read more";
                      }
                    }
                  </script>
                </p>
              </div>
              <p class="text-secondary" style="word-wrap: break-word;">
                <a class="text-dark" href="<?php echo $image['link']; ?>">
                  <small>
                    <?php echo (strlen($image['link']) > 40) ? substr($image['link'], 0, 40) . '...' : $image['link']; ?>
                  </small>
                </a>
              </p>
              <div class="container-fluid bg-body-secondary p-2 mt-2 mb-2 rounded-4 text-center align-items-center d-flex justify-content-center">
                <div class="dropdown-center">
                  <button class="btn text-secondary border-0 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <small>
                      <?php echo date('Y/m/d', strtotime($image['date'])); ?>
                    </small
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-semibold text-center" href="#">
                        uploaded at <?php echo date('F j, Y', strtotime($image['date'])); ?>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn text-secondary border-0 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-heart-fill text-sm"></i> <small><?php echo $fav_count; ?></small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-semibold text-center" href="#">
                        total <?php echo $fav_count; ?> favorites
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn text-secondary border-0 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-eye-fill"></i> <small><?php echo $viewCount; ?> </small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-semibold text-center" href="#">
                        total <?php echo $viewCount; ?> views
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="w-100 my-2">
                <a class="btn btn-dark rounded-4 fw-bold w-100" href="/preview/similar_image_search/?image=/images/<?php echo urlencode($image['filename']); ?>">
                  <small>find similar image</small>
                </a>
              </div>
              <div class="btn-group w-100" role="group" aria-label="Basic example">
                <button class="btn btn-dark fw-bold rounded-start-4" data-bs-toggle="modal" data-bs-target="#shareLink">
                  <i class="bi bi-share-fill"></i> <small>share</small>
                </button>
                <a class="btn btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#signinModal">
                  <i class="bi bi-cloud-arrow-down-fill"></i> <small>download</small>
                </a>
                <button class="btn btn-dark dropdown-toggle fw-bold rounded-end-4" type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#dataModal">
                  <i class="bi bi-info-circle-fill"></i> <small>info</small>
                </button>
                <?php
                  // Function to calculate the size of an image in MB
                  function getImageSizeInMB($artworkId) {
                    return round(filesize('../images/' . $artworkId) / (1024 * 1024), 2);
                  }

                  // Get the total size of images from 'images' table
                  $stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
                  $stmt->bindParam(':artworkid', $artworkId);
                  $stmt->execute();
                  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                  // Get the total size of images from 'image_child' table
                  $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :artworkid");
                  $stmt->bindParam(':artworkid', $artworkId);
                  $stmt->execute();
                  $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                  
                  // Function to format the date
                  function formatDate($date) {
                    return date('Y/F/l jS') ;
                  }

                  $images_total_size = 0;
                  foreach ($images as $image) {
                    $images_total_size += getImageSizeInMB($image['filename']);
                  }

                  $image_child_total_size = 0;
                  foreach ($image_childs as $image_child) {
                    $image_child_total_size += getImageSizeInMB($image_child['filename']);
                  }
                                
                  $total_size = $images_total_size + $image_child_total_size;
                ?>

                <!-- Data Modal -->
                <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                    <div class="modal-content rounded-4 border-0">
                      <div class="modal-header border-0">
                        <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">All Data from <?php echo $image['title']; ?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div>
                          <div class="text-dark text-center mt-2 mb-4">
                            <h6 class="fw-bold"><i class="bi bi-file-earmark-plus"></i> Total size of all images: <?php echo $total_size; ?> MB</h6>
                          </div>
                          <button class="btn btn-outline-dark fw-bold w-100 mb-2" id="toggleButton3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDataImage1" aria-expanded="false" aria-controls="collapseExample">
                            <i class="bi bi-caret-down-fill"></i> <small>show more</small>
                          </button>
                          <div class="collapse mt-2" id="collapseDataImage1">
                            <?php foreach ($images as $index => $image) { ?>
                              <div class="mb-3 img-thumbnail bg-body-tertiary shadow border-0 p-3">
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Filename</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo $image['filename']; ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image data size</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo getImageSizeInMB($image['filename']); ?> MB" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image dimensions</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php list($width, $height) = getimagesize('../images/' . $image['filename']); echo $width . 'x' . $height; ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">MIME type</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo mime_content_type('../images/' . $image['filename']); ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image date</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo date('Y/m/d', strtotime($image['date'])); ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <a class="text-decoration-none text-dark" href="../images/<?php echo $image['filename']; ?>">
                                    <p><i class='bi bi-arrows-fullscreen text-stroke'></i> View original image</p>
                                  </a>
                                </div>
                                <div>
                                  <a class="text-decoration-none text-dark" href="../images/<?php echo $image['filename']; ?>" download>
                                    <p><i class='bi bi-cloud-arrow-down-fill'></i> Download original image</p>
                                  </a>
                                </div>
                              </div>
                            <?php } ?>
                            <?php
                              $images_total_size = 0;
                              foreach ($images as $image) {
                                $images_total_size += getImageSizeInMB($image['filename']);
                              }

                              $image_child_total_size = 0;
                              foreach ($image_childs as $image_child) {
                                $image_child_total_size += getImageSizeInMB($image_child['filename']);
                              }
                                
                              $total_size = $images_total_size + $image_child_total_size;
                            ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End of Data Modal -->

              </div>
              <?php if (isset($image['episode_name']) && !empty($image['episode_name'])): ?>
                <div class="btn-group gap-2 w-100 my-2">
                  <a class="btn btn-dark fw-bold rounded-4 w-50" target="_blank" href="/manga/title.php?title=<?php echo urlencode($image['episode_name']); ?>&uid=<?php echo $user['id']; ?>">
                    <small>go to manga</small>
                  </a>
                  <a class="btn btn-dark fw-bold rounded-4 w-50" target="_blank" href="/manga/view.php?title=<?php echo urlencode($image['episode_name']); ?>&uid=<?php echo $user['id']; ?>&id=<?php echo $image['id']; ?>&page=1">
                    <small>read in manga mode</small>
                  </a>
                </div>
              <?php endif; ?>
              <div class="d-none d-md-flex d-lg-flex mt-2 gap-2">
                <?php if ($next_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $next_image['id'] ?>">
                    <div class="position-relative">
                      <div class="ratio ratio-1x1">
                        <img class="img-blur object-fit-cover rounded opacity-75" src="../thumbnails/<?php echo $next_image['filename']; ?>" alt="<?php echo $next_image['title']; ?>">
                      </div>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-arrow-left-circle text-stroke"></i> Next
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="/artist.php?by=newest&id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                        </div>
                      <?php else: ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="icon/profile.svg">
                        </div>
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                      </h6>
                    </div>
                  </a>
                <?php endif; ?>
                <a class="image-containerA shadow rounded" href="?artworkid=<?= $image['id'] ?>">
                  <div class="ratio ratio-1x1">
                    <img class="object-fit-cover opacity-50 rounded" src="../thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
                  </div>
                </a>
                <?php if ($prev_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $prev_image['id'] ?>">
                    <div class="position-relative">
                      <div class="ratio ratio-1x1">
                        <img class="img-blur object-fit-cover rounded opacity-75" src="../thumbnails/<?php echo $prev_image['filename']; ?>" alt="<?php echo $prev_image['title']; ?>">
                      </div>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        Prev <i class="bi bi-arrow-right-circle text-stroke"></i>
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="/artist.php?by=newest&id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                        </div>
                      <?php else: ?>
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="icon/profile.svg">
                        </div>
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                      </h6>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
              <a class="btn btn-dark rounded-4 mt-2 fw-bold w-100" style="word-wrap: break-word;" href="../artist.php?by=newest&id=<?= $user['id'] ?>">
                <small>
                  <i class="bi bi-images"></i> view all <?php echo $user['artist']; ?>'s images
                </small>
              </a>
              <?php include 'imguser.php'; ?>
              <div class="card shadow border-0 rounded-4 bg-body-tertiary mt-3">
                <div class="card-body">
                  <!-- Tags -->
                  <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-tags-fill"></i> Tags</h6>
                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php
                    $tagCount = 0;
                    
                    if (!empty($image['tags'])) {
                      $tags = explode(',', $image['tags']);
                      foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if (!empty($tag)) {
                          $query = "SELECT COUNT(*) FROM images WHERE tags LIKE :tag";
                          $tagParam = '%' . $tag . '%';
                          $stmt = $db->prepare($query);
                          $stmt->bindParam(':tag', $tagParam);
                          $stmt->execute();
                          
                          $tagCount = $stmt->fetchColumn();
                          ?>
                          <a href="/preview/keyword/?tag=<?php echo urlencode($tag); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                            <i class="bi bi-tag-fill"></i> <?php echo $tag; ?> <span class="badge bg-light text-dark"><?php echo $tagCount; ?></span>
                          </a>
                          <?php
                        }
                      }
                    } else {
                      echo "<p class='text-muted'>No tags available.</p>";
                    }
                    ?>
                  </div>
            
                  <!-- Characters -->
                  <?php if (isset($image['characters']) && !empty($image['characters'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-people-fill"></i> Characters</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      <?php
                      if (!empty($image['characters'])) {
                        $characters = explode(',', $image['characters']);
                        foreach ($characters as $character) {
                          $character = trim($character);
                          if (!empty($character)) {
                            $query = "SELECT COUNT(*) FROM images WHERE characters LIKE :character";
                            $characterParam = '%' . $character . '%';
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':character', $characterParam);
                            $stmt->execute();
                            
                            $characterCount = $stmt->fetchColumn();
                            ?>
                            <a href="/preview/keyword/?character=<?php echo urlencode($character); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                              <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($character); ?> <span class="badge bg-light text-dark"><?php echo $characterCount; ?></span>
                            </a>
                            <?php
                          }
                        }
                      } else {
                        echo "<p class='text-muted'>No characters available.</p>";
                      }
                      ?>
                    </div>
                  <?php endif; ?>
            
                  <!-- Parodies -->
                  <?php if (isset($image['parodies']) && !empty($image['parodies'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-journals"></i> Parodies</h6>
                    <div class="d-flex flex-wrap gap-2">
                      <?php
                      if (!empty($image['parodies'])) {
                        $parodies = explode(',', $image['parodies']);
                        foreach ($parodies as $parody) {
                          $parody = trim($parody);
                          if (!empty($parody)) {
                            $query = "SELECT COUNT(*) FROM images WHERE parodies LIKE :parody";
                            $parodyParam = '%' . $parody . '%';
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':parody', $parodyParam);
                            $stmt->execute();
                            
                            $parodyCount = $stmt->fetchColumn();
                            ?>
                            <a href="/preview/keyword/?parody=<?php echo urlencode($parody); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                              <i class="bi bi-journal"></i> <?php echo htmlspecialchars($parody); ?> <span class="badge bg-light text-dark"><?php echo $parodyCount; ?></span>
                            </a>
                            <?php
                          }
                        }
                      } else {
                        echo "<p class='text-muted'>No parodies available.</p>";
                      }
                      ?>
                    </div>
                  <?php endif; ?>

                  <!-- Group -->
                  <?php if (isset($image['group']) && !empty($image['group'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-person-fill"></i> group</h6>
                    <div class="d-flex flex-wrap gap-2">
                      <?php
                      if (!empty($image['group'])) {
                        $group = explode(',', $image['group']);
                        foreach ($group as $group) {
                          $group = trim($group);
                          if (!empty($group)) {
                            $query = "SELECT COUNT(*) FROM images WHERE `group` LIKE :group";
                            $groupParam = '%' . $group . '%';
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':group', $groupParam);
                            $stmt->execute();
                            
                            $groupCount = $stmt->fetchColumn();
                            ?>
                            <a href="/preview/keyword/?group=<?php echo urlencode($group); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                              <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($group); ?> <span class="badge bg-light text-dark"><?php echo $groupCount; ?></span>
                            </a>
                            <?php
                          }
                        }
                      } else {
                        echo "<p class='text-muted'>No group available.</p>";
                      }
                      ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div> 
        </div>
      </div>
    </div>
    <!-- Share Modal -->
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn border-0" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>">
                <i class="bi bi-twitter"></i>
              </a>
                
              <!-- Line -->
              <a class="btn border-0" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                
              <!-- Email -->
              <a class="btn border-0" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                
              <!-- Reddit -->
              <a class="btn border-0" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                
              <!-- Instagram -->
              <a class="btn border-0" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                
              <!-- Facebook -->
              <a class="btn border-0" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <!-- Second Social Media Section -->
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn border-0" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
  
              <!-- Pinterest -->
              <a class="btn border-0" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
  
              <!-- LinkedIn -->
              <a class="btn border-0" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
  
              <!-- Messenger -->
              <a class="btn border-0" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
  
              <!-- Telegram -->
              <a class="btn border-0" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
  
              <!-- Snapchat -->
              <a class="btn border-0" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
            <!-- End -->
            <div class="input-group mb-2">
              <input type="text" id="urlInput1" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" class="form-control border-2 fw-bold" readonly>
              <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                <i class="bi bi-clipboard-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End of Share Modal -->
    <style>
      .hide-scrollbar::-webkit-scrollbar {
        display: none;
      }

      .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
      }

      .img-pointer {
        transition: opacity 0.3s ease-in-out;
      }
    
      .img-pointer:hover {
        opacity: 0.8;
        cursor: pointer;
      }
      
      .img-blur {
        filter: blur(2px);
      }
      
      .text-stroke-2 {
        -webkit-text-stroke: 3px;
      }
      
      .media-scrollerF {
        display: grid;
        gap: 3px; /* Updated gap value */
        grid-auto-flow: column;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
      }

      .snaps-inlineF {
        scroll-snap-type: inline mandatory;
        scroll-padding-inline: var(--_spacer, 1rem);
      }

      .snaps-inlineF > * {
        scroll-snap-align: start;
      }
  
      .scroll-container {
        scrollbar-width: none;  /* Firefox */
        -ms-overflow-style: none;  /* Internet Explorer 10+ */
        margin-left: auto;
        margin-right: auto;
      }
      
      .w-98 {
        width: 98%;
      }
      
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      .scroll-container::-webkit-scrollbar {
        width: 0;  /* Safari and Chrome */
        height: 0;
      }
      
      .scrollable-div {
        overflow: auto;
        scrollbar-width: thin;  /* For Firefox */
        -ms-overflow-style: none;  /* For Internet Explorer and Edge */
        scrollbar-color: transparent transparent;  /* For Chrome, Safari, and Opera */
      }

      .scrollable-div::-webkit-scrollbar {
        width: 0;
        background-color: transparent;
      }
      
      .scrollable-div::-webkit-scrollbar-thumb {
        background-color: transparent;
      }

      .image-containerA {
        width: 33.33%;
        flex-grow: 1;
      }
  
      .text-sm {
        font-size: 13px;
      }
      
      .display-f {
        font-size: 33px;
      } 

      .roow {
        display: flex;
        flex-wrap: wrap;
      }

      .cool-6 {
        width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
      }

      .caard {
        margin-bottom: 15px;
      }
      
      .rounded-r {
        border-radius: 15px;
      }

      .scrollable-title::-webkit-scrollbar {
        width: 0;
        height: 0;
      }
  
      @media (max-width: 767px) {
        .cool-6 {
          width: 100%;
          padding: 0;
        }
        
        .display-small-none {
          display: none;
        }
        
        .rounded-r {
          border-radius: 0;
        }

        .img-UF {
          width: 100%;
          height: 200px;
        }
      }
      
      @media (min-width: 768px) {
        .img-UF {
          width: 100%;
          height: 300px;
        }
      }
      
      .overlay {
        position: relative;
        display: flex;
        flex-direction: column; /* Change to column layout */
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Adjust background color and opacity */
        text-align: center;
        position: absolute;
        top: 0;
        left: 0;
      }

      .overlay i {
        font-size: 48px; /* Adjust icon size */
      }

      .overlay span {
        font-size: 18px; /* Adjust text size */
        margin-top: 8px; /* Add spacing between icon and text */
      }
    </style> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery library -->
    <div class="mt-5"></div>
    <script>
      function copyToClipboard() {
        var urlInput = document.getElementById('urlInput');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }

      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }
      
      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton = document.getElementById("toggleButton");
        const caretIcon = toggleButton.querySelector("i");
        const collapseExample = document.getElementById("collapseExample");

        toggleButton.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton1 = document.getElementById("toggleButton1");
        const caretIcon = toggleButton1.querySelector("i");
        const collapseExample = document.getElementById("collapseExample1");

        toggleButton1.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton2 = document.getElementById("toggleButton2");
        const caretIcon = toggleButton2.querySelector("i");
        const collapseExample = document.getElementById("collapseDataImage");

        toggleButton2.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton3 = document.getElementById("toggleButton3");
        const caretIcon = toggleButton3.querySelector("i");
        const collapseExample = document.getElementById("collapseDataImage1");

        toggleButton3.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
            toggleButton3.innerHTML = "<i class='bi bi-caret-down-fill'></i> <small>show more</small>";
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
            toggleButton3.innerHTML = "<i class='bi bi-caret-up-fill'></i> <small>show less</small>";
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          toggleButton3.innerHTML = "<i class='bi bi-caret-down-fill'></i> <small>show more</small>";
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          toggleButton3.innerHTML = "<i class='bi bi-caret-up-fill'></i> <small>show less</small>";
        });
      });
    </script>
    <script>
      var originalImageLink = document.getElementById("originalImageLink");
      var originalImage = document.getElementById("originalImage");
      var originalImageSrc = originalImageLink.getAttribute("data-original-src");

      originalImageLink.addEventListener("click", function(event) {
        event.preventDefault();
        originalImage.setAttribute("src", originalImageSrc);
      });

      var modal = document.getElementById("signinModal");
      modal.addEventListener("hidden.bs.modal", function() {
        originalImage.setAttribute("src", "");
      });

      modal.addEventListener("shown.bs.modal", function() {
        originalImage.setAttribute("src", originalImageSrc);
      });

      // Update the Load Original button functionality
      var loadOriginalBtn = document.getElementById("loadOriginalBtn");
      var showProgressBtn = document.getElementById("showProgressBtn");
      var thumbnailImage = document.querySelector("#originalImageLink img");

      loadOriginalBtn.addEventListener("click", function(event) {
        event.preventDefault();

        var originalSrc = originalImageLink.getAttribute("data-original-src");
        thumbnailImage.setAttribute("src", originalSrc);

        // Hide the "loadOriginalBtn" after it's clicked
        loadOriginalBtn.style.display = "none";

        // Show the "showProgressBtn" to indicate progress
        showProgressBtn.style.display = "block";

        var xhr = new XMLHttpRequest();
        xhr.open("GET", originalSrc, true);
        xhr.responseType = "blob";

        xhr.onprogress = function(event) {
          if (event.lengthComputable) {
            var percentLoaded = (event.loaded / event.total) * 100;
            showProgressBtn.textContent = "Loading Image: " + percentLoaded.toFixed(2) + "% (<?php echo $images_total_size; ?> MB)";
          }
        };

        xhr.onload = function() {
          var blob = xhr.response;
          var objectURL = URL.createObjectURL(blob);
          thumbnailImage.setAttribute("src", objectURL);
          // Hide the progress button when loading is complete
          showProgressBtn.style.display = "none";
        };

        xhr.send();
      });
    </script>
    <script>
      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    <script>
      function shareArtist(userId) {
        // Compose the share URL
        var shareUrl = '../artist.php?by=newest&id=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>