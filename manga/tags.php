<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tags</title>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid my-3">
        <h1 class="mb-4 fw-bold">Tags</h1>
        <?php
        // Fetch JSON data from api_manga_tags.php
        $json = file_get_contents($web . '/api_manga_artists_tags.php?tag=all');
        $data = json_decode($json, true);

        // Check if the data is an array and not empty
        if (is_array($data) && !empty($data)) {
          $tags = $data['tags'];
        ?>
          <?php foreach ($tags as $tag => $count): ?>
            <div class="btn-group mb-2 me-1">
              <a href="index.php?tag=<?php echo urlencode($tag); ?>" class="btn bg-secondary-subtle fw-bold">
                <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
              </a>
              <a href="#" class="btn bg-body-tertiary fw-bold">
                <?php echo $count; ?>
              </a>
              </div>
          <?php endforeach; ?>
        <?php 
        } else { 
        ?>
          <p>No data found.</p>
        <?php 
        } 
        ?>
    </div>
  </body>
</html>
