<?php
require_once('prompt.php'); 

// database connection
$pdo = new PDO('sqlite:../database.sqlite');

// get the username to remove
if(isset($_GET['remove_user'])){
  $username = $_GET['remove_user'];

  // delete user's images
  $stmt = $pdo->prepare("DELETE FROM images WHERE username=:username");
  $stmt->execute(['username' => $username]);

  // delete user's favorites
  $stmt = $pdo->prepare("DELETE FROM favorites WHERE username=:username");
  $stmt->execute(['username' => $username]);

  // delete user's following
  $stmt = $pdo->prepare("DELETE FROM following WHERE follower_username=:username OR following_username=:username");
  $stmt->execute(['username' => $username]);

  // delete user
  $stmt = $pdo->prepare("DELETE FROM users WHERE username=:username");
  $stmt->execute(['username' => $username]);
}

// get all users
$stmt = $pdo->query("SELECT * FROM users");

// count the images for each user
$users = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
  $user = $row['username'];
  $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM images WHERE username=:username");
  $stmt2->execute(['username' => $user]);
  $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
  $users[] = [
    'id' => $row['id'],
    'username' => $user,
    'artist' => $row['artist'],
    'pic' => $row['pic'],
    'desc' => $row['desc'],
    'bgpic' => $row['bgpic'],
    'image_count' => $row2['count']
  ];
}
?>

    <?php include('admin_header.php'); ?>
    <div class="container">
      <br>
      <center><span class="bg-danger fw-bold text-white rounded fs-2 py-2 px-2 mt-5">DANGER ZONE</span></center>
      <div class="row mt-3">
        <?php foreach($users as $user): ?>
          <div class="col-md-4 mb-2">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="card-title"><?php echo $user['username']; ?></h5>
                <p class="card-text"><?php echo $user['desc']; ?></p>
                <p class="card-text">Images: <?php echo $user['image_count']; ?></p>
                <a href="?remove_user=<?php echo $user['username']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i> Remove</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php include('end.php'); ?>