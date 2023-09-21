<?php
require_once('../../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../../database.sqlite');

// Get the filename from the query string
$filename = $_GET['artworkid'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename ");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

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

// Handle following/unfollowing actions
if (isset($_POST['follow'])) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_email, following_email) VALUES (:follower_email, :following_email)');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user_email);
  $query->execute();
  $is_following = true;
  header("Location: ?artworkid={$image['id']}");
  exit;
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user_email);
  $query->execute();
  $is_following = false;
  header("Location: ?artworkid={$image['id']}");
  exit;
}

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: ?artworkid={$image['id']}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: ?artworkid={$image['id']}");
  exit();
}

// Increment the view count for the image
$stmt = $db->prepare("UPDATE images SET view_count = view_count + 1 WHERE id = :filename");
$stmt->bindParam(':filename', $filename);
$stmt->execute();

// Get the updated image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

// Retrieve the updated view count from the image information
$viewCount = $image['view_count'];

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?> 

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div id="content">
      <?php if (empty($image['filename'])) : ?>
        <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
          <h1 class="fw-bold">Image not found</h1>
          <div class="d-flex justify-content-center">
            <a class="btn btn-primary fw-bold" href="/">back to home</a>
          </div>
        </div>
      <?php else : ?>
        <?php if (empty($child_images)) : ?>
          <div class="w-100 h-100">
            <div class="d-flex justify-content-center vh-100">
              <img src="../../images/<?php echo $image['filename']; ?>" class="mb-1 object-fit-contain" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
            </div>
          </div>
        <?php else : ?>
          <img src="../../images/<?php echo $image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
        <?php endif; ?>        
        <div id="scrollButton">
          <div class="fixed-top" >
            <div class="py-3 container-fluid mb-2 d-flex" style="background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));">
              <?php
                $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <div class="d-flex d-md-none d-lg-none">
                <a class="text-decoration-none text-light fw-bold rounded-pill" href="../../artist.php?id=<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#userModal">
                  <?php if (!empty($user['pic'])): ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                  <?php else: ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../icon/profile.svg" style="width: 32px; height: 32px;">
                  <?php endif; ?>
                  <?php echo (mb_strlen($user['artist']) > 10) ? mb_substr($user['artist'], 0, 10) . '...' : $user['artist']; ?> <small class="badge rounded-pill bg-primary"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
                </a>
              </div>
              <div class="d-flex d-none d-md-block d-lg-block">
                <a class="text-decoration-none text-light fw-bold rounded-pill" href="../../artist.php?id=<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#userModal">
                  <?php if (!empty($user['pic'])): ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                  <?php else: ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../icon/profile.svg" style="width: 32px; height: 32px;">
                  <?php endif; ?>
                  <?php echo $user['artist']; ?> <small class="badge rounded-pill bg-primary"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
                </a>
              </div>
              <div class="ms-auto">
                <form method="post">
                  <?php if ($is_following): ?>
                    <button class="btn btn-sm btn-outline-light rounded-pill fw-bold" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
                  <?php else: ?>
                    <button class="btn btn-sm btn-outline-light rounded-pill fw-bold" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          </div>
          <div class="w-100 fixed-bottom" style="background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));">
            <div class="container py-3 d-flex justify-content-center">
              <div class="btn-group">
                <?php if ($next_image): ?>
                  <button class="text-start fw-bold btn rounded" id="option5" onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
                    <i class="fs-4 bi bi-chevron-left text-stroke-3"></i>
                  </button>
                <?php endif; ?> 
                <a class="btn rounded" href="../carousel/?artworkid=<?php echo $image['id']; ?>"><i class="fs-4 bi bi-distribute-horizontal"></i></a>
                <button class="btn rounded" id="option1"><i class="fs-4 bi bi-fullscreen text-stroke-2"></i></button>
                <button class="btn rounded" id="option2"><i class="fs-4 bi bi-file-image"></i></button>
                <?php
                  $image_id = $image['id'];
                  $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
                  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
                  $stmt->bindParam(':email', $email);
                  $stmt->bindParam(':image_id', $image_id);
                  $stmt->execute();
                  $is_favorited = $stmt->fetchColumn();
                  if ($is_favorited) : ?>
                  <form class="w-100" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="text-start btn" name="unfavorite" id="unfavoriteButton">
                      <i class="fs-4 bi bi-heart-fill"></i>
                    </button>
                  </form>
                <?php else : ?>
                  <form class="w-100" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="text-start btn" name="favorite" id="favoriteButton">
                      <i class="fs-4 bi bi-heart text-stroke"></i>
                    </button>
                  </form>
                <?php endif; ?>
                <button class="text-start fw-bold btn rounded" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fs-4 bi bi-info-circle-fill"></i></button>
                <a class="text-start fw-bold btn rounded" id="option3" href="../../image.php?artworkid=<?php echo $image['id']; ?>"><i class="fs-4 bi bi-arrow-left-circle-fill"></i></a>
                <?php if ($prev_image): ?>
                  <button class="text-start fw-bold btn rounded" id="option6" onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
                    <i class="fs-4 bi bi-chevron-right text-stroke-3"></i>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <?php foreach ($child_images as $child_image) : ?>
        <?php if (empty($child_image['filename'])) : ?>
          <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
            <h1 class="fw-bold">Image not found</h1>
            <div class="d-flex justify-content-center">
              <a class="btn btn-primary fw-bold" href="/">back to home</a>
            </div>
          </div>
        <?php else : ?>
          <img src="../../images/<?php echo $child_image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
        <?php endif; ?>
      <?php endforeach; ?>
      <?php
        // Function to calculate the size of an image in MB
        function getImageSizeInMB($filename) {
          return round(filesize('../../images/' . $filename) / (1024 * 1024), 2);
        }

        // Get the total size of images from 'images' table
        $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
        $stmt->bindParam(':filename', $filename);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get the total size of images from 'image_child' table
        $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
        $stmt->bindParam(':filename', $filename);
        $stmt->execute();
        $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-body">
            <h6 class="fw-bold text-center"><?php echo $image['title']; ?></h6>
            <p class="text-start fw-bold mt-4" style="word-wrap: break-word;">
              <?php
                if (!empty($image['imgdesc'])) {
                  $messageText = $image['imgdesc'];
                  $messageTextWithoutTags = strip_tags($messageText);
                  $pattern = '/\bhttps?:\/\/\S+/i';

                  $formattedText = preg_replace_callback($pattern, function ($matches) {
                    $url = htmlspecialchars($matches[0]);
                    return '<a href="' . $url . '">' . $url . '</a>';
                  }, $messageTextWithoutTags);

                  $formattedTextWithLineBreaks = nl2br($formattedText);
                    echo $formattedTextWithLineBreaks;
                  } else {
                    echo "Image description is empty.";
                }
              ?>
            </p>
            <button class="btn btn-outline-light fw-bold w-100 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDataImage" aria-expanded="false"aria-controls="collapseExample">
              <i class="bi bi-caret-down-fill"></i> <small>more</small>
            </button> 
            <div class="collapse mt-2 fw-bold" id="collapseDataImage">
              <?php
                // Get the total size of images from 'images' table
                $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
                $stmt->bindParam(':filename', $filename);
                $stmt->execute();
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get the total size of images from 'image_child' table
                $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
                $stmt->bindParam(':filename', $filename);
                $stmt->execute();
                $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
              
                // Function to format the date
                function formatDate($date) {
                  return date('Y/F/l jS') ;
                }
              ?>
              <?php foreach ($images as $index => $image) { ?>
                <div class="text-white mt-3 mb-3 rounded border border-1 border-light">
                  <ul class="list-unstyled m-1">
                    <li class="mb-2"><i class="bi bi-file-earmark"></i> Filename: <?php echo $image['filename']; ?></li>
                    <li class="mb-2"><i class="bi bi-file-earmark-bar-graph"></i> Image data size: <?php echo getImageSizeInMB($image['filename']); ?> MB</li>
                    <li class="mb-2"><i class="bi bi-arrows-angle-expand text-stroke"></i> Image dimensions: <?php list($width, $height) = getimagesize('../../images/' . $image['filename']); echo $width . 'x' . $height; ?></li>
                    <li class="mb-2"><i class="bi bi-file-earmark-text"></i> MIME type: <?php echo mime_content_type('../../images/' . $image['filename']); ?></li>
                    <li class="mb-2"><i class="bi bi-calendar"></i> Image date: <?php echo date('Y/m/d', strtotime($image['date'])); ?></li>
                    <li class="mb-2">
                      <a class="text-decoration-none text-primary" href="../../images/<?php echo $image['filename']; ?>">
                        <i class="bi bi-arrows-fullscreen text-stroke"></i> View original image
                      </a>
                    </li>
                    <li>
                      <a class="text-decoration-none text-primary" href="../../images/<?php echo $image['filename']; ?>" download>
                        <i class="bi bi-cloud-arrow-down-fill"></i> Download original image
                      </a>
                    </li>
                  </ul>
                </div>
              <?php } ?>
              <?php foreach ($image_childs as $index => $image_child) { ?>
                <div class="text-white mt-3 mb-3 rounded border border-1 border-light">
                  <ul class="list-unstyled m-1">
                    <li class="mb-2"><i class="bi bi-file-earmark"></i> Filename: <?php echo $image_child['filename']; ?></li>
                    <li class="mb-2"><i class="bi bi-file-earmark-bar-graph"></i> Image data size: <?php echo getImageSizeInMB($image_child['filename']); ?> MB</li>
                    <li class="mb-2"><i class="bi bi-arrows-angle-expand text-stroke"></i> Image dimensions: <?php list($width, $height) = getimagesize('../../images/' . $image_child['filename']); echo $width . 'x' . $height; ?></li>
                    <li class="mb-2"><i class="bi bi-file-earmark-text"></i> MIME type: <?php echo mime_content_type('../../images/' . $image_child['filename']); ?></li>
                    <li class="mb-2"><i class="bi bi-calendar"></i> Image date: <?php echo date('Y/m/d', strtotime($image['date'])); ?></li>
                    <li class="mb-2">
                      <a class="text-decoration-none text-primary" href="../../images/<?php echo $image_child['filename']; ?>">
                        <i class="bi bi-arrows-fullscreen text-stroke"></i> View original image
                      </a>
                    </li>
                    <li>
                      <a class="text-decoration-none text-primary" href="../../images/<?php echo $image_child['filename']; ?>" download>
                        <i class="bi bi-cloud-arrow-down-fill"></i> Download original image
                      </a>
                    </li>
                  </ul>
                </div>
              <?php } ?>
              <div class="text-white mt-3 mb-3">
                <ul class="list-unstyled m-0">
                  <li class="mb-2"><i class="bi bi-file-earmark-plus"></i> Total size of all images: <?php echo $total_size; ?> MB</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <style>
      #scrollButton {
        transition: opacity 0.5s ease-in-out; /* Add smooth opacity transition */
        opacity: 1; /* Initially visible */
      }
        
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      .text-stroke-2 {
        -webkit-text-stroke: 2px;
      }
      
      .text-stroke-3 {
        -webkit-text-stroke: 3px;
      }
    </style>
    <script>
      var lastScrollPosition = 0;

      window.addEventListener("scroll", function () {
        var currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        var scrollButton = document.getElementById("scrollButton");

        if (currentScrollPosition > lastScrollPosition) {
          scrollButton.style.opacity = "0"; // Scroll down, fade out button
        } else {
          scrollButton.style.opacity = "1"; // Scroll up, fade in button
        }

        lastScrollPosition = currentScrollPosition;
      });

      const option1Button = document.getElementById('option1');
      const option2Button = document.getElementById('option2');
      const contentDiv = document.getElementById('content');

      option1Button.style.display = 'none'; // Hide option1 by default

      option1Button.addEventListener('click', function () {
        contentDiv.classList.remove('container', 'my-5');
        option1Button.style.display = 'none'; // Hide option1
        option2Button.style.display = 'block'; // Show option2
      });

      option2Button.addEventListener('click', function () {
        contentDiv.classList.add('container', 'my-5');
        option1Button.style.display = 'block'; // Show option1
        option2Button.style.display = 'none'; // Hide option2
      });
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>