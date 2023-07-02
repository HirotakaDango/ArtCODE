<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

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

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
$stmt->bindParam(':filename', $filename);
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

// Handle following/unfollowing actions
if (isset($_POST['follow'])) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_email, following_email) VALUES (:follower_email, :following_email)');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user_email);
  $query->execute();
  $is_following = true;
  header("Location: image.php?artworkid={$image['id']}");
  exit;
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user_email);
  $query->execute();
  $is_following = false;
  header("Location: image.php?artworkid={$image['id']}");
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
  header("Location: image.php?artworkid={$image['id']}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: image.php?artworkid={$image['id']}");
  exit();
}

$url = "comment_preview.php?imageid=" . $image_id;

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
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="mt-2">
      <div class="container-fluid mb-2 d-flex d-md-none d-lg-none">
        <?php
          $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
          $stmt->bindParam(':id', $id);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="d-flex">
          <a class="text-decoration-none text-dark fw-bold rounded-pill" href="artist.php?id=<?= $user['id'] ?>">
            <?php if (!empty($user['pic'])): ?>
              <img class="object-fit-cover border border-1 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
            <?php else: ?>
              <img class="object-fit-cover border border-1 rounded-circle" src="icon/profile.svg" style="width: 32px; height: 32px;">
            <?php endif; ?>
            <?php echo (strlen($user['artist']) > 20) ? substr($user['artist'], 0, 20) . '...' : $user['artist']; ?> 
          </a>
        </div>
        <div class="ms-auto">
          <form method="post">
            <?php if ($is_following): ?>
              <button class="btn btn-sm btn-secondary rounded-pill fw-bold opacity-50" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
            <?php else: ?>
              <button class="btn btn-sm btn-secondary rounded-pill fw-bold opacity-50" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
            <?php endif; ?>
          </form>
        </div>
      </div>
      <div class="roow">
        <div class="cool-6">
          <div class="caard position-relative">
            <a href="images/<?php echo $image['filename']; ?>">
              <img class="img-fluid rounded-r" src="thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>" width="100%" height="auto">
            </a> 
            <?php if ($next_image): ?>
              <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute start-0 top-50 translate-middle-y ms-1"  onclick="location.href='image.php?artworkid=<?= $next_image['id'] ?>'">
                <i class="bi bi-arrow-left-circle-fill display-f"></i>
              </button>
            <?php endif; ?> 
            <?php if ($prev_image): ?>
              <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute end-0 top-50 translate-middle-y me-1"  onclick="location.href='image.php?artworkid=<?= $prev_image['id'] ?>'">
                <i class="bi bi-arrow-right-circle-fill display-f"></i>
              </button>
            <?php endif; ?> 
            <div class="position-absolute bottom-0 start-0 ms-2 mb-2">
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
                if ($is_favorited) {
              ?>
                <form action="image.php?artworkid=<?php echo $image['id']; ?>" method="POST">
                  <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-dark opacity-75 rounded-3 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
                </form>
              <?php } else { ?>
                <form action="image.php?artworkid=<?php echo $image['id']; ?>" method="POST">
                  <div class="btn-group">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-dark opacity-75 rounded-3 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
                  </div>
                </form>
              <?php } ?> 
            </div>
          </div>
        </div>
        <div class="cool-6">
          <div class="caard border-md-lg">
            <div class="container-fluid mb-2 d-none d-md-flex d-lg-flex">
              <?php
                $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <div class="d-flex">
                <a class="text-decoration-none text-dark fw-bold rounded-pill" href="artist.php?id=<?= $user['id'] ?>">
                 <?php if (!empty($user['pic'])): ?>
                   <img class="object-fit-cover border border-1 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                  <?php else: ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="icon/profile.svg" style="width: 32px; height: 32px;">
                  <?php endif; ?>
                  <?php echo (strlen($user['artist']) > 20) ? substr($user['artist'], 0, 20) . '...' : $user['artist']; ?> 
                </a>
              </div>
              <div class="ms-auto">
                <form method="post">
                  <?php if ($is_following): ?>
                    <button class="btn btn-sm btn-secondary rounded-pill fw-bold opacity-50" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
                  <?php else: ?>
                    <button class="btn btn-sm btn-secondary rounded-pill fw-bold opacity-50" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
                  <?php endif; ?>
                </form>
              </div>
            </div>
            <div class="me-2 ms-2 rounded fw-bold">
              <h5 class="text-dark fw-bold text-center mt-2"><?php echo $image['title']; ?></h5>
              <div style="word-break: break-word;" data-lazyload>
                <p class="text-secondary" style="word-break: break-word;">
                  <small>
                    <?php
                      $messageText = $image['imgdesc'];
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $formattedTextWithLineBreaks = nl2br($formattedText);
                      echo $formattedTextWithLineBreaks;
                    ?>
                  </small>
                </p>
              </div>
              <p class="text-secondary" style="word-wrap: break-word;">
                <a class="text-primary" href="<?php echo $image['link']; ?>">
                  <small>
                    <?php echo (strlen($image['link']) > 40) ? substr($image['link'], 0, 40) . '...' : $image['link']; ?>
                  </small>
               </a>
             </p>
              <p class="text-secondary fw-bold">
                <small>
                  <?php echo $image['date']; ?>
                </small>
              </p>
              <div class="container-fluid bg-body-secondary p-2 mt-2 mb-2 rounded-4 text-center align-items-center d-flex justify-content-center">
                <button class="btn border-0 disabled fw-semibold"><i class="bi bi-heart-fill"></i> <small><?php echo $fav_count; ?> likes</small></button>
                <button class="btn border-0 disabled fw-semibold"><i class="bi bi-eye-fill"></i> <small><?php echo $viewCount; ?> views</small></button>
              </div>
              <div class="btn-group w-100" role="group" aria-label="Basic example">
                <button class="btn btn-secondary opacity-50 fw-bold rounded-start-4" data-bs-toggle="modal" data-bs-target="#shareLink">
                  <i class="bi bi-share-fill"></i> <small>Share</small>
                </button>
                <a class="btn btn-primary fw-bold" href="images/<?php echo $image['filename']; ?>" download>
                  <i class="bi bi-cloud-arrow-down-fill"></i> <small>Download</small>
                </a>
                <button class="btn btn-primary dropdown-toggle fw-bold rounded-end-4" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-info-circle-fill"></i> <small>Info</small>
                </button>
                <ul class="dropdown-menu dropdown-menu-end bg-light border-0 shadow-sm p-0">
                  <?php
                    // Get the image information from the database
                    $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
                    $stmt->bindParam(':filename', $filename);
                    $stmt->execute();
                    $image = $stmt->fetch();

                    // Get image size in megabytes
                    $image_size = round(filesize('images/' . $image['filename']) / (1024 * 1024), 2);

                    // Get image dimensions
                    list($width, $height) = getimagesize('images/' . $image['filename']);
                  ?>
                  <div class="card border-0 m-0">
                    <div class="card-body p-3">
                      <ul class="list-unstyled m-0">
                        <li class="mb-2"><i class="bi bi-image"></i> image data size: <?php echo $image_size; ?> MB</li>
                        <li class="mb-2"><i class="bi bi-arrows-angle-expand text-stroke"></i> image dimensions: <?php echo $width; ?>x<?php echo $height; ?></li>
                        <li>
                          <a class="text-decoration-none text-primary" href="images/<?php echo $image['filename']; ?>">
                            <i class="bi bi-arrows-fullscreen text-stroke"></i> view original image
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </ul>
              </div>
              <a class="btn btn-primary rounded-4 w-100 mt-2 fw-bold" style="word-wrap: break-word;" href="artist.php?id=<?= $user['id'] ?>"><small><i class="bi bi-images"></i> view all <?php echo $user['artist']; ?>'s images</small></a>
              <?php include 'imguser.php'; ?>
              <a class="btn btn-primary rounded-4 w-100 fw-bold text-center" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                <i class="bi bi-caret-down-fill"></i> <small>more</small>
              </a> 
              <div class="collapse" id="collapseExample">
                <form class="mt-2" action="add_to_album.php" method="post">
                  <input class="form-control" type="hidden" name="image_id" value="<?= $filename ?>">
                  <select class="form-select fw-bold text-secondary rounded-4 mb-2" name="album_id">
                    <option class="form-control" value=""><small>add to album:</small></option>
                    <?php
                      // Connect to the SQLite database
                      $db = new SQLite3('database.sqlite');

                      // Get the email of the current user
                      $email = $_SESSION['email'];

                      // Retrieve the list of albums created by the current user
                      $stmt = $db->prepare('SELECT album_name, id FROM album WHERE email = :email');
                      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                      $results = $stmt->execute();

                      // Loop through each album and create an option in the dropdown list
                      while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                        $album_name = $row['album_name'];
                        $id = $row['id'];
                        echo '<option value="' . $id. '">' . htmlspecialchars($album_name). '</option>';
                      }

                      $db->close();
                    ?>
                  </select>
                  <button class="form-control bg-primary text-white fw-bold rounded-4" type="submit"><small>add to album</small></button>
                </form>
                <iframe class="mt-2 rounded" style="width: 100%; height: 300px;" src="<?php echo $url; ?>"></iframe>
                <a class="btn btn-primary w-100 rounded-pill fw-bold mt-2" href="comment.php?imageid=<?php echo $image['id']; ?>"><i class="bi bi-chat-left-text-fill"></i> <small>view all comments</small></a>
              </div>
              <p class="text-secondary mt-3"><i class="bi bi-tags-fill"></i> tags</p>
              <div class="tag-buttons container">
                <?php
                  $tags = explode(',', $image['tags']);
                  foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                ?>
                  <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                    class="btn btn-sm btn-secondary mb-1 rounded-3 fw-bold opacity-50">
                    <?php echo $tag; ?>
                  </a>
                    <?php
                    }
                  }
                ?>
              </div>
            </div>
          </div> 
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">share to:</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn btn-outline-dark" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>">
                <i class="bi bi-twitter"></i>
              </a>
                
              <!-- Line -->
              <a class="btn btn-outline-dark" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                
              <!-- Email -->
              <a class="btn btn-outline-dark" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                
              <!-- Reddit -->
              <a class="btn btn-outline-dark" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                
              <!-- Instagram -->
              <a class="btn btn-outline-dark" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                
              <!-- Facebook -->
              <a class="btn btn-outline-dark" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <button class="btn btn-secondary opacity-50 fw-bold w-100" onclick="sharePage()"><i class="bi bi-share-fill"></i> <small>share</small></button>
          </div>
        </div>
      </div>
    </div>
    <style>
      .font-sm {
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
      }
      

    </style> 
    <p class="text-secondary fw-bold ms-2 mt-2">Latest Images</p>
    <?php
      include 'latest.php';
    ?>
    <p class="text-secondary fw-bold ms-2 mt-4">Popular Images</p>
    <?php
      include 'most_popular.php';
    ?>
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
      document.addEventListener("DOMContentLoaded", function() {
        let lazyloadImages;
        if("IntersectionObserver" in window) {
          lazyloadImages = document.querySelectorAll(".lazy-load");
          let imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
              if(entry.isIntersecting) {
                let image = entry.target;
                image.src = image.dataset.src;
                image.classList.remove("lazy-load");
                imageObserver.unobserve(image);
              }
            });
          });
          lazyloadImages.forEach(function(image) {
            imageObserver.observe(image);
          });
        } else {
          let lazyloadThrottleTimeout;
          lazyloadImages = document.querySelectorAll(".lazy-load");

          function lazyload() {
            if(lazyloadThrottleTimeout) {
              clearTimeout(lazyloadThrottleTimeout);
            }
            lazyloadThrottleTimeout = setTimeout(function() {
              let scrollTop = window.pageYOffset;
              lazyloadImages.forEach(function(img) {
                if(img.offsetTop < (window.innerHeight + scrollTop)) {
                  img.src = img.dataset.src;
                  img.classList.remove('lazy-load');
                }
              });
              if(lazyloadImages.length == 0) {
                document.removeEventListener("scroll", lazyload);
                window.removeEventListener("resize", lazyload);
                window.removeEventListener("orientationChange", lazyload);
              }
            }, 20);
          }
          document.addEventListener("scroll", lazyload);
          window.addEventListener("resize", lazyload);
          window.addEventListener("orientationChange", lazyload);
        }
      })
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
