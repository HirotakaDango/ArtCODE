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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
    <div class="container mt-2">
      <div class="row">
        <div class="col-md-6">
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
                echo "<p>This user is not followed by any users.</p>";
            }
            ?>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>

