<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new SQLite3('../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

$idEdit = $_GET['id'];

// Retrieve image details
if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Retrieve the email of the logged-in user
  $email = $_SESSION['email'];

  // Select the image details using the image ID and the email of the logged-in user
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id AND email = :email');
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
  $result = $stmt->execute();
  $image = $result->fetchArray(SQLITE3_ASSOC); // Retrieve result as an associative array

  // Check if the image exists and belongs to the logged-in user
  if (!$image) {
    echo '<meta charset="UTF-8"> 
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">
         ';
    exit();
  }
} else {
  // Redirect to the error page if the image ID is not specified
  header('Location: ?id=' . $id);
  exit();
}

// Update image details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $imgdesc = nl2br(filter_var($_POST['imgdesc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $link = filter_var($_POST['link'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $type = filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW); // Sanitize the type input

  // Check if the user provided a new episode name
  $new_episode_name = trim($_POST['new_episode_name']);
  if (!empty($new_episode_name)) {
    // Check if the episode_name already exists in the episode table
    $stmt = $db->prepare('SELECT * FROM episode WHERE email = :email AND episode_name = :episode_name');
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':episode_name', $new_episode_name);
    $result = $stmt->execute();
    $existingEpisode = $result->fetchArray(SQLITE3_ASSOC);

    // If episode_name doesn't exist, insert it into both episode and images tables
    if (!$existingEpisode) {
      $stmt = $db->prepare('INSERT INTO episode (email, episode_name) VALUES (:email, :episode_name)');
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':episode_name', $new_episode_name);
      $stmt->execute();

      // Update the images table with the new episode_name
      $episode_name = $new_episode_name;
    }
  } else {
    // Check if the episode_name already exists in the episode table
    $episode_name = $_POST['selected_episode_name'];
    $stmt = $db->prepare('SELECT * FROM episode WHERE email = :email AND episode_name = :episode_name');
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':episode_name', $episode_name);
    $result = $stmt->execute();
    $existingEpisode = $result->fetchArray(SQLITE3_ASSOC);

    // If episode_name exists, use it for the update
    if (!$existingEpisode) {
      // If episode_name doesn't exist, insert it into both episode and images tables
      $stmt = $db->prepare('INSERT INTO episode (email, episode_name) VALUES (:email, :episode_name)');
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':episode_name', $episode_name);
      $stmt->execute();
    }
  }

  // Update the images table with the selected or new episode_name
  $stmt = $db->prepare('UPDATE images SET title = :title, imgdesc = :imgdesc, link = :link, tags = :tags, type = :type, episode_name = :episode_name WHERE id = :id');
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':imgdesc', $imgdesc);
  $stmt->bindParam(':link', $link);
  $stmt->bindParam(':tags', $tags);
  $stmt->bindParam(':type', $type); // Bind the type parameter
  $stmt->bindParam(':episode_name', $episode_name);
  $stmt->bindParam(':id', $id);
  $stmt->execute();

  // Redirect to image details page after update
  header('Location: ?id=' . $id);
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit <?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <div class="container-fluid mt-5">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-secondary bg-opacity-25 rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/image.php?artworkid=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/?id=<?php echo $image['id']; ?>">Edit <?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item mb-2 mb-md-0">
              <a class="link-body-emphasis py-2 text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/upload.php?id=<?php echo $image['id']; ?>">Upload new images to <?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/all.php?id=<?php echo $image['id']; ?>">All images from <?php echo $image['title']; ?></a>
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn bg-secondary p-3 bg-opacity-25 fw-bold w-100 text-start" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-secondary bg-opacity-25 mt-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">Home</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/image.php?artworkid=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/?id=<?php echo $image['id']; ?>"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Edit <?php echo $image['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/upload.php?id=<?php echo $image['id']; ?>">Upload new images to <?php echo $image['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/all.php?id=<?php echo $image['id']; ?>">All images from <?php echo $image['title']; ?></a>
            </div>
          </div>
        </div>
      </nav>
      <div class="mt-3">
        <div class="row">
          <div class="col-md-4 pe-md-1">
            <div class="position-relative">
              <a data-bs-toggle="modal" data-bs-target="#originalImage">
                <img src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>" class="h-100 w-100 rounded shadow">
              </a>
              <a class="position-absolute top-0 end-0 m-2 btn btn-sm btn-dark opacity-75 fw-bold" href="replace.php?id=<?php echo $image['id']; ?>">replace image</a>
              <div class="text-c">
                <div class="border border-4 bg-light text-dark fw-bold rounded-3 my-2">
                  <a class="btn fw-bold text-dark w-100 border-0" data-bs-toggle="collapse" href="#collapseExpand" role="button" aria-expanded="false" aria-controls="collapseExample">
                    more information
                  </a>
                  <div class="collapse container" id="collapseExpand">
                    <?php
                      // Your existing code for the main image
                      $image_size = round(filesize('../images/' . $image['filename']) / (1024 * 1024), 2);
                      list($width, $height) = getimagesize('../images/' . $image['filename']);

                      echo "<div class='mb-3 row'>
                              <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Filename</label>
                                <div class='col-sm-8'>
                                  <input type='text' class='form-control-plaintext fw-bold' id='' value='" . $image['filename'] . "' readonly>
                                </div>
                            </div>";
  
                      echo "<div class='mb-3 row'>
                              <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Data Size</label>
                              <div class='col-sm-8'>
                                <input type='text' class='form-control-plaintext fw-bold' id='' value='{$image_size} MB' readonly>
                              </div>
                            </div>";
  
                      echo "<div class='mb-3 row'>
                              <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Dimensions</label>
                              <div class='col-sm-8'>
                                <input type='text' class='form-control-plaintext fw-bold' id='' value='{$width}x{$height}' readonly>
                              </div>
                            </div>";
  
                      echo "<div class='mb-3 row'>
                              <a href=\"../images/{$image['filename']}\" download>Download Image</a>
                            </div>";

                      // Retrieve child images' information
                      $stmt = $db->prepare('SELECT * FROM image_child WHERE image_id = :image_id');
                      $stmt->bindParam(':image_id', $id);
                      $result = $stmt->execute();
                      // Loop through child images
                      while ($childImage = $result->fetchArray(SQLITE3_ASSOC)) {
                        $child_image_size = round(filesize('../images/' . $childImage['filename']) / (1024 * 1024), 2);
                        list($child_width, $child_height) = getimagesize('../images/' . $childImage['filename']);

                        echo "<hr class='border-dark border-5 rounded-pill'>";

                        echo "<div class='mb-3 row'>
                                <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Filename</label>
                                  <div class='col-sm-8'>
                                    <input type='text' class='form-control-plaintext fw-bold' id='' value='" . $childImage['filename'] . "' readonly>
                                  </div>
                              </div>";

                        echo "<div class='mb-3 row'>
                                <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Data Size</label>
                                <div class='col-sm-8'>
                                  <input type='text' class='form-control-plaintext fw-bold' id='' value='{$child_image_size} MB' readonly>
                                </div>
                               </div>";

                        echo "<div class='mb-3 row'>
                                <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Dimensions</label>
                                <div class='col-sm-8'>
                                  <input type='text' class='form-control-plaintext fw-bold' id='' value='{$child_width}x{$child_height}' readonly>
                                </div>
                              </div>";

                        echo "<div class='mb-3 row'>
                                <a href=\"../images/{$childImage['filename']}\" download>Download Image</a>
                              </div>";
                      }
                    ?>

                  </div>
                </div>
              </div>
            </center>
          </div>
        </div>
        <div class="col-md-8 ps-md-1">
          <div class="">
            <form method="POST">
              <div class="row">
                <div class="col-md-6 pe-md-1">
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['title']); ?>" name="title" placeholder="Image title" maxlength="500" required>  
                    <label for="floatingInput" class="text-dark fw-bold">Enter title for your image</label>
                  </div>
                </div>
                <div class="col-md-6 ps-md-1">
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['tags']); ?>" name="tags" placeholder="Image tag" maxlength="500" required>
                    <label for="floatingInput" class="text-dark fw-bold">Enter tag for your image</label>
                  </div>
                </div>
              </div>
              <div class="form-floating mb-2">
                <textarea class="form-control border rounded-3 text-dark fw-bold border-4" oninput="stripHtmlTags(this)" type="text" value="<?php echo htmlspecialchars($image['imgdesc']); ?>" name="imgdesc" placeholder="Image description" maxlength="2000" style="height: 200px;" required><?php echo strip_tags($image['imgdesc']); ?></textarea>
                <label for="floatingInput" class="text-dark fw-bold">Enter description for your image</label>
              </div>
              <div class="row">
                <div class="col-md-6 pe-md-1">
                  <div class="form-floating mb-2">
                    <select class="form-select border rounded-3 fw-bold border-4 py-0 text-start" name="selected_episode_name">
                      <option class="form-control" value="">Make it empty to add your own episode:</option>
                      <?php
                        // Connect to the SQLite database
                        $db = new SQLite3('../database.sqlite');

                        // Get the email of the current user
                        $email = $_SESSION['email'];

                        // Retrieve the list of albums created by the current user
                        $stmt = $db->prepare('SELECT * FROM episode WHERE email = :email ORDER BY id DESC');
                        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                        $results = $stmt->execute();

                        // Loop through each episode and create an option in the dropdown list
                        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                          $episode_name = $row['episode_name'];
                          $id = $row['id'];
                          $selected = ($image['episode_name'] === $episode_name) ? 'selected' : '';
                          echo '<option value="' . htmlspecialchars($episode_name) . '" ' . $selected . '>' . htmlspecialchars($episode_name) . '</option>';
                        }

                        $db->close();
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 ps-md-1">
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['episode_name']); ?>" name="new_episode_name" id="new_episode_name" placeholder="Add episode name" maxlength="500">  
                    <label for="episode_name" class="fw-bold">Add episode name</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 pe-md-1">
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['link']); ?>" name="link" placeholder="Image link" maxlength="300"> 
                    <label for="floatingInput" class="text-dark fw-bold">Enter link for your image</label>
                  </div>
                </div>
                <div class="col-md-6 ps-md-1">
                  <select class="form-select rounded-3 text-dark fw-bold mb-2 border-4" style="height: 58px;" name="type" aria-label="Large select example" required>
                    <option value="safe" <?php echo ($image['type'] === 'safe') ? 'selected' : ''; ?>>Safe For Works</option>
                    <option value="nsfw" <?php echo ($image['type'] === 'nsfw') ? 'selected' : ''; ?>>NSFW/R-18</option>
                  </select>
                </div>
              </div>
              <div class="btn-group gap-2 w-100">
                <button type="button" class="btn btn-dark fw-bold w-100 mb-2 rounded" data-bs-toggle="modal" data-bs-target="#deleteImage">
                  <i class="bi bi-trash-fill"></i> delete this image
                </button>
                <button type="submit" class="btn btn-dark fw-bold w-100 mb-2 rounded">
                  <i class="bi bi-floppy-fill"></i> save changes
                </button>
              </div>
              <div class="btn-group gap-2 w-100">
                <a class="btn btn-dark fw-bold w-100 mb-2 rounded" href="upload.php?id=<?php echo $image['id']; ?>">
                  <i class="bi bi-cloud-arrow-up-fill"></i> upload new images child
                </a>
                <a class="btn btn-dark fw-bold w-100 mb-2 rounded" href="all.php?id=<?php echo $image['id']; ?>">
                  <i class="bi bi-images"></i> all images child
                </a>
              </div>
              <div class="btn-group w-100">
                <a class="btn btn-dark fw-bold" href="../profile.php">Back to Profile</a>
                <a class="btn btn-dark fw-bold" href="../image.php?artworkid=<?php echo $image['id']; ?>">Back to Image</a>
              </div>
              <div class="mt-5"></div>
            </form> 
          </div> 
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="../images/<?php echo $image['filename']; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="deleteImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-body p-4 text-center">
            <h5 class="mb-0">Delete this image?</h5>
            <p class="mb-0">This action can't be undone</p>
          </div>
          <form method="POST" action="delete.php">
            <input type="hidden" name="id" value="<?php echo $idEdit; ?>">
            <div class="modal-footer flex-nowrap p-0">
              <button type="submit" class="btn btn-lg btn-link fs-6 text-danger text-decoration-none col-6 py-3 m-0 rounded-0 border-end"><strong>Yes, delete</strong></button>
              <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script>
      function goBack() {
        window.location.href = "../image.php?artworkid=<?php echo $image['id']; ?>";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>