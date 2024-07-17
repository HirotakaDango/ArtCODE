<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');
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
        let offset = 0;
        const limit = 25;

        function loadUsers(query = '', append = false) {
          $.ajax({
            url: 'search_load.php',
            method: 'POST',
            data: {query: query, offset: offset, limit: limit},
            success: function(data) {
              if (append) {
                $('#user-list').append(data);
              } else {
                $('#user-list').html(data);
              }
            }
          });
        }

        loadUsers();

        $('input[name="search"]').on('keyup', function() {
          var query = $(this).val();
          offset = 0;
          loadUsers(query);
        });

        $('#load-more').on('click', function() {
          offset += limit;
          loadUsers($('input[name="search"]').val(), true);
        });
      });

      if (window.innerWidth >= 768) {
        window.location.href = '/messages_desktop/';
      }
    </script>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container mb-5">
      <div class="btn-group w-100 mb-2 gap-2">
        <a class="btn bg-body-tertiary p-3 rounded-4 w-50 fw-bold text-nowrap" href="/messages/">Current Contacts</a>
        <a class="btn bg-body-tertiary p-3 rounded-4 w-50 fw-bold opacity-75 text-nowrap" href="search.php">Search Users</a>
      </div>
      <form class="">
        <div class="input-group">
          <input type="text" name="search" class="form-control rounded-end-0 rounded-4 border-0 bg-body-tertiary" placeholder="Search user by the name or id...">
          <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 rounded-start-0 rounded-4"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
        </div>
      </form>
      <div id="user-list">
        <!-- Users will be dynamically loaded here -->
      </div>
      <button id="load-more" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-4 fw-medium w-100">Load More</button>
    </div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>