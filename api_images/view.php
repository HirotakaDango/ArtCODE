<?php
include 'connect.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$display = isset($_GET['display']) ? $_GET['display'] : '';
$artworkId = isset($_GET['artworkid']) ? intval($_GET['artworkid']) : 0;

$artworkData = [];
$allImages = [];
$pageTitle = 'All Images'; // Default page title

if ($artworkId > 0) {
  // API URL to get data for the specific artwork
  $apiUrl = $baseUrl . "/api.php?artworkid=$artworkId" . ($display ? "&display=$display" : '');

  // Fetch the main image data
  $jsonData = @file_get_contents($apiUrl);
  $data = json_decode($jsonData, true);

  if ($jsonData === false || $data === null) {
    die('Error fetching or decoding JSON data.');
  }

  if ($display === 'info') {
    // Handle the 'info' display case
    if (isset($data['images'][0])) {
      $artworkData = $data;
      $pageTitle = $artworkData['images'][0]['title']; // Set the title to the image's title
    }
  } else {
    // Handle the default or 'all_images' case
    if (isset($data['image'])) {
      $allImages[] = $data['image'];  // Start with the main image
    }
    if (isset($data['image_child']) && is_array($data['image_child'])) {
      $allImages = array_merge($allImages, $data['image_child']);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <?php include('bootstrap.php'); ?>
  </head>
  <body>
    <div>
      <?php if ($artworkId > 0 && $display === 'info'): ?>
        <?php include('navbar.php'); ?>
        <div class="container-fluid mt-3">
          <div class="row">
            <div class="col-md-6">
              <?php if (isset($artworkData['images'][0]['filename'])): ?>
                <img src="<?php echo $baseUrl . '/images/' . $artworkData['images'][0]['filename']; ?>" class="w-100 mb-3 rounded-4" alt="Artwork Image">
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <?php if (isset($artworkData['images'][0])): ?>
                <h1 class="mb-4 fw-bold mt-4 mt-md-0"><?php echo $artworkData['images'][0]['title']; ?></h1>
                <?php
                if (!empty($artworkData['images'][0]['imgdesc'])) {
                  $messageText = $artworkData['images'][0]['imgdesc'];
                  $messageTextWithoutTags = strip_tags($messageText);
                  $pattern = '/\bhttps?:\/\/\S+/i';

                  $formattedText = preg_replace_callback($pattern, function ($matches) {
                    $url = $matches[0];
                    return '<a href="' . $url . '">' . $url . '</a>';
                  }, $messageTextWithoutTags);

                  $charLimit = 400; // Set your character limit

                  if (strlen($formattedText) > $charLimit) {
                    $limitedText = substr($formattedText, 0, $charLimit);
                    echo '<span id="limitedText">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                    echo '<span id="more" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                    echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0 text-white" onclick="myFunction()" id="myBtn"><small>read more</small></button>';
                  } else {
                    // If the text is within the character limit, just display it with line breaks.
                    echo nl2br($formattedText);
                  }
                } else {
                  echo "User description is empty.";
                }
                ?>
                <script>
                  function myFunction() {
                    var dots = document.getElementById("limitedText");
                    var moreText = document.getElementById("more");
                    var btnText = document.getElementById("myBtn");

                    if (moreText.style.display === "none") {
                      dots.style.display = "none";
                      moreText.style.display = "inline";
                      btnText.innerHTML = "read less";
                    } else {
                      dots.style.display = "inline";
                      moreText.style.display = "none";
                      btnText.innerHTML = "read more";
                    }
                  }
                </script>
              <div class="my-2 row align-items-center">
                <label for="views" class="col-3 col-form-label text-nowrap">Artist</label>
                <div class="col-9">
                  <h6 class="form-control-plaintext" id="views"><a class="btn border-0 p-0 fw-medium" href="index.php?uid=1"><?php echo $artworkData['images'][0]['artist_name']; ?></a></h6>
                </div>
              </div>
              <div class="my-2 row align-items-center">
                <label for="views" class="col-3 col-form-label text-nowrap">Views</label>
                <div class="col-9">
                  <h6 class="form-control-plaintext" id="views"><?php echo $artworkData['images'][0]['view_count']; ?></h6>
                </div>
              </div>
              <div class="mb-2 row align-items-center">
                <label for="favorites" class="col-3 col-form-label text-nowrap">Favorites</label>
                <div class="col-9">
                  <h6 class="form-control-plaintext" id="favorites"><?php echo $artworkData['favorites_count']; ?></h6>
                </div>
              </div>
              <div class="card shadow border-0 rounded-4 bg-body-tertiary mt-3">
                <div class="card-body">
                  <!-- Tags -->
                  <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-tags-fill"></i> Tags</h6>
                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php
                    if (!empty($artworkData['images'][0]['tags'])) {
                      $tags = explode(',', $artworkData['images'][0]['tags']);
                      foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if (!empty($tag)) {
                          ?>
                          <a href="index.php?display=all_images&tag=<?php echo urlencode($tag); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                            <i class="bi bi-tag-fill"></i> <?php echo htmlspecialchars($tag); ?>
                          </a>
                          <?php
                        }
                      }
                    } else {
                      echo "<p class='text-muted'>No tags available.</p>";
                    }
                    ?>
                  </div>
              
                  <!-- Characters -->
                  <?php if (!empty($artworkData['images'][0]['characters'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-people-fill"></i> Characters</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      <?php
                      $characters = explode(',', $artworkData['images'][0]['characters']);
                      foreach ($characters as $character) {
                        $character = trim($character);
                        if (!empty($character)) {
                          ?>
                          <a href="index.php?display=all_images&character=<?php echo urlencode($character); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                            <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($character); ?>
                          </a>
                          <?php
                        }
                      }
                      ?>
                    </div>
                  <?php endif; ?>
              
                  <!-- Parodies -->
                  <?php if (!empty($artworkData['images'][0]['parodies'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-journals"></i> Parodies</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      <?php
                      $parodies = explode(',', $artworkData['images'][0]['parodies']);
                      foreach ($parodies as $parody) {
                        $parody = trim($parody);
                        if (!empty($parody)) {
                          ?>
                          <a href="index.php?display=all_images&parody=<?php echo urlencode($parody); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                            <i class="bi bi-journal"></i> <?php echo htmlspecialchars($parody); ?>
                          </a>
                          <?php
                        }
                      }
                      ?>
                    </div>
                  <?php endif; ?>
              
                  <!-- Group -->
                  <?php if (!empty($artworkData['images'][0]['group'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-person-fill"></i> Group</h6>
                    <div class="d-flex flex-wrap gap-2">
                      <?php
                      $groups = explode(',', $artworkData['images'][0]['group']);
                      foreach ($groups as $group) {
                        $group = trim($group);
                        if (!empty($group)) {
                          ?>
                          <a href="index.php?display=all_images&group=<?php echo urlencode($group); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                            <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($group); ?>
                          </a>
                          <?php
                        }
                      }
                      ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
                <div class="btn-group w-100 gap-2 mt-2">
                  <a href="view.php?artworkid=<?php echo $artworkId; ?>&back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/index.php'); ?>" class="btn border-0 fw-medium w-50">View All Images</a>
                  <a href="redirect.php?back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/index.php'); ?>" class="btn border-0 fw-medium w-50">Back to Gallery</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="mt-5"></div>
      <?php else: ?>
        <div class="w-100">
          <a href="view.php?artworkid=<?php echo $artworkId; ?>&display=info&back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/index.php'); ?>" class="btn border-0 position-fixed top-0 start-0"><i class="bi bi-chevron-left fs-5" style="-webkit-text-stroke: 2px;"></i></a>
          <?php if (!empty($allImages)): ?>
            <?php foreach ($allImages as $image): ?>
              <img src="<?php echo $baseUrl . '/' . $image['url']; ?>" class="w-100 vh-100 object-fit-contain" alt="Image">
            <?php endforeach; ?>
          <?php else: ?>
            <p>No images found.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <script>
      // Get the theme toggle button, icon element, and html element
      const themeToggle = document.getElementById('themeToggle');
      const themeIcon = document.getElementById('themeIcon');
      const htmlElement = document.documentElement;

      // Check if the user's preference is stored in localStorage
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme) {
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
      }

      // Add an event listener to the theme toggle button
      themeToggle.addEventListener('click', () => {
        // Toggle the theme
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
        // Apply the new theme
        htmlElement.setAttribute('data-bs-theme', newTheme);
        updateThemeIcon(newTheme);

        // Store the user's preference in localStorage
        localStorage.setItem('theme', newTheme);
      });

      // Function to update the theme icon
      function updateThemeIcon(theme) {
        if (theme === 'dark') {
          themeIcon.classList.remove('bi-moon-fill');
          themeIcon.classList.add('bi-sun-fill');
        } else {
          themeIcon.classList.remove('bi-sun-fill');
          themeIcon.classList.add('bi-moon-fill');
        }
      }
    </script>
  </body>
</html>