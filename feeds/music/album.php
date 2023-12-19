<?php
require_once('../../auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

// Get the album parameter from the URL
$album = isset($_GET['album']) ? $_GET['album'] : null;

// Fetch music records with user information and filter by album if provided
$query = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id AS userid, users.artist 
          FROM music 
          LEFT JOIN users ON music.email = users.email";

// If album parameter is provided, filter by album
if (!empty($album)) {
  $query .= " WHERE music.album = :album";
}

$query .= " ORDER BY music.id DESC";

$stmt = $db->prepare($query);

// Bind album parameter if provided
if (!empty($album)) {
  $stmt->bindValue(':album', $album, SQLITE3_TEXT);
}

$result = $stmt->execute();

// Fetch all rows as an associative array
$rows = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $rows[] = $row;
}

// Check if there are any rows
if (!empty($rows)) {
  // Get the first row
  $firstRow = $rows[0];

  // Extract the image file path from the first row
  $imagePath = $firstRow['cover'];
}

// Calculate the total number of tracks in the same album
$countQuery = "SELECT COUNT(*) as count FROM music WHERE album = :album";
$countStmt = $db->prepare($countQuery);
$countStmt->bindValue(':album', $album, SQLITE3_TEXT);
$countResult = $countStmt->execute();
$albumTrackCount = $countResult->fetchArray(SQLITE3_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <?php include('header.php'); ?>
      <div class="row p-4 p-md-5 mb-3">
        <div class="col-md-3 order-md-1 mb-3 p-md-0 pe-md-4">
          <div class="position-relative">
            <div class="ratio ratio-1x1">
              <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="covers/<?php echo $imagePath; ?>" class="object-fit-cover img-fluid rounded shadow" alt="..."></a>
            </div>
            <button class="btn btn-dark opacity-75 position-absolute bottom-0 end-0 m-2 fw-medium" onclick="sharePage()"><small><i class="bi bi-share-fill"></i> share</small></button>
          </div>
        </div>
        <div class="col-md-7 order-md-2">
          <h2 class="featurette-heading fw-normal fw-bold">Album: <?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></span></h2>
          <p class="fw-medium mt-3">Artist : <a class="text-decoration-none text-white" href="artist.php?id=<?php echo $row['userid']; ?>"><?php echo isset($rows[0]['artist']) ? htmlentities($rows[0]['artist']) : ''; ?></a></p>
          <p class="fw-medium mt-3">Total Tracks in Album: <?php echo $albumTrackCount; ?> songs</p>
        </div>
      </div>
    </div>
    <hr>
    <div class="container-fluid mt-3">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php foreach ($rows as $row): ?>
          <?php include('music_info.php'); ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="covers/<?php echo $imagePath; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
          </div>
        </div>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <div class="mt-5"></div>
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
