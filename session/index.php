<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite'); 
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT, token TEXT, twitter TEXT, pixiv TEXT, other, region TEXT, joined DATETIME, born DATETIME, numpage TEXT, display TEXT, message_1 TEXT, message_2 TEXT, message_3 TEXT, message_4 TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME, view_count INT DEFAULT 0, type TEXT, episode_name TEXT, artwork_type TEXT, `group` TEXT, categories TEXT, language TEXT, parodies TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_child (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT NOT NULL, image_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (image_id) REFERENCES images (id))");
$stmt->execute();

// Create the "visit" table if it doesn't exist
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS visit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  visit_count INTEGER,
  visit_date DATE DEFAULT CURRENT_DATE,
  UNIQUE(visit_date)
)");
$stmt->execute();

// Process any visit requests
$stmt = $db->prepare("SELECT id, visit_count FROM visit WHERE visit_date = CURRENT_DATE");
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if ($row) {
  // If the record for the current date exists, increment the visit_count
  $visitCount = $row['visit_count'] + 1;
  $stmt = $db->prepare("UPDATE visit SET visit_count = :visitCount WHERE id = :id");
  $stmt->bindValue(':visitCount', $visitCount, SQLITE3_INTEGER);
  $stmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
  $stmt->execute();
} else {
  // If the record for the current date doesn't exist, insert a new record
  $stmt = $db->prepare("INSERT INTO visit (visit_count) VALUES (:visitCount)");
  $stmt->bindValue(':visitCount', 1, SQLITE3_INTEGER);
  $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="../manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" type="text/css" href="style.css" />
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script>
      (function () {
        window.onload = function () {
          var preloader = document.querySelector('.page-loading');
          preloader.classList.remove('active');
          setTimeout(function () {
            preloader.remove();
          }, 2000);
        };
      })();
    </script>
  </head>
  <body>
    <!-- Page loading spinner-->
    <div class="page-loading active">
      <div class="page-loading-inner">
        <div class="page-spinner"></div><span class="fw-bold">Loading...</span>
      </div>
    </div>
    
    <main class="page-wrapper w-100">
      <!-- Hero -->
      <div class="bg-dark pb-5" style="background-image: url('../session/contents/mountain-1.jpg'); background-size: cover; background-repeat: no-repeat; height: 100vh;">
        <div style="background-color: rgba(0, 0, 0, 0.5); height: 100%; height: 100vh;">
          <!-- Navbar -->
          <?php include('lp_header.php');?>
          <!-- End of Navbar -->
        
          <br>
        
          <!-- Main -->
          <?php include('main.php');?>
          <!-- End of Main -->
        
          <div style="padding-bottom: 200px;"></div>
        </div>
      </div>
      
      <!-- Features -->
      <?php include('features.php');?>
      <!-- End of Features -->
      
      <!-- Advantages -->
      <?php include('advantages.php');?>
      <!-- End of Advantages -->
      
    </main>

    <!-- Footer -->
    <?php include('footer.php');?>
    <!-- End of footer -->
    
    <!-- additional style -->
    <style>
      .clickable-card {
        cursor: pointer;
        transition: box-shadow 0.8s ease, transform 0.8s ease;
      }

      .clickable-card:hover {
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        transform: translateY(-2px);
      }

      .feature-icon {
        width: 4rem;
        height: 4rem;
        border-radius: .75rem;
      }

      .icon-square {
        width: 3rem;
        height: 3rem;
        border-radius: .75rem;
      }

      .parallax { position:relative }
      .parallax-layer { position:absolute; top:0; left:0; width:100%; height:100% }
      .parallax-layer> img { display:block; width:100% }

      .text-shadow-1 { text-shadow: 0 .125rem .25rem rgba(0, 0, 0, .25); }
      .text-shadow-2 { text-shadow: 0 .25rem .5rem rgba(0, 0, 0, .25); }
      .text-shadow-3 { text-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .25); }

      .card-cover {
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
      }

      .feature-icon-small {
        width: 3rem;
        height: 3rem;
      }
      
      .page-loading {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        -webkit-transition: all .4s .2s ease-in-out;
        transition: all .4s .2s ease-in-out;
        background-color: #000;
        opacity: 0;
        visibility: hidden;
        z-index: 9999;
      }
      
      .page-loading.active {
        opacity: 1;
        visibility: visible;
      }
      
      .page-loading-inner {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        text-align: center;
        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
        -webkit-transition: opacity .2s ease-in-out;
        transition: opacity .2s ease-in-out;
        opacity: 0;
      }
      
      .page-loading.active > .page-loading-inner {
        opacity: 1;
      }
      .page-loading-inner > span {
        display: block;
        font-size: 1rem;
        font-weight: normal;
        color: #666276;;
      }
      
      .page-spinner {
        display: inline-block;
        width: 2.75rem;
        height: 2.75rem;
        margin-bottom: .75rem;
        vertical-align: text-bottom;
        border: 2.55em solid #bbb7c5;
        border-right-color: transparent;
        border-radius: 50%;
        -webkit-animation: spinner .75s linear infinite;
        animation: spinner .75s linear infinite;
      }
      
      @-webkit-keyframes spinner {
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }
      
      @keyframes spinner {
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }
    </style>
    <!-- additional style -->
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>