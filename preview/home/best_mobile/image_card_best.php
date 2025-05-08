    <div class="w-100 px-1 my-2">
      <h5 class="fw-bold px-1">Popular Artworks</h5>
      <h6 class="fw-bold small px-1 mb-3">
        These images are displayed based on their view counts from <?php $time = isset($_GET['time']) ? $_GET['time'] : 'day'; echo $time === 'alltime' ? 'all time' : "this $time"; ?>. The more views an image has, the higher its ranking in this list.
      </h6>
      <div class="<?php include('../../rows_columns/row-cols.php'); echo $rows_columns; ?>">
        <?php 
        $j = 0; // Initialize the counter variable
        while ($image = $result->fetchArray()): 
        ?>
          <div class="col position-relative">
            <div class="position-relative">
              <a class="rounded ratio ratio-1x1" href="/image.php?artworkid=<?php echo $image['id']; ?>">
                <img class="rounded rounded-bottom-0 shadow object-fit-cover lazy-load <?php echo ($image['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="/thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a>
              <?php
                $current_image_id = $image['id'];
                
                // Query to count main image from the images table
                $stmt = $db->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
                $stmt->bindValue(':id', $current_image_id, SQLITE3_INTEGER);
                $imageCountQuery = $stmt->execute();
                $imageCount = $imageCountQuery ? $imageCountQuery->fetchArray(SQLITE3_ASSOC)['image_count'] : 0;
              
                // Query to count associated images from the image_child table
                $stmt = $db->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
                $stmt->bindValue(':image_id', $current_image_id, SQLITE3_INTEGER);
                $childImageCountQuery = $stmt->execute();
                $childImageCount = $childImageCountQuery ? $childImageCountQuery->fetchArray(SQLITE3_ASSOC)['child_image_count'] : 0;
              
                // Total count of main images and associated images
                $totalImagesCount = $imageCount + $childImageCount;
    
                // Determine badge color
                $badgeClass = '';
                switch ($j) {
                  case 0:
                    $badgeClass = 'gold text-white shadow'; // Gold
                    break;
                  case 1:
                    $badgeClass = 'silver text-white shadow'; // Silver
                    break;
                  case 2:
                    $badgeClass = 'bronze text-white shadow'; // Bronze
                    break;
                  default:
                    $badgeClass = 'nothing text-white shadow'; // Default color for others
                }
              ?>
              <span class="position-absolute top-0 end-0 m-2 badge <?php echo $badgeClass; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);">No. <?php echo $j + 1; ?></span>
              <?php include('../../rows_columns/image_counts.php'); ?>
                <div class="dropdown">
                  <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <?php
                      $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$image['id']}");
                      if ($is_favorited) {
                    ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>Unfavorite</small></button></li>
                      </form>
                    <?php } else { ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>Favorite</small></button></li>
                      </form>
                    <?php } ?>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $image['id']; ?>"><i class="bi bi-share-fill"></i> <small>Share</small></button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>Info</small></button></li>
                  </ul>
                  <?php include('share_home.php'); ?>
                  <?php include('card_image_preview.php'); ?>
                </div>
              </div>
            </div>
          </div>
          <?php 
            $j++; // Increment the counter after each image
          endwhile; 
          ?>
      </div>
    </div>
    <style>
      .gold {
        background-color: gold;
      }
    
      .silver {
        background-color: silver;
      }
    
      .bronze {
        background-color: brown;
      }
      
      .nothing {
        background-color: lightgray;
      }
 
       .ratio-cover {
        position: relative;
        width: 100%;
        padding-bottom: 140%;
      }

      .ratio-cover img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    </style>