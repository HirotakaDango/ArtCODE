<?php
require_once('auth.php');

// Connect to the database
$db = new SQLite3('database.sqlite');

// Get the email of the current user
$email = $_SESSION['email'];

// Get the emails of the users that the current user is following
$following_query = $db->prepare("SELECT following_email FROM following WHERE follower_email = :email");
$following_query->bindValue(':email', $email, SQLITE3_TEXT);
$following_result = $following_query->execute();

// Create an array to store the emails of the users that the current user is following
$following_emails = array();
while ($row = $following_result->fetchArray(SQLITE3_ASSOC)) {
  $following_emails[] = $row['following_email'];
}

// Join the users table and the status table to get the messages from the users that the current user is following
$status_query = $db->prepare("SELECT users.email, users.artist, users.pic, users.id AS userid, status.message, status.date, status.id FROM users JOIN status ON users.email = status.email WHERE users.email IN (".implode(',', array_fill(0, count($following_emails), '?')).") ORDER BY status.id DESC");
foreach ($following_emails as $i => $following_email) {
  $status_query->bindValue($i+1, $following_email, SQLITE3_TEXT);
}
$status_result = $status_query->execute();

// Create an array to store the messages
$messages = array();
while ($row = $status_result->fetchArray(SQLITE3_ASSOC)) {
  $messages[] = $row;
}

// Handle the delete button
if(isset($_POST['delete'])) {
  $id = $_POST['id'];
  $delete_query = $db->prepare("DELETE FROM status WHERE id = :id AND email = :email");
  $delete_query->bindValue(':id', $id, SQLITE3_INTEGER);
  $delete_query->bindValue(':email', $email, SQLITE3_TEXT);
  $delete_query->execute();

  // Refresh the page after deleting the message
  header('Location: ' . $_SERVER['PHP_SELF']);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Status</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container mt-2">
    <a href="status_send.php" type="button" class="btn btn-primary w-100 fw-bold mb-3"><i class="bi bi-send-fill"></i> write something</a>
    <div class="messages">
      <?php foreach ($messages as $message): ?>
        <div class="card mb-1 rounded-4 shadow border-0">
          <div class="card-header border-0 fw-bold">
            <img class="rounded-circle object-fit-cover" src="<?php echo !empty($message['pic']) ? $message['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
            <a class="text-dark text-decoration-none fw-medium link-body-emphasis" href="artist.php?id=<?php echo $message['userid'];?>" target="_blank"><small>@<?php echo (mb_strlen($message['artist']) > 15) ? mb_substr($message['artist'], 0, 15) . '...' : $message['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo date('Y/m/d', strtotime($message['date'])); ?></small></small>
          </div>
          <div class="card-body position-relative">
            <div class="fw-medium">
              <?php
                if (!function_exists('getYouTubeVideoId')) {
                  function getYouTubeVideoId($urlComment)
                  {
                    $videoId = '';
                    $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                    if (preg_match($pattern, $urlComment, $matches)) {
                      $videoId = $matches[1];
                    }
                    return $videoId;
                  }
                }

                $commentText = isset($message['message']) ? $message['message'] : '';

                if (!empty($commentText)) {
                  $paragraphs = explode("\n", $commentText);

                  foreach ($paragraphs as $index => $paragraph) {
                    $messageTextWithoutTags = strip_tags($paragraph);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $urlComment = htmlspecialchars($matches[0]);

                      if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlComment)) {
                        return '<a href="' . $urlComment . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlComment . '" alt="Image"></a>';
                      } elseif (strpos($urlComment, 'youtube.com') !== false) {
                        $videoId = getYouTubeVideoId($urlComment);
                        if ($videoId) {
                          $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/default.jpg';
                          return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                        } else {
                          return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                        }
                      } else {
                        return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                      }
                    }, $messageTextWithoutTags);
                
                    echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }
              ?>
            </div>
            <?php if ($message['email'] == $email): ?>
              <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                <button type="submit" name="delete" class="btn btn-outline-dark border-0 btn-sm position-absolute top-0 end-0 m-2" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-5"></div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>