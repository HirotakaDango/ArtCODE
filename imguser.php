<!DOCTYPE html>
<html>
  <head>
    <style>
      .containerP {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
      }
  
      .imagesP {
        flex: 0 0 auto;
        margin-right: 3px;
        scroll-snap-align: start;
      }
  
      .hori {
        border-radius: 5px;
        width: 100px;
        height: 120px;
        object-fit: cover;
        border: 2px solid lightgray;
      }
  
      .imagesP:last-of-type {
        margin-right: 0;
      }

      .imageP {
        display: inline-block;
      }
    </style> 
  </head>
  <body>
    <p class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-images"></i> Latest images by <?php echo $user['artist']; ?></p>
    <div class="containerP mb-2">
      <?php
        // Get all images for the given user_username
        $stmt = $db->prepare("SELECT * FROM images WHERE username = :username ORDER BY id DESC");
        $stmt->bindParam(':username', $user_username);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?> 
      <div class="imagesP">
        <?php foreach ($images as $imageU): ?>
        <?php
          $image_id = $imageU['id'];
          $user_username = $imageU['username'];
          $image_url = $imageU['filename'];
        ?>
          <a class="imageP" href="image.php?filename=<?php echo $image_url; ?>">
            <img class="lazy-load hori" data-src="thumbnails/<?php echo $image_url; ?>">
          </a>
        <?php endforeach; ?>
      </div> 
    </div>
    
  </body>
</html>
