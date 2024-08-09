<?php
// admin/music_section/edit.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Connect to the SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Get music ID from the query parameters
$id = $_GET['id'] ?? '';
$by = $_GET['by'] ?? '';
$mode = $_GET['mode'] ?? '';

// Fetch music record with user information using JOIN
$query = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, music.lyrics, music.description, users.id as userid, users.artist
          FROM music
          JOIN users ON music.email = users.email
          WHERE music.id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

// Redirect to the music section page if the record is not found
if (!$row) {
  $mode = isset($_GET['mode']) ? $_GET['mode'] : 'grid';
  $by = isset($_GET['by']) ? $_GET['by'] : 'newest';
  header('Location: /admin/music_section/?mode=' . $mode . '&by=' . $by);
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newTitle = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $newAlbum = filter_input(INPUT_POST, 'album', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $newLyrics = nl2br(filter_input(INPUT_POST, 'lyrics', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
  $newDescription = nl2br(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

  // Handle image upload
  $coverFile = $_FILES['cover'];

  if ($coverFile['error'] === UPLOAD_ERR_OK) {
    // Delete previous image if it exists and is not the default cover
    if (!empty($row['cover']) && $row['cover'] !== 'default_cover.jpg') {
      unlink($_SERVER['DOCUMENT_ROOT'] . '/feeds/music/covers/' . $row['cover']);
    }

    // Process the new image
    $coverName = 'cover_' . uniqid() . '.' . pathinfo($coverFile['name'], PATHINFO_EXTENSION);
    $coverPath = $_SERVER['DOCUMENT_ROOT'] . '/feeds/music/covers/' . $coverName;

    // Resize or crop the image to maintain a 1x1 aspect ratio
    $image = imagecreatefromstring(file_get_contents($coverFile['tmp_name']));
    $width = imagesx($image);
    $height = imagesy($image);
    $size = min($width, $height);

    // Create a square canvas
    $canvas = imagecreatetruecolor($size, $size);

    // Crop or resize the image to fit the square canvas
    imagecopyresampled($canvas, $image, 0, 0, ($width - $size) / 2, ($height - $size) / 2, $size, $size, $size, $size);

    // Save the processed image
    imagejpeg($canvas, $coverPath);
    imagedestroy($canvas);
    imagedestroy($image);

    // Update the music record with the new cover and other information
    $updateCoverQuery = "UPDATE music
                        SET title = :title, album = :album, cover = :cover, lyrics = :lyrics, description = :description
                        WHERE id = :id";
    $updateCoverStmt = $db->prepare($updateCoverQuery);
    $updateCoverStmt->bindValue(':title', $newTitle, SQLITE3_TEXT);
    $updateCoverStmt->bindValue(':album', $newAlbum, SQLITE3_TEXT);
    $updateCoverStmt->bindValue(':cover', $coverName, SQLITE3_TEXT);
    $updateCoverStmt->bindValue(':lyrics', $newLyrics, SQLITE3_TEXT);
    $updateCoverStmt->bindValue(':description', $newDescription, SQLITE3_TEXT);
    $updateCoverStmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $updateCoverStmt->execute();
  } else {
    // Update the music record without changing the cover
    $updateQuery = "UPDATE music
                    SET title = :title, album = :album, lyrics = :lyrics, description = :description
                    WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindValue(':title', $newTitle, SQLITE3_TEXT);
    $updateStmt->bindValue(':album', $newAlbum, SQLITE3_TEXT);
    $updateStmt->bindValue(':lyrics', $newLyrics, SQLITE3_TEXT);
    $updateStmt->bindValue(':description', $newDescription, SQLITE3_TEXT);
    $updateStmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $updateStmt->execute();
  }

  // Redirect to the music section page after the update
  $mode = isset($_GET['mode']) ? $_GET['mode'] : 'grid';
  $by = isset($_GET['by']) ? $_GET['by'] : 'newest';
  header('Location: /admin/music_section/?mode=' . $mode . '&by=' . $by);
  exit;
}

$db->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Edit <?php echo $row['title']; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../navbar.php'); ?>
          <div class="container-fluid mt-1 mx-auto row g-2">
            <div class="col-md-4 mb-2">
              <a data-bs-toggle="modal" data-bs-target="#originalImage">
                <div class="ratio ratio-1x1">
                  <div id="file-preview-container" class="d-flex align-items-center justify-content-center h-100 border border-3 rounded-4">
                    <?php if (!empty($row['cover'])): ?>
                      <img src="/feeds/music/covers/<?php echo $row['cover']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover" id="coverImage">
                    <?php else: ?>
                      <div class="text-center">
                        <h6><i class="bi bi-image fs-1"></i></h6>
                        <h6>Your image cover here!</h6>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-8">
              <form oninput="showPreview(event)" enctype="multipart/form-data" action="" method="post">
                <div class="mb-2">
                  <label for="cover" class="form-label">Select Cover Image</label>
                  <input type="file" class="form-control border border-3 rounded-4" id="cover" name="cover" accept="image/*">
                </div>
                <div class="row">
                  <div class="col-md-6 pe-md-1">
                    <div class="form-floating mb-2">
                      <input class="form-control border border-3 rounded-4" type="text" id="floatingInput" value="<?php echo $row['title']; ?>" id="title" name="title" required>
                      <label class="fw-medium" for="floatingInput">title</label>
                    </div>
                  </div>
                  <div class="col-md-6 ps-md-1">
                    <div class="form-floating mb-2">
                      <input class="form-control border border-3 rounded-4" type="text" id="floatingInput" value="<?php echo $row['album']; ?>" id="album" name="album" required>
                      <label class="fw-medium" for="floatingInput">album</label>
                    </div>
                  </div>
                </div>
                <button class="btn btn-secondary border border-secondary-subtle border-3 rounded-4 w-100 fw-bold mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDescription" aria-expanded="false" aria-controls="collapseExample">
                  edit description
                </button>
                <div class="collapse" id="collapseDescription">
                  <div class="form-floating mb-2">
                    <textarea class="form-control border border-3 rounded-4 vh-100" oninput="stripHtmlTags(this)" id="description" placeholder="description" name="description"><?php echo strip_tags($row['description']); ?></textarea>
                    <label class="fw-medium" for="album">description</label>
                  </div>
                </div>
                <button class="btn btn-secondary border border-secondary-subtle border-3 rounded-4 w-100 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLyrics" aria-expanded="false" aria-controls="collapseExample">
                  edit lyrics
                </button>
                <div class="collapse mt-2" id="collapseLyrics">
                  <div class="form-floating">
                    <textarea class="form-control border border-3 rounded-4 vh-100" oninput="stripHtmlTags(this)" id="lyrics" placeholder="lyrics" name="lyrics"><?php echo strip_tags($row['lyrics']); ?></textarea>
                    <label class="fw-medium" for="album">lyrics</label>
                  </div>
                </div>
                <button type="button" class="btn btn-danger w-100 fw-bold border-danger-subtle border border-3 rounded-4 my-2" data-bs-toggle="modal" data-bs-target="#modalDelete">
                  delete this song
                </button>
                <button type="submit" class="btn btn-primary w-100 fw-bold border-primary-subtle border border-3 rounded-4">save changes</button>
              </form>
            </div>
            <div class="mt-5"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="modalDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-bottom-0">
            <h1 class="modal-title fs-5">Delete <?php echo $row['title'] ?></h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body py-0 text-center fw-medium">
            <p>Are you sure want to delete <strong><?php echo $row['title'] ?></strong>?</p>
            <p class="small">(Warning: You can't restore back after you delete this!)</p>
            <div class="btn-group w-100 my-3 gap-3">
              <a class="btn btn-danger px-0 border border-danger-subtle border-3 rounded-4 fw-medium" href="delete.php?mode=<?php echo $mode; ?>&by=<?php echo $by; ?>&id=<?php echo $id; ?>">delete this!</a>
              <button type="button" class="btn btn-secondary px-4 border border-3 rounded-4 fw-medium" data-bs-dismiss="modal">cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="/feeds/music/covers/<?php echo $row['cover']; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="covers/<?php echo $row['cover']; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    <script>
      function showPreview(event) {
        var fileInput = event.target;
        var previewContainer = document.getElementById("file-preview-container");
        var coverImage = document.getElementById("coverImage");

        if (fileInput.files.length > 0) {
          var img = document.createElement("img");
          img.src = URL.createObjectURL(fileInput.files[0]);
          img.classList.add("d-block", "object-fit-cover");
          img.style.borderRadius = "0.85em";
          img.style.width = "100%";
          img.style.height = "100%";
          previewContainer.innerHTML = "";
          previewContainer.appendChild(img);
        } else {
          // Show the existing cover image
          <?php if (!empty($row['cover'])): ?>
            previewContainer.innerHTML = '<img src="covers/<?php echo $row['cover']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover">';
          <?php else: ?>
            // If no file is selected, show the default content
            previewContainer.innerHTML = '<div class="text-center"><h6><i class="bi bi-image fs-1"></i></h6><h6>Your image cover here!</h6></div>';
          <?php endif; ?>
        }
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
