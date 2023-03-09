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
        padding: 6px 12px;
        margin: 6px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        line-height: 1;
        font-weight: 800;
        background-color: #eee;
        color: #333;
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
    <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
      <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
        <div class="container">
          <a class="nav-link px-2 text-secondary" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
          <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
          <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE</a></h1>
          <a class="nav-link text-secondary" href="global.php"><i class="bi bi-images"></i></a>
          <div class="dropdown">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item text-secondary fw-bold mt-1" href="popular.php"><i class="bi bi-graph-up"></i> Popular</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="tags.php"><i class="bi bi-tags-fill"></i> Tags</a></li>
              <li><a class="dropdown-item text-secondary fw-bold border-bottom" href="users.php"><i class="bi bi-person-fill"></i> Users</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </center>
    <div>
      <h5 class="text-secondary ms-2 fw-bold"><i class="bi bi-people-fill"></i> users</h5>
  
      <!-- Add a search input field -->
      <div class="input-group mb-3">
        <input type="text" class="form-control me-2 ms-2" placeholder="Search creators" id="search-input">
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
