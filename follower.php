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

  // Get the list of users that the logged in user is followers
  $stmt = $db->prepare("SELECT * FROM following WHERE following_email = :email");
  $stmt->bindValue(':email', $email);
  $result = $stmt->execute();
  $follower_users = array();
  while($row = $result->fetchArray()){
    $follower_users[] = $row['follower_email'];
  }

  // Get the details of the following users
  $stmt = $db->prepare("SELECT * FROM users WHERE email IN ('" . implode("','", $follower_users) . "')");
  $result = $stmt->execute();

  $follower_count = count($follower_users);
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Followers</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('bootstrapcss.php'); ?>
    <style>
      .tag-buttons {
        display: flex;
        flex-wrap: wrap;
      }
    
      .tag-button {
        display: inline-block;
        padding: 6px 8px;
        margin: 4px;
        background-color: #eee;
        color: #333;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        line-height: 1;
        font-weight: 800;
      }
    
      .tag-button:hover {
        background-color: #ccc;
      }
    
      .tag-button:active {
        background-color: #aaa;
      }

    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid mt-2">
      <h5 class="text-secondary fw-bold ms-2"><i class="bi bi-people-fill"></i> Followers</h5> 
      <div class="tag-buttons">
        <?php
          while($row = $result->fetchArray()){
            echo "<a href='artist.php?id=".$row['id']."' class='tag-button btn'>".$row['artist']."</a>";
          }
        ?>
      </div>
      <?php
        if($follower_count == 0){
          echo "
          <div class='container'>
            <p class='text-secondary fw-bold text-center'>This user is not followed by any users.</p>
            <p class='text-secondary text-center fw-bold'>Probably because they're not famous... just kidding!</p>
            <img src='icon/Empty.svg' style='width: 100%; height: 100%;'>
          </div>";
        }
      ?>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>

