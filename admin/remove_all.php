<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../admin/access.php');
    exit;
}

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

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>User List</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>
    <ul class="me-2 ms-2 mt-2 nav nav-pills nav-fill justify-content-center">
      <li class="nav-item"><a class="nav-link" href="../admin/index.php"><i class="bi bi-house-fill"></i></a></li>
      <li class="nav-item"><a class="nav-link" aria-current="page" href="../admin/edit_users.php"><i class="bi bi-person-fill-gear"></i></a></li>
      <li class="nav-item"><a class="nav-link" href="../admin/remove_images.php"><i class="bi bi-images"></i></a></li> 
      <li class="nav-item"><a class="nav-link active" href="../admin/remove_all.php"><i class="bi bi-person-fill-exclamation"></i></a></li>
    </ul> 
	<div class="container">
	    <br>
        <center><span class="bg-danger fw-bold text-white rounded fs-2 py-2 px-2 mt-5">DANGER ZONE</span></center>
		<div class="row mt-3">
			<?php foreach($users as $user): ?>
			<div class="col-md-4 mb-2">
				<div class="card">
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
</body>
</html>
