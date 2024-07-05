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
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container mt-3 mb-5" style="max-width: 750px;">
      <div class="d-flex">
        <h6 class="fw-bold mx-auto mb-4 pt-1">view <?php echo $image['title']; ?></h6>
      </div>
      <div class="row">
        <div class="col-md-6 order-md-2">
          <div class="mb-2 d-flex">
            <?php
              $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
              $stmt->bindParam(':id', $id);
              $stmt->execute();
              $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="d-flex me-auto">
              <a class="text-decoration-none text-dark fw-bold rounded-pill" href="#" data-bs-toggle="modal" data-bs-target="#userModal">
                <?php if (!empty($user['pic'])): ?>
                  <img class="object-fit-cover border border-1 rounded-circle" src="/<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                <?php else: ?>
                  <img class="object-fit-cover border border-1 rounded-circle" src="/icon/profile.svg" style="width: 32px; height: 32px;">
                <?php endif; ?>
                <?php echo (mb_strlen($user['artist']) > 10) ? mb_substr($user['artist'], 0, 10) . '...' : $user['artist']; ?>
                <small class="badge rounded-pill bg-dark">
                  <i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?>
                </small>
              </a>
            </div>
            <div class="ms-auto">
              <form method="post">
                <?php if ($is_following): ?>
                  <button class="btn btn-sm btn-outline-dark rounded-pill fw-bold opacity-75" type="submit" name="unfollow">
                    <i class="bi bi-person-dash-fill"></i> unfollow
                  </button>
                <?php else: ?>
                  <button class="btn btn-sm btn-outline-dark rounded-pill fw-bold opacity-75" type="submit" name="follow">
                    <i class="bi bi-person-fill-add"></i> follow
                  </button>
                <?php endif; ?>
              </form>
            </div>
          </div>
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
                  echo '</br>
                        <button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction1()" id="myBtn1">
                          <small>read more</small>
                        </button>';
                } else {
                  // If the text is within the character limit, just display it with line breaks.
                  echo nl2br($formattedText);
                }
              } else {
                echo "User description is empty.";
              }
            ?>
          </p>
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
        </div>
        <div class="col-md-6">
          <img class="w-100 rounded-4" src="/thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
          <div class="d-flex justify-content-center w-100 my-3">
            <div class="row g-4">
              <div class="col-3 d-flex justify-content-between">
                <button type="button" class="btn border-0" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-share-fill"></i>
                </button>
              </div>
              <div class="col-3 d-flex justify-content-between">
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
                  if ($is_favorited):
                ?>
                  <form action="?artworkid=<?php echo $image['id']; ?>" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="btn border-0" name="unfavorite">
                      <i class="bi bi-heart-fill"></i>
                    </button>
                  </form>
                <?php else: ?>
                  <form action="?artworkid=<?php echo $image['id']; ?>" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="btn border-0" name="favorite">
                      <i class="bi bi-heart"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
              <div class="col-3 d-flex justify-content-between">
                <button class="btn d-flex gap-2 border-0">
                  <i class="bi bi-bar-chart-line-fill"></i> <?= $image['view_count']; ?>
                </button>
              </div>
              <div class="col-3 d-flex justify-content-between">
                <a href="/image.php?artworkid=<?= $image['id']; ?>" class="btn border-0" target="_blank">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
              </div>
            </div>
          </div>
          <div class="card rounded-4 p-3 border-0 shadow collapse" id="collapseExample">
            <h1 class="modal-title fw-bold fs-5 mb-2" id="exampleModalLabel">share to:</h1>
            <?php
              $domain = $_SERVER['HTTP_HOST'];
              $imageId = $image['id'];
              $url = "http://$domain/image.php?artworkid=$imageId";
            ?>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo $url; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo $url; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
            <div class="input-group">
              <input type="text" id="urlInput1" value="<?php echo $url; ?>" class="form-control border-2 fw-bold" readonly>
              <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                <i class="bi bi-clipboard-fill"></i>
              </button>
            </div>
            <script>
              function copyToClipboard1() {
                var urlInput1 = document.getElementById('urlInput1');
                urlInput1.select();
                urlInput1.setSelectionRange(0, 99999); // For mobile devices
        
                document.execCommand('copy');
              }
            </script>
          </div>
        </div>
      </div>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>