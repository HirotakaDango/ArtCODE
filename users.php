<?php
require_once('auth.php');

// Establish SQLite connection
$db = new SQLite3('database.sqlite');

$searchQuery = isset($_GET['q']) ? $_GET['q'] : ''; // Capture the search query
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
    <form action="users.php" method="GET">
      <div class="input-group my-3 px-2 w-100">
        <input type="hidden" name="by" value="<?php echo isset($_GET['by']) ? $_GET['by'] : 'ascending'; ?>">
        <input type="text" class="form-control rounded-4 rounded-end-0 border border-3 fw-bold" placeholder="Search user" name="q" value="<?php echo $searchQuery = isset($_GET['q']) ? $_GET['q'] : ''; ?>">
        <button class="btn btn-outline-secondary rounded-4 rounded-start-0 border border-start-0 border-3 fw-bold" type="submit">Search</button>
      </div>
    </form>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=ascending&q=<?php echo htmlspecialchars($searchQuery); ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'ascending') echo 'active'; ?>">ascending</a></li>
        <li><a href="?by=descending&q=<?php echo htmlspecialchars($searchQuery); ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'descending') echo 'active'; ?>">descending</a></li>
        <li><a href="?by=popular&q=<?php echo htmlspecialchars($searchQuery); ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
      </ul> 
    </div> 
    <?php 
    if(isset($_GET['by'])){
      $sort = $_GET['by'];
    
      switch ($sort) {
        case 'ascending':
          include "users_asc.php";
          break;
        case 'descending':
          include "users_desc.php";
          break;
        case 'popular':
          include "users_pop.php";
          break;
      }
    }
    else {
      include "users_asc.php"; // Include ascending by default
    }
    
    ?>
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
