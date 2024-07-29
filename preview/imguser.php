<?php
// Assuming $db is your PDO database connection object

// Get all images for the given user_email
$stmt = $db->prepare("SELECT id, filename, tags, title, imgdesc, type FROM images WHERE email = :email ORDER BY id DESC");
$stmt->bindParam(':email', $user_email);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

              <p class="mt-3 fw-bold">
                <i class="bi bi-images"></i> Latest images by <?php echo htmlspecialchars($user['artist']); ?>
              </p>
            
              <div class="container px-0">
                <div id="imageCarouselUser" class="carousel slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <?php
                    $totalImages = count($images);
                    $slidesCount = ceil($totalImages / 5);
            
                    for ($i = 0; $i < $slidesCount; $i++) :
                      $startIndex = $i * 5;
                      $endIndex = min($startIndex + 5, $totalImages);
                    ?>
                      <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                        <div class="row row-cols-5 g-1">
                          <?php for ($j = $startIndex; $j < $endIndex; $j++) :
                            $imageU = $images[$j];
                            $image_id = $imageU['id'];
                            $image_url = $imageU['filename'];
                            $image_title = $imageU['title'];
                            $current_image_id = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
                          ?>
                            <div class="col">
                              <a href="?artworkid=<?php echo $image_id; ?>" class="position-relative">
                                <div class="ratio ratio-1x1">
                                  <img class="object-fit-cover rounded <?php echo ($imageU['type'] === 'nsfw') ? 'blurred' : ''; ?> <?php echo ($image_id == $current_image_id) ? 'opacity-50' : ''; ?>" src="/thumbnails/<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($image_title); ?>" style="object-fit: cover;">
                                </div>
                              </a>
                            </div>
                          <?php endfor; ?>
                        </div>
                      </div>
                    <?php endfor; ?>
                  </div>
                  <div class="d-flex mt-2">
                    <button class="me-auto btn btn-dark p-1 py-0" type="button" data-bs-target="#imageCarouselUser" data-bs-slide="prev">
                      <i class="bi bi-chevron-left" style="-webkit-text-stroke: 1px;"></i>
                    </button>
                    <button class="ms-auto btn btn-dark p-1 py-0" type="button" data-bs-target="#imageCarouselUser" data-bs-slide="next">
                      <i class="bi bi-chevron-right" style="-webkit-text-stroke: 1px;"></i>
                    </button>
                  </div>
                </div>
              </div>

<script>
  var carousel = new bootstrap.Carousel(document.getElementById('imageCarouselUser'), {
    interval: false // Disable auto sliding
  });
</script>