<?php
require_once('auth.php');

$db = new PDO('sqlite:../../database.sqlite');

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to the specified URL for non-logged-in users
  $redirect_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/';
  header('Location: ' . $redirect_url);
  exit;
}

$email = $_SESSION['email'];

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Check if the form is submitted
  if (isset($_POST['submit'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $newImageName = null;

    // Check if a new image file is provided
    if ($_FILES['image']['error'] === 0) {
      // Process the uploaded image
      $imageFile = $_FILES['image'];
      $imageName = $imageFile['name'];
      $imageTmpName = $imageFile['tmp_name'];

      // Validate the file type
      $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
      $fileType = mime_content_type($imageTmpName);

      if (!in_array($fileType, $allowedTypes)) {
        // Handle invalid file type
        header('Location: edit.php?id=' . $id . '&error=Invalid file type.');
        exit;
      }

      // Move the uploaded file to the desired location (original resolution)
      $originalUploadPath = 'images/';
      $newImageName = 'cover_' . $id . '_' . $imageName;
      move_uploaded_file($imageTmpName, $originalUploadPath . $newImageName);

      // Generate and save the thumbnail
      $thumbnailPath = 'thumbnails/';
      $thumbnail_width = 300;
      $thumbnail_height = 300; // You can adjust this as needed

      switch ($fileType) {
        case 'image/jpeg':
          $source = imagecreatefromjpeg($originalUploadPath . $newImageName);
          break;
        case 'image/png':
          $source = imagecreatefrompng($originalUploadPath . $newImageName);
          break;
        case 'image/gif':
          $source = imagecreatefromgif($originalUploadPath . $newImageName);
          break;
        case 'image/webp':
          $source = imagecreatefromwebp($originalUploadPath . $newImageName);
          break;
        case 'image/avif':
          $source = imagecreatefromavif($originalUploadPath . $newImageName);
          break;
        case 'image/bmp':
          $source = imagecreatefrombmp($originalUploadPath . $newImageName);
          break;
        case 'image/wbmp':
          $source = imagecreatefromwbmp($originalUploadPath . $newImageName);
          break;
        default:
          header('Location: edit.php?id=' . $id . '&error=Unsupported image format.');
          exit;
      }

      $source_width = imagesx($source);
      $source_height = imagesy($source);
      $source_ratio = $source_width / $source_height;

      $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
      imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $source_width, $source_height);

      // Save the thumbnail to the thumbnails folder
      imagejpeg($thumbnail, $thumbnailPath . $newImageName, 90);

      // Free up memory
      imagedestroy($source);
      imagedestroy($thumbnail);
    }

    // Update the novel entry in the database
    $stmt = $db->prepare("UPDATE novel SET title = :title, tags = :tags, description = :description, content = :content" . ($newImageName ? ', filename = :filename' : '') . " WHERE id = :id AND email = :email");
    if ($newImageName) {
      $stmt->bindValue(':filename', $newImageName);
    }
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':tags', $tags);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':content', $content);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':email', $email);

    $stmt->execute();

    header('Location: view.php?id=' . $id);
    exit;
  }

  // Fetch the novel data for editing
  $query = "SELECT * FROM novel WHERE id=:id AND email=:email";
  $stmt = $db->prepare($query);
  $stmt->bindValue(':id', $id);
  $stmt->bindValue(':email', $email);
  $stmt->execute();
  $novel = $stmt->fetch();

  if (!$novel) {
    // Redirect to the specified URL for users who try to edit novels that don't belong to them
    $redirect_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/';
    header('Location: ' . $redirect_url);
    exit;
  }
} else {
  header('Location: view.php?id=' . $id);
  exit;
}
?>

<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit <?php echo $novel['title']; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include ('header.php'); ?>
    <div class="container mt-5">
      <nav aria-label="breadcrumb mt-2">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                ArtCODE
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white" href="view.php?id=<?php echo $id; ?>"><?php echo $novel['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white fw-bold" href="edit.php?id=<?php echo $novel['id']; ?>">
                Edit <?php echo $novel['title']; ?>
              </a>
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn bg-body-tertiary p-3 fw-bold w-100 text-start mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
              <a class="btn py-2 rounded text-start fw-medium" href="view.php?id=<?php echo $id; ?>"><?php echo $novel['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-bold" href="edit.php?id=<?php echo $novel['id']; ?>">
                <i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Edit <?php echo $novel['title']; ?>
              </a>
            </div>
          </div>
        </div>
      </nav>
      <form method="post" enctype="multipart/form-data">
        <?php if (isset($_GET['error'])): ?>
          <p><?php echo $_GET['error']; ?></p>
        <?php endif ?>
        <div class="row featurette">
          <div class="col-md-3 order-md-1 mb-2 pe-md-0" style="height: 500px;">
            <a data-bs-toggle="modal" data-bs-target="#originalImage">
              <div id="file-preview-container" class="d-flex align-items-center justify-content-center h-100 border border-3 rounded-4">
                <?php if (!empty($novel['filename'])): ?>
                  <img src="thumbnails/<?php echo $novel['filename']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover" id="coverImage">
                <?php else: ?>
                  <div class="text-center">
                    <h6><i class="bi bi-image fs-1"></i></h6>
                    <h6>Your image cover here!</h6>
                  </div>
                <?php endif; ?>
              </div>
            </a>
          </div>
          <div class="col-md-9 order-md-2 ps-md-2">
            <input class="form-control border border-3 rounded-4 mb-2" type="file" name="image" type="file" id="file-ip-1" accept="image/*" onchange="showPreview(event);">
            <div class="input-group mb-2 gap-2">
              <div class="form-floating">
                <input class="form-control border border-3 rounded-4" type="text" id="title" name="title" value="<?php echo htmlspecialchars($novel['title']); ?>" required>
                <label class="fw-medium" for="floatingInput">title</label>
              </div>
              <div class="form-floating">
                <input class="form-control border border-3 rounded-4" type="text" id="floatingInput" placeholder="tags" id="tags" name="tags" value="<?php echo htmlspecialchars($novel['tags']); ?>" required>
                <label class="fw-medium" for="floatingInput">tags</label>
              </div>
            </div>
            <div class="form-floating mb-2">
              <textarea class="form-control border border-3 rounded-4" type="text" id="floatingTextarea" placeholder="description" oninput="stripHtmlTags(this)" id="description" style="height: 384px; max-height: 384px;" name="description" required><?php echo strip_tags($novel['description']); ?></textarea>
              <label class="fw-medium" for="floatingTextarea">description</label>
            </div>
          </div>
        </div>
        <div class="form-floating mb-2">
          <textarea class="form-control vh-100 border border-3 rounded-4" type="text" id="floatingTextarea" placeholder="content" oninput="stripHtmlTags(this)" id="content" name="content" required><?php echo strip_tags($novel['content']); ?></textarea>
          <label class="fw-medium" for="floatingTextarea">content</label>
        </div>
        <div class="btn-group w-100 gap-2">
          <button class="btn btn-outline-light fw-bold text-nowrap border border-light-subtle border-3 rounded-4" type="submit" name="submit">save changes</button>
          <button type="button" class="btn btn-outline-danger fw-bold text-nowrap border border-danger-subtle border-3 rounded-4" data-bs-toggle="modal" data-bs-target="#modalDelete">
            delete this work
          </button>
        </div>
      </form>
    </div>
    <div class="mt-5"></div>
    <div class="modal fade" id="modalDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header border-bottom-0">
            <h1 class="modal-title fs-5">Delete <?php echo $novel['title'] ?></h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body py-0 text-center fw-medium">
            <p>Are you sure want to delete <strong><?php echo $novel['title'] ?></strong> from your works?</p>
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
            <img class="object-fit-contain h-100 w-100 rounded" src="images/<?php echo $novel['filename']; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
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
          <?php if (!empty($novel['filename'])): ?>
            previewContainer.innerHTML = '<img src="thumbnails/<?php echo $novel['filename']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover">';
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
