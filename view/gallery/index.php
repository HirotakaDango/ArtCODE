<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: ../../session.php");
  exit;
}

// Connect to the database using PDO
$db = new PDO('sqlite:../../database.sqlite');

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

// Get the next image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id > :id AND email = :email ORDER BY id ASC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $email);
$stmt->execute();
$next_image = $stmt->fetch();

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
  header("Location: ?artworkid={$image['id']}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: ?artworkid={$image['id']}");
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
        <img src="../../images/<?php echo $image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
        <div id="scrollButton" class="btn-group position-fixed bottom-0 start-50 translate-middle-x mb-2">
          <button class="text-start fw-bold btn btn-primary rounded-end-0 mb-0 rounded-start-4" id="option1"><i class="fs-4 bi bi-fullscreen text-stroke"></i></button>
          <button class="text-start fw-bold btn btn-primary rounded-0 mb-0" id="option2"><i class="fs-4 bi bi-file-image"></i></button>
          <?php
            $image_id = $image['id'];
            $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
            $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':image_id', $image_id);
            $stmt->execute();
            $is_favorited = $stmt->fetchColumn();
            if ($is_favorited) : ?>
            <form class="w-100" method="POST">
              <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
              <button type="submit" class="text-start btn btn-primary rounded-0 fw-bold" name="unfavorite" id="unfavoriteButton">
                <i class="fs-4 bi bi-heart-fill"></i>
              </button>
            </form>
          <?php else : ?>
            <form class="w-100" method="POST">
              <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
              <button type="submit" class="text-start btn btn-primary rounded-0 fw-bold" name="favorite" id="favoriteButton">
                <i class="fs-4 bi bi-heart text-stroke"></i>
              </button>
            </form>
          <?php endif; ?>
          <button class="text-start fw-bold btn btn-primary rounded-end-0" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fs-4 bi bi-info-circle-fill"></i></button>
          <?php if ($next_image): ?>
            <button class="text-start fw-bold btn btn-primary rounded-end-0" id="option5" onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
              <i class="fs-4 bi bi-chevron-left text-stroke"></i>
            </button>
          <?php endif; ?> 
          <?php if ($prev_image): ?>
            <button class="text-start fw-bold btn btn-primary rounded-end-0" id="option6" onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
              <i class="fs-4 bi bi-chevron-right text-stroke"></i>
            </button>
          <?php endif; ?>
          <a class="text-start fw-bold btn btn-primary rounded-end-4" id="option3" href="../../image.php?artworkid=<?php echo $image['id']; ?>"><i class="fs-4 bi bi-arrow-left-circle-fill"></i></a>
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
          <img src="../../images/<?php echo $child_image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
        <?php endif; ?>
      <?php endforeach; ?>
      <?php
        // Function to calculate the size of an image in MB
        function getImageSizeInMB($filename) {
          return round(filesize('../../images/' . $filename) / (1024 * 1024), 2);
        }

        // Get the total size of images from 'images' table
        $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
        $stmt->bindParam(':filename', $filename);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get the total size of images from 'image_child' table
        $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
        $stmt->bindParam(':filename', $filename);
        $stmt->execute();
        $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $images_total_size = 0;
        foreach ($images as $image) {
          $images_total_size += getImageSizeInMB($image['filename']);
        }

        $image_child_total_size = 0;
        foreach ($image_childs as $image_child) {
          $image_child_total_size += getImageSizeInMB($image_child['filename']);
        }
      
        $total_size = $images_total_size + $image_child_total_size;
      ?>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body">
            <p class="text-start fw-bold" style="word-wrap: break-word;">
              <?php
                if (!empty($image['imgdesc'])) {
                  $messageText = $image['imgdesc'];
                  $messageTextWithoutTags = strip_tags($messageText);
                  $pattern = '/\bhttps?:\/\/\S+/i';

                  $formattedText = preg_replace_callback($pattern, function ($matches) {
                    $url = htmlspecialchars($matches[0]);
                    return '<a href="' . $url . '">' . $url . '</a>';
                  }, $messageTextWithoutTags);

                  $formattedTextWithLineBreaks = nl2br($formattedText);
                    echo $formattedTextWithLineBreaks;
                  } else {
                    echo "Image description is empty.";
                }
              ?>
            </p>
            <h6 class="text-start fw-bold"><?php echo $total_size; ?> MB</h6>
          </div>
        </div>
      </div>
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
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
