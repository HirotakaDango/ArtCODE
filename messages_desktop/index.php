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
    <style>
      body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
      }

      .message-container {
        display: flex;
        height: 93%;
      }

      .left-column {
        width: 35%;
        height: 100%;
      }

      .right-column {
        width: 65%;
        height: 100%;
      }

      iframe {
        width: 100%;
        height: 100%;
        border: none;
      }
    </style>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="message-container">
      <div class="left-column">
        <iframe src="desktop.php" id="leftFrame"></iframe>
      </div>
      <div class="right-column">
        <iframe src="nothing.php" id="rightFrame" name="chatFrame"></iframe>
      </div>
    </div>

    <script>
      document.getElementById('leftFrame').addEventListener('load', function() {
        this.contentWindow.document.body.addEventListener('click', function(e) {
          const link = e.target.closest('a[href]');
          if (link) {
            const href = link.getAttribute('href');
            if (href.includes('send.php') || href.includes('message_group.php')) {
              e.preventDefault();
              document.getElementById('rightFrame').src = href;
            }
          }
        });
      });

      if (window.innerWidth <= 767) {
        window.location.href = '/messages/';
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>