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
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('taguserheader.php'); ?> 
    <!-- Add a search input field -->
    <div class="input-group mb-3 mt-2">
      <input type="text" class="form-control me-2 ms-2 fw-bold" placeholder="Search user" id="search-input">
    </div>
    <div class="container-fluid">
      <?php
        // Establish SQLite connection
        $db = new SQLite3('database.sqlite');

        // Retrieve all users from the database and sort them by letter and number ASC
        $users = $db->query('SELECT *, SUBSTR(artist, 1, 1) AS first_letter FROM users ORDER BY first_letter COLLATE NOCASE ASC, artist COLLATE NOCASE ASC');
 
        // Pagination
        $perPage = 300; // Number of users per page
        $totalUsers = $users->numColumns(); // Total number of users
        $totalPages = ceil($totalUsers / $perPage); // Calculate total pages

        // Get the current page number
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
        if ($currentPage < 1) {
          $currentPage = 1;
        } elseif ($currentPage > $totalPages) {
          $currentPage = $totalPages;
        }

        // Calculate the offset for the SQL query
        $offset = ($currentPage - 1) * $perPage;

        // Retrieve the users for the current page
        $usersPage = $db->query("SELECT *, SUBSTR(artist, 1, 1) AS first_letter FROM users ORDER BY first_letter COLLATE NOCASE ASC, artist COLLATE NOCASE ASC LIMIT $perPage OFFSET $offset");
      ?>
        <!-- Display each user's id as a button in a button group -->
        <div class="row">
          <?php
            $currentLetter = null;
            while ($user = $usersPage->fetchArray()):
              $letter = strtoupper($user['first_letter']);
              if ($letter !== $currentLetter):
                $currentLetter = $letter;
                echo "<h5 class='fw-semibold text-secondary text-start'>Category $letter</h5>"; // Display the category name
              endif;
          ?>
            <div class="col-md-3 col-sm-6">
              <button type="button" class="btn artist btn-secondary mb-2 fw-bold text-start w-100 opacity-75" onclick="location.href='artist.php?id=<?= $user['id'] ?>'"><?= $user['artist'] ?></button>
            </div>
          <?php endwhile; ?>
        </div> 
        <div class="mt-5 mb-2 d-flex justify-content-center btn-toolbar container">
          <?php if ($currentPage > 1): ?>
            <a href="users.php?page=<?= $currentPage - 1 ?>" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>
          <?php endif; ?>
          <?php if ($currentPage < $totalPages): ?>
            <a href="users.php?page=<?= $currentPage + 1 ?>" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1">next <i class="bi bi-arrow-right-circle-fill"></i></a>
          <?php endif; ?>
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