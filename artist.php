<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Get the ID of the selected user from the URL
$id = $_GET['id'];

// Get the artist name for the selected user from the users table
$query = $db->prepare('SELECT artist FROM users WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$artist = $user['artist'];

// Get the artist name for the selected user from the users table
$query = $db->prepare('SELECT desc FROM users WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$desc = $user['desc'];

// Get the artist name for the selected user from the users table
$query = $db->prepare('SELECT pic FROM users WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$pic = $user['pic'];

// Get all images for the selected user from the images table
$query = $db->prepare('SELECT images.filename FROM images JOIN users ON images.username = users.username WHERE users.id = :id ORDER BY images.id DESC');
$query->bindParam(':id', $id);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Artist <?php echo $id; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>
    <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
      <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
        <div class="bb1 container">
          <a class="nav-link" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
          <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
          <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
          <a class="nav-link px-2 text-secondary" href="users.php"><i class="bi bi-person-fill"></i></a>
          <div class="dropdown">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item" href="tags.php"><i class="bi bi-tags-fill"></i> Tags</a></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </center>  
    <center>
      <div class="container w-auto mb-2">
        <div class="row justify-content-center">
          <div>
            <div class="card">
              <div class="card-body">
                <div class="row featurette container">
                  <div class="col-md-5 order-md-1">
                    <img class="img-thumbnail" src="<?php echo $pic; ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 4px;">
                  </div>
                  <div class="col-md-7 order-md-2">
                    <h3 class="text-secondary ms-1 mt-2"><i class="bi bi-person-circle"></i> <?php echo $artist; ?> <i class="ms-2 bi bi-images"></i> <?php echo count($images); ?> </h3>
                    <p class="text-secondary text-center fw-bold"><?php echo $desc; ?></p>
                  </div>      
                </div> 
              </div>
            </div>
          </div>
        </div>
      </div>
    </center>
    <div class="images">
        <?php foreach ($images as $image): ?>
            <div>
                <a class="open-modal" href="#" data-src="images/<?php echo $image['filename']; ?>">
                    <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>">
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Modal -->
    <div class="modal mt-5" id="myModal">
      <a class="dirdown" id="downloadBtn" href="" download>
        <button type="button" class="btn btn-secondary mt-2 download-btn fw-bold"><i class="bi bi-cloud-arrow-down-fill"></i> Download</button>
      </a>
      <span class="close btn btn-secondary mt-2"><i class="bi bi-x-circle-fill"></i></span>
      <center>
        <div style="width: 80%; margin-bottom: -8px;">
          <button class="btn btn-secondary mt-2 next" id="prevBtn"><i class="bi bi-arrow-left-circle-fill"></i></button> 
          <button class="btn btn-secondary mt-2 previous" id="nextBtn"><i class="bi bi-arrow-right-circle-fill"></i></button>
        </div>
      </center>
      <center><img class="modal-content" id="img01"/></center>
      <div class="mt-5"></div>
    </div>
    
    <style>
    .image-container {
      margin-bottom: -16px;  
    }
      
    .images {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      grid-gap: 2px;
      justify-content: center;
      margin-right: 3px;
      margin-left: 3px;
    }

    .images a {
      display: block;
      border-radius: 4px;
      overflow: hidden;
      border: 2px solid #ccc;
    }

    .images img {
      width: 100%;
      height: auto;
      object-fit: cover;
      height: 200px;
      transition: transform 0.5s ease-in-out;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      padding-top: 70px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      overflow: auto;
      background-color: #fff;
    }

    .modal-content {
      max-width: 99%;
      max-height: auto;
    }

    .close {
      position: absolute;
      top: 4px;
      right: 75px;
      color: gray;
      font-weight: bold;
      transition: 0.3s;
      color: white;
    }

    .close:hover,
    .close:focus {
      color: #bbb;
      text-decoration: none;
      cursor: pointer;
    }

    .dirdown {
      position: absolute;
      top: 4px;
      left: 75px;
      color: #f1f1f1;
      font-weight: bold;
      transition: 0.3s;
    } 
    
    .next {
      position: absolute;
      top: 4px;
      left: 15px;
      color: #f1f1f1;
      font-weight: bold;
      transition: 0.3s;
    } 
    
    .previous {
      position: absolute;
      top: 4px;
      right: 15px;
      color: #f1f1f1;
      font-weight: bold;
      transition: 0.3s;
    } 
    </style>
<script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the image and insert it inside the modal
var modalImg = document.getElementById("img01");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// Get the download button
var downloadBtn = document.getElementById("downloadBtn");

// Get all elements with class "open-modal"
var elements = document.getElementsByClassName("open-modal");

// Store the current index of the image in a variable
var currentIndex;

// Store the current position of the page
var currentPosition;

// Loop through the elements and add a click event listener to each
for (var i = 0; i < elements.length; i++) {
  elements[i].addEventListener("click", function() {
    currentPosition = window.pageYOffset;
    currentIndex = Array.from(elements).indexOf(this);
    modal.style.display = "block";
    modalImg.src = this.getAttribute("data-src");
    downloadBtn.href = this.getAttribute("data-src");
  });
}

// Get the previous button
var prevBtn = document.getElementById("prevBtn");

// Get the next button
var nextBtn = document.getElementById("nextBtn");

// When the user clicks on the previous button, show the previous image
prevBtn.addEventListener("click", function() {
  currentIndex--;
  if (currentIndex < 0) {
    currentIndex = elements.length - 1;
  }
  modalImg.src = elements[currentIndex].getAttribute("data-src");
  downloadBtn.href = elements[currentIndex].getAttribute("data-src");
});

// When the user clicks on the next button, show the next image
nextBtn.addEventListener("click", function() {
  currentIndex++;
  if (currentIndex >= elements.length) {
    currentIndex = 0;
  }
  modalImg.src = elements[currentIndex].getAttribute("data-src");
  downloadBtn.href = elements[currentIndex].getAttribute("data-src");
});

// When the user clicks anywhere outside of the modal, close it
window.addEventListener("click", function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
    window.scrollTo(0, currentPosition);
  }
});

// When the user clicks on <span> (x), close the modal
span.addEventListener("click", function() {
  modal.style.display = "none";
  window.scrollTo(0, currentPosition);
});
</script>
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
</body>
</html>
