<?php
session_start();

class Settings {
  private $db;

  function __construct() {
    // Connect to the SQLite database
    $this->db = new SQLite3('database.sqlite');
  }

  function checkLoggedIn() {
    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
      header("Location: session.php");
      exit;
    }
  }

  function updateArtist() {
    // Check if the form was submitted
    if (isset($_POST['submit'])) {
      $username = $_SESSION['username'];
      $artist = htmlspecialchars($_POST['artist']);

      // Update the user's artist name in the database
      $stmt = $this->db->prepare("UPDATE users SET artist = :artist WHERE username = :username");
      $stmt->bindValue(':username', $username);
      $stmt->bindValue(':artist', $artist);
      $stmt->execute();

      // Store the new artist name in the session for future use
      $_SESSION['artist'] = $artist;

      // Redirect the user to the homepage
      header("Location: yourname.php");
      exit;
    }
  }

  function getCurrentArtist() {
    // Get the user's current artist name from the database
    $username = $_SESSION['username'];
    $stmt = $this->db->prepare("SELECT artist FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username);
    $result = $stmt->execute();
    $user = $result->fetchArray();
    $artist = htmlspecialchars($user['artist']);
    return $artist;
  }
}

$settings = new Settings();
$settings->checkLoggedIn();
$settings->updateArtist();
$artist = $settings->getCurrentArtist();

?>

    <?php include('setheader.php'); ?>
    <div class="container">
      <h3 class="text-secondary text-center mt-4 fw-bold"><i class="bi bi-gear"></i> Change Your Name</h3>

      <form method="post" action="yourname.php">
        <div class="mb-3">
          <label for="artist" class="form-label text-secondary fw-bold">Your Name: <?php echo $artist; ?></label>
          <input type="text" class="form-control" id="artist" name="artist" value="<?php echo $artist; ?>" maxlength="40">
        </div>
        <div class="container">
          <header class="d-flex justify-content-center py-3">
            <ul class="nav nav-pills">
              <li class="nav-item"><button type="submit" class="btn btn-primary me-1 fw-bold" name="submit">Save</button></li>
              <li class="nav-item"><a href="setting.php" class="btn btn-danger ms-1 fw-bold">Back</a></li>
            </ul>
          </header>
        </div>
      </form>
    </div>
    <?php include('end.php'); ?>