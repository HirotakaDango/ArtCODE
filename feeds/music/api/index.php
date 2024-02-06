<?php
// Initialize variables
$websiteUrl = '';

// SQLite database connection
$db = new SQLite3('music.sqlite'); // Replace with your actual database file

// Create settings table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, website_url TEXT)";
$db->exec($createTableQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $websiteUrl = $_POST['website_url'];

  // Save website URL to the database
  $stmt = $db->prepare("INSERT OR REPLACE INTO settings (id, website_url) VALUES (1, :website_url)");
  $stmt->bindValue(':website_url', $websiteUrl, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect to the same page to prevent form resubmission
  header('Location: ' . $_SERVER['REQUEST_URI']);
  exit();
}

// Retrieve website URL from the database
$stmt = $db->prepare("SELECT website_url FROM settings WHERE id = 1");
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$websiteUrl = $row ? $row['website_url'] : '';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE - Music</title>
    <link rel="icon" type="image/png" href="<?php echo $websiteUrl; ?>/icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  </head>
  <body>
    <nav class="navbar fixed-top navbar-expand-lg bg-body-tertiary shadow">
      <form class="container-fluid" method="POST">
        <div class="input-group">
          <input class="form-control bg-dark-subtle border-0 focus-ring focus-ring-dark rounded-start-4 fw-medium" type="text" name="website_url" value="<?php echo $websiteUrl; ?>" placeholder="website url">
          <button class="btn bg-dark-subtle border-0 link-body-emphasis fw-medium rounded-end-4">save</button>
        </div>
      </form>
    </nav>
    <br>
    <div class="container-fluid mt-5">
      <?php
        $sourceApiUrl = $websiteUrl . '/feeds/music/api_music.php'; // Construct API URL based on user input

        try {
          $json = @file_get_contents($sourceApiUrl);
          if ($json === false) {
            throw new Exception("<h5 class='text-center'>Error fetching data from API</h5>");
          }

          $data = json_decode($json, true);

          if (!is_array($data) || empty($data)) {
            throw new Exception("<h5 class='text-center'>No data found</h5>");
          }
        ?>
          <div class="song-list">
            <?php foreach ($data as $song): ?>
              <div class="card rounded-4 bg-dark-subtle bg-opacity-10 my-2 border-0 shadow">
                <div class="card-body p-1">
                  <a class="link-body-emphasis text-decoration-none music text-start w-100 text-white btn fw-bold border-0" href="play.php?album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>">
                    <?php echo $song['title']; ?><br>
                    <small class="text-muted"><?php echo $song['artist']; ?> - <?php echo $song['album']; ?></small>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php
        } catch (Exception $e) {
          echo "<h5 class='text-center mt-3 fw-bold'>Error or nothing found: </h5>" . $e->getMessage();
        }
      ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js" integrity="sha384-Rx+T1VzGupg4BHQYs2gCW9It+akI2MM/mndMCy36UVfodzcJcF0GGLxZIzObiEfa" crossorigin="anonymous"></script>
  </body>
</html>
