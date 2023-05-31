<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

$db = new SQLite3('database.sqlite');

if(isset($_GET['id'])){
  $user_id = $_GET['id'];
    
  // Get the logged in user's email
  $stmt = $db->prepare("SELECT email FROM users WHERE id = :id");
  $stmt->bindValue(':id', $user_id);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $email = $row['email'];

  // Get the list of users that the logged in user is following
  $stmt = $db->prepare("SELECT * FROM following WHERE follower_email = :email");
  $stmt->bindValue(':email', $email);
  $result = $stmt->execute();
  $following_users = array();
  while($row = $result->fetchArray()){
    $following_users[] = $row['following_email'];
  }

  // Get the details of the following users
  $stmt = $db->prepare("SELECT * FROM users WHERE email IN ('" . implode("','", $following_users) . "') ORDER BY id DESC");
  $result = $stmt->execute();

  $following_count = count($following_users);
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Following</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="input-group mb-3 mt-2">
      <input type="text" class="form-control me-2 ms-2" placeholder="Search user that you follow" id="search-input">
    </div>
    <div class="container-fluid mt-2">
      <h5 class="text-secondary fw-bold ms-2"><i class="bi bi-people-fill"></i> Following</h5>
      <div class="row">
        <?php while($row = $result->fetchArray()) { ?>
          <div class="col-md-3 col-sm-6">
            <a href="artist.php?id=<?php echo $row['id']; ?>" class="opacity-75 btn user-artist btn-secondary mb-2 fw-bold text-start w-100">
              <?php echo $row['artist']; ?>
            </a>
          </div>
        <?php } ?>
      </div>
      <?php
        if($following_count == 0){
          echo "
          <div class='container'>
            <p class='text-secondary fw-bold text-center'>This user is not following any users.</p>
            <p class='text-secondary text-center fw-bold'>Probably because they're not interested to follow anyone... just kidding!</p>
            <img src='icon/Empty.svg' style='width: 100%; height: 100%;'>
          </div>";
        }
      ?> 
    </div>
    <script>
      // Get the search input element
      const searchInput = document.getElementById('search-input');

      // Get all the tag buttons
      const tagButtons = document.querySelectorAll('.user-artist');

      // Add an event listener to the search input field
      searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();

        // Filter the tag buttons based on the search term
        tagButtons.forEach(button => {
          const tag = button.textContent.toLowerCase();

          if (tag.includes(searchTerm)) {
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
