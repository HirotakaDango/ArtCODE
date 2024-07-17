<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Ensure chat_group table exists
$db->exec("CREATE TABLE IF NOT EXISTS chat_group (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL,
  group_message TEXT NOT NULL,
  date TEXT NOT NULL
)");

// Fetch messages from chat_group table
$results = $db->query("SELECT * FROM chat_group ORDER BY date ASC");

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
  $messageId = $row['id'];
  $senderEmail = $row['email'];
  $messageText = $row['group_message'];
  $messageDate = date('l, j F Y | H:i', strtotime($row['date']));

  // Determine if the current user is the sender
  $is_sender = ($senderEmail == $email);

  // Fetch sender's information
  $stmt = $db->prepare("SELECT id, artist FROM users WHERE email = :email");
  $stmt->bindValue(':email', $senderEmail, SQLITE3_TEXT);
  $senderInfo = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $sender_id = $senderInfo['id'];
  $sender_artist = $senderInfo['artist'];
  ?>
    <div id="message_<?php echo $messageId; ?>" class="message my-2 shadow p-3 <?php echo ($is_sender ? "bg-light-subtle text-align-right ms-auto rounded-4 rounded-top-0 rounded-start-4 rounded-bottom-4" : "bg-secondary-subtle text-align-left me-auto rounded-4 rounded-top-0 rounded-end-4 rounded-bottom-4"); ?>">
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
                <li>
                  <a class="dropdown-item" href="#" onclick="openEditModal(<?php echo $messageId; ?>)">Edit</a>
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
          ob_start();
          include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php');
          $oppositeColor = ob_get_clean();
          echo "<a class='text-decoration-none text-{$oppositeColor}' href='/artist.php?id={$sender_id}' target='_blank'>@{$sender_artist}</a>";
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
              if (preg_match('/\.(png|jpg|jpeg|webp|gif)$/i', $urlThread)) {
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
      <div class="message-date">
        <h6 class="<?php echo ($is_sender ? "text-end" : "text-start"); ?> mt-4 small"><small><?php echo $messageDate; ?></small></h6>
      </div>
    </div>
  <?php
}
?>