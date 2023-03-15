<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  // Redirect to the login page if not
  header('Location:../session.php');
  exit();
}

// Connect to the database
$db = new SQLite3('../database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS forum (id INTEGER PRIMARY KEY, username TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();

// Function to get the elapsed time since a certain date in a human-readable format
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Check if the form was submitted for adding a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  // Get the comment from the form data
  $comment = htmlspecialchars($_POST['comment']);
  $username = $_SESSION['username'];

  // Get the current time
  $now = date('Y-m-d');

  // Insert the comment into the database
  $stmt = $db->prepare("INSERT INTO forum (username, comment, created_at) VALUES (:username, :comment, :created_at)");
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
  $stmt->bindValue(':created_at', $now, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect back to the image page
  header("Location:../forum-chat/index.php");
  exit();
}

// Check if the form was submitted for updating or deleting a comment
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $comment_id = $_POST['comment_id'];

  // Get the username of the current user
  $username = $_SESSION['username'];

  // Check if the comment belongs to the current user
  $stmt = $db->prepare("SELECT * FROM forum WHERE id=:comment_id AND username=:username");
  $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment) {
    if ($action == 'update') {
      $new_comment = htmlspecialchars($_POST['new_comment']);
      $stmt = $db->prepare("UPDATE forum SET comment=:new_comment WHERE id=:comment_id");
      $stmt->bindValue(':new_comment', $new_comment, SQLITE3_TEXT);
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    } elseif ($action == 'delete') {
      $stmt = $db->prepare("DELETE FROM forum WHERE id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  // Redirect back to the image page
  header("Location:../forum-chat/index.php");
  exit();
}

// Get all forum for the current image
$stmt = $db->prepare("SELECT forum.*, users.artist FROM forum JOIN users ON forum.username = users.username ORDER BY forum.id DESC");

$forum = $stmt->execute(); 
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Comment Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .random-class-name {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
      }
    </style>  
  </head>
  <body>
    <br><br> 
    <div class="container mt-2">
      <div class="row">
        <div class="col-md-8 mx-auto">
          <?php
          while ($comment = $forum->fetchArray()) :
          ?>
          <div class="card mb-2">
            <div class="me-1 ms-1 mt-1 text-secondary fw-bold">
              <p><?php echo $comment['artist']; ?> :</p>
              <p><?php echo $comment['comment']; ?></p>
              <small><?php echo $comment['created_at']; ?></small>
              <?php if ($comment['username'] == $_SESSION['username']) : ?>
                <form action="" method="POST">
                  <input type="hidden" name="filename" value="<?php echo $filename; ?>">
                  <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                  <?php
                    // Only show textarea when edit button is clicked
                    $showTextarea = isset($_POST['action']) && $_POST['action'] === 'update' && $_POST['comment_id'] === $comment['id'];
                  ?>
                  <div class="form-group <?php echo $showTextarea ? '' : 'd-none'; ?>">
                    <textarea class="form-control" name="new_comment" rows="3"><?php echo $comment['comment']; ?></textarea>
                  </div>
                  <div class="btn-group comment-buttons mt-1 me-1 opacity-50">
                    <?php if (!$showTextarea) : ?>
                      <button type="button" onclick="this.closest('form').querySelector('.form-group').classList.remove('d-none');" class="btn btn-sm btn-secondary"><i class="bi bi-pencil-fill"></i></button>
                    <?php endif; ?>
                    <button type="submit" name="action" value="update" class="btn btn-sm btn-secondary"><i class="bi bi-check"></i></button>
                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-secondary"><i class="bi bi-trash-fill"></i></button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <?php
          endwhile;
          ?>
          <nav class="navbar fixed-bottom">
            <form class="" action="" method="POST">
              <div class="form-group d-flex ms-2" style="height: 40px; margin-top: -13px;">
                <textarea class="form-control width-vw flex-grow-1 ms-3" name="comment" id="comment" maxlength="400" placeholder="comment" required></textarea>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill me-1"></i></button>
              </div>
            </form>
          </nav>
        </div>
      </div>
    </div>
    <nav class="navbar fixed-top navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="../icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="../index.php">
          ArtCODE
        </a>
          <div class="dropdown nav-right">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item text-secondary fw-bold" href="../profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="../setting.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="../favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="../logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div> 
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title text-secondary" id="navbarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold">
              <li class="nav-item">
                <a class="nav-link nav-center" href="../index.php">
                  <i class="bi bi-house-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Home</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center active" href="../forum-chat/index.php">
                  <i class="bi bi-chat-left-dots-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Forum</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="../imgupload.php">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Uploads</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="../popular.php">
                  <i class="bi bi-star-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Popular</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="../tags.php">
                  <i class="bi bi-tags-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Tags</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="../users.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Users</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="../global.php">
                  <i class="bi bi-clock-history fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Recents</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="../news.php">
                  <i class="bi bi-newspaper fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Update & News</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="../support.php">
                  <i class="bi bi-headset fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Support</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
    <style>
      .comment-buttons {
        position: absolute;
        top: 0;
        right: 0;
      }

      .comment-buttons button {
        margin-left: 5px; /* optional: add some margin between the buttons */
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
        
        .width-vw {
          width: 89vw;
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
    <script>
      function goBack() {
        window.location.href = "../index.php";
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
