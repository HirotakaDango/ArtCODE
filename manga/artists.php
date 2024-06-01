<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artists</title>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid my-3">
        <h1 class="mb-4 fw-bold">Artists</h1>
        <?php
        // Fetch JSON data from api_manga_artists_tags.php
        $json = file_get_contents($web . '/api_manga_artists_tags.php?artist=all');
        $data = json_decode($json, true);

        // Check if the data is an array and not empty
        if (is_array($data) && !empty($data)) {
          $artists = $data['artists'];
          foreach ($artists as $artist_name => $artistData) {
            // Adjust variable names according to the new JSON structure
            $artistImageCount = $artistData['count'];
            $user_id = $artistData['userid'];
        ?>
          <div class="btn-group mb-2 me-1">
            <a href="index.php?artist=<?php echo urlencode($artist_name); ?>&uid=<?php echo $user_id; ?>" class="btn bg-secondary-subtle fw-bold"><?php echo htmlspecialchars($artist_name, ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#" class="btn bg-body-tertiary fw-bold" disabled><?php echo $artistImageCount; ?></a>
          </div>
        <?php 
          }
        } else { 
        ?>
          <p>No data found.</p>
        <?php 
        } 
        ?>
    </div>
  </body>
</html>
