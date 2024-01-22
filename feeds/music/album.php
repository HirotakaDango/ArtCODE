<?php
require_once('auth.php');
$db = new PDO('sqlite:../../database.sqlite');
$userEmail = $_SESSION['email'];

// Get the album parameter from the URL
$album = isset($_GET['album']) ? $_GET['album'] : null;

// Fetch music records with user information and filter by album if provided
$query = "SELECT music.id, music.file, music.email as music_email, music.cover, music.album, music.title, users.id AS userid, users.artist 
          FROM music 
          LEFT JOIN users ON music.email = users.email";

// If album parameter is provided, filter by album
if (!empty($album)) {
  $query .= " WHERE music.album = :album";
}

// Order the results by track ID in ascending order and then by title in ascending order
$query .= " ORDER BY music.id ASC, music.title ASC";

$stmt = $db->prepare($query);

// Bind album parameter if provided
if (!empty($album)) {
  $stmt->bindValue(':album', $album, PDO::PARAM_STR);
}

$result = $stmt->execute();

// Fetch all rows for displaying in the album
$rows = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $rows[] = $row;
}

// Fetch all rows for shuffling
$stmt->execute();  // No need to assign to $result again
$rowsForShuffle = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $rowsForShuffle[] = $row;
}

// Check if there are any rows
if (!empty($rows)) {
  // Get the first row
  $firstRow = $rows[0];

  // Extract the user's email from the music record
  $userEmail = $firstRow['music_email'];

  // Extract the image file path from the first row
  $imagePath = $firstRow['cover'];

  // Extract the user ID from the first row
  $userid = $firstRow['userid'];

  // Extract the album from the first row
  $album = $firstRow['album'];

  // Extract the id from the first row
  $id = $firstRow['id'];
}

// Calculate the total number of tracks in the same album
$countQuery = "SELECT COUNT(*) as count FROM music WHERE album = :album";
$countStmt = $db->prepare($countQuery);
$countStmt->bindValue(':album', $album, PDO::PARAM_STR);
$countStmt->execute();
$albumTrackCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

// Calculate the total number of tracks by the user from all albums
$countQuery = "SELECT COUNT(*) as count FROM music WHERE email = :userEmail";
$countStmt = $db->prepare($countQuery);
$countStmt->bindValue(':userEmail', $userEmail, PDO::PARAM_STR);
$countStmt->execute();
$userTotalTracksCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

// Fetch user details using the user's email from the music record
$queryUserDetails = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, twitter, pixiv, other, region, joined, born, email, message_1, message_2, message_3, message_4 FROM users WHERE email = :email');
$queryUserDetails->bindParam(':email', $userEmail, PDO::PARAM_STR);
$queryUserDetails->execute();
$user = $queryUserDetails->fetch(PDO::FETCH_ASSOC);

$artist = $user['artist'];
$desc = $user['desc'];
$pic = $user['pic'];
$bgpic = $user['bgpic'];
$twitter = $user['twitter'];
$pixiv = $user['pixiv'];
$other = $user['other'];
$region = $user['region'];
$joined = $user['joined'];
$born = $user['born'];
$message_1 = $user['message_1'];
$message_2 = $user['message_2'];
$message_3 = $user['message_3'];
$message_4 = $user['message_4'];

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
$query->bindParam(':follower_email', $userEmail);
$query->bindParam(':following_email', $user['email']);
$query->execute();
$is_following = $query->fetchColumn();

// Get the number of followers for the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE following_email = :following_email');
$query->bindParam(':following_email', $user['email']);
$query->execute();
$num_followers = $query->fetchColumn();

// Get the number of people the selected user is following
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email');
$query->bindParam(':follower_email', $user['email']);
$query->execute();
$num_following = $query->fetchColumn(); 

// Handle following/unfollowing actions
if (isset($_POST['follow'])) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_email, following_email) VALUES (:follower_email, :following_email)');
  $query->bindParam(':follower_email', $userEmail);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = true;

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $userEmail);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = false;

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></title>
    <link rel="icon" type="image/png" href="covers/<?php echo $imagePath; ?>">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid mt-3 mt-md-4">
      <div class="row">
        <div class="col-md-3 pe-md-1">
          <div class="bg-body-tertiary rounded-4 p-3 h-100 w-100 d-none d-md-block d-lg-block">
            <div class="d-flex align-content-center justify-content-center">
              <img class="img-thumbnail border-0 shadow text-center rounded-circle mt-3 object-fit-cover" src="../<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
            </div>
            <h5 class="fw-bold mt-3 text-center"><?php echo $artist; ?></h5>
            <div class="d-flex align-content-center justify-content-center">
              <a class="btn btn-sm rounded-3 btn-outline-light border-0 fw-bold" href="artist.php?id=<?php echo $userid; ?>">view artist <i class="bi bi-box-arrow-up-right text-stroke"></i></a>
            </div>
            <div class="mt-3">
              <form method="post">
                <?php if ($is_following): ?>
                  <span class="me-3"><button class="btn btn-outline-light rounded-pill fw-medium w-100" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button></span>
                <?php else: ?>
                  <span class="me-3"><button class="btn btn-outline-light rounded-pill fw-medium w-100" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button></span>
                <?php endif; ?>
              </form>
              <div class="btn-group w-100 mt-2">
                <a class="btn border-0 fw-medium text-center w-50" href="../../follower.php?id=<?php echo $userid; ?>"> <?php echo $num_followers ?> <small>Followers</small></a>
                <a class="btn border-0 fw-medium text-center w-50" href="../../following.php?id=<?php echo $userid; ?>"> <?php echo $num_following ?> <small>Following</small></a>
              </div>
              <div class="btn-group w-100 mt-2">
                <a class="btn border-0 fw-medium text-center w-50" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $userTotalTracksCount; ?> <small>songs</small></a>
                <button class="btn border-0 fw-medium text-center w-50" onclick="shareArtist(<?php echo $userid; ?>)"><small>Shares</small></button>
              </div>
              <p class="mt-4 ms-3 fw-medium text-break">
                <small>
                  <?php
                    if (!empty($desc)) {
                      $messageText = $desc;
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $charLimit = 100; // Set your character limit

                      if (strlen($formattedText) > $charLimit) {
                        $limitedText = substr($formattedText, 0, $charLimit);
                        echo '<span id="limitedText">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                        echo '<span id="more" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                        echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction()" id="myBtn">read more</button>';
                      } else {
                        // If the text is within the character limit, just display it with line breaks.
                        echo nl2br($formattedText);
                      }
                    } else {
                      echo "User description is empty.";
                    }
                  ?>
                </small>
              </p>

              <script>
                function myFunction() {
                  var dots = document.getElementById("limitedText");
                  var moreText = document.getElementById("more");
                  var btnText = document.getElementById("myBtn");

                  if (moreText.style.display === "none") {
                    dots.style.display = "none";
                    moreText.style.display = "inline";
                    btnText.innerHTML = "read less";
                  } else {
                    dots.style.display = "inline";
                    moreText.style.display = "none";
                    btnText.innerHTML = "read more";
                  }
                }
              </script>
            </div>
          </div>
        </div>
        <div class="col-md-9">
          <div class="p-0">
            <div class="row mb-3 ps-md-2">
              <div class="col-md-3 order-md-1 mb-3 p-md-0 pe-md-4 p-4">
                <div class="position-relative">
                  <div class="ratio ratio-1x1">
                    <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="covers/<?php echo $imagePath; ?>" class="object-fit-cover h-100 w-100 rounded shadow" alt="..."></a>
                  </div>
                  <button type="button" class="btn btn-dark opacity-75 position-absolute bottom-0 end-0 m-2 fw-medium" data-bs-toggle="modal" data-bs-target="#shareLink"><small><i class="bi bi-share-fill"></i> share</small></button>
                </div>
              </div>
              <div class="col-md-7 order-md-2">
                <h2 class="featurette-heading fw-normal fw-bold"><?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></span></h2>
                <p class="fw-medium mt-3 d-none d-md-block d-lg-block">Artist: <a class="text-decoration-none text-white" href="artist.php?id=<?php echo $userid; ?>"><?php echo isset($rows[0]['artist']) ? htmlentities($rows[0]['artist']) : ''; ?></a></p>
                <p class="fw-medium mt-3 d-md-none d-lg-none">Artist: <a class="text-decoration-none text-white" data-bs-toggle="modal" data-bs-target="#profileModal"><?php echo isset($rows[0]['artist']) ? htmlentities($rows[0]['artist']) : ''; ?></a></p>
                <p class="fw-medium mt-3">Total Tracks in Album: <?php echo $albumTrackCount; ?> songs</p>
                <div class="btn-group gap-2">
                  <a class="btn btn-outline-light fw-medium rounded-pill" href="play.php?album=<?php echo $album; ?>&id=<?php echo $id; ?>"><i class="bi bi-play-circle"></i> play the first song</a>
                  <a class="btn btn-outline-light fw-medium rounded-pill" href="play.php?album=<?php echo $album; ?>&id=<?php echo $shuffledId; ?>"><i class="bi bi-shuffle"></i> shuffle</a>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="mt-3">
            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
              <?php foreach ($rows as $row): ?>
                <?php include('music_info.php'); ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel"><?php echo $artist; ?>'s Profile</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex align-content-center justify-content-center">
              <img class="img-thumbnail border-0 shadow text-center rounded-circle mt-3 object-fit-cover" src="../<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
            </div>
            <h5 class="fw-bold mt-3 text-center"><?php echo $artist; ?></h5>
            <div class="d-flex align-content-center justify-content-center">
              <a class="btn btn-sm rounded-3 btn-outline-light border-0 fw-bold" href="artist.php?id=<?php echo $userid; ?>">view artist <i class="bi bi-box-arrow-up-right text-stroke"></i></a>
            </div>
            <div class="mt-3">
              <form method="post">
                <?php if ($is_following): ?>
                  <span class="me-3"><button class="btn btn-outline-light rounded-pill fw-medium w-100" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button></span>
                <?php else: ?>
                  <span class="me-3"><button class="btn btn-outline-light rounded-pill fw-medium w-100" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button></span>
                <?php endif; ?>
              </form>
              <div class="btn-group w-100 mt-2">
                <a class="btn border-0 fw-medium text-center w-50" href="../../follower.php?id=<?php echo $userid; ?>"> <?php echo $num_followers ?> <small>Followers</small></a>
                <a class="btn border-0 fw-medium text-center w-50" href="../../following.php?id=<?php echo $userid; ?>"> <?php echo $num_following ?> <small>Following</small></a>
              </div>
              <div class="btn-group w-100 mt-2">
                <a class="btn border-0 fw-medium text-center w-50" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $userTotalTracksCount; ?> <small>songs</small></a>
                <button class="btn border-0 fw-medium text-center w-50" onclick="shareArtist(<?php echo $userid; ?>)"><small>Shares</small></button>
              </div>
              <p class="mt-4 ms-3 fw-medium text-break">
                <small>
                  <?php
                    if (!empty($desc)) {
                      $messageText = $desc;
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $charLimit = 100; // Set your character limit

                      if (strlen($formattedText) > $charLimit) {
                        $limitedText = substr($formattedText, 0, $charLimit);
                        echo '<span id="limitedText">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                        echo '<span id="more" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                        echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction()" id="myBtn">read more</button>';
                      } else {
                        // If the text is within the character limit, just display it with line breaks.
                        echo nl2br($formattedText);
                      }
                    } else {
                      echo "User description is empty.";
                    }
                  ?>
                </small>
              </p>

              <script>
                function myFunction() {
                  var dots = document.getElementById("limitedText");
                  var moreText = document.getElementById("more");
                  var btnText = document.getElementById("myBtn");

                  if (moreText.style.display === "none") {
                    dots.style.display = "none";
                    moreText.style.display = "inline";
                    btnText.innerHTML = "read less";
                  } else {
                    dots.style.display = "inline";
                    moreText.style.display = "none";
                    btnText.innerHTML = "read more";
                  }
                }
              </script>
            </div>
          </div>
        </div>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="covers/<?php echo $imagePath; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="covers/<?php echo $imagePath; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <script>
      function shareArtist(userId) {
        // Compose the share URL
        var shareUrl = 'artist.php?id=' + userId;

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
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
