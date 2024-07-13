<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Get the artist information from the database
$email = $_SESSION['email'];
try {
  $stmt = $db->prepare("SELECT id, artist, pic, `desc`, bgpic, twitter, pixiv, other, region, joined, born, message_1, message_2, message_3, message_4 FROM users WHERE email = :email");
  $stmt->bindValue(':email', $email);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the user exists
  if (!$row) {
    die("User not found");
  }

  $user_id = $row['id'];
  $artist = $row['artist'];
  $pic = $row['pic'];
  $desc = $row['desc'];
  $bgpic = $row['bgpic'];
  $twitter = $row['twitter'];
  $pixiv = $row['pixiv'];
  $other = $row['other'];
  $region = $row['region'];
  $joined = $row['joined'];
  $born = $row['born'];
  $message_1 = $row['message_1'];
  $message_2 = $row['message_2'];
  $message_3 = $row['message_3'];
  $message_4 = $row['message_4'];
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}

// Function to format numbers
function formatNumber($num) {
  if ($num >= 1000000) {
    return round($num / 1000000, 1) . 'm';
  } elseif ($num >= 100000) {
    return round($num / 1000) . 'k';
  } elseif ($num >= 10000) {
    return round($num / 1000, 1) . 'k';
  } elseif ($num >= 1000) {
    return round($num / 1000) . 'k';
  } else {
    return $num;
  }
}

// Count the number of followers
$stmt = $db->prepare("SELECT COUNT(*) AS num_followers FROM following WHERE following_email = :email");
$stmt->bindValue(':email', $email);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$num_followers = $row['num_followers'];

// Count the number of following
$stmt = $db->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_email = :email");
$stmt->bindValue(':email', $email);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$num_following = $row['num_following'];

// Format the numbers
$formatted_followers = formatNumber($num_followers);
$formatted_following = formatNumber($num_following);

// Process any favorite/unfavorite requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['favorite']) && isset($_POST['image_id'])) {
    $image_id = $_POST['image_id'];

    // Check if the image has already been favorited by the current user
    $stmt = $db->prepare("SELECT COUNT(*) AS num_favorites FROM favorites WHERE email = :email AND image_id = :image_id");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':image_id', $image_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_favorites = $row['num_favorites'];

    if ($num_favorites == 0) {
      $stmt = $db->prepare("INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)");
      $stmt->bindValue(':email', $email);
      $stmt->bindValue(':image_id', $image_id);
      $stmt->execute();
    }

    // Redirect to the current page to prevent duplicate form submissions
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit();
  } elseif (isset($_POST['unfavorite']) && isset($_POST['image_id'])) {
    $image_id = $_POST['image_id'];
    $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':image_id', $image_id);
    $stmt->execute();

    // Redirect to the current page to prevent duplicate form submissions
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit();
  }
}

// Get all of the images uploaded by the current user
$stmtI = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
$stmtI->bindValue(':email', $email);
$stmtI->execute();
$images = $stmtI->fetchAll(PDO::FETCH_ASSOC);

// Count the number of images uploaded by the current user
$count = count($images);

// Count the number of favorites
$stmtFav = $db->prepare("SELECT COUNT(*) AS num_favorites FROM favorites WHERE email = :email");
$stmtFav->bindValue(':email', $email);
$stmtFav->execute();
$fav_row = $stmtFav->fetch(PDO::FETCH_ASSOC);
$fav_count = $fav_row['num_favorites'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="../image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../style.css">
  </head>
  <body>
    <?php include('../contents/header.php'); ?>
    
    <!-- Porfile Header -->
    <div class="mt-2 vh-100 d-none d-md-block d-lg-block position-relative" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; height: 100%; width: 100%;">
      <a class="btn-sm btn btn-dark fw-bold rounded-pill opacity-75 position-absolute bottom-0 start-0 m-2" type="button" href="/settings/background.php">change background <i class="bi bi-camera-fill"></i></a>
      <button class="btn btn-sm btn-dark opacity-75 rounded-3 position-absolute bottom-0 end-0 m-1" data-bs-toggle="modal" data-bs-target="#modalUserInfo"><i class="bi bi-info-circle-fill"></i></button>
    </div>
    <div class="container-fluid d-none d-md-block d-lg-block mt-3">
      <div class="row">
        <div class="col-md-2 d-flex align-item-center">
          <div class="card border-0">
            <img class="img-thumbnail border-0 shadow text-center rounded-circle mt-3 mx-3 object-fit-cover" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
            <div class="card-body">
              <h6 class="text-center"><span class="badge bg-secondary fw-medium rounded-pill"><?php echo $region; ?></span></h6>
            </div>
          </div>
        </div>
        <div class="col-md-7 d-flex align-items-center">
          <div>
            <h1 class="fw-bold d-none d-md-block d-lg-block mt-2"><?php echo $artist; ?></h1>
            <div>
              <span class="me-4"><a class="btn border-0 fw-medium" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $count; ?> <small> Images</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../list_favorite.php?id=<?php echo $user_id; ?>"> <?php echo $fav_count;?> <small> Favorites</small></a></span>
            </div>
            <div>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../follower.php?id=<?php echo $user_id; ?>"> <?php echo $num_followers ?> <small>Followers</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../following.php?id=<?php echo $user_id; ?>"> <?php echo $num_following ?> <small>Following</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../album.php"><small>My Albums</small></a></span>
              <span class="me-4"><button class="btn border-0 fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#shareUser"><small>Shares</small></button></span>
            </div>
            
            <p class="mt-4 ms-3 fw-medium">
              <small>
                <?php
                  if (!empty($desc)) {
                    $messageText = $desc;
                    $messageTextWithoutTags = strip_tags($messageText);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $url = htmlspecialchars($matches[0]);
                      return '<a href="' . $url . '">' . $url . '</a>';
                    }, $messageTextWithoutTags);

                    $charLimit = 100; // Set your character limit

                    if (strlen($formattedText) > $charLimit) {
                      $limitedText = substr($formattedText, 0, $charLimit);
                      echo '<span id="limitedText">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                      echo '<span id="more" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                      echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction()" id="myBtn">read more</button>';
                    } else {
                      // If the text is within the character limit, just display it with line breaks.
                      echo nl2br($formattedText);
                    }
                  } else {
                    echo "User description is empty.";
                  }
                ?>
              </small>
            </p>

            <script>
              function myFunction() {
                var dots = document.getElementById("limitedText");
                var moreText = document.getElementById("more");
                var btnText = document.getElementById("myBtn");

                if (moreText.style.display === "none") {
                  dots.style.display = "none";
                  moreText.style.display = "inline";
                  btnText.innerHTML = "read less";
                } else {
                  dots.style.display = "inline";
                  moreText.style.display = "none";
                  btnText.innerHTML = "read more";
                }
              }
            </script>

          </div>
        </div>
        <div class="col-md-2 d-flex align-item-center">
          <div class="btn-group gap-2 mt-2" role="group" aria-label="Social Media Links">
            <span>
              <?php if (!empty($twitter)): ?>
                <?php $twitterUrl = (strpos($twitter, 'https') !== false) ? $twitter : 'https://' . $twitter; ?>
                <a href="<?php echo $twitterUrl; ?>" class="btn fw-medium" role="button">
                  <img class="" width="16" height="16" src="../icon/twitter.svg"> <small>Twitter</small>
                </a>
              <?php endif; ?>
            </span>
            <span>
              <?php if (!empty($pixiv)): ?>
                <?php $pixivUrl = (strpos($pixiv, 'https') !== false) ? $pixiv : 'https://' . $pixiv; ?>
                <a href="<?php echo $pixivUrl; ?>" class="btn fw-medium" role="button">
                  <img class="" width="16" height="16" src="../icon/pixiv.svg"> <small>Pixiv</small>
                </a>
              <?php endif; ?>
            </span>
            <span>
              <?php if (!empty($other)): ?>
                <?php $otherUrl = (strpos($other, 'https') !== false) ? $other : 'https://' . $other; ?>
                <a href="<?php echo $otherUrl; ?>" class="btn fw-medium" role="button">
                  <img class="" width="16" height="16" src="../icon/globe-asia-australia.svg"> <small>Other</small>
                </a>
              <?php endif; ?>
            </span>
            <span>
              <button class="btn fw-medium" data-bs-toggle="modal" data-bs-target="#contactModal"><i class="bi bi-chat-fill"></i> <small>Message</small></button>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid d-md-none d-lg-none">
      <div class="row">
        <div class="b-radius position-relative" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; height: 250px; width: 100%;">
          <img class="img-thumbnail border-0 shadow position-absolute top-50 start-50 translate-middle rounded-circle object-fit-cover" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
          <a class="btn-sm btn btn-dark fw-bold rounded-pill opacity-75 position-absolute top-0 start-0 m-2" type="button" href="setting.php">change background <i class="bi bi-camera-fill"></i></a>
          <button class="btn btn-sm btn-dark opacity-75 rounded-3 position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#modalUserInfo"><i class="bi bi-info-circle-fill"></i></button>
        </div>
        <div class="d-flex align-items-center justify-content-center">
          <div>
            <h1 class="fw-bold text-center d-md-none d-lg-none mt-2"><?php echo $artist; ?></h1>
            <h6 class="text-center"><span class="badge bg-secondary fw-medium rounded-pill"><?php echo $region; ?></span></h6>
            <div class="d-flex my-2 justify-content-center align-item-center">
              <div class="btn-group gap-2 mt-2" role="group" aria-label="Social Media Links">
                <span>
                  <?php if (!empty($twitter)): ?>
                    <?php $twitterUrl = (strpos($twitter, 'https') !== false) ? $twitter : 'https://' . $twitter; ?>
                    <a href="<?php echo $twitterUrl; ?>" class="btn fw-medium" role="button">
                      <img class="" width="16" height="16" src="../icon/twitter.svg"> <small>Twitter</small>
                    </a>
                  <?php endif; ?>
                </span>
                <span>
                  <?php if (!empty($pixiv)): ?>
                    <?php $pixivUrl = (strpos($pixiv, 'https') !== false) ? $pixiv : 'https://' . $pixiv; ?>
                    <a href="<?php echo $pixivUrl; ?>" class="btn fw-medium" role="button">
                      <img class="" width="16" height="16" src="../icon/pixiv.svg"> <small>Pixiv</small>
                    </a>
                  <?php endif; ?>
                </span>
                <span>
                  <?php if (!empty($other)): ?>
                    <?php $otherUrl = (strpos($other, 'https') !== false) ? $other : 'https://' . $other; ?>
                    <a href="<?php echo $otherUrl; ?>" class="btn fw-medium" role="button">
                      <img class="" width="16" height="16" src="../icon/globe-asia-australia.svg"> <small>Other</small>
                    </a>
                  <?php endif; ?>
                </span>
                <span>
                  <button class="btn fw-medium" data-bs-toggle="modal" data-bs-target="#contactModal"><i class="bi bi-chat-fill"></i> <small>Message</small></button>
                </span>
              </div>
            </div>
            <div class="btn-group w-100 mt-2">
              <a class="btn border-0 fw-medium text-center w-50" href="../../follower.php?id=<?php echo $user_id; ?>"> <?php echo $num_followers ?> <small>Followers</small></a>
              <a class="btn border-0 fw-medium text-center w-50" href="../../following.php?id=<?php echo $user_id; ?>"> <?php echo $num_following ?> <small>Following</small></a>
              <a class="btn border-0 fw-medium text-center w-50" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $count; ?> <small> Images</small></a>
            </div>
            <div class="btn-group w-100 mt-2">
              <a class="btn border-0 fw-medium text-center w-50" href="../list_favorite.php?id=<?php echo $user_id; ?>"> <?php echo $fav_count;?> <small> Favorites</small></a>
              <a class="btn border-0 fw-medium text-center w-50" href="../album.php"><small>My Albums</small></a>
              <button class="btn border-0 fw-medium text-center w-50" href="#" data-bs-toggle="modal" data-bs-target="#shareUser"><small>Shares</small></button>
            </div>
            <p class="mt-4 fw-medium text-break">
              <small>
                <?php
                  if (!empty($desc)) {
                    $messageText = $desc;
                    $messageTextWithoutTags = strip_tags($messageText);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $url = htmlspecialchars($matches[0]);
                      return '<a href="' . $url . '">' . $url . '</a>';
                    }, $messageTextWithoutTags);

                    $charLimit = 100; // Set your character limit

                    if (strlen($formattedText) > $charLimit) {
                      $limitedText = substr($formattedText, 0, $charLimit);
                      echo '<span id="limitedText1">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                      echo '<span id="more1" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                      echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction1()" id="myBtn1"><small>read more</small></button>';
                    } else {
                      // If the text is within the character limit, just display it with line breaks.
                      echo nl2br($formattedText);
                    }
                  } else {
                    echo "User description is empty.";
                  }
                ?>
              </small>
            </p>
            
            <script>
              function myFunction1() {
                var dots1 = document.getElementById("limitedText1");
                var moreText1 = document.getElementById("more1");
                var btnText1 = document.getElementById("myBtn1");

                if (moreText1.style.display === "none") {
                  dots1.style.display = "none";
                  moreText1.style.display = "inline";
                  btnText1.innerHTML = "read less";
                } else {
                  dots1.style.display = "inline";
                  moreText1.style.display = "none";
                  btnText1.innerHTML = "read more";
                }
              }
            </script>
            
          </div>
        </div>
      </div>
    </div>
    <!-- End of Profile Header -->

    <?php include('profile_header.php'); ?>
    <?php include('contact_header.php'); ?>
    <?php include('most_popular_profile.php'); ?>

    <h6 class="container-fluid fw-bold"><i class="bi bi-images"></i> All <?php echo $artist; ?>'s Images</h6>
    <?php
    $validTaggedFilters = [
      'tagged_oldest', 'tagged_newest', 'tagged_popular', 'tagged_view',
      'tagged_least', 'tagged_liked', 'tagged_order_asc', 'tagged_order_desc'
    ];
    $validHeaderPages = [
      'header_profile_asc.php', 'header_profile_desc.php', 'header_profile_pop.php',
      'header_profile_view.php', 'header_profile_least.php', 'header_profile_like.php',
      'header_profile_order_asc.php', 'header_profile_order_desc.php'
    ];
    
    $isTagHidden = false;
    
    if (isset($_GET['by']) && in_array($_GET['by'], $validTaggedFilters)) {
      $isTagHidden = true;
    } else {
      foreach ($validHeaderPages as $page) {
        if (strpos($_SERVER['REQUEST_URI'], $page) !== false) {
          $isTagHidden = true;
          break;
        }
      }
    }
    ?>
    <div class="dropdown <?php echo $isTagHidden ? 'd-none' : ''; ?>">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-3 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=liked&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=order_asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
      </ul>
    </div> 

    <?php
    $validFilters = ['oldest', 'newest', 'popular', 'view', 'least', 'liked', 'order_asc', 'order_desc'];
    $validPages = ['profile_asc.php', 'profile_desc.php', 'profile_pop.php', 'profile_view.php', 'profile_least.php', 'profile_like.php', 'profile_order_asc.php', 'profile_order_desc.php'];
    
    $isHidden = false;
    
    if (isset($_GET['by']) && in_array($_GET['by'], $validFilters)) {
      $isHidden = true;
    } else {
      foreach ($validPages as $page) {
        if (strpos($_SERVER['REQUEST_URI'], $page) !== false) {
          $isHidden = true;
          break;
        }
      }
    }
    ?>
    <div class="dropdown <?php echo $isHidden ? 'd-none' : ''; ?>">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-3 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=tagged_newest&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'tagged_newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=tagged_oldest&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=tagged_view&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=tagged_least&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=tagged_liked&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=tagged_order_asc&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=tagged_order_desc&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_order_desc') echo 'active'; ?>">from Z to A</a></li>
      </ul>
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "profile_desc.php";
            break;
            case 'oldest':
            include "profile_asc.php";
            break;
            case 'popular':
            include "profile_pop.php";
            break;
            case 'view':
            include "profile_view.php";
            break;
            case 'least':
            include "profile_least.php";
            break;
            case 'liked':
            include "profile_like.php";
            break;
            case 'order_asc':
            include "profile_order_asc.php";
            break;
            case 'order_desc':
            include "profile_order_desc.php";
            break;

            case 'tagged_newest':
            include "profile_tagged_desc.php";
            break;
            case 'tagged_oldest':
            include "profile_tagged_asc.php";
            break;
            case 'tagged_popular':
            include "profile_tagged_pop.php";
            break;
            case 'tagged_view':
            include "profile_tagged_view.php";
            break;
            case 'tagged_least':
            include "profile_tagged_least.php";
            break;
            case 'tagged_liked':
            include "profile_tagged_like.php";
            break;
            case 'tagged_order_asc':
            include "profile_tagged_order_asc.php";
            break;
            case 'tagged_order_desc':
            include "profile_tagged_order_desc.php";
            break;
          }
        }
        else {
          include "profile_desc.php";
        }
        
        ?>
    <div class="mt-5"></div>
    <?php include('share.php'); ?>
    <style>
      @media only screen and (min-width: 767px) {
        .rounded-min-5 {
          border-radius: 1.6rem;
        }
      }
    </style>
    <script>
      function shareImage(userId) {
        // Compose the share URL
        var shareUrl = '../image.php?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "../icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder; // Apply default placeholder
          imageObserver.observe(image);
          image.style.filter = "blur(5px)"; // Apply initial blur to all images

          // Remove blur and apply custom blur to NSFW images after they load
          image.addEventListener("load", function() {
            image.style.filter = ""; // Remove initial blur
            if (image.classList.contains("nsfw")) {
              image.style.filter = "blur(4px)"; // Apply blur to NSFW images
          
              // Add overlay with icon and text
              let overlay = document.createElement("div");
              overlay.classList.add("overlay", "rounded");
              let icon = document.createElement("i");
              icon.classList.add("bi", "bi-eye-slash-fill", "text-white");
              overlay.appendChild(icon);
              let text = document.createElement("span");
              text.textContent = "R-18";
              text.classList.add("shadowed-text", "fw-bold", "text-white");
              overlay.appendChild(text);
              image.parentNode.appendChild(overlay);
            }
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
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

      // Infinite scrolling
      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

        // Simulate loading delay for demo purposes
        setTimeout(function() {
          for (let i = 0; i < 10; i++) {
            if (lazyloadImages.length === 0) {
              break;
            }
            let image = lazyloadImages[0];
            imageContainer.appendChild(image);
            lazyloadImages = Array.from(lazyloadImages).slice(1);
          }
          loading = false;
        }, 1000);
      }

      window.addEventListener("scroll", function() {
        if (window.innerHeight + window.scrollY >= imageContainer.clientHeight) {
          loadMoreImages();
        }
      });

      // Initial loading
      loadMoreImages();
    </script>
    <script>
      function shareArtist(userId) {
        // Compose the share URL
        var shareUrl = '../artist.php?id=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>