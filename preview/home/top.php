<?php
// Get the images for the current page
$stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count, SUM(images.view_count) AS total_view_count, users.id AS userid, users.artist, users.pic
                     FROM images 
                     LEFT JOIN favorites ON images.id = favorites.image_id 
                     LEFT JOIN users ON images.email = users.email
                     GROUP BY images.id 
                     ORDER BY (favorite_count + total_view_count) DESC 
                     LIMIT 72");
$result = $stmt->execute();
?>

    <div class="container-fluid mt-2">
      <h5 class="fw-bold mb-2">Top images</h5>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-1">
        <?php while ($image = $result->fetchArray()): ?>
          <div class="col">
            <div class="card border-0 bg-body-tertiary shadow h-100 rounded-4">
              <a class="text-decoration-none link-body-emphasis" href="/image.php?artworkid=<?php echo $image['id']; ?>">
                <div class="row g-0">
                  <div class="col-4">
                    <div class="ratio ratio-1x1 rounded-4">
                      <img class="object-fit-cover lazy-load h-100 w-100 rounded-start-4" data-src="/thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
                    </div>
                  </div>
                  <div class="col-8">
                    <div class="card-body d-flex align-items-center justify-content-start h-100">
                      <div class="text-truncate">
                        <h6 class="card-title fw-bold text-truncate"><?php echo $image['title']; ?></h6>
                        <h6 class="small fw-medium text-truncate">image by <?php echo $image['artist']; ?></h6>
                        <h6 class="small fw-medium text-truncate"><?php echo $image['view_count']; ?> views</h6>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>