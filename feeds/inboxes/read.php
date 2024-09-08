<?php
require_once('../../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../../database.sqlite');

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: index.php');
  exit;
}

$email = $_SESSION['email'];

$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;

if ($id) {
  // Fetch the email content and ensure the email belongs to the logged-in user
  $stmt = $db->prepare("SELECT * FROM inboxes WHERE id = :id AND to_email = :to_email");
  $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
  $stmt->bindValue(':to_email', $email, SQLITE3_TEXT);
  $result = $stmt->execute();
  $emailData = $result->fetchArray(SQLITE3_ASSOC);

  if ($emailData) {
    // Update the email's read status to 'yes'
    $updateStmt = $db->prepare("UPDATE inboxes SET read = 'yes' WHERE id = :id");
    $updateStmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $updateStmt->execute();
    
    // Fetch additional data about the sender
    $query = "
      SELECT
        sender.artist AS sender_artist,
        sender.pic AS sender_pic,
        sender.id AS sender_id
      FROM
        users AS sender
      WHERE
        sender.email = :email
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':email', $emailData['email'], SQLITE3_TEXT);
    $result = $stmt->execute();
    $artists = $result->fetchArray(SQLITE3_ASSOC);

    // Fetch sender artist name, sender's avatar, and sender's ID
    $sender_artist = $artists['sender_artist'];
    $sender_pic = $artists['sender_pic'] ?: '/icon/profile.svg'; // Default avatar if none provided
    $sender_id = $artists['sender_id'];
  } else {
    // Redirect if the email does not belong to the logged-in user
    header('Location: /feeds/inboxes/');
    exit;
  }
} else {
  // Redirect if no ID is provided
  header('Location: /feeds/inboxes/');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email - <?php echo htmlspecialchars($emailData['title']); ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <div class="container mt-4">
      <a href="/feeds/inboxes/" class="btn btn-primary mb-3 fw-medium rounded-pill"><i class="bi bi-arrow-left"></i> Back</a>
      <div class="mt-4">
        <div class="position-relative">
          <!-- Badge for read/unread status -->
          <span class="position-absolute top-0 end-0 badge rounded-pill bg-<?php echo $emailData['read'] === 'yes' ? 'secondary' : 'primary'; ?>" style="margin: 10px;">
            <?php echo $emailData['read'] === 'yes' ? 'Read' : 'Unread'; ?>
          </span>
          <div class="d-flex align-items-start">
            <!-- Sender Avatar -->
            <img src="/<?php echo htmlspecialchars($sender_pic); ?>" alt="<?php echo htmlspecialchars($sender_artist); ?> Avatar" class="rounded-circle me-3" width="60" height="60">
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($sender_artist); ?> <?php if ($sender_id == 1) echo '<small>(Owner)</small>'; ?></h5>
                  <small class="text-muted">&lt;<?php echo htmlspecialchars($emailData['email']); ?>&gt;</small><br>
                  <small class="text-muted">Recipient: <?php echo htmlspecialchars($emailData['to_email']); ?></small>
                  <small class="text-muted"><?php echo date("d M Y, H:i", strtotime($emailData['date'])); ?></small>
                </div>
              </div>
              <h5 class="mb-3 mt-5 fw-bold"><?php echo htmlspecialchars($emailData['title']); ?></h5>
              <?php
                $novelText = isset($emailData['post']) ? $emailData['post'] : '';

                if (!empty($novelText)) {
                  $paragraphs = explode("\n", $novelText);

                  foreach ($paragraphs as $index => $paragraph) {
                    $messageTextWithoutTags = strip_tags($paragraph);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $url = htmlspecialchars($matches[0]);

                      // Check if the URL ends with .png, .jpg, .jpeg, or .webp
                      if (preg_match('/\.(png|jpg|jpeg|webp|gif|svg|heic|tiff|bmp|raw|heif|icns|webp2)$/i', $url)) {
                        return '<a href="' . $url . '" target="_blank"><img class="w-100 h-100 rounded-4" loading="lazy" src="' . $url . '" alt="Image"></a>';
                      } elseif (strpos($url, 'youtube.com') !== false) {
                        // If the URL is from YouTube, embed it as an iframe with a very low-resolution thumbnail
                        $videoId = getYouTubeVideoId($url);
                        if ($videoId) {
                          $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/default.jpg';
                          return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                        } else {
                          return '<a href="' . $url . '">' . $url . '</a>';
                        }
                      } else {
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }
                    }, $messageTextWithoutTags);

                    echo "<p style=\"white-space: break-spaces; overflow: hidden; margin: 0; padding: 0;\">$formattedText</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }

                function getYouTubeVideoId($url)
                {
                  $videoId = '';
                  $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                  if (preg_match($pattern, $url, $matches)) {
                    $videoId = $matches[1];
                  }
                  return $videoId;
                }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>