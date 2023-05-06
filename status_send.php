<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Connect to the database
  $db = new SQLite3('database.sqlite');

  // Get the email and message from the form
  $email = $_SESSION['email'];
  $message = $_POST['message'];

  // Sanitize the message
  $message = filter_var($message, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $message = nl2br($message);

  // Insert the status update into the database
  $insert_query = $db->prepare("INSERT INTO status (email, message, date) VALUES (:email, :message, :date)");
  $insert_query->bindValue(':email', $email, SQLITE3_TEXT);
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
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <br><br>
    <div class="container-fluid mt-2">
      <form method="post" action="status_send.php">
        <div class="form-floating mb-3">
          <textarea class="form-control" name="message" placeholder="Enter your status update" id="message" style="height: 400px"></textarea>
          <label class="text-secondary" for="message">Status Update</label>
        </div>
        <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-check-circle-fill"></i> Send</button>
        <a href="status.php" class="btn btn-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Cancel</a>
      </form>
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>