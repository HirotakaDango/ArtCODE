<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

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

<!DOCTYPE html>
<html>
  <head>
    <title>Tags</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('taguserheader.php'); ?>
    <div class="input-group mb-3 mt-2">
      <input type="text" class="form-control me-2 ms-2" placeholder="Search tag" id="search-input">
    </div>
    <!-- Display the tags as a group of buttons -->
    <div class="tag-buttons">
      <?php foreach ($tags as $tag): ?>
        <?php
          // Check if the tag has any associated images
          $stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE tags LIKE ?");
          $stmt->bindValue(1, '%' . $tag . '%');
          $countResult = $stmt->execute()->fetchArray()[0];
          if ($countResult > 0):
        ?>
          <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
            class="btn tag-button">
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
        padding: 6px 8px;
        margin: 4px;
        background-color: #eee;
        color: #333;
        border-radius: 10px;
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
    <script>
      // Get the search input element
      const searchInput = document.getElementById('search-input');

      // Get all the tag buttons
      const tagButtons = document.querySelectorAll('.tag-button');

      // Add an event listener to the search input field
      searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();

        // Filter the tag buttons based on the search term
        tagButtons.forEach(button => {
          const tag = button.textContent.toLowerCase();

          if (tag.includes(searchTerm)) {
            button.style.display = 'inline-block';
          } else {
            button.style.display = 'none';
          }
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>