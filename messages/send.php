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

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

  if ($message) {
    $stmt = $db->prepare("INSERT INTO messages (email, message, date, to_user_email) VALUES (:from_email, :message, datetime('now'), :to_email)");
    $stmt->bindValue(':from_email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':message', $message, SQLITE3_TEXT);
    $stmt->bindValue(':to_email', $chat_user['email'], SQLITE3_TEXT);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
  } else {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo $chat_user['artist']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <style>
      .message {
        margin-bottom: 15px;
        position: relative;
        max-width: 60%;
        overflow: hidden;
      }
    </style>
  </head>
  <body>
    <div class="fixed-top container rounded-bottom-4 bg-dark-subtle px-0 py-2">
      <div class="d-flex justify-content-between">
        <div class="d-flex align-items-center">
          <a class="btn border-0" href="/messages/">
            <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
          </a>
          <span class="fs-5 d-flex align-items-center gap-2">
            <img src="/<?php echo !empty($chat_user['pic']) ? $chat_user['pic'] : 'icon/profile.svg'; ?>" class="rounded-circle" style="width: 32px; height: 32px;">
            <?php echo strlen($chat_user['artist']) > 15 ? substr($chat_user['artist'], 0, 15) . '...' : $chat_user['artist']; ?>
          </span>
        </div>
        <div class="dropdown">
          <button class="btn border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <ul class="dropdown-menu">
            <li>
              <button class="dropdown-item" id="toggleButton" onclick="toggleAutoScroll()">Auto-Scroll: ON</button>
              <a class="dropdown-item" href="/artist.php?id=<?php echo $_GET['userid']; ?>">Check User's Profile</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="chat-container py-5">
        <div id="messages" class="py-4">
        </div>
      </div>
    </div>
    <div class="fixed-bottom container py-3">
      <form id="messageForm">
        <div class="input-group w-100 rounded-0 shadow-lg rounded-4">
          <input type="hidden" id="userid" name="userid" value="<?php echo $user_id; ?>">
          <textarea id="message" name="message" class="form-control bg-body-tertiary border-0 rounded-start-5 focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" style="height: 40px; max-height: 150px;" placeholder="Type a message..." aria-label="Type a message..." aria-describedby="basic-addon2" 
            onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
            onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 150) { this.style.height = '150px'; } else { this.style.height = newHeight; }"></textarea>
          <button type="submit" class="btn bg-body-tertiary border-0 rounded-end-5"><i class="bi bi-send-fill"></i></button>
        </div>
      </form> 
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      // Function to load messages initially and periodically
      function loadMessages() {
        $.get('send_load.php?userid=<?php echo $user_id; ?>', function(data) {
          $('#messages').html(data);
          // Scroll to bottom of the chat container
          $('#messages').scrollTop($('#messages')[0].scrollHeight);
        });
      }

      // Function to send message using AJAX
      function sendMessage() {
        var formData = $('#messageForm').serialize();
        $.post('send_message.php', formData, function(response) {
          if (response.success) {
            $('#message').val(''); // Clear the message input
            loadMessages(); // Reload messages
          } else {
            alert(response.message || 'Failed to send message.');
          }
        }, 'json');
      }

      function deleteMessage(id) {
        if (confirm('Are you sure you want to delete this message?')) {
          $.post('delete_message.php', {id: id}, function(data) {
            loadMessages();
          });
        }
      }

      // Load messages initially and set interval to refresh messages
      $(document).ready(function() {
        loadMessages();
        setInterval(loadMessages, 5000); // Refresh messages every 5 seconds

        // Submit form using AJAX on form submission
        $('#messageForm').submit(function(event) {
          event.preventDefault(); // Prevent default form submission
          sendMessage(); // Call sendMessage function
        });
      });

      // Function to scroll to the bottom of the page
      function scrollToBottom() {
        window.scrollTo({
          top: document.body.scrollHeight,
          behavior: 'auto'  // Instant scroll
        });
      }

      // Toggle function for auto-scroll
      function toggleAutoScroll() {
        autoScrollEnabled = !autoScrollEnabled;

        // Update button text based on autoScrollEnabled
        const toggleButton = document.getElementById('toggleButton');
        if (autoScrollEnabled) {
          toggleButton.textContent = 'Auto-Scroll: ON';
          scrollToBottom();  // Scroll to bottom when enabling auto-scroll
        } else {
          toggleButton.textContent = 'Auto-Scroll: OFF';
        }

        // Store autoScrollEnabled state in localStorage
        localStorage.setItem('autoScrollEnabled', autoScrollEnabled);
      }

      // Initialize autoScrollEnabled from localStorage if available
      let autoScrollEnabled = localStorage.getItem('autoScrollEnabled') === 'true';

      // Initially set button text and scroll based on autoScrollEnabled
      const toggleButton = document.getElementById('toggleButton');
      if (autoScrollEnabled) {
        toggleButton.textContent = 'Auto-Scroll: ON';
        scrollToBottom();
      } else {
        toggleButton.textContent = 'Auto-Scroll: OFF';
      }

      // Check and scroll to bottom periodically
      setInterval(function() {
        if (autoScrollEnabled) {
          scrollToBottom();
        }
      }, 1000);  // Adjust the interval as needed
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>