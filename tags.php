<?php
// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve all tags used in the images table
$result = $db->query("SELECT DISTINCT tags FROM images");

// Store the tags as an array
$tags = [];
while ($row = $result->fetchArray()) {
  $tagList = explode(',', $row['tags']);
  foreach ($tagList as $tag) {
    $tags[] = htmlspecialchars(trim($tag));
  }
}
$tags = array_unique($tags);

// Filter out any empty tags
$tags = array_filter($tags);

?>

<html>
<head>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
    <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
      <div class="bb1 container">
        <a class="nav-link" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
        <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
        <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
        <a class="nav-link px-2 text-secondary" href="favorite.php"><i class="bi bi-heart-fill"></i></a>
        <div class="dropdown">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
            <li><a class="dropdown-item" href="tags.php"><i class="bi bi-tags"></i> Tags</a></li>
            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </center> 
  <h3 class="text-secondary ms-2 mt-3 fw-bold"><i class="bi bi-tags"></i> tags</h3>
  <!-- Display the tags as a group of buttons -->
  <div class="tag-buttons">
    <?php foreach ($tags as $tag): ?>
      <?php
      // Check if the tag has any associated images
      $countResult = $db->querySingle("SELECT COUNT(*) FROM images WHERE tags LIKE '%{$tag}%'");
      if ($countResult > 0):
      ?>
        <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
           class="tag-button">
          <?php echo $tag; ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <style>
    .tag-buttons {
      display: flex;
      flex-wrap: wrap;
    }

    .tag-button {
      display: inline-block;
      padding: 6px 12px;
      margin: 6px;
      background-color: #eee;
      color: #333;
      border-radius: 3px;
      text-decoration: none;
      font-size: 14px;
      line-height: 1;
      font-weight: 800;
    }

    .tag-button:hover {
      background-color: #ccc;
    }

    .tag-button:active {
      background-color: #aaa;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
</body>
</html>