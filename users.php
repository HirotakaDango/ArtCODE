<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Buttons</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
