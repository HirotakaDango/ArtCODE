<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

// Validate and sanitize the user_id from the GET request
$user_id = filter_input(INPUT_GET, 'userid', FILTER_VALIDATE_INT);

if (!$user_id) {
  die('Invalid user ID.');
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$chat_user = $result->fetchArray(SQLITE3_ASSOC);

if (!$chat_user) {
  die('User not found.');
}

$stmt = $db->prepare("SELECT * FROM messages 
                      WHERE (email = :email1 AND to_user_email = :email2)
                      OR (email = :email2 AND to_user_email = :email1)
                      ORDER BY date ASC");
$stmt->bindValue(':email1', $email, SQLITE3_TEXT);
$stmt->bindValue(':email2', $chat_user['email'], SQLITE3_TEXT);
$result = $stmt->execute();

// Check if there are messages to display
if (!$result) {
  die('Error fetching messages.');
}

while ($message = $result->fetchArray(SQLITE3_ASSOC)) {
  $is_sender = $message['email'] == $email;
  $messageText = htmlspecialchars($message['message']);
  $messageDate = date('l, j F Y | H:i', strtotime($message['date']));
  $messageId = $message['id'];

  // Get sender details
  $sender_stmt = $db->prepare("SELECT id, artist FROM users WHERE email = :email");
  $sender_stmt->bindValue(':email', $message['email'], SQLITE3_TEXT);
  $sender_result = $sender_stmt->execute();
  $sender = $sender_result->fetchArray(SQLITE3_ASSOC);

  $sender_id = $sender['id'];
  $sender_artist = $sender['artist'];
  ?>
    <div id="message_<?php echo $messageId; ?>" class="message shadow rounded-4 p-3 <?php echo ($is_sender ? "bg-success-subtle text-align-right ms-auto" : "bg-secondary-subtle text-align-left me-auto"); ?>">
      <div class="position-relative">
        <div class="position-absolute top-0 end-0 translate-middle-y mt-2">
          <?php if ($is_sender): ?>
            <div class="dropdown z-2">
              <button class="btn border-0 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical text-white link-body-emphasis" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#" onclick="deleteMessage(<?php echo $messageId; ?>)">Delete</a>
                </li>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <h6 class="fw-bold mb-4">
        <?php
        if ($is_sender) {
          echo "You";
        } else {
          // Include opposite.php to get the color class
          ob_start();  // Start output buffering
          include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php');
          $oppositeColor = ob_get_clean();  // Get and clear output buffer
          echo "<a class='text-decoration-none text-{$oppositeColor}' href='/artist.php?id={$sender_id}' target='_blank'>{$sender_artist}</a>";
        }
        ?>
      </h6>
      <div>
        <?php
          if (!function_exists('getYouTubeVideoId')) {
            function getYouTubeVideoId($urlCommentThread)
            {
              $videoIdThread = '';
              $patternThread = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
              if (preg_match($patternThread, $urlCommentThread, $matchesThread)) {
                $videoIdThread = $matchesThread[1];
              }
              return $videoIdThread;
            }
          }
    
          $mainTextThread = isset($messageText) ? $messageText : '';
    
          if (!empty($mainTextThread)) {
            $paragraphsThread = explode("\n", $mainTextThread);
    
            foreach ($paragraphsThread as $indexThread => $paragraphThread) {
              $textWithoutTagsThread = strip_tags($paragraphThread);
              $patternThread = '/\bhttps?:\/\/\S+/i';
    
              $formattedTextThread = preg_replace_callback($patternThread, function ($matchesThread) {
                $urlThread = htmlspecialchars($matchesThread[0]);
    
                // Check if the URL ends with .png, .jpg, .jpeg, or .webp
                if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlThread)) {
                  return '<a href="' . $urlThread . '" target="_blank"><img class="img-fluid rounded-4" loading="lazy" src="' . $urlThread . '" alt="Image"></a>';
                } elseif (strpos($urlThread, 'youtube.com') !== false) {
                  // If the URL is from YouTube, embed it as an iframe with a very low-resolution thumbnail
                  $videoIdThread = getYouTubeVideoId($urlThread);
                  if ($videoIdThread) {
                    $thumbnailUrlThread = 'https://img.youtube.com/vi/' . $videoIdThread . '/default.jpg';
                    return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoIdThread . '" frameborder="0" allowfullscreen></iframe></div>';
                  } else {
                    return '<a href="' . $urlThread . '">' . $urlThread . '</a>';
                  }
                } else {
                  return '<a href="' . $urlThread . '">' . $urlThread . '</a>';
                }
              }, $textWithoutTagsThread);
    
              echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedTextThread</p>";
            }
          } else {
            echo "Sorry, no text...";
          }
        ?>
      </div>
      <h6 class="text-muted text-end small"><small><?php echo $messageDate; ?></small></h6>
  </div>
  <?php
}
?>