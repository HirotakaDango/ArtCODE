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
          $.getJSON('load_new_message.php', function(latestMessages) {
            $('#latest-messages').empty(); // Clear existing messages
            
            $.each(latestMessages, function(index, message) {
              // Build HTML for each latest message using the provided template design
              var messageHtml = '<a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="send.php?userid=' + message.id + '"><div class="card p-3 rounded-4 bg-body-tertiary shadow my-2 border-0">';
              messageHtml += '<div class="d-flex align-items-center">';
              messageHtml += '<div class="d-inline-flex align-items-center justify-content-center me-3">';
              messageHtml += '<img id="previewImage" src="' + (message.pic ? message.pic : "../icon/bg.png") + '" alt="Current Background Picture" style="width: 96px; height: 96px;" class="border border-4 rounded-circle object-fit-cover">';
              messageHtml += '</div>';
              messageHtml += '<div>';
              messageHtml += '<h5 class="fw-bold">' + message.artist + '</h5>';
              let limitedMessage = message.message.length > 30 ? message.message.substring(0, 30) + '...' : message.message;
              messageHtml += '<p class="mb-2"><strong>' + message.sender_artist + ':</strong> ' + limitedMessage + '</p>';
              messageHtml += '<h6 class="text-muted small"><small>' + message.date + '</small></h6>';
              messageHtml += '</div>';
              messageHtml += '</div>';
              messageHtml += '</div></a>';
              $('#latest-messages').append(messageHtml);
            });
          });
        }
    
        // Load latest messages initially
        loadLatestMessages();
    
        // Periodically refresh latest messages every 10 seconds
        setInterval(loadLatestMessages, 10000); // Adjust interval as needed
      });

      if (window.innerWidth >= 768) {
        window.location.href = '/messages_desktop/';
      }
    </script>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container mb-5">
      <div class="btn-group w-100 mt-2 mb-3 gap-2">
        <a class="btn bg-body-tertiary p-4 rounded-4 shadow w-50 fw-bold opacity-75 shadow text-nowrap" href="/messages/">Current Contacts</a>
        <a class="btn bg-body-tertiary p-4 rounded-4 shadow w-50 fw-bold text-nowrap" href="/messages/search.php">Search Users</a>
      </div>
      <div id="latest-messages">
        <!-- Latest messages will be dynamically loaded here -->
      </div>
    </div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>