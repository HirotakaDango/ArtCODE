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
    //get input values and limit password length to 40 characters
    $current_password = substr(htmlspecialchars($_POST['current_password']), 0, 40);
    $new_password = substr(htmlspecialchars($_POST['new_password']), 0, 40);
    $confirm_password = substr(htmlspecialchars($_POST['confirm_password']), 0, 40);
    
    //get username from session
    $username = $_SESSION['username'];
    
    //get password from database
    $stmt = $db->prepare('SELECT password FROM users WHERE username=:username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $password = $row['password'];
    
    //check if current password is correct
    if($current_password == $password){
        //check if new password and confirm password match
        if($new_password == $confirm_password){
            //update password in database
            $stmt = $db->prepare('UPDATE users SET password=:new_password WHERE username=:username');
            $stmt->bindValue(':new_password', $new_password, SQLITE3_TEXT);
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->execute();
            
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

    <?php include('setheader.php'); ?>
    <div class="container">
      <div class="text-center mt-4">
        <h3 class="text-secondary fw-bold">Change Password</h3>
      </div>
      <div class="container fw-bold text-secondary mt-4">
        <?php if(isset($error)){ ?>
          <p class="text-danger"><?php echo $error; ?></p>
        <?php } ?>
        <form method="POST">
          <div class="form-floating mb-2">
            <input type="password" class="form-control rounded-3 border text-secondary fw-bold border-4" name="current_password" placeholder="Enter current password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$">
            <label for="floatingPassword" class="text-secondary fw-bold">Enter current password</label>
          </div>
          <div class="form-floating mb-2">
            <input type="password" class="form-control rounded-3 border text-secondary fw-bold border-4" name="new_password" max placeholder="Type new password"length="40" pattern="^[a-zA-Z0-9_@.-]+$">
            <label for="floatingPassword" class="text-secondary fw-bold">Type new password</label>
          </div>
          <div class="form-floating mb-2">
            <input type="password" class="form-control rounded-3 border text-secondary fw-bold border-4" name="confirm_password" placeholder="Confirm new password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$">
            <label for="floatingPassword" class="text-secondary fw-bold">Confirm new password</label>
          </div>
          <a class="text-decoration-none fw-bold text-primary mb-2" href="setsupport.php">Having a trouble?</a>
          <div class="container">
            <header class="d-flex justify-content-center py-3">
              <ul class="nav nav-pills">
                <li class="nav-item"><button type="submit" class="btn btn-primary me-1 fw-bold" name="submit">Save</button></li>
                <li class="nav-item d-md-none"><a href="setting.php" class="btn btn-danger ms-1 fw-bold">Back</a></li>
              </ul>
            </header>
          </div>
        </form>
      <div>
    </div>
    <?php include('end.php'); ?>