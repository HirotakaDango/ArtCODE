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
  $stmt = $db->prepare("SELECT id, artist, pic, `desc`, bgpic FROM users WHERE username = :username");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $user_id = $row['id'];
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

  // Process any favorite/unfavorite requests
  if (isset($_POST['favorite'])) {
    $image_id = $_POST['image_id'];

    // Check if the image has already been favorited by the current user
    $existing_fav = $db->query("SELECT COUNT(*) FROM favorites WHERE username = '{$_SESSION['username']}' AND image_id = $image_id")->fetchArray()[0];

    if ($existing_fav == 0) {
      $db->exec("INSERT INTO favorites (username, image_id) VALUES ('{$_SESSION['username']}', $image_id)");
    }

    // Redirect to the same page to prevent duplicate form submissions
    header("Location: profile.php");
    exit();

  } elseif (isset($_POST['unfavorite'])) {
    $image_id = $_POST['image_id'];
    $db->exec("DELETE FROM favorites WHERE username = '{$_SESSION['username']}' AND image_id = $image_id");

    // Redirect to the same page to prevent duplicate form submissions
    header("Location: profile.php");
    exit();
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
    <?php include('header.php'); ?>
    <div class="roow mb-2">
      <div class="cool-6 text-center">
        <div class="caard art">
          <div class="col-md-5 order-md-1 mt-3 b-radius" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "default_bg_thumbnail.jpg"; ?>'); background-size: cover; height: 200px; width: 100%;">
            <img class="img-thumbnail rounded-circle text-secondary" src="<?php echo !empty($pic) ? $pic : "icon/profile.svg"; ?>" alt="Profile Picture" style="object-fit: cover; width: 110px; height: 110px; border-radius: 4px; margin-left: -166px; margin-top: 45px;">
            <a class="btn-sm btn btn-secondary fw-bold float-start mt-2 ms-2 rounded-pill opacity-50" type="button" href="setting.php">change background <i class="bi bi-camera-fill"></i></a>
          </div>
        </div>
      </div>
      <div class="cool-6 border-down">
        <div class="caard art text-center">
          <h3 class="text-secondary mt-2 fw-bold"><?php echo $artist; ?></h3>
          <p class="text-secondary ms-1 mt-2 fw-bold">
            <a class="text-secondary" href="follower.php?id=<?php echo $user_id; ?>"><?php echo $num_followers ?> followers</a><span style="padding-right: 10px;"></span>
            <a class="text-secondary" href="following.php?id=<?php echo $user_id; ?>"><?php echo $num_following ?> following</a><span style="padding-right: 10px;"></span>
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
              <button class="p-b1 btn btn-sm btn-dark opacity-50 fw-bold" type="submit" onclick="return confirm('Are you sure?')" value="Delete"><i class="bi bi-trash-fill"></i>
            </form>
            <button class="p-b2 btn btn-sm btn-dark opacity-50 fw-bold" onclick="location.href='edit_image.php?id=<?php echo $image['id']; ?>'" ><i class="bi bi-pencil-fill"></i></button> 
            <?php
              $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = {$image['id']}");
              if ($is_favorited) {
            ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm btn-dark opacity-50 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm btn-dark opacity-50 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
              </form>
            <?php } ?> 
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <style>
      @media (min-width: 769px) {
        .p-b1 {
          margin-top: -393px;
          margin-left: 5px;
          border-radius: 4px 4px 4px 4px;
        }
        
        .p-b2 {
          margin-top: -177px; 
          margin-left: 5px;
          border-radius: 4px 4px 0 0;
        }
        
        .p-b3 {
          margin-top: -163px; 
          margin-left: 5px;
          border-radius: 0 0 4px 4px; 
        } 
        
        .image-container {
          margin-bottom: -71px;  
        }
      }
      
      @media (max-width: 767px) {
        .p-b1 {
          margin-top: -192px;
          margin-left: 5px;
          border-radius: 4px 4px 0 0;
        }
        
        .p-b2 {
          margin-top: -178px; 
          margin-left: 5px;
          border-radius: 0 0 0 0;
        }
        
        .p-b3 {
          margin-top: -164px;
          margin-left: 5px;
          border-radius: 0 0 4px 4px; 
        }
        
        .image-container {
          margin-bottom: -72px;  
        }
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
        margin-top: 10px;
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
          margin-top: -15px;
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
