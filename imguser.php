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
    <p class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-images"></i> Latest images by <?php echo $user['artist']; ?></p>
    <div class="containerP mb-2">
      <?php
        // Get all images for the given user_email
        $stmt = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
        $stmt->bindParam(':email', $user_email);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?> 
      <div class="imagesP">
        <?php foreach ($images as $imageU): ?>
        <?php
          $image_id = $imageU['id'];
          $user_email = $imageU['email'];
          $image_url = $imageU['filename'];
        ?>
          <a class="imageP" href="image.php?filename=<?php echo $image_id; ?>">
            <img class="lazy-load hori" data-src="thumbnails/<?php echo $image_url; ?>">
          </a>
        <?php endforeach; ?>
      </div> 
    </div>