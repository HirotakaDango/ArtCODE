<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Get the artist name from the database
  $username = $_SESSION['username'];
  $stmt = $db->prepare("SELECT artist, pic, `desc`, bgpic FROM users WHERE username = :username");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $artist = $row['artist'];
  $pic = $row['pic'];
  $desc = $row['desc'];
  $bgpic = $row['bgpic'];

  // Count the number of followers
  $stmt = $db->prepare("SELECT COUNT(*) AS num_followers FROM following WHERE following_username = :username");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $num_followers = $row['num_followers'];

  // Count the number of following
  $stmt = $db->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_username = :username");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $num_following = $row['num_following'];

  // Handle the user's click on the "Favorite" button
  if (isset($_POST['favorite'])) {
    $filename = $_POST['filename'];
    $stmt = $db->prepare("UPDATE images SET favorite = 1 WHERE filename = :filename AND username = :username");
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':username', $_SESSION['username']);
    $stmt->execute();
  }

  // Get all of the images uploaded by the current user
  $stmt = $db->prepare("SELECT * FROM images WHERE username = :username ORDER BY id DESC");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();

  // Count the number of images uploaded by the current user
  $count = 0;
  while ($image = $result->fetchArray()) {
    $count++;
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <nav class="navbar fixed-top navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
          <div class="dropdown nav-right">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="setting.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div> 
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="navbarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold">
              <li class="nav-item">
                <a class="nav-link nav-center" href="index.php">
                  <i class="bi bi-house-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Home</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="forum-chat/index.php">
                  <i class="bi bi-chat-left-dots-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Forum</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="imgupload.php">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Uploads</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="popular.php">
                  <i class="bi bi-star-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Popular</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="tags.php">
                  <i class="bi bi-tags-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Tags</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="users.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Users</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="global.php">
                  <i class="bi bi-clock-history fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Recents</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
    <div class="roow mb-2">
      <div class="cool-6 text-center">
        <div class="caard art">
          <div class="col-md-5 order-md-1 mt-3 b-radius" style="background-image: url('<?php echo $bgpic; ?>'); background-size: cover; height: 200px; width: 100%;">
            <img class="img-thumbnail rounded-circle" src="<?php echo $pic; ?>" alt="Profile Picture" style="width: 110px; height: 110px; border-radius: 4px; margin-left: -166px; margin-top: 45px;">
            <a class="btn-sm btn btn-secondary fw-bold float-start mt-2 ms-2 rounded-pill opacity-50" type="button" href="setting.php">change background <i class="bi bi-camera-fill"></i></a>
          </div> 
        </div>
      </div>
      <div class="cool-6 border-down">
        <div class="caard art text-center">
          <h3 class="text-secondary mt-2 fw-bold"><?php echo $artist; ?></h3>
          <p class="text-secondary ms-1 mt-2 fw-bold">
            <?php echo $num_followers ?> followers<span style="padding-right: 10px;"></span>
            <?php echo $num_following ?> following<span style="padding-right: 10px;"></span>
            <?php echo $count; ?> posts
          </p>
          <p class="text-secondary text-center fw-bold"><?php echo $desc; ?></p>
        </div>
      </div>
    </div>
    <div class="images">
      <?php while ($image = $result->fetchArray()): ?>
        <div class="image-container">
          <a href="image.php?filename=<?php echo $image['filename']; ?>">
            <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>">
          </a> 
          <div>
            <form action="delete.php" method="post">
              <input type="hidden" name="filename" value="<?php echo $image['filename']; ?>">
              <input style="margin-top: -73px; margin-left: 8px; font-size: 10px;" class="btn btn-danger fw-bold" type="submit" onclick="return confirm('Are you sure?')" value="Delete">
            </form>
            <button style="margin-top: -450px;" class="btn btn-sm btn-primary fw-bold" onclick="location.href='edit_image.php?id=<?php echo $image['id']; ?>'" ><i class="bi bi-pencil-fill"></i></button> 
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <style>
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

      .image-container {
        margin-bottom: -48px;  
      }
      
      .images {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        grid-gap: 2px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }
 
      .images a {
        display: block;
        border-radius: 4px;
        overflow: hidden;
        border: 2px solid #ccc;
       }
 
      .images img {
        width: 100%;
        height: 200px;
        object-fit: cover;
      }

      .roow {
        display: flex;
        flex-wrap: wrap;
        border-radius: 5px;
        border: 2px solid lightgray;
        margin-right: 10px;
        margin-left: 10px;
        margin-top: 55px;
      }

      .cool-6 {
        width: 50%;
        padding: 0 15px;
      }

      .caard {
        background-color: #fff;
        margin-bottom: 15px;
      }
      
      .b-radius {
        border-radius: 10px;
      }

      .art {
        border-radius: 10px;
      }

      @media (max-width: 768px) {
        .roow {
          border: none;
          margin-right: 0;
          margin-left: 0;
          margin-top: 32px;
        }
        
        .cool-6 {
          width: 100%;
          padding: 0;
        }
        
        .b-radius {
          border-right: none;
          border-left: none;
          border-top: 1px solid lightgray;
          border-bottom: 1px solid lightgray;
          border-radius: 0;
        }
        
        .border-down {
          border-bottom: 2px solid lightgray;
        }
      }
    </style>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        let lazyloadImages;
        if("IntersectionObserver" in window) {
          lazyloadImages = document.querySelectorAll(".lazy-load");
          let imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
              if(entry.isIntersecting) {
                let image = entry.target;
                image.src = image.dataset.src;
                image.classList.remove("lazy-load");
                imageObserver.unobserve(image);
              }
            });
          });
          lazyloadImages.forEach(function(image) {
            imageObserver.observe(image);
          });
        } else {
          let lazyloadThrottleTimeout;
          lazyloadImages = document.querySelectorAll(".lazy-load");

          function lazyload() {
            if(lazyloadThrottleTimeout) {
              clearTimeout(lazyloadThrottleTimeout);
            }
            lazyloadThrottleTimeout = setTimeout(function() {
              let scrollTop = window.pageYOffset;
              lazyloadImages.forEach(function(img) {
                if(img.offsetTop < (window.innerHeight + scrollTop)) {
                  img.src = img.dataset.src;
                  img.classList.remove('lazy-load');
                }
              });
              if(lazyloadImages.length == 0) {
                document.removeEventListener("scroll", lazyload);
                window.removeEventListener("resize", lazyload);
                window.removeEventListener("orientationChange", lazyload);
              }
            }, 20);
          }
          document.addEventListener("scroll", lazyload);
          window.addEventListener("resize", lazyload);
          window.addEventListener("orientationChange", lazyload);
        }
      })
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
