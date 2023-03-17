<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

$db = new SQLite3('database.sqlite');

if(isset($_GET['id'])){
    $user_id = $_GET['id'];
    
    // Get the logged in user's username
    $stmt = $db->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->bindValue(':id', $user_id);
    $result = $stmt->execute();
    $row = $result->fetchArray();
    $username = $row['username'];

    // Get the list of users that the logged in user is following
    $stmt = $db->prepare("SELECT * FROM following WHERE follower_username = :username");
    $stmt->bindValue(':username', $username);
    $result = $stmt->execute();
    $following_users = array();
    while($row = $result->fetchArray()){
        $following_users[] = $row['following_username'];
    }

    // Get the details of the following users
    $stmt = $db->prepare("SELECT * FROM users WHERE username IN ('" . implode("','", $following_users) . "')");
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <style>
      .tag-buttons {
        display: flex;
        flex-wrap: wrap;
      }
    
      .tag-button {
        display: inline-block;
        padding: 6px 12px;
        margin: 6px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        line-height: 1;
        font-weight: 800;
        background-color: #eee;
        color: #333;
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
          <h5 class="text-secondary fw-bold ms-2"><i class="bi bi-people-fill"></i> Following</h5>
          <div class="tag-buttons">
            <?php
              while($row = $result->fetchArray()){
                echo "<a href='artist.php?id=".$row['id']."' class='tag-button'>".$row['artist']."</a>";
              }
              ?>
          </div>
            <?php
              if($following_count == 0){
                echo "<p>This user is not following any users.</p>";
            }
            ?>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
