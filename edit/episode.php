<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new SQLite3('../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

// Get the email of the logged-in user
$email = $_SESSION['email'];

// Get the current image ID from the URL parameter
$currentImageId = $_GET['id'] ?? '';

// Retrieve image details
if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Retrieve the email of the logged-in user
  $email = $_SESSION['email'];

  // Select the image details using the image ID and the email of the logged-in user
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id AND email = :email');
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
  $result = $stmt->execute();
  $image = $result->fetchArray(SQLITE3_ASSOC); // Retrieve result as an associative array

  // Check if the image exists and belongs to the logged-in user
  if (!$image) {
    echo '<meta charset="UTF-8"> 
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">
         ';
    exit();
  }
} else {
  // Redirect to the error page if the image ID is not specified
  header('Location: ?id=' . $id);
  exit();
}

// Handle add new episode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_episode_name'])) {
  $new_episode_name = trim($_POST['new_episode_name']);
  if (!empty($new_episode_name)) {
    // Check if the episode_name already exists in the episode table
    $stmt = $db->prepare('SELECT * FROM episode WHERE email = :email AND episode_name = :episode_name');
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':episode_name', $new_episode_name);
    $result = $stmt->execute();
    $existingEpisode = $result->fetchArray(SQLITE3_ASSOC);
    // If episode_name doesn't exist, insert it into the episode table
    if (!$existingEpisode) {
      $stmt = $db->prepare('INSERT INTO episode (email, episode_name) VALUES (:email, :episode_name)');
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':episode_name', $new_episode_name);
      $stmt->execute();
    }
  }

  // Redirect to the current episode.php page with the image ID
  header("Location: episode.php?id=" . $currentImageId);
  exit;
}

// Handle delete episode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_episode_id'])) {
  $delete_episode_id = $_POST['delete_episode_id'];
  $stmt = $db->prepare('DELETE FROM episode WHERE id = :id AND email = :email');
  $stmt->bindParam(':id', $delete_episode_id);
  $stmt->bindParam(':email', $email);
  $stmt->execute();

  // Redirect to the current episode.php page with the image ID
  header("Location: episode.php?id=" . $currentImageId);
  exit;
}

// Handle edit episode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_episode_id']) && isset($_POST['edit_episode_name'])) {
  $edit_episode_id = $_POST['edit_episode_id'];
  $edit_episode_name = trim($_POST['edit_episode_name']);
  if (!empty($edit_episode_name)) {
    $stmt = $db->prepare('UPDATE episode SET episode_name = :episode_name WHERE id = :id AND email = :email');
    $stmt->bindParam(':episode_name', $edit_episode_name);
    $stmt->bindParam(':id', $edit_episode_id);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
  }

  // Redirect to the current episode.php page with the image ID
  header("Location: episode.php?id=" . $currentImageId);
  exit;
}

// Retrieve all episodes for the logged-in user
$stmt = $db->prepare('SELECT * FROM episode WHERE email = :email ORDER BY id DESC');
$stmt->bindParam(':email', $email);
$results = $stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Episode</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <div class="container-fluid mt-5">
      <?php include('nav.php'); ?>
      <form method="POST" class="mt-4">
        <div class="form-floating mb-2">
          <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" name="new_episode_name" id="new_episode_name" placeholder="Add episode name" maxlength="500" required>  
          <label for="floatingInput" class="text-dark fw-bold">Add episode name</label>
          <button type="submit" class="btn btn-primary fw-bold mt-2 w-100">Add Episode</button>
        </div>
      </form>
      <h5 class="text-center fw-bold my-4">All Episodes</h5>
      <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
        <div class="d-flex justify-content-between align-items-center rounded-4 bg-body-tertiary shadow my-2 p-4">
          <h5 class="link-body-emphasis text-decoration-none text-start w-100 fw-bold border-0">
            <?php echo $row['episode_name']; ?>
          </h5>
          <div class="dropdown dropdown-menu-end">
            <button class="text-decoration-none btn fw-bold border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
            <ul class="dropdown-menu rounded-4">
              <li><button class="dropdown-item fw-medium edit-episode-btn" data-bs-toggle="modal" data-bs-target="#editEpisodeModal" data-episode-id="<?php echo $row['id']; ?>" data-episode-name="<?php echo $row['episode_name']; ?>"><i class="bi bi-pencil-fill"></i> edit</button></li>
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this episode?');">
                <input type="hidden" name="delete_episode_id" value="<?php echo $row['id']; ?>">
                <li><button class="dropdown-item fw-medium" type="submit"><i class="bi bi-trash-fill"></i> delete</button></li>
              </form>
            </ul>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- Edit Episode Modal -->
    <div class="modal fade" id="editEpisodeModal" tabindex="-1" aria-labelledby="editEpisodeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header border-0">
            <h5 class="modal-title" id="editEpisodeModalLabel">Edit Episode Name</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST">
            <div class="modal-body">
              <input type="hidden" name="edit_episode_id" id="edit_episode_id">
              <div class="form-floating">
                <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" name="edit_episode_name" id="edit_episode_name" placeholder="Edit episode name" maxlength="500" required>
                <label for="edit_episode_name" class="text-dark fw-bold">Edit episode name</label>
              </div>
            </div>
            <div class="btn-group w-100 pb-3 px-3 gap-3">
              <button type="button" class="btn btn-secondary fw-bold w-50 rounded" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary fw-bold w-50 rounded">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="mt-5"></div>
    <?php include('bootstrapjs.php'); ?>

    <script>
      // Populate the edit episode modal with episode data
      document.querySelectorAll('.edit-episode-btn').forEach(function(button) {
        button.addEventListener('click', function() {
          var episodeId = this.getAttribute('data-episode-id');
          var episodeName = this.getAttribute('data-episode-name');
          document.getElementById('edit_episode_id').value = episodeId;
          document.getElementById('edit_episode_name').value = episodeName;
        });
      });
    </script>
  </body>
</html>