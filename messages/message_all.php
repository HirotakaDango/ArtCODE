<?php
require_once('../auth.php');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages</title>
    <link rel="../manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      $(document).ready(function() {
        function loadLatestMessages() {
          // Load the latest group message
          $.getJSON('message_group_new.php', function(latestGroupMessage) {
            if (latestGroupMessage) {
              var groupMessageHtml = '<a class="text-decoration-none text-light" href="message_group.php" target="chatFrame" onclick="return false;"><div class="card p-3 rounded-4 bg-body-tertiary shadow my-2 border-0">';
              groupMessageHtml += '<div class="d-flex align-items-center">';
              groupMessageHtml += '<div class="d-inline-flex align-items-center justify-content-center me-3">';
              groupMessageHtml += '<img id="previewImage" src="../icon/bg.png" alt="Current Background Picture" style="width: 96px; height: 96px;" class="border border-4 rounded-circle object-fit-cover">';
              groupMessageHtml += '</div>';
              groupMessageHtml += '<div>';
              groupMessageHtml += '<h5 class="fw-bold">Group Chat</h5>';
              let limitedMessage1 = latestGroupMessage.group_message.length > 30 ? latestGroupMessage.group_message.substring(0, 30) + '...' : latestGroupMessage.group_message;
              groupMessageHtml += '<p class="mb-2"><strong>' + latestGroupMessage.artist + ':</strong> ' + limitedMessage1 + '</p>';
              groupMessageHtml += '<h6 class="text-muted small"><small>' + latestGroupMessage.date + '</small></h6>';
              groupMessageHtml += '</div>';
              groupMessageHtml += '</div>';
              groupMessageHtml += '</div></a>';
              $('#latest-group-message').html(groupMessageHtml);
  
              // Add click event listener to the newly added elements
              $('#latest-group-message a').on('click', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                window.parent.document.getElementById('rightFrame').src = href;
              });
            }
          });
        }
  
        // Load latest messages initially
        loadLatestMessages();
  
        // Periodically refresh latest messages every 10 seconds
        setInterval(loadLatestMessages, 10); // Adjust interval as needed
      });
    </script>
  </head>
  <body>
    <div class="container mb-5">
      <div class="btn-group w-100 mb-3 gap-2">
        <a class="btn bg-body-tertiary p-4 rounded-4 shadow w-50 fw-bold opacity-75 shadow text-nowrap" href="desktop.php">Current Contacts</a>
        <a class="btn bg-body-tertiary p-4 rounded-4 shadow w-50 fw-bold text-nowrap" href="search.php">Search Users</a>
      </div>
      <div id="latest-group-message">
        <!-- Latest group message will be dynamically loaded here -->
      </div>
    </div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
