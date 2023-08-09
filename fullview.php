<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Get the filename from the query string
$filename = $_GET['artworkid'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename ");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];
$email = $image['email'];

// Get the previous image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id < :id AND email = :email ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $email);
$stmt->execute();
$prev_image = $stmt->fetch();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: fullview.php?artworkid={$image['id']}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: fullview.php?artworkid={$image['id']}");
  exit();
}

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?> 

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <!DOCTYPE html>
    <html lang="en" data-bs-theme="dark">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?php echo $image['title']; ?></title>
      <link rel="icon" type="image/png" href="icon/favicon.png">
      <?php include('bootstrapcss.php'); ?>
    </head>
    <body>
      <div id="content">
        <?php if (empty($image['filename'])) : ?>
          <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
            <h1 class="fw-bold">Image not found</h1>
            <div class="d-flex justify-content-center">
              <a class="btn btn-primary fw-bold" href="/">back to home</a>
            </div>
          </div>
        <?php else : ?>
          <img src="images/<?php echo $image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
          <div id="scrollButton" class="btn-group-vertical position-fixed top-50 end-0">
            <button class="text-start btn btn-primary rounded-end-0 fw-bold" id="moreInfoButton"><i class="bi bi-caret-left-fill"></i></button>
            <button class="text-start fw-bold btn btn-primary rounded-end-0 mb-0" id="option1"><i class="bi bi-fullscreen text-stroke"></i></button>
            <button class="text-start fw-bold btn btn-primary rounded-0 mb-0" id="option2"><i class="bi bi-file-image"></i></button>
            <?php
            $image_id = $image['id'];
            $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
            $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':image_id', $image_id);
            $stmt->execute();
            $is_favorited = $stmt->fetchColumn();
            if ($is_favorited) : ?>
              <form class="w-100" action="fullview.php?artworkid=<?php echo $image['id']; ?>" method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="text-start btn btn-primary rounded-0 fw-bold" name="unfavorite" id="unfavoriteButton">
                  <i class="bi bi-heart-fill"></i>
                </button>
              </form>
            <?php else : ?>
              <form class="w-100" action="fullview.php?artworkid=<?php echo $image['id']; ?>" method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="text-start btn btn-primary rounded-0 fw-bold" name="favorite" id="favoriteButton">
                  <i class="bi bi-heart"></i>
                </button>
              </form>
            <?php endif; ?>
            <a class="text-start fw-bold btn btn-primary rounded-end-0" id="option3"
               href="image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-arrow-left-circle-fill"></i></a>
          </div>
        <?php endif; ?>
        <?php foreach ($child_images as $child_image) : ?>
          <?php if (empty($child_image['filename'])) : ?>
            <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
              <h1 class="fw-bold">Image not found</h1>
              <div class="d-flex justify-content-center">
                <a class="btn btn-primary fw-bold" href="/">back to home</a>
              </div>
            </div>
          <?php else : ?>
            <img src="images/<?php echo $child_image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;"
                 alt="<?php echo $image['title']; ?>">
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <style>
        #scrollButton {
          transition: opacity 0.5s ease-in-out; /* Add smooth opacity transition */
          opacity: 1; /* Initially visible */
        }
        
        .text-stroke {
          -webkit-text-stroke: 1px;
        }
      </style>
      <script>
        var lastScrollPosition = 0;

        window.addEventListener("scroll", function () {
          var currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
          var scrollButton = document.getElementById("scrollButton");

          if (currentScrollPosition > lastScrollPosition) {
            scrollButton.style.opacity = "0"; // Scroll down, fade out button
          } else {
            scrollButton.style.opacity = "1"; // Scroll up, fade in button
          }

          lastScrollPosition = currentScrollPosition;
        });

        const option1Button = document.getElementById('option1');
        const option2Button = document.getElementById('option2');
        const contentDiv = document.getElementById('content');

        option1Button.addEventListener('click', function () {
          contentDiv.classList.remove('container', 'my-5');
        });

        option2Button.addEventListener('click', function () {
          contentDiv.classList.add('container', 'my-5');
        });

        document.addEventListener('DOMContentLoaded', function () {
          const moreInfoButton = document.getElementById('moreInfoButton');
          const option1Button = document.getElementById('option1');
          const option2Button = document.getElementById('option2');
          const option3Button = document.getElementById('option3');
          const favoriteButton = document.getElementById('favoriteButton');
          const unfavoriteButton = document.getElementById('unfavoriteButton');

          let isInfoExpanded = false;

          moreInfoButton.addEventListener('click', function () {
            isInfoExpanded = !isInfoExpanded;

            if (isInfoExpanded) {
              moreInfoButton.innerHTML = '<i class="bi bi-caret-right-fill"></i> less information';
              option1Button.innerHTML = '<i class="bi bi-fullscreen text-stroke"></i> full images';
              option2Button.innerHTML = '<i class="bi bi-file-image"></i> container';
              option3Button.innerHTML = '<i class="bi bi-arrow-left-circle-fill"></i> back';
              if (unfavoriteButton) {
                unfavoriteButton.innerHTML = '<i class="bi bi-heart-fill"></i> unfavorite';
                unfavoriteButton.classList.add('w-100');
              }
              if (favoriteButton) {
                favoriteButton.innerHTML = '<i class="bi bi-heart"></i> favorite';
                favoriteButton.classList.add('w-100');
              }
            } else {
              moreInfoButton.innerHTML = '<i class="bi bi-caret-left-fill"></i>';
              option1Button.innerHTML = '<i class="bi bi-fullscreen text-stroke"></i>';
              option2Button.innerHTML = '<i class="bi bi-file-image"></i>';
              option3Button.innerHTML = '<i class="bi bi-arrow-left-circle-fill"></i>';
              if (unfavoriteButton) unfavoriteButton.innerHTML = '<i class="bi bi-heart-fill"></i>';
              if (favoriteButton) favoriteButton.innerHTML = '<i class="bi bi-heart"></i>';
            }
          });
        });
      </script>
      <?php include('bootstrapjs.php'); ?>
    </body>
    </html>
  </body>
</html>
