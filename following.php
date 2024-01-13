<?php
require_once('auth.php');

$db = new SQLite3('database.sqlite');

if(isset($_GET['id'])){
  $user_id = $_GET['id'];
    
  // Get the logged-in user's email and artist name
  $stmt = $db->prepare("SELECT id AS userid, email, artist FROM users WHERE id = :id");
  $stmt->bindValue(':id', $user_id);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $email = $row['email'];
  $userName = $row['artist'];
  $userId = $row['userid'];

  // Get the list of users that the logged in user is following
  $stmt = $db->prepare("SELECT * FROM following WHERE follower_email = :email");
  $stmt->bindValue(':email', $email);
  $result = $stmt->execute();
  $following_users = array();
  while($row = $result->fetchArray()){
    $following_users[] = $row['following_email'];
  }

  // Get the details of the following users
  $stmt = $db->prepare("SELECT *, SUBSTR(artist, 1, 1) AS first_letter FROM users WHERE email IN ('" . implode("','", $following_users) . "') ORDER BY id DESC");
  $result = $stmt->execute();

  // Count the number of users being followed
  $following_count = count($following_users);

  // Group users by category
  $groupedUsers = [];
  while ($user = $result->fetchArray()) {
    $letter = strtoupper($user['first_letter']);
    $groupedUsers[$letter][] = $user;
  }

  // Order the grouped users by letter
  ksort($groupedUsers);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $userName; ?>'s followings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('userheader.php'); ?>
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
    <div>
      <?php
        if($following_count == 0){
          echo "
          <div class='container'>
            <p class='text-secondary fw-bold text-center'>This user is not followed by any users.</p>
            <p class='text-secondary text-center fw-bold'>Probably because they're not famous... just kidding!</p>
            <img src='icon/Empty.svg' style='width: 100%; height: 100%;'>
          </div>";
        }
      ?>
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
