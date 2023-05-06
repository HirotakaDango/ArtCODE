<?php
  // Start the session
  session_start();
  
  // Redirect if user is not logged in
  if (!isset($_SESSION['email'])) {
    header("Location: session.php");
    exit;
  }
  
  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');
  
  // Check if the form has been submitted
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the album name from the form data
    $old_album_name = filter_input(INPUT_POST, 'old_album_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $new_album_name = filter_input(INPUT_POST, 'new_album_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    
    // Update the album name in the database
    $stmt = $db->prepare('UPDATE album SET album_name = :new_album_name WHERE email = :email AND album_name = :old_album_name');
    $stmt->bindValue(':email', $_SESSION['email'], SQLITE3_TEXT);
    $stmt->bindValue(':old_album_name', $old_album_name, SQLITE3_TEXT);
    $stmt->bindValue(':new_album_name', $new_album_name, SQLITE3_TEXT);
    $stmt->execute();
    
    // Redirect to the album list page
    header("Location: album.php");
    exit;
  }
  
  // Check if the album name is set in the URL
  if (!isset($_GET['album'])) {
    header("Location: album.php");
    exit;
  }
  
  // Get the album name from the URL
  $album_name = filter_input(INPUT_GET, 'album', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  
  // Get the email of the logged in user
  $email = $_SESSION['email'];
  
  // Check if the album belongs to the logged in user
  $stmt = $db->prepare('SELECT COUNT(*) as count FROM album WHERE email = :email AND album_name = :album_name');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $result = $stmt->execute();
  $row = $result->fetchArray(SQLITE3_ASSOC);
  if ($row['count'] == 0) {
    header("Location: album.php");
    exit;
  }
  
  // Close the database connection
  $db->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Edit Album</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <br><br>
    <div class="container mt-3">
      <h5 class="text-secondary fw-bold text-center">Edit Album</h5>
      <form method="post">
        <div class="form-floating mb-3">
          <input type="text" class="form-control rounded-3 border text-secondary fw-bold border-4" id="new_album_name" name="new_album_name" value="<?php echo $album_name; ?>" maxlength="25">
          <label for="floatingInput" class="text-secondary fw-bold">Update new album name (<?php echo $album_name; ?>)</label>
        </div>
        <div class="container">
          <header class="d-flex justify-content-center py-3">
            <input type="hidden" name="old_album_name" value="<?= $album_name ?>">
            <button type="submit" class="btn btn-primary fw-bold me-1">Save</button> 
            <a class="btn btn-danger fw-bold ms-1" href="album.php">Cancel</a>
          </header>
        </div>
      </form>
    </div>
    <style>
      @media (min-width: 768px) {
        .navbar-nav {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
      
        .nav-center {
          margin-left: 15px;
          margin-right: 15px;
        }

        .width-vw {
          width: 89vw;
        }
        
        .nav-right {
          position: absolute;
          right: 10px;
          top: 10;
          align-items: center;
        }
      }
      
      @media (max-width: 767px) {
        .navbar-brand {
          position: static;
          display: block;
          text-align: center;
          margin: auto;
          transform: none;
        }
        
        .width-vw {
          width: 75vw;
        }

        .navbar-brand {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 18px;
        }
      }
    
      .navbar {
        height: 45px;
      }
      
      .navbar-brand {
        font-size: 18px;
      }

      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-top: -2px;
        margin-left: 8px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      }
    </style> 
    <script>
      function goBack() {
        window.location.href = "album.php";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>