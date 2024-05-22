<?php
// Initialize variables
$websiteUrl = '';
$folderPath = '';
$thumbPath = '';

// SQLite database connection
$db = new SQLite3('your_database.sqlite'); // Replace with your actual database file

// Create settings table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS settings (
  id INTEGER PRIMARY KEY,
  website_url TEXT,
  folder_path TEXT,
  thumb_path TEXT
)";
$db->exec($createTableQuery);

// Fetch website URL, folder path, and thumb path from the database
$selectQuery = "SELECT website_url, folder_path, thumb_path FROM settings WHERE id = 1";
$result = $db->querySingle($selectQuery, true);

if ($result) {
  $websiteUrl = $result['website_url'];
  $folderPath = $result['folder_path'];
  $thumbPath = $result['thumb_path'];
}

// Construct API URL based on user input
$sourceApiUrl = $websiteUrl . '/api.php';

try {
  $json = @file_get_contents($sourceApiUrl);
  if ($json === false) {
    throw new Exception("<h5 class='text-center'>Error fetching data from API</h5>");
  }

  $data = json_decode($json, true);

  if (!is_array($data) || empty($data)) {
    throw new Exception("<h5 class='text-center'>No data found</h5>");
  }

  $images = $data['images'];
  $imageChildData = $data['image_child'];
} catch (Exception $e) {
  echo "<h5 class='text-center mt-3 fw-bold'>Error or nothing found: </h5>" . $e->getMessage();
}

$primaryImageCount = count($images);
$childImageCount = count($imageChildData);

$resultCount = $primaryImageCount + $childImageCount;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE - Preview</title>
    <link rel="icon" type="image/png" href="<?php echo $websiteUrl; ?>/icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary shadow">
      <div class="container-fluid gap-2 justify-content-end">
        <a class="navbar-brand me-auto fw-bold" href="index.php">ArtCODE</a>
        <button id="themeToggle" class="btn btn-primary fw-bold">
          <i id="themeIcon" class="bi"></i> toggle theme
        </button>
      </div>
    </nav>
    <h1 class="fw-bold text-center mt-4">Primary Images</h1>
    <p class="fw-bold text-center"><small>Total: <?= $primaryImageCount ?></small></p>
    <div class="container">
      <a class="btn btn-primary fw-bold w-100" data-bs-toggle="collapse" href="#collapseExample1" role="button" aria-expanded="false" aria-controls="collapseExample">
        show more
      </a>
    </div>
    <div class="collapse" id="collapseExample1">
      <div class="container-fluid table-responsive mt-4">
        <table class="table table-bordered">
          <thead>
            <tr class="text-nowrap text-center">
              <th>ID</th>
              <th>Filename</th>
              <th>Tags</th>
              <th>Title</th>
              <th>Description</th>
              <th>View Count</th>
              <th>Artist</th>
              <th>User ID</th>
              <th>Favorites Count</th>          
            </tr>
          </thead>
          <tbody>
            <?php foreach ($images as $image): ?>
              <tr class="text-nowrap">
                <td><a href="<?php echo $websiteUrl . '/image.php?artworkid='; ?><?= $image['id'] ?>"><?= $image['id'] ?></a></td>
                <td><a href="<?php echo $websiteUrl . '/' . $folderPath . '/'; ?><?= $image['filename'] ?>"><?= $image['filename'] ?></a></td>
                <td><?= $image['tags'] ?></td>
                <td><?= $image['title'] ?></td>
                <td><?= $image['imgdesc'] ?></td>
                <td><?= $image['view_count'] ?></td>
                <td><?= $image['artist'] ?></td>
                <td><?= $image['userId'] ?></td>
                <td><?= $image['favorites_count'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <h1 class="fw-bold text-center mt-2">Images Child</h1>
    <p class="fw-bold text-center"><small>Total: <?= $childImageCount ?></small></p>
    <div class="container">
      <a class="btn btn-primary fw-bold w-100" data-bs-toggle="collapse" href="#collapseExample2" role="button" aria-expanded="false" aria-controls="collapseExample">
        show more
      </a>
    </div>
    <div class="collapse" id="collapseExample2">
      <div class="container-fluid table-responsive mt-4">
        <table class="table table-bordered">
          <thead>
            <tr class="text-nowrap text-center">
              <th>ID</th>
              <th>Filename</th>
              <th>Image ID</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($imageChildData as $childImage): ?>
              <tr class="text-nowrap">
                <td><?= $childImage['id'] ?></td>
                <td><a href="<?php echo $websiteUrl . '/' . $folderPath . '/'; ?><?= $childImage['filename'] ?>"><?= $childImage['filename'] ?></a></td>
                <td><?= $childImage['image_id'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <p class="fw-bold text-center my-4"><small>Total of all images: <?php echo $resultCount; ?><small></p>
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js" integrity="sha384-Rx+T1VzGupg4BHQYs2gCW9It+akI2MM/mndMCy36UVfodzcJcF0GGLxZIzObiEfa" crossorigin="anonymous"></script>
  </body>
</html>