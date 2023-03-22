<?php
require_once('prompt.php'); 

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// If the "Remove" button is clicked, delete the image from the database and the folder
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $filename = $row['filename'];
        unlink('../images/' . $filename);
        unlink('../thumbnails/' . $filename);
        $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Query to get all images from the "images" table
$stmt = $db->query('SELECT * FROM images ORDER BY id DESC');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>
    <ul class="me-2 ms-2 mt-2 nav nav-pills nav-fill justify-content-center">
      <li class="nav-item"><a class="nav-link" href="../admin/index.php"><i class="bi bi-house-fill"></i></a></li>
      <li class="nav-item"><a class="nav-link" href="../admin/edit_users.php"><i class="bi bi-person-fill-gear"></i></a></li>
      <li class="nav-item"><a class="nav-link" aria-current="page" href="../admin/update_news.php"><i class="bi bi-newspaper"></i></a></li>
      <li class="nav-item"><a class="nav-link active" href="../admin/remove_images.php"><i class="bi bi-images"></i></a></li> 
      <li class="nav-item"><a class="nav-link" href="../admin/remove_all.php"><i class="bi bi-person-fill-exclamation"></i></a></li>
    </ul>
    <div class="container mt-5">
      <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?> 
        <div class="card mb-2">
          <div class="card-header bg-primary text-white fw-bold">
            <i class="bi bi-images"></i>
            Images
          </div>
          <div class="card-body">
            <img data-src="../thumbnails/<?= $row['filename'] ?>" class="rounded img-thumbnail lazy-load" style="width: 100%; height: 200px; object-fit: cover;">
            <h5 class="card-title mt-1"><?= $row['filename'] ?></h5>
            <p class="card-text fw-bold mt-1">Username: <?= $row['username'] ?></p>
            <p class="card-text fw-bold mt-1">Title: <?= $row['title'] ?></p>
            <p class="card-text fw-bold mt-1">Desc: <?= $row['imgdesc'] ?></p>
            <p class="card-text fw-bold mt-1">Link: <a href="<?= $row['link'] ?>"><?= $row['link'] ?></a></p>
            <p class="card-text fw-bold mt-1">Tags: <?= $row['tags'] ?></p>
            <form method="post" action="" class="mt-3">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this image?')">
                <i class="bi bi-trash"></i> Remove
              </button>
            </form>
          </div>
        </div>
      <?php endwhile; ?> 
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
          let lazyloadImages;
          if("IntersectionObserver" in window) {
            lazyloadImages = document.querySelectorAll(".lazy-load");
            let imageObserver = new IntersectionObserver(function(entries, observer) {
              entries.forEach(function(entry) {
                if(entry.isIntersecting) {
                  let image = entry.target;
                  image.src = image.dataset.src;
                  image.classList.remove("lazy-load");
                  imageObserver.unobserve(image);
                }
              });
            });
            lazyloadImages.forEach(function(image) {
              imageObserver.observe(image);
            });
          } else {
            let lazyloadThrottleTimeout;
            lazyloadImages = document.querySelectorAll(".lazy-load");

            function lazyload() {
              if(lazyloadThrottleTimeout) {
                clearTimeout(lazyloadThrottleTimeout);
              }
              lazyloadThrottleTimeout = setTimeout(function() {
                let scrollTop = window.pageYOffset;
                lazyloadImages.forEach(function(img) {
                  if(img.offsetTop < (window.innerHeight + scrollTop)) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-load');
                  }
                });
                if(lazyloadImages.length == 0) {
                  document.removeEventListener("scroll", lazyload);
                  window.removeEventListener("resize", lazyload);
                  window.removeEventListener("orientationChange", lazyload);
                }
              }, 20);
            }
            document.addEventListener("scroll", lazyload);
            window.addEventListener("resize", lazyload);
            window.addEventListener("orientationChange", lazyload);
          }
        })
    </script>
</body>
</html>
