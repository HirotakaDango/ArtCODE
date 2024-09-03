<?php
require_once('auth.php');
$db = new PDO('sqlite:../../database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$email = $_SESSION['email'];
$userID = $_GET['id']; // Retrieve user ID from the URL parameter

// Fetch user details
$queryUserDetails = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, twitter, pixiv, other, region, joined, born, email, message_1, message_2, message_3, message_4 FROM users WHERE id = :id');
$queryUserDetails->bindParam(':id', $userID, PDO::PARAM_INT);
$queryUserDetails->execute();
$user = $queryUserDetails->fetch(PDO::FETCH_ASSOC);

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

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
$query->bindParam(':follower_email', $email);
$query->bindParam(':following_email', $user['email']);
$query->execute();
$is_following = $query->fetchColumn();

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

// Count the number of music records for the user
$queryCount = "SELECT COUNT(*) FROM music WHERE email IN (SELECT email FROM users WHERE id = :userID)";
$stmtCount = $db->prepare($queryCount);
$stmtCount->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmtCount->execute();
$musicCount = $stmtCount->fetchColumn();

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

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $artist; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="mt-2 vh-100 d-none d-md-block d-lg-block position-relative" style="background-image: url('../<?php echo !empty($bgpic) ? $bgpic : "../../icon/bg.png"; ?>'); background-size: cover; height: 100%; width: 100%;">
    </div>
    <div class="container-fluid d-none d-md-block d-lg-block mt-3">
      <div class="row">
        <div class="col-md-2 d-flex align-item-center">
          <div class="card border-0">
            <img class="img-thumbnail border-0 shadow text-center rounded-circle mt-3 mx-3 object-fit-cover" src="../<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
            <div class="card-body">
              <h6 class="text-center"><span class="badge bg-secondary fw-medium rounded-pill"><?php echo $region; ?></span></h6>
            </div>
          </div>
        </div>
        <div class="col-md-7 d-flex align-items-center">
          <div>
            <h1 class="fw-bold d-none d-md-block d-lg-block mt-2"><?php echo $artist; ?></h1>
            <div class="button-group mt-3">
              <span class="me-4"><a class="btn border-0 fw-medium" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $musicCount; ?> <small>songs</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../../follower.php?id=<?php echo $userID; ?>"> <?php echo $num_followers ?> <small>Followers</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../../following.php?id=<?php echo $userID; ?>"> <?php echo $num_following ?> <small>Following</small></a></span>
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
      </div>
    </div>
    <div class="container-fluid d-md-none d-lg-none">
      <div class="row mt-2">
        <div class="mt-5 b-radius position-relative" style="background-image: url('../<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; height: 250px; width: 100%;">
          <img class="img-thumbnail border-0 shadow position-absolute top-50 start-50 translate-middle rounded-circle object-fit-cover" src="../<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
        </div>
        <div class="container-fluid w-100">
          <div>
            <h1 class="fw-bold text-center d-md-none d-lg-none mt-2"><?php echo $artist; ?></h1>
            <h6 class="text-center"><span class="badge bg-secondary fw-medium rounded-pill"><?php echo $region; ?></span></h6>
            <div class="btn-group w-100 mt-2">
              <a class="btn border-0 fw-medium text-center w-50" href="../../follower.php?id=<?php echo $userid; ?>"> <?php echo $num_followers ?> <small>Followers</small></a>
              <a class="btn border-0 fw-medium text-center w-50" href="../../following.php?id=<?php echo $userid; ?>"> <?php echo $num_following ?> <small>Following</small></a>
            </div>
            <div class="btn-group w-100 mt-2">
              <a class="btn border-0 fw-medium text-center w-50" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $musicCount; ?> <small>songs</small></a>
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
    <?php include('../header_preview.php'); ?>
    <div class="container-fluid d-flex">
      <!-- only visible for grid mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest_lists' || $_GET['by'] === 'oldest_lists' || $_GET['by'] === 'popular_lists' || $_GET['by'] === 'asc_lists' || $_GET['by'] === 'desc_lists')) || (strpos($_SERVER['REQUEST_URI'], 'artist_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_pop_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_order_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_order_desc_lists.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=newest&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=popular&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=asc&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc') echo 'active'; ?>">ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=desc&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <!-- only visible for lists mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest' || $_GET['by'] === 'oldest' || $_GET['by'] === 'popular' || $_GET['by'] === 'asc' || $_GET['by'] === 'desc')) || (strpos($_SERVER['REQUEST_URI'], 'artist_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_pop.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_order_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_order_desc.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=newest_lists&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest_lists') echo 'active'; ?>">newest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest_lists&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest_lists') echo 'active'; ?>">oldest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=popular_lists&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular_lists') echo 'active'; ?>">popular</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=asc_lists&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc_lists') echo 'active'; ?>">ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=desc_lists&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc_lists') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <div class="btn-group mt-2 pt-1">
        <a class="btn border-0 link-body-emphasis" href="?mode=grid&by=<?php echo isset($_GET['by']) ? str_replace('_lists', '', $_GET['by']) : 'newest'; ?>&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-grid-fill"></i></a>
        <a class="btn border-0 link-body-emphasis" href="?mode=lists&by=<?php echo isset($_GET['by']) ? (strpos($_GET['by'], '_lists') === false ? $_GET['by'] . '_lists' : $_GET['by']) : 'desc'; ?>&id=<?php echo $userID; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-view-list"></i></a>
      </div>
    </div>
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "artist_desc.php";
            break;
            case 'oldest':
            include "artist_asc.php";
            break;
            case 'popular':
            include "artist_pop.php";
            break;
            case 'desc':
            include "artist_order_desc.php";
            break;
            case 'asc':
            include "artist_order_asc.php";
            break;
            case 'newest_lists':
            include "artist_desc_lists.php";
            break;
            case 'oldest_lists':
            include "artist_asc_lists.php";
            break;
            case 'popular_lists':
            include "artist_pop_lists.php";
            break;
            case 'desc_lists':
            include "artist_order_desc_lists.php";
            break;
            case 'asc_lists':
            include "artist_order_asc_lists.php";
            break;
          }
        }
        else {
          include "artist_desc.php";
        }
        
        ?>
    <div class="modal fade" id="shareUser" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
            <div class="input-group">
              <input type="text" id="urlInput1" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="form-control border-2 fw-bold" readonly>
              <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                <i class="bi bi-clipboard-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }
    </script>
    <script>
      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
