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
  $type = filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $episode_name = filter_var($_POST['episode_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $artwork_type = filter_var($_POST['artwork_type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $group = filter_var($_POST['group'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $categories = filter_var($_POST['categories'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $language = filter_var($_POST['language'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $parodies = filter_var($_POST['parodies'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $characters = filter_var($_POST['characters'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Update the images table with the selected or new episode_name
  $stmt = $db->prepare('UPDATE images SET title = :title, imgdesc = :imgdesc, link = :link, tags = :tags, type = :type, episode_name = :episode_name, artwork_type = :artwork_type, `group` = :group, categories = :categories, language = :language, parodies = :parodies, characters = :characters WHERE id = :id');
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':imgdesc', $imgdesc);
  $stmt->bindParam(':link', $link);
  $stmt->bindParam(':tags', $tags);
  $stmt->bindParam(':type', $type); // Bind the type parameter
  $stmt->bindParam(':episode_name', $episode_name);
  $stmt->bindParam(':artwork_type', $artwork_type);
  $stmt->bindParam(':group', $group);
  $stmt->bindParam(':categories', $categories);
  $stmt->bindParam(':language', $language);
  $stmt->bindParam(':parodies', $parodies);
  $stmt->bindParam(':characters', $characters);
  $stmt->bindParam(':id', $id);
  $stmt->execute();

  // Redirect to image details page after update
  header('Location: ?id=' . $id);
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit <?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <?php include('sections.php'); ?>
    <div class="container">
      <?php include('nav.php'); ?>
      <div class="mt-3">
        <div class="row g-2">
          <div class="col-md-6">
            <div class="position-relative">
              <a data-bs-toggle="modal" data-bs-target="#originalImage">
                <img src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>" class="h-100 w-100 rounded shadow">
              </a>
              <a class="position-absolute top-0 end-0 m-2 btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> opacity-75 fw-medium" href="replace.php?id=<?php echo $image['id']; ?>">replace image</a>
              <div class="">
                <div class="border border-4 bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> fw-medium rounded-3 my-2">
                  <a class="btn fw-medium w-100 border-0" data-bs-toggle="collapse" href="#collapseExpand" role="button" aria-expanded="false" aria-controls="collapseExample">
                    more information
                  </a>
                  <div class="collapse container" id="collapseExpand">
                    <?php
                      // Main image information
                      $image_size = round(filesize('../images/' . $image['filename']) / (1024 * 1024), 2);
                      list($width, $height) = getimagesize('../images/' . $image['filename']);
                    ?>
                    
                    <div class="mb-3 row">
                      <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Filename</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control-plaintext fw-medium" id="" value="<?= $image['filename'] ?>" readonly>
                      </div>
                    </div>
                
                    <div class="mb-3 row">
                      <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Data Size</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control-plaintext fw-medium" id="" value="<?= $image_size ?> MB" readonly>
                      </div>
                    </div>
                
                    <div class="mb-3 row">
                      <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Dimensions</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control-plaintext fw-medium" id="" value="<?= "{$width}x{$height}" ?>" readonly>
                      </div>
                    </div>
                
                    <div class="mb-3 row">
                      <a href="../images/<?= $image['filename'] ?>" class="text-decoration-none" download>Download Image</a>
                    </div>
                
                    <?php
                      // Child images information
                      $stmt = $db->prepare('SELECT * FROM image_child WHERE image_id = :image_id');
                      $stmt->bindParam(':image_id', $id);
                      $result = $stmt->execute();
                      
                      while ($childImage = $result->fetchArray(SQLITE3_ASSOC)) {
                        $child_image_size = round(filesize('../images/' . $childImage['filename']) / (1024 * 1024), 2);
                        list($child_width, $child_height) = getimagesize('../images/' . $childImage['filename']);
                    ?>
                    
                        <div class="border border-3 rounded-pill"></div>
                
                        <div class="mb-3 row">
                          <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Filename</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext fw-medium" id="" value="<?= $childImage['filename'] ?>" readonly>
                          </div>
                        </div>
                
                        <div class="mb-3 row">
                          <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Data Size</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext fw-medium" id="" value="<?= $child_image_size ?> MB" readonly>
                          </div>
                        </div>
                
                        <div class="mb-3 row">
                          <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Dimensions</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext fw-medium" id="" value="<?= "{$child_width}x{$child_height}" ?>" readonly>
                          </div>
                        </div>
                
                        <div class="mb-3 row">
                          <a href="../images/<?= $childImage['filename'] ?>" class="text-decoration-none" download>Download Image</a>
                        </div>
                
                    <?php
                      }
                    ?>
                  </div>
                </div>
              </div>
            </center>
          </div>
        </div>
        <div class="col-md-6">
          <div class="">
            <form method="POST">
              <div class="form-floating mb-2">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" value="<?php echo $image['title']; ?>" name="title" placeholder="Image title" maxlength="500" required>  
                <label for="floatingInput" class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium">Enter title for your image</label>
              </div>
              <div class="form-floating mb-2">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" value="<?php echo htmlspecialchars($image['tags']); ?>" name="tags" placeholder="Image tag" maxlength="500" required>
                <label for="floatingInput" class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium">Enter tag for your image</label>
              </div>
              <div class="form-floating mb-2">
                <textarea class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" oninput="stripHtmlTags(this)" type="text" value="<?php echo htmlspecialchars($image['imgdesc']); ?>" name="imgdesc" placeholder="Image description" maxlength="5000" style="height: 200px;" required><?php echo strip_tags($image['imgdesc']); ?></textarea>
                <label for="floatingInput" class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium">Enter description for your image</label>
              </div>
              <h6 class="fw-medium mb-2 mt-4">Group is optional, to displaying group names for <a class="text-decoration-none fw-medium" href="/manga/?group=">manga section only!</a></h6>
              <div class="form-floating mb-2">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" value="<?php echo $image['group']; ?>" name="group" placeholder="Image group" maxlength="4500">  
                <label for="floatingInput" class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium">Enter group for your image</label>
              </div>
              <h6 class="fw-medium mb-2 mt-4">Characters is optional, to displaying character names for <a class="text-decoration-none fw-medium" href="/manga/?character=">manga section only!</a></h6>
              <div class="form-floating mb-2">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" value="<?php echo htmlspecialchars($image['characters']); ?>" name="characters" placeholder="Image characters" maxlength="4500">
                <label for="floatingInput" class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium">Enter characters for your image</label>
              </div>
              <h6 class="fw-medium mb-2 mt-4">Parodies is optional, to displaying fiction names for <a class="text-decoration-none fw-medium" href="/manga/?parody=">manga section only!</a></h6>
              <div class="form-floating mb-4">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" value="<?php echo htmlspecialchars($image['parodies']); ?>" name="parodies" placeholder="Image parodies" maxlength="4500">
                <label for="floatingInput" class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium">Enter parodies for your image</label>
              </div>
              <div class="form-floating mb-2">
                <select class="form-select border-0 bg-body-tertiary shadow rounded-3 fw-medium py-0 text-start" name="episode_name">
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
              <div class="row">
                <div class="col-md-6 pe-md-1">
                  <select class="form-select border-0 bg-body-tertiary shadow rounded-3 fw-medium mb-2" style="height: 58px;" name="artwork_type" aria-label="Large select example" required>
                    <option value="illustration" <?php echo ($image['artwork_type'] === 'illustration') ? 'selected' : ''; ?>>Illustration</option>
                    <option value="manga" <?php echo ($image['artwork_type'] === 'manga') ? 'selected' : ''; ?>>Manga</option>
                  </select>
                </div>
                <div class="col-md-6 ps-md-1">
                  <select class="form-select border-0 bg-body-tertiary shadow rounded-3 fw-medium mb-2" style="height: 58px;" name="type" aria-label="Large select example" required>
                    <option value="safe" <?php echo ($image['type'] === 'safe') ? 'selected' : ''; ?>>Safe For Works</option>
                    <option value="nsfw" <?php echo ($image['type'] === 'nsfw') ? 'selected' : ''; ?>>NSFW/R-18</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 pe-md-1">
                  <select class="form-select border-0 bg-body-tertiary shadow rounded-3 fw-medium mb-2" style="height: 58px;" name="categories" aria-label="Large select example" required>
                    <option value="artworks/illustrations" <?php echo ($image['categories'] === 'artworks/illustrations') ? 'selected' : ''; ?>>artworks/illustrations</option>
                    <option value="3DCG" <?php echo ($image['categories'] === '3DCG') ? 'selected' : ''; ?>>3DCG</option>
                    <option value="real" <?php echo ($image['categories'] === 'real') ? 'selected' : ''; ?>>real</option>
                    <option value="MMD" <?php echo ($image['categories'] === 'MMD') ? 'selected' : ''; ?>>MMD</option>
                    <option value="multi-work series" <?php echo ($image['categories'] === 'multi-work series') ? 'selected' : ''; ?>>multi-work series</option>
                    <option value="manga series" <?php echo ($image['categories'] === 'manga series') ? 'selected' : ''; ?>>manga series</option>
                    <option value="doujinshi series" <?php echo ($image['categories'] === 'doujinshi series') ? 'selected' : ''; ?>>doujinshi series</option>
                    <option value="oneshot manga" <?php echo ($image['categories'] === 'oneshot manga') ? 'selected' : ''; ?>>oneshot manga</option>
                    <option value="oneshot doujinshi" <?php echo ($image['categories'] === 'oneshot doujinshi') ? 'selected' : ''; ?>>oneshot doujinshi</option>
                    <option value="doujinshi" <?php echo ($image['categories'] === 'doujinshi') ? 'selected' : ''; ?>>doujinshi</option>
                  </select>
                </div>
                <div class="col-md-6 ps-md-1">
                  <select class="form-select border-0 bg-body-tertiary shadow rounded-3 fw-medium mb-2" style="height: 58px;" name="language" aria-label="Large select example" required>
                    <option value="English" <?php echo ($image['language'] === 'English') ? 'selected' : ''; ?>>English</option>
                    <option value="Japanese" <?php echo ($image['language'] === 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                    <option value="Chinese" <?php echo ($image['language'] === 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                    <option value="Korean" <?php echo ($image['language'] === 'Korean') ? 'selected' : ''; ?>>Korean</option>
                    <option value="Russian" <?php echo ($image['language'] === 'Russian') ? 'selected' : ''; ?>>Russian</option>
                    <option value="Indonesian" <?php echo ($image['language'] === 'Indonesian') ? 'selected' : ''; ?>>Indonesian</option>
                    <option value="Spanish" <?php echo ($image['language'] === 'Spanish') ? 'selected' : ''; ?>>Spanish</option>
                    <option value="Other" <?php echo ($image['language'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    <option value="None" <?php echo ($image['language'] === 'None') ? 'selected' : ''; ?>>None</option>
                  </select>
                </div>
              </div>
              <div class="form-floating mb-2">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" value="<?php echo htmlspecialchars($image['link']); ?>" name="link" placeholder="Image link" maxlength="300"> 
                <label for="floatingInput" class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium">Enter link for your image</label>
              </div>
              <div class="btn-group gap-2 w-100 mb-2">
                <button type="button" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium w-50 rounded" data-bs-toggle="modal" data-bs-target="#deleteImage">
                  <i class="bi bi-trash-fill"></i> delete
                </button>
                <button type="submit" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium w-50 rounded">
                  <i class="bi bi-floppy-fill"></i> save
                </button>
              </div>
              <div class="btn-group gap-2 w-100 mb-2">
                <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium w-50 rounded" href="upload.php?id=<?php echo $image['id']; ?>">
                  <i class="bi bi-cloud-arrow-up-fill"></i> upload new images child
                </a>
                <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium w-50 rounded" href="all.php?id=<?php echo $image['id']; ?>">
                  <i class="bi bi-images"></i> view all images child
                </a>
              </div>
              <div class="btn-group w-100 mb-2">
                <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium w-50" href="redirect.php?back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/profile.php'); ?>">Back to Profile</a>
                <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium w-50" href="../image.php?artworkid=<?php echo $image['id']; ?>">Back to Artwork</a>
              </div>
              <a class="btn w-100 btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium" href="export.php?artworkid=<?php echo $image['id']; ?>">Export Your Artwork</a>
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