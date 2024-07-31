<?php
session_start();

// Initialize $logged_in variable
$logged_in = isset($_SESSION['email']);

// Check if the user is logged in and get the email
$email = $logged_in ? $_SESSION['email'] : null;

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Get the ID of the selected user from the URL
$id = $_GET['id'];
$query = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, twitter, pixiv, other, region, joined, born, email, message_1, message_2, message_3, message_4 FROM users WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$artist = $user['artist'];
$desc = $user['desc'];
$pic = $user['pic'];
$bgpic = $user['bgpic'];
$twitter = $user['twitter'];
$pixiv = $user['pixiv'];
$other = $user['other'];
$region = $user['region'];
$joined = $user['joined'];
$born = $user['born'];
$message_1 = $user['message_1'];
$message_2 = $user['message_2'];
$message_3 = $user['message_3'];
$message_4 = $user['message_4'];

// Get all images for the selected user from the images table
$query = $db->prepare('SELECT images.id, images.filename, images.title, images.imgdesc FROM images JOIN users ON images.email = users.email WHERE users.id = :id ORDER BY images.id DESC');
$query->bindParam(':id', $id);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);

// Check if the logged-in user is already following the selected user
if ($logged_in) {
  $query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = $query->fetchColumn();
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

// Get the number of followers for the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE following_email = :following_email');
$query->bindParam(':following_email', $user['email']);
$query->execute();
$num_followers = $query->fetchColumn();

// Get the number of people the selected user is following
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email');
$query->bindParam(':follower_email', $user['email']);
$query->execute();
$num_following = $query->fetchColumn(); 

// Handle following/unfollowing actions
if (isset($_POST['follow']) && $logged_in) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_email, following_email) VALUES (:follower_email, :following_email)');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = true;

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
} elseif (isset($_POST['unfollow']) && $logged_in) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = false;

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}

// Process any favorite/unfavorite requests
if (isset($_POST['favorite']) && $logged_in) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id")->fetchColumn();

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();

} elseif (isset($_POST['unfavorite']) && $logged_in) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}

$fav_count = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email");
$fav_count->bindParam(':email', $user['email']);
$fav_count->execute();
$fav_count = $fav_count->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row">
        <div class="position-relative" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; background-position: center; height: 100dvh; width: 100%;">
          <div class="position-absolute top-50 start-50 translate-middle d-flex justify-content-center align-items-center">
            <div class="container text-center">
              <img class="shadow border border-light border-5 rounded-circle object-fit-cover" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 96px; height: 96px;">
              <div class="container">
                <h5 class="fw-bold text-white mt-2" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);"><?php echo $artist; ?></h5>
                <?php if ($logged_in): ?>
                  <form class="w-100" method="post">
                    <?php if ($is_following): ?>
                      <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill w-100 fw-medium mt-1 shadow" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button>
                    <?php else: ?>
                      <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill w-100 fw-medium mt-1 shadow" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button>
                    <?php endif; ?>
                  </form>
                <?php endif; ?>
                <div class="position-absolute start-50 translate-middle-x">
                  <div class="d-flex mt-2">
                    <div class="fw-bold p-0 d-flex flex-column justify-content-center align-items-center border-0 text-white" style="width: 4em; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                      <?php echo $num_followers ?>
                      <span class="d-lg-inline small"><small>Followers</small></span>
                    </div>
                    <div class="fw-bold p-0 d-flex flex-column justify-content-center align-items-center border-0 text-white" style="width: 4em; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                      <?php echo $num_following ?>
                      <span class="d-lg-inline small"><small>Following</small></span>
                    </div>
                    <div class="fw-bold p-0 d-flex flex-column justify-content-center align-items-center border-0 text-white" style="width: 4em; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                      <?php echo count($images); ?>
                      <span class="d-lg-inline small"><small>Images</small></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>