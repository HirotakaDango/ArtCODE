<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Connect to the database
  $db = new SQLite3('database.sqlite');

  // Get the username and message from the form
  $username = $_SESSION['username'];
  $message = $_POST['message'];

  // Insert the status update into the database
  $insert_query = $db->prepare("INSERT INTO status (username, message, date) VALUES (:username, :message, :date)");
  $insert_query->bindValue(':username', $username, SQLITE3_TEXT);
  $insert_query->bindValue(':message', $message, SQLITE3_TEXT);
  $insert_query->bindValue(':date', date('Y-m-d'), SQLITE3_TEXT);
  $insert_query->execute();

  // Redirect the user to status.php
  header("Location: status.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Status</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body>
    <nav class="navbar fixed-top navbar-expand-md navbar-light bg-white shadow-sm shadow-md shadow-lg">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" onclick="goBack()">
          <img src="icon/back.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
        <div class="dropdown nav-right">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-5"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="setting.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
          </ul>
        </div> 
      </div>
    </nav>
    <br><br>
    <div class="container mt-2">
      <div class="row">
        <div class="col-md-6 mx-auto mt-4">
          <form method="post" action="status_send.php">
            <div class="form-floating mb-3">
              <textarea class="form-control" name="message" placeholder="Enter your status update" id="message" style="height: 100px"></textarea>
              <label class="text-secondary" for="message">Status Update</label>
            </div>
            <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-check-circle-fill"></i> Send</button>
            <a href="status.php" class="btn btn-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Cancel</a>
          </form>
        </div>
      </div>
    </div>
    <style>
      .comment-buttons {
        position: absolute;
        top: 0;
        right: 0;
      }

      .comment-buttons button {
        margin-left: 5px; /* optional: add some margin between the buttons */
      }

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
        window.location.href = "status.php";
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>