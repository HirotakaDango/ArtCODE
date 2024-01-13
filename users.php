<?php
require_once('auth.php');

// Establish SQLite connection
$db = new SQLite3('database.sqlite');

// Retrieve all users from the database and sort them by letter and number ASC
$users = $db->query('SELECT *, SUBSTR(artist, 1, 1) AS first_letter FROM users ORDER BY first_letter COLLATE NOCASE ASC, artist COLLATE NOCASE ASC');

// Group users by category
$groupedUsers = [];
while ($user = $users->fetchArray()) {
  $letter = strtoupper($user['first_letter']);
  $groupedUsers[$letter][] = $user;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Users</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('taguserheader.php'); ?>
    <div class="input-group my-3 px-2">
      <input type="text" class="form-control rounded-4 border border-3 fw-bold" placeholder="Search user" id="search-input">
    </div>
    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedUsers as $group => $users) : ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-dark border-0 fw-medium d-flex flex-column align-items-center" href="#category-<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php foreach ($groupedUsers as $group => $users) : ?>
        <?php include('user_card.php'); ?>
      <?php endforeach; ?>
    </div>
    <div class="mt-5"></div>
    <button class="z-3 btn btn-primary btn-md rounded-pill fw-bold position-fixed bottom-0 end-0 m-2" id="scrollToTopBtn" onclick="scrollToTop()"><i class="bi bi-chevron-up" style="-webkit-text-stroke: 3px;"></i></button>
    <script>
      // Show or hide the button based on scroll position
      window.onscroll = function() {
        showScrollButton();
      };

      // Function to show or hide the button based on scroll position
      function showScrollButton() {
        var scrollButton = document.getElementById("scrollToTopBtn");
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
          scrollButton.style.display = "block";
        } else {
          scrollButton.style.display = "none";
        }
      }

      // Function to scroll to the top of the page
      function scrollToTop() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE, and Opera
      }
    </script>
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
