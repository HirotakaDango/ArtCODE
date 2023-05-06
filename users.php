<?php
  session_start();
  if (!isset($_SESSION['email'])) {
    header("Location: session.php");
    exit;
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search User</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <style>
      .tag-buttons {
        display: flex;
        flex-wrap: wrap;
      }
    
      .tag-button {
        display: inline-block;
        padding: 6px 8px;
        margin: 4px;
        background-color: #eee;
        color: #333;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        line-height: 1;
        font-weight: 800; 
      }
    
      .tag-button:hover {
        background-color: #ccc;
      }
    
      .tag-button:active {
        background-color: #aaa;
      }
    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('taguserheader.php'); ?> 
    <div>
      <!-- Add a search input field -->
      <div class="input-group mb-3 mt-2">
        <input type="text" class="form-control me-2 ms-2" placeholder="Search user" id="search-input">
      </div>

      <?php
        // Establish SQLite connection
        $db = new SQLite3('database.sqlite');

        // Retrieve all users from the database
        $users = $db->query('SELECT * FROM users');
      ?>
      <!-- Display each user's id as a button in a button group -->
      <div class="tag-buttons" role="group" aria-label="User buttons">
        <?php while ($user = $users->fetchArray()): ?>
          <button type="button" class="btn tag-button artist" onclick="location.href='artist.php?id=<?= $user['id'] ?>'"><?= $user['artist'] ?></button>
        <?php endwhile; ?>
      </div>

      <?php
        // Close the SQLite connection
        $db->close();
      ?>
    </div>
    <script>
      // Get the search input element
      const searchInput = document.getElementById('search-input');

      // Get all the artist buttons
      const artistButtons = document.querySelectorAll('.artist');

      // Add an event listener to the search input field
      searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();

        // Filter the artist buttons based on the search term
        artistButtons.forEach(button => {
          const artistName = button.textContent.toLowerCase();

          if (artistName.includes(searchTerm)) {
          button.style.display = 'inline-block';
          } else {
            button.style.display = 'none';
          }
        });
      });
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
