<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Create chat_group table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS chat_group (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL,
  group_message TEXT NOT NULL,
  date TEXT NOT NULL
)");

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

  if ($message) {
    $stmt = $db->prepare("INSERT INTO chat_group (email, group_message, date) VALUES (:email, :message, datetime('now'))");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':message', $message, SQLITE3_TEXT);
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
    <title>Group Chat</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <style>
      .message {
        position: relative;
        max-width: 55%;
        overflow: hidden;
      }
    </style>
  </head>
  <body>
    <div class="fixed-top container-fluid p-0">
      <div class="container-fluid d-flex rounded-4 rounded-top-0 bg-dark-subtle p-2 px-3 justify-content-between">
        <div class="d-flex align-items-center">
          <span class="fs-5 d-flex align-items-center">
            Group Chat
          </span>
        </div>
        <div class="dropdown">
          <button class="btn border-0 px-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <ul class="dropdown-menu">
            <li>
              <button class="dropdown-item" id="toggleButton" onclick="toggleAutoScroll()">Auto-Scroll: ON</button>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="chat-container-fluid py-5">
        <div id="messages" class="pt-1 pb-2">
        </div>
      </div>
    </div>
    <div class="fixed-bottom container-fluid py-3">
      <form id="messageForm">
        <div class="input-group w-100 rounded-0 shadow-lg rounded-4">
          <textarea id="message" name="message" class="form-control bg-body-tertiary border-0 rounded-start-5 focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" style="height: 40px; max-height: 150px;" placeholder="Type a message..." aria-label="Type a message..." aria-describedby="basic-addon2"
            onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
            onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 150) { this.style.height = '150px'; } else { this.style.height = newHeight; }" required></textarea>
          <button type="submit" class="btn bg-body-tertiary border-0 rounded-end-5"><i class="bi bi-send-fill"></i></button>
        </div>
      </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <div class="modal fade" id="editMessageModal" tabindex="-1" aria-labelledby="editMessageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 p-3 position-relative">
          <button type="button" class="btn border-0 position-absolute top-0 end-0" data-bs-dismiss="modal"><i class="bi bi-x fs-5" style="-webkit-text-stroke: 2px;"></i></button>
          <form id="editMessageForm">
            <input type="hidden" id="editMessageId" name="editMessageId">
            <div class="mb-2">
              <label for="editMessageText" class="form-label fw-medium">Edit Message:</label>
              <textarea class="form-control border-0 bg-body-tertiary shadow" id="editMessageText" name="editMessageText" rows="10" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-medium">Save changes</button>
          </form>
        </div>
      </div>
    </div>
    <script>
      // Function to open edit modal with message content
      function openEditModal(messageId) {
        // Fetch message content via AJAX
        $.ajax({
          url: 'message_group_fetch.php',
          method: 'GET',
          data: { messageId: messageId },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              $('#editMessageId').val(messageId);
              $('#editMessageText').val(response.messageText);
              $('#editMessageModal').modal('show');
            } else {
              alert('Failed to fetch message.');
            }
          },
          error: function() {
            alert('Error fetching message.');
          }
        });
      }
      
      // Handle edit message submission
      $('#editMessageForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
          url: 'message_group_edit.php',
          method: 'POST',
          data: formData,
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              $('#editMessageModal').modal('hide');
              loadMessages(); // Reload messages after editing
            } else {
              alert('Failed to edit message.');
            }
          },
          error: function() {
            alert('Error editing message.');
          }
        });
      });
      
      // Function to load messages initially and periodically
      function loadMessages() {
        $.get('message_group_load.php', function(data) {
          $('#messages').html(data);
          // Scroll to bottom of the chat container
          $('#messages').scrollTop($('#messages')[0].scrollHeight);
        });
      }
      
      // Submit form using AJAX on form submission
      $('#messageForm').submit(function(event) {
        event.preventDefault(); // Prevent default form submission
        sendMessage(); // Call sendMessage function
      });
      
      // Function to send message using AJAX
      function sendMessage() {
        var formData = $('#messageForm').serialize();
        $.post('message_group_send.php', formData, function(response) {
          if (response.success) {
            $('#message').val(''); // Clear the message input
            loadMessages(); // Reload messages after sending
          } else {
            alert('Failed to send message.');
          }
        }, 'json');
      }
      
      // Function to delete message using AJAX
      function deleteMessage(messageId) {
        if (confirm('Are you sure you want to delete this message?')) {
          $.post('message_group_delete.php', { messageId: messageId }, function(response) {
            if (response.success) {
              loadMessages(); // Reload messages after deletion
            } else {
              alert('Failed to delete message.');
            }
          }, 'json');
        }
      }
      
      // Load messages initially and set interval to refresh messages
      $(document).ready(function() {
        loadMessages();
        setInterval(loadMessages, 5000); // Refresh messages every 5 seconds
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
