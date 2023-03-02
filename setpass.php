<?php
//start session
session_start();

//connect to sqlite database
$db = new SQLite3('database.sqlite');

//check if user is logged in
if(!isset($_SESSION['username'])){
    header("Location: session.php");
    exit();
}

//check if form is submitted
if(isset($_POST['submit'])){
    //get input values
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    //get username from session
    $username = $_SESSION['username'];
    
    //get password from database
    $query = "SELECT password FROM users WHERE username='$username'";
    $result = $db->query($query);
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $password = $row['password'];
    
    //check if current password is correct
    if($current_password == $password){
        //check if new password and confirm password match
        if($new_password == $confirm_password){
            //update password in database
            $query = "UPDATE users SET password='$new_password' WHERE username='$username'";
            $db->query($query);
            
            //redirect to profile page
            header("Location: setting.php");
            exit();
        } else {
            //display error message
            $error = "New password and confirm password do not match";
        }
    } else {
        //display error message
        $error = "Current password is incorrect";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <title>Change Password</title>
</head>
<body>
    <div class="container">
      <div class="text-center mt-4">
        <h3 class="text-secondary fw-bold">Change Password</h3>
      </div>
      <div class="container fw-bold text-secondary mt-4">
        <?php if(isset($error)){ ?>
            <p class="text-danger"><?php echo $error; ?></p>
        <?php } ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Current Password:</label>
                <input type="password" class="form-control" name="current_password">
            </div>
            <div class="mb-3">
                <label class="form-label">New Password:</label>
                <input type="password" class="form-control" name="new_password">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password:</label>
                <input type="password" class="form-control" name="confirm_password">
            </div>
            <div class="container">
              <header class="d-flex justify-content-center py-3">
                <ul class="nav nav-pills">
                  <li class="nav-item"><button type="submit" class="btn btn-primary me-1 fw-bold" name="submit">Save</button></li>
                  <li class="nav-item"><a href="setting.php" class="btn btn-danger ms-1 fw-bold">Back</a></li>
                </ul>
              </header>
            </div>
        </form>
      <div>
    </div>
</body>
</html> 