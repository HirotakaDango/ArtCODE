<?php
// Connect to the SQLite database
$db1 = new SQLite3('../database.sqlite');

// Get the artist name from the database
$email1 = $_SESSION['email'];
$stmt1 = $db1->prepare("SELECT id, artist, pic FROM users WHERE email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$pic1 = $row1['pic'];
$artist1 = $row1['artist'];
$user_id1 = $row1['id'];

// Count the number of followers
$stmt1 = $db1->prepare("SELECT COUNT(*) AS num_followers FROM following WHERE following_email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$num_followers1 = $row1['num_followers'];

// Count the number of following
$stmt1 = $db1->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$num_following1 = $row1['num_following'];

// Get all of the images uploaded by the current user
$stmt1 = $db1->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();

// Count the number of images uploaded by the current user
$count1 = 0;
while ($image1 = $result1->fetchArray()) {
  $count1++;
}
  
$fav_result1 = $db1->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}'");
$fav_count1 = $fav_result1->fetchArray()[0];
?>

    <nav class="navbar fixed-top navbar-expand-md navbar-light bg-body-tertiary">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" onclick="goBack()">
          <img src="../icon/back.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="../?by=newest">
          ArtCODE
        </a>
        <div class="dropdown nav-right">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
            <img class="rounded-circle object-fit-cover border border-1" width="32" height="32" src="<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture" style="margin-top: -2px;">
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
            <div class="text-center mb-2">
              <a class="d-block" href="../settings/profile_picture.php"><img class="rounded-circle object-fit-cover border border-5" width="150" height="150" src="<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture"></a>
              <h5 class="fw-bold mt-2 "><?php echo $artist1; ?></h5>
              <p class="text-secondary fw-bold" style="margin-top: -12px;"><small><?php echo $email1; ?></small></p>
            </div>
            <div class="btn-group mt-2 mb-1 container" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-secondary rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'follower.php') echo 'active' ?>" href="../follower.php?id=<?php echo $user_id1; ?>"><i class="bi bi-people-fill"></i> <?php echo $num_followers1 ?> <small>followers</small></a>
              <a class="btn btn-sm btn-outline-secondary ms-1 rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'following.php') echo 'active' ?>" href="../following.php?id=<?php echo $user_id1; ?>"><i class="bi bi-person-fill"></i> <?php echo $num_following1 ?> <small>following</small></a>
            </div>
            <div class="btn-group mb-3 container" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-secondary rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'myworks.php') echo 'active' ?>" href="../myworks.php"><i class="bi bi-images"></i> <?php echo $count1; ?> <small>images</small></a>
              <a class="btn btn-sm btn-outline-secondary ms-1 rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'favorite.php') echo 'active' ?>" href="../favorite.php"><i class="bi bi-heart-fill"></i> <?php echo $fav_count1;?> <small>favorites</small></a> 
            </div>
            <div class="ms-1 me-1">
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="../profile.php">
                  Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'myworks.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="../myworks.php">
                  My Works
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'album.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="../album.php">
                  My Albums
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="../history.php">
                  History
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'setting.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="../setting.php">
                  Settings
                </a>
              </li>
              <hr class="border-3 rounded">
              <?php if(isset($_SESSION['email']) && isset($_COOKIE['token'])): ?>
                <li>
                  <a class="btn btn-danger fw-bold w-100" href="#" data-bs-toggle="modal" data-bs-target="#logOut">
                    Logout
                  </a>
                </li>
              <?php else: ?>
                <li>
                  <a class="btn btn-primary fw-bold w-100 <?php echo (basename($_SERVER['PHP_SELF']) == 'session.php'); ?>" href="../session.php">
                    Signin
                  </a>
                </li>
              <?php endif; ?> 
            </div>
          </ul>
        </div> 
      </div>
    </nav>
    <!-- Modal -->
    <div class="modal fade" id="logOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content rounded-3 shadow">
          <div class="modal-body p-4 text-center">
            <h5 class="mb-0">Do you want to end the session?</h5>
            <p class="mb-0 mt-2">You can always comeback whenever you want later.</p>
          </div>
          <div class="modal-footer flex-nowrap p-0">
            <a class="btn btn-lg btn-link text-danger fs-6 text-decoration-none col-6 py-3 m-0 rounded-0 border-end" href="../logout.php"><strong>Yes, end the session!</strong></a>
            <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel, keep it!</button>
          </div>
        </div>
      </div>
    </div> 
    <style>
      .hover-effect:hover {
        color: white;
        background-color: #6c757d;
        border-radius: 5px;
      }
      
      .text-s {
        color: #6c757d;
      }
 
      .bg-sec {
        background-color: #6c757d;
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
        
        .d-none-sm {
          display: none;
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