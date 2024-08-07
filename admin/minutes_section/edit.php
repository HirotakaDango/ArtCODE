<?php
// admin/minutes_section/edit.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Connect to the SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Get video ID from the query parameters
$id = $_GET['id'] ?? '';

// Fetch video record with user information using JOIN
$query = "SELECT videos.id, videos.video, videos.email, videos.thumb, videos.title, videos.description, videos.date, videos.view_count, users.id as userid, users.artist
          FROM videos
          JOIN users ON videos.email = users.email
          WHERE videos.id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

// Redirect to the home page if the record is not found
if (!$row) {
  header('Location: /admin/minutes_section/');
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newTitle = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $newDescription = nl2br(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

  // Handle image upload
  $thumbFile = $_FILES['cover'];

  if ($thumbFile['error'] === UPLOAD_ERR_OK) {
    // Delete previous image if it exists and it's not the default thumb
    if (!empty($row['thumb']) && $row['thumb'] !== 'default_thumb.jpg') {
      unlink($_SERVER['DOCUMENT_ROOT'] . '/feeds/minutes/thumbnails/' . $row['thumb']);
    }

    // Process the new image
    $thumbName = 'thumb_' . uniqid() . '.' . pathinfo($thumbFile['name'], PATHINFO_EXTENSION);
    $thumbPath = $_SERVER['DOCUMENT_ROOT'] . '/feeds/minutes/thumbnails/' . $thumbName;

    // Resize or crop the image to maintain a 16x9 aspect ratio
    $image = imagecreatefromstring(file_get_contents($thumbFile['tmp_name']));
    $width = imagesx($image);
    $height = imagesy($image);
    $targetWidth = 16 * $height / 9;

    // Crop the image to fit the 16x9 aspect ratio
    $cropX = max(0, ($width - $targetWidth) / 2);
    $cropWidth = min($width, $targetWidth);
    $cropHeight = min($height, $cropWidth * 9 / 16);

    // Create a canvas with 16x9 dimensions
    $canvas = imagecreatetruecolor(640, 360);

    // Crop the image
    imagecopyresampled($canvas, $image, 0, 0, $cropX, 0, 640, 360, $cropWidth, $cropHeight);

    // Save the processed image
    imagejpeg($canvas, $thumbPath);
    imagedestroy($canvas);
    imagedestroy($image);

    // Update the video record with the new thumb and other information
    $updateThumbQuery = "UPDATE videos
                        SET title = :title, description = :description, thumb = :thumb
                        WHERE id = :id";
    $updateThumbStmt = $db->prepare($updateThumbQuery);
    $updateThumbStmt->bindValue(':title', $newTitle, SQLITE3_TEXT);
    $updateThumbStmt->bindValue(':description', $newDescription, SQLITE3_TEXT);
    $updateThumbStmt->bindValue(':thumb', $thumbName, SQLITE3_TEXT);
    $updateThumbStmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $updateThumbStmt->execute();
  } else {
    // Update the video record without changing the thumb
    $updateQuery = "UPDATE videos
                    SET title = :title, description = :description
                    WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindValue(':title', $newTitle, SQLITE3_TEXT);
    $updateStmt->bindValue(':description', $newDescription, SQLITE3_TEXT);
    $updateStmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $updateStmt->execute();
  }

  // Redirect to the home page after the update
  header('Location: /admin/minutes_section/');
  exit;
}
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
            <div class="col-md-6">
              <a data-bs-toggle="modal" data-bs-target="#originalImage">
                <div class="ratio ratio-16x9">
                  <div id="file-preview-container" class="d-flex align-items-center justify-content-center h-100 border border-3 rounded-4">
                    <?php if (!empty($row['thumb'])): ?>
                      <img src="/feeds/minutes/thumbnails/<?php echo $row['thumb']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover" id="coverImage">
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
            <div class="col-md-6">
              <form oninput="showPreview(event)" enctype="multipart/form-data" action="" method="post">
                <div class="mb-2">
                  <label for="cover" class="form-label">Select Cover Image</label>
                  <input type="file" class="form-control border border-3 rounded-4" id="cover" name="cover" accept="image/*">
                </div>
                <div class="form-floating mb-2">
                  <input class="form-control border border-3 rounded-4" type="text" id="floatingInput" value="<?php echo $row['title']; ?>" id="title" name="title" required>
                  <label class="fw-medium" for="floatingInput">title</label>
                </div>
                <div class="form-floating mb-2">
                  <textarea class="form-control border border-3 rounded-4 vh-100" oninput="stripHtmlTags(this)" id="description" placeholder="description" name="description"><?php echo strip_tags($row['description']); ?></textarea>
                  <label class="fw-medium" for="album">description</label>
                </div>
                <button type="button" class="btn btn-danger w-100 fw-bold border-danger-subtle border border-3 rounded-4 my-2" data-bs-toggle="modal" data-bs-target="#modalDelete">
                  delete this video
                </button>
                <button type="submit" class="btn btn-primary w-100 fw-bold border-primary-subtle border border-3 rounded-4">save changes</button>
              </form>
            </div>
          </div>
          <div class="mt-5"></div>
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
              <a class="btn btn-danger px-0 border border-danger-subtle border-3 rounded-4 fw-medium" href="delete.php?id=<?php echo $id; ?>">delete this!</a>
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
            <img class="object-fit-contain h-100 w-100 rounded" src="/feeds/minutes/thumbnails/<?php echo $row['thumb']; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="thumbnails/<?php echo $row['thumb']; ?>" download>Download Cover Image</a>
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
            previewContainer.innerHTML = '<img src="thumbnails/<?php echo $row['thumb']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover">';
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