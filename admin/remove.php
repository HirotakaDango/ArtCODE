<?php
// redirect to login page if not logged in


// create a PDO connection to the SQLite database
$db = new PDO('sqlite:../database.sqlite');

// query to get all users and their image count
$query = 'SELECT u.id, u.username, COUNT(i.id) as image_count
          FROM users u LEFT JOIN images i ON u.username = i.username
          GROUP BY u.id';

// execute the query
$stmt = $db->query($query);

// handle user removal
if (isset($_POST['remove_user'])) {
    $user_id = $_POST['remove_user'];

    // begin a transaction to ensure data consistency
    $db->beginTransaction();

    // remove the user from the 'users' table and their related data from other tables
    $stmt = $db->prepare('DELETE FROM users 
                          WHERE id = :user_id;
                          DELETE FROM images 
                          WHERE username = (SELECT username FROM users WHERE id = :user_id);
                          DELETE FROM favorites 
                          WHERE image_id IN (SELECT id FROM images WHERE username = (SELECT username FROM users WHERE id = :user_id))');
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // commit the transaction
    $db->commit();

    header('Location: remove.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Danger Zone</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>
   <ul class="me-2 ms-2 mt-2 nav nav-pills nav-fill justify-content-center">
      <li class="nav-item"><a class="nav-link" aria-current="page" href="../admin/index.php"><i class="bi bi-person-fill-gear"></i></a></li>
      <li class="nav-item"><a class="nav-link active" href="../admin/remove.php"><i class="bi bi-person-fill-exclamation"></i></a></li>
    </ul>
    <div class="container">
        <br>
        <center><span class="bg-danger fw-bold text-white rounded fs-2 py-2 px-2">DANGER ZONE</span></center>
        <div class="row row-cols-1 row-cols-md-3 g-4 mt-3">
            <?php while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">user: <?php echo $user['username']; ?></h5>
                            <p class="card-text"><?php echo $user['image_count']; ?> images</p>
                            <form method="post">
                                <input type="hidden" name="remove_user" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <br>
    </div>
</body>
</html>
