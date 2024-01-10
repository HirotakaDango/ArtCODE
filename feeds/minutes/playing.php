<?php
require_once('auth.php');
require_once '../music/getID3/getid3/getid3.php';

try {
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
  exit();
}

$email = $_SESSION['email'];

// Get id from the query parameters
$id = $_GET['id'] ?? '';

// Fetch video record with user information using JOIN
$query = "SELECT videos.*, users.id as userid, users.pic, users.artist
          FROM videos
          JOIN users ON videos.email = users.email
          WHERE videos.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect to the home page if the record is not found
if (!$row) {
  header('Location: index.php');
  exit;
}

// Get the email and artist ID of the selected user
$user_email = $row['email'];
$artist_id = $row['userid'];

// Video file and thumbnail paths
$videoFile = $row['video'];
$thumbnail = $row['thumb'];

if (!file_exists($videoFile)) {
  echo "File not found: $videoFile";
  exit;
}

// Use getID3 to analyze the video file
$getID3 = new getID3();
$fileInfo = $getID3->analyze($videoFile);

// Function to format bytes
function formatBytes($bytes, $precision = 2)
{
  $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  $bytes = max($bytes, 0);
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow = min($pow, count($units) - 1);
  $bytes /= (1 << (10 * $pow));
  return round($bytes, $precision) . ' ' . $units[$pow];
}

// Extract information
$duration = !empty($fileInfo['playtime_string']) ? $fileInfo['playtime_string'] : 'Unknown';
$size = !empty($fileInfo['filesize']) ? formatBytes($fileInfo['filesize']) : 'Unknown';

// Fetch all music records for the specified artist
$queryAll = "SELECT videos.*, users.id as userid, users.pic, users.artist
             FROM videos
             JOIN users ON videos.email = users.email
             WHERE users.id = :artist_id
             ORDER BY videos.id ASC";
$stmtAll = $db->prepare($queryAll);
$stmtAll->bindParam(':artist_id', $artist_id, PDO::PARAM_INT);
$stmtAll->execute();
$allRows = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
$query->bindParam(':follower_email', $email);
$query->bindParam(':following_email', $user['email']);
$query->execute();
$is_following = $query->fetchColumn();

// Function to format numbers
function formatNumber($num) {
  if ($num >= 1000000) {
    return round($num / 1000000, 1) . 'm';
  } elseif ($num >= 100000) {
    return round($num / 1000) . 'k';
  } elseif ($num >= 10000) {
    return round($num / 1000, 1) . 'k';
  } elseif ($num >= 1000) {
    return round($num / 1000) . 'k';
  } else {
    return $num;
  }
}

// Get the number of followers for the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE following_email = :following_email');
$query->bindParam(':following_email', $row['email']);
$query->execute();
$num_followers = $query->fetchColumn();

// Get the number of people the selected user is following
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email');
$query->bindParam(':follower_email', $row['email']);
$query->execute();
$num_following = $query->fetchColumn(); 

// Check if the user is logged in and get their email
$email = '';
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];
}

// Get the email of the selected user
$user_email = $row['email'];

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
  header("Location: ?id={$row['id']}");
  exit;
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user_email);
  $query->execute();
  $is_following = false;
  header("Location: ?id={$row['id']}");
  exit;
}

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $video_id = $_POST['video_id'];

  // Check if the novel has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_videos WHERE email = :email AND video_id = :video_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':video_id', $video_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites_videos (email, video_id) VALUES (:email, :video_id)");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->bindParam(':video_id', $video_id);
    $stmt->execute();
  }

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: playing.php?id=' . $row['id']);
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $video_id = $_POST['video_id'];
  $stmt = $db->prepare("DELETE FROM favorites_videos WHERE email = :email AND video_id = :video_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':video_id', $video_id);
  $stmt->execute();

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: playing.php?id=' . $row['id']);
  exit();
}

// Increment the view count for the image
$stmt = $db->prepare("UPDATE videos SET view_count = view_count + 1 WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

// Query to fetch comments
$comments_query = "SELECT comments_minutes.*, users.artist, users.pic, users.id AS iduser, COUNT(reply_comments_minutes.id) AS reply_count FROM comments_minutes JOIN users ON comments_minutes.email = users.email LEFT JOIN reply_comments_minutes ON comments_minutes.id = reply_comments_minutes.comment_id WHERE comments_minutes.minute_id = :minute_id GROUP BY comments_minutes.id ORDER BY comments_minutes.id DESC LIMIT 25";
$stmt = $db->prepare($comments_query);
$stmt->bindParam(':minute_id', $id, PDO::PARAM_STR);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $row['title']; ?></title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
    <script>
      const player = document.getElementById('player');
      let currentTrackId = <?= $id ?>;
      let isSeeking = false;

      // Set metadata for the currently playing media
      const setMediaMetadata = () => {
        const coverPath = 'thumbnails/<?= htmlspecialchars($row['thumb']) ?>';
        console.log('Cover Path:', coverPath);

        navigator.mediaSession.metadata = new MediaMetadata({
          title: '<?= htmlspecialchars($row['title']) ?>',
          artist: '<?= htmlspecialchars($row['artist']) ?>',
          artwork: [
            { src: coverPath, sizes: '1600x1600', type: 'image/png' },
            // Add additional artwork sizes if needed
          ],
        });
      };
    </script>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid mt-2">
      <div class="row">
        <div class="col-md-9 pe-md-1">
          <video id="player" class="ratio ratio-16x9 bg-transparent" controls crossorigin playsinline data-poster="thumbnails/<?php echo $row['thumb']; ?>" id="player" autoplay>
            <source src="<?php echo $videoFile; ?>" type="video/mp4">
            Your browser does not support the video tag.
          </video>
          <h5 class="fw-bold"><?php echo $row['title']; ?></h5>
          <h6 class="small fw-medium text-nowrap ms-auto"><?php echo $row['view_count']; ?> views</h6>
          <div class="d-flex mt-4">
            <a class="text-decoration-none text-white" href="artist.php?id=<?php echo $row['userid']; ?>"><h6 class="small fw-medium text-nowrap"><img height="32" width="32" class="rounded-circle border border-dark-subtle border-2 object-fit-cover" src="../../<?php echo $row['pic']; ?>"> <?php echo (!is_null($row['artist']) && strlen($row['artist']) > 15) ? substr($row['artist'], 0, 15) . '...' : $row['artist']; ?></h6></a>
          </div>
          <div class="d-flex">
          <form class="w-100" method="post">
            <?php if ($is_following): ?>
              <button class="btn btn-outline-light btn-sm rounded-pill fw-medium" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button> <small class="ms-2 fw-medium"><?php echo $num_followers ?> followers</small>
            <?php else: ?>
              <button class="btn btn-outline-light btn-sm rounded-pill fw-medium" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button> <small class="ms-2 fw-medium"><?php echo $num_followers ?> followers</small>
            <?php endif; ?>
          </form>
          </div>
          <div class="d-flex mt-3">
            <div class="ms-auto">
              <div class="btn-group gap-2">
                <?php
                  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_videos WHERE email = :email AND video_id = :video_id");
                  $stmt->bindParam(':email', $_SESSION['email']);
                  $stmt->bindParam(':video_id', $row['id']);
                  $stmt->execute();
                  $is_favorited = $stmt->fetchColumn();

                  if ($is_favorited) {
                ?>
                  <form method="POST">
                    <input type="hidden" name="video_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="btn btn-outline-light rounded-pill btn-sm rounded-1 fw-bold" name="unfavorite">
                      <small><i class="bi bi-heart-fill"></i> unfavorite</small>
                    </button>
                  </form>
                <?php } else { ?>
                  <form method="POST">
                    <input type="hidden" name="video_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="btn btn-outline-light rounded-pill btn-sm rounded-1 fw-bold" name="favorite">
                      <small><i class="bi bi-heart"></i> favorite</small>
                    </button>
                  </form>
                <?php } ?>
                <a class="btn btn-outline-light rounded-pill btn-sm rounded-1 fw-bold" href="<?php echo $videoFile; ?>">
                  <small><i class="bi bi-cloud-arrow-down-fill"></i> dowload</small>
                </a>
                <a class="btn btn-outline-light rounded-pill btn-sm rounded-1 fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#shareLink"><small><i class="bi bi-share-fill"></i> share</small></a>
                <?php if ($user_email === $email): ?>
                  <a class="btn btn-outline-light rounded-pill btn-sm rounded-1 fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/minutes/edit.php?id=<?php echo $row['id']; ?>"><small><i class="bi bi-pencil-fill"></i> edit</small></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <a class="btn btn-outline-light bg-body-tertiary text-white border-0 rounded-4 w-100 fw-medium mt-3" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
            description
          </a>
          <div class="collapse bg-body-tertiary rounded-4 p-3 mt-2" id="collapseExample">
            <p class="fw-medium text-shadow small">
              <?php
                // Convert the date to the desired format
                $formattedDate = date('j F, Y', strtotime($row['date']));
                echo $formattedDate;
              ?>
            </p>
            <p style="white-space: break-spaces; overflow: hidden; margin-top: -75px;">
              <?php
                $bioText = isset($row['description']) ? $row['description'] : '';

                if (!empty($bioText)) {
                  $paragraphs = explode("\n", $bioText);

                  foreach ($paragraphs as $index => $paragraph) {
                    echo "<p style=\"white-space: break-spaces; overflow: hidden;\">";
                    echo preg_replace('/\bhttps?:\/\/\S+/i', '<a href="$0" target="_blank">$0</a>', strip_tags($paragraph));
                    echo "</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }
              ?>
            </p>
          </div>
          <div class="d-none d-md-block d-lg-block mt-2 bg-body-tertiary p-3 rounded-4">
            <h5 class="fw-bold text-center mb-4">comments section</h5>
            <?php foreach ($comments as $comment) : ?>
              <div class="card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
                <div class="d-flex align-items-center mb-2 position-relative">
                  <div class="position-absolute top-0 start-0 m-1">
                    <img class="rounded-circle" src="../../<?php echo !empty($comment['pic']) ? $comment['pic'] : "../../icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
                    <a class="text-white text-decoration-none fw-semibold" href="../../artist.php?id=<?php echo $comment['iduser']; ?>" target="_blank"><small>@<?php echo (mb_strlen($comment['artist']) > 15) ? mb_substr($comment['artist'], 0, 15) . '...' : $comment['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo $comment['created_at']; ?></small></small>
                  </div>
                </div>
                <div class="mt-5 container-fluid fw-medium">
                  <div>
                    <?php
                    // Function to get YouTube video ID
                    if (!function_exists('getYouTubeVideoId')) {
                      function getYouTubeVideoId($urlComment1A)
                      {
                        $videoId1A = '';
                        $pattern1A = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                        if (preg_match($pattern1A, $urlComment1A, $matches1A)) {
                          $videoId1A = $matches1A[1];
                        }
                        return $videoId1A;
                      }
                    }

                    $commentText1A = isset($comment['comment']) ? $comment['comment'] : '';

                    if (!empty($commentText1A)) {
                      $paragraphs1A = explode("\n", $commentText1A);

                      foreach ($paragraphs1A as $index1A => $paragraph1A) {
                        $messageTextWithoutTags1A = strip_tags($paragraph1A);
                        $pattern1A = '/\bhttps?:\/\/\S+/i';

                        $formattedText1A = preg_replace_callback($pattern1A, function ($matches1A) {
                          $urlComment1A = htmlspecialchars($matches1A[0]);

                          if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlComment1A)) {
                            return '<a href="' . $urlComment1A . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlComment1A . '" alt="Image"></a>';
                          } elseif (strpos($urlComment1A, 'youtube.com') !== false) {
                            $videoId1A = getYouTubeVideoId($urlComment1A);
                            if ($videoId1A) {
                              $thumbnailUrl1A = 'https://img.youtube.com/vi/' . $videoId1A . '/default.jpg';
                              return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId1A . '" frameborder="0" allowfullscreen></iframe></div>';
                            } else {
                              return '<a href="' . $urlComment1A . '">' . $urlComment1A . '</a>';
                            }
                          } else {
                            return '<a href="' . $urlComment1A . '">' . $urlComment1A . '</a>';
                          }
                        }, $messageTextWithoutTags1A);
        
                        echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText1A</p>";
                      }
                    } else {
                      echo "Sorry, no text...";
                    }
                    ?>
                  </div>
                </div>
                <div class="mx-2 me-auto">
                  <h6 class="fw-medium small"><small><?php echo $comment['reply_count']; ?> Replies</small></h6>
                </div>
                <div class="m-2 ms-auto">
                  <?php
                    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    $by = isset($_GET['by']) ? $_GET['by'] : 'newest';
                    $comment_id = isset($comment['id']) ? $comment['id'] : '';

                    $url = "reply_comment_minute.php?by=$by&minuteid=$id&comment_id=$comment_id&page=$page";
                  ?>
                  <a class="btn btn-sm fw-semibold" href="<?php echo $url; ?>">
                    <i class="bi bi-reply-fill"></i> Reply
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
            <a class="btn btn-secondary w-100 mt-3 fw-bold border border-3 rounded-4" href="comments.php?minute_id=<?php echo $row['id']; ?>">view all comments</a>
          </div>
        </div>
        <div class="col-md-3 order-md-2 ps-md-1">
          <div id="playList">
            <h6 class="text-start fw-bold mb-2 pt-3">latest from <?php echo $row['artist']; ?></h6>
            <div class="overflow-y-auto d-none d-md-block d-lg-block" id="autoHeightDiv" style="max-height: 100%;">
              <?php foreach ($allRows as $vid): ?>
                <div class="col mt-1">
                  <div class="card shadow-sm h-100 position-relative rounded-4 border-0">
                    <a class="shadow position-relative btn p-0 ratio ratio-16x9 border-0" href="playing.php?id=<?php echo $vid['id']; ?>">
                      <img class="w-100 object-fit-cover rounded-4 rounded-bottom-0" height="200" src="thumbnails/<?php echo $vid['thumb']; ?>">
                    </a>
                    <div class="p-2 bg-body-tertiary rounded-bottom-4">
                      <h5 class="card-text fw-bold text-shadow">
                        <?php echo (!is_null($vid['title']) && strlen($vid['title']) > 15) ? substr($vid['title'], 0, 15) . '...' : $vid['title']; ?>
                      </h5>
                      <h6 class="card-text small fw-bold text-shadow">
                        <small><a class="text-decoration-none text-white" href="artist.php?id=<?php echo $vid['userid']; ?>">
                          <img height="20" width="20" class="rounded-circle object-fit-cover" src="../../<?php echo $vid['pic']; ?>"> <?php echo (!is_null($vid['artist']) && strlen($vid['artist']) > 15) ? substr($vid['artist'], 0, 15) . '...' : $vid['artist']; ?>
                        </a></small>
                      </h6>
                      <div class="d-flex">
                        <small class="me-auto"><?php echo $vid['view_count']; ?> views</small>
                        <small class="ms-auto">
                          <?php
                            // Convert the date to the desired format
                            $formattedDateVid = date('j F, Y', strtotime($vid['date']));
                            echo $formattedDateVid;
                          ?>
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
              <br><br><br><br><br><br><br><br><br><br>
            </div>
            <div class="d-md-none d-lg-none">
              <?php foreach ($allRows as $vid): ?>
                <div class="col mt-1">
                  <div class="card shadow-sm h-100 position-relative rounded-4 border-0">
                    <a class="shadow position-relative btn p-0 ratio ratio-16x9 border-0" href="playing.php?id=<?php echo $vid['id']; ?>">
                      <img class="w-100 object-fit-cover rounded-4 rounded-bottom-0" height="200" src="thumbnails/<?php echo $vid['thumb']; ?>">
                    </a>
                    <div class="p-2 bg-body-tertiary rounded-bottom-4">
                      <h5 class="card-text fw-bold text-shadow">
                        <?php echo (!is_null($vid['title']) && strlen($vid['title']) > 15) ? substr($vid['title'], 0, 15) . '...' : $vid['title']; ?>
                      </h5>
                      <h6 class="card-text small fw-bold text-shadow">
                        <small><a class="text-decoration-none text-white" href="artist.php?id=<?php echo $vid['userid']; ?>">
                          <img height="20" width="20" class="rounded-circle object-fit-cover" src="../../<?php echo $vid['pic']; ?>"> <?php echo (!is_null($vid['artist']) && strlen($vid['artist']) > 15) ? substr($vid['artist'], 0, 15) . '...' : $vid['artist']; ?>
                        </a></small>
                      </h6>
                      <div class="d-flex">
                        <small class="me-auto"><?php echo $vid['view_count']; ?> views</small>
                        <small class="ms-auto">
                          <?php
                            // Convert the date to the desired format
                            $formattedDateVid = date('j F, Y', strtotime($vid['date']));
                            echo $formattedDateVid;
                          ?>
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="d-md-none d-lg-none mt-2 bg-body-tertiary p-3 rounded-4">
        <h5 class="fw-bold text-center mb-4">comments section</h5>
        <?php foreach ($comments as $comment) : ?>
          <div class="card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
            <div class="d-flex align-items-center mb-2 position-relative">
              <div class="position-absolute top-0 start-0 m-1">
                <img class="rounded-circle" src="../../<?php echo !empty($comment['pic']) ? $comment['pic'] : "../../icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
                <a class="text-white text-decoration-none fw-semibold" href="../../artist.php?id=<?php echo $comment['iduser']; ?>" target="_blank"><small>@<?php echo (mb_strlen($comment['artist']) > 15) ? mb_substr($comment['artist'], 0, 15) . '...' : $comment['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo $comment['created_at']; ?></small></small>
              </div>
            </div>
            <div class="mt-5 container-fluid fw-medium">
              <div>
                <?php
                // Function to get YouTube video ID
                if (!function_exists('getYouTubeVideoId')) {
                  function getYouTubeVideoId($urlComment1A)
                  {
                    $videoId1A = '';
                    $pattern1A = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                    if (preg_match($pattern1A, $urlComment1A, $matches1A)) {
                      $videoId1A = $matches1A[1];
                    }
                    return $videoId1A;
                  }
                }

                $commentText1A = isset($comment['comment']) ? $comment['comment'] : '';

                if (!empty($commentText1A)) {
                  $paragraphs1A = explode("\n", $commentText1A);

                  foreach ($paragraphs1A as $index1A => $paragraph1A) {
                    $messageTextWithoutTags1A = strip_tags($paragraph1A);
                    $pattern1A = '/\bhttps?:\/\/\S+/i';

                    $formattedText1A = preg_replace_callback($pattern1A, function ($matches1A) {
                      $urlComment1A = htmlspecialchars($matches1A[0]);

                      if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlComment1A)) {
                        return '<a href="' . $urlComment1A . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlComment1A . '" alt="Image"></a>';
                      } elseif (strpos($urlComment1A, 'youtube.com') !== false) {
                        $videoId1A = getYouTubeVideoId($urlComment1A);
                        if ($videoId1A) {
                          $thumbnailUrl1A = 'https://img.youtube.com/vi/' . $videoId1A . '/default.jpg';
                          return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId1A . '" frameborder="0" allowfullscreen></iframe></div>';
                        } else {
                          return '<a href="' . $urlComment1A . '">' . $urlComment1A . '</a>';
                        }
                      } else {
                        return '<a href="' . $urlComment1A . '">' . $urlComment1A . '</a>';
                      }
                    }, $messageTextWithoutTags1A);
        
                    echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText1A</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }
                ?>
              </div>
            </div>
            <div class="mx-2 me-auto">
              <h6 class="fw-medium small"><small><?php echo $comment['reply_count']; ?> Replies</small></h6>
            </div>
            <div class="m-2 ms-auto">
              <?php
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $by = isset($_GET['by']) ? $_GET['by'] : 'newest';
                $comment_id = isset($comment['id']) ? $comment['id'] : '';

                $url = "reply_comment_minute.php?by=$by&minuteid=$id&comment_id=$comment_id&page=$page";
              ?>
              <a class="btn btn-sm fw-semibold" href="<?php echo $url; ?>">
                <i class="bi bi-reply-fill"></i> Reply
              </a>
            </div>
          </div>
        <?php endforeach; ?>
        <a class="btn btn-secondary w-100 mt-3 fw-bold border border-3 rounded-4" href="comments.php?minute_id=<?php echo $row['id']; ?>">view all comments</a>
      </div>
    </div>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/minutes/playing.php?id=' . $row['id']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <style>
      .plyr__poster{
        background-color: #000;
        background-color: var(--plyr-video-background,var(--plyr-video-background,#000));
        background-position: 50% 50%;
        background-repeat: no-repeat;
        backgro und-size: contain;
        height:100%;
        left: 0;
        opacity: 0;
        position: absolute;
        top: 0;
        transition: opacity .2s ease;
        width: 100%;
        z-index: 1
      }
      
      .plyr{
        --shadow-color: 197deg 32% 65%;
        border-radius: 1rem;
        margin: 16px auto
      }

      /* For Webkit-based browsers */
      ::-webkit-scrollbar {
        width: 0;
        height: 0;
        border-radius: 10px;
      }

      ::-webkit-scrollbar-track {
        border-radius: 0;
      }

      ::-webkit-scrollbar-thumb {
        border-radius: 0;
      }
    </style>
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const player = new Plyr('#player', {
          controls: ['play', 'progress', 'current-time', 'mute', 'pip', 'volume', 'settings', 'fullscreen'],
        });
      });
    </script>
    <script>
      // Get a reference to the element
      const autoHeightDiv = document.getElementById('autoHeightDiv');
    
      // Set the element's height to match the screen's height
      autoHeightDiv.style.height = window.innerHeight + 'px';
    
      // Listen for window resize events to update the height dynamically
      window.addEventListener('resize', () => {
        autoHeightDiv.style.height = window.innerHeight + 'px';
      });
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>