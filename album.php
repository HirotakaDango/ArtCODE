<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: session.php');
  exit();
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Create the album table if it doesn't exist 
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS image_album (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER NOT NULL, email TEXT NOT NULL, album_id INTEGER NOT NULL, FOREIGN KEY (image_id) REFERENCES image(id), FOREIGN KEY (album_id) REFERENCES album(id));');
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS album ( id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, album_name TEXT NOT NULL);');
$stmt->execute();

// Check if an image has been added to the album
if (isset($_GET['add'])) {
  $image_id = intval($_GET['add']);
  $email = $_SESSION['email'];
  $stmt = $db->prepare('INSERT INTO album (email, image_id) VALUES (:email, :image_id)');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
  $stmt->execute();

  // Store success message in session
  $_SESSION['success_message'] = 'New album added';

  // Redirect to current page to prevent duplicate form submissions
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}

// Check if a new album name has been submitted
if (isset($_POST['album_name']) && !empty($_POST['album_name'])) {
  $album_name = filter_input(INPUT_POST, 'album_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $email = $_SESSION['email'];
  $stmt = $db->prepare('INSERT INTO album (email, album_name) VALUES (:email, :album_name)');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $stmt->execute();

  // Store success message in session
  $_SESSION['success_message'] = 'New album added';

  // Redirect to current page to prevent duplicate form submissions
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}

// Check if an album has been deleted
if (isset($_POST['delete_album'])) {
  $album_name = filter_input(INPUT_POST, 'delete_album', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $email = $_SESSION['email'];
  $stmt = $db->prepare('DELETE FROM album WHERE email = :email AND album_name = :album_name');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $stmt->execute();

  // Store danger message in session
  $_SESSION['danger_message'] = 'Album has been deleted';

  // Redirect to current page to prevent duplicate form submissions
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}

// Close the database connection
$db->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>My Album</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="text-secondary fw-bold text-center mt-2"><i class="bi bi-images"></i> Create New Album</h5>
    <form method="post" class="container">
      <div class="form-floating mb-2">
        <input type="text" class="form-control rounded-3 border text-secondary fw-bold border-4" id="album_name" name="album_name" maxlength="25">
        <label for="floatingInput" class="text-secondary fw-bold">Create new album</label>
      </div>
      <input class="form-control bg-primary text-white fw-bold" type="submit" value="Create">
    </form>
   
    <!-- Display alerts -->
    <div class="container mt-3">
      <?php if (isset($_SESSION['success_message'])) { ?>
        <div class="alert alert-success" role="alert">
          <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php } ?> 

      <?php if (isset($_SESSION['danger_message'])) { ?>
        <div class="alert alert-danger" role="alert">
          <?= $_SESSION['danger_message'] ?>
        </div>
        <?php unset($_SESSION['danger_message']); ?>
      <?php } ?>
    </div>
    
    <h5 class="text-secondary fw-bold text-center mt-4 mb-3"><i class="bi bi-images"></i> My Albums</h5>
    <?php
      // Connect to the SQLite database
      $db = new SQLite3('database.sqlite');

       // Display the album list
       $email = $_SESSION['email'];
       $stmt = $db->prepare('SELECT DISTINCT id, album_name FROM album WHERE email = :email ORDER BY id DESC');
       $stmt->bindValue(':email', $email, SQLITE3_TEXT);
       $results = $stmt->execute();
    ?>
      <div class="container-fluid text-center">
        <div class="row">
          <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $album_name = $row['album_name'];
            $album_id = $row['id'];
            // Check if the album name is not null before calling urlencode()
            if ($album_name) {
            // Output the HTML for each album
          ?>
            <div class="col-6 col-md-3 col-lg-3 mb-4">
              <div class="card border-4 h-100">
                <a class="border-bottom display-1 mt-1" href="album_images.php?album=<?= urlencode($album_id) ?>"><i class="bi bi-images text-secondary"></i></a>
                <div class="dropdown-center">
                  <a class="form-control border-0 text-decoration-none opacity-50 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
                    <small class="fw-bold" style="word-wrap: break-all;"><?= substr($album_name, 0, 13) ?></small>
                  </a>
                  <ul class="dropdown-menu container mt-2">
                    <header class="d-flex justify-content-center">
                      <form method="post">
                        <input type="hidden" name="delete_album" value="<?= $album_name ?>">
                        <button class="btn button-w btn-sm btn-secondary opacity-50 me-1 fw-bold" onclick="return confirm('Are you sure?')" type="submit"><i class="bi bi-trash-fill"></i></button>
                      </form> 
                      <a class="btn button-w btn-sm btn-secondary opacity-50 fw-bold" href="edit_album.php?album=<?php echo $album_name; ?>"><i class="bi bi-pencil-fill"></i></a>
                    </header>
                  </ul>
                </div>
              </div>
            </div>
          <?php }
          } ?>
        </div>
      </div>
    <?php
      // Close the database connection
      $db->close();
    ?>
    <style>
      .button-w {
        width: 67px;
      }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>