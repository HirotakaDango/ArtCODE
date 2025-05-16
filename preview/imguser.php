<?php
// Get all images for the given user_email
$stmt = $db->prepare("SELECT id, filename, tags, title, imgdesc, type FROM images WHERE email = :email ORDER BY id DESC");
$stmt->bindParam(':email', $user_email);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

              <p class="mt-3 fw-bold">
                <i class="bi bi-images"></i> Latest images by <?php echo htmlspecialchars($user['artist']); ?>
              </p>

              <?php if (isset($_GET['mode']) && $_GET['mode'] === 'desktop'): ?>
              <div class="container px-0 d-none d-md-block">
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
                                <?php
                                // Example of error handling and querying
                                try {
                                  $stmt_count_main = $db->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
                                  $stmt_count_main->bindValue(':id', $image_id, PDO::PARAM_INT);
                                  $stmt_count_main->execute();
                                  $imageCountRow = $stmt_count_main->fetch(PDO::FETCH_ASSOC);
                                  $imageCount = $imageCountRow ? $imageCountRow['image_count'] : 0;
            
                                  $stmt_count_child = $db->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
                                  $stmt_count_child->bindValue(':image_id', $image_id, PDO::PARAM_INT);
                                  $stmt_count_child->execute();
                                  $childImageCountRow = $stmt_count_child->fetch(PDO::FETCH_ASSOC);
                                  $childImageCount = $childImageCountRow ? $childImageCountRow['child_image_count'] : 0;
            
                                  $totalImagesCount = $imageCount + $childImageCount;
                                } catch (PDOException $e) {
                                  echo "Error: " . $e->getMessage();
                                  $totalImagesCount = 0; // Handle error condition
                                }
                                ?>
                                <?php include('../rows_columns/image_counts_prev_artwork.php'); ?>
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
              <?php endif; ?>
              <?php if (isset($_GET['mode']) && $_GET['mode'] === 'mobile'): ?>
              <div class="container px-0 d-md-none">
                <div id="imageCarouselUserMobile" class="carousel slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <?php
                    $totalImages = count($images);
                    $slidesCount = ceil($totalImages / 3);
            
                    for ($i = 0; $i < $slidesCount; $i++) :
                      $startIndex = $i * 3;
                      $endIndex = min($startIndex + 3, $totalImages);
                    ?>
                      <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                        <div class="row row-cols-3 g-1">
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
                                <?php
                                // Example of error handling and querying
                                try {
                                  $stmt_count_main = $db->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
                                  $stmt_count_main->bindValue(':id', $image_id, PDO::PARAM_INT);
                                  $stmt_count_main->execute();
                                  $imageCountRow = $stmt_count_main->fetch(PDO::FETCH_ASSOC);
                                  $imageCount = $imageCountRow ? $imageCountRow['image_count'] : 0;
            
                                  $stmt_count_child = $db->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
                                  $stmt_count_child->bindValue(':image_id', $image_id, PDO::PARAM_INT);
                                  $stmt_count_child->execute();
                                  $childImageCountRow = $stmt_count_child->fetch(PDO::FETCH_ASSOC);
                                  $childImageCount = $childImageCountRow ? $childImageCountRow['child_image_count'] : 0;
            
                                  $totalImagesCount = $imageCount + $childImageCount;
                                } catch (PDOException $e) {
                                  echo "Error: " . $e->getMessage();
                                  $totalImagesCount = 0; // Handle error condition
                                }
                                ?>
                                <?php include('../rows_columns/image_counts_prev_artwork.php'); ?>
                              </a>
                            </div>
                          <?php endfor; ?>
                        </div>
                      </div>
                    <?php endfor; ?>
                  </div>
                  <div class="d-flex mt-2">
                    <button class="me-auto btn btn-dark p-1 py-0" type="button" data-bs-target="#imageCarouselUserMobile" data-bs-slide="prev">
                      <i class="bi bi-chevron-left" style="-webkit-text-stroke: 1px;"></i>
                    </button>
                    <button class="ms-auto btn btn-dark p-1 py-0" type="button" data-bs-target="#imageCarouselUserMobile" data-bs-slide="next">
                      <i class="bi bi-chevron-right" style="-webkit-text-stroke: 1px;"></i>
                    </button>
                  </div>
                </div>
              </div>
              <?php endif; ?>

<script>
  var carousel = new bootstrap.Carousel(document.getElementById('imageCarouselUser'), {
    interval: false // Disable auto sliding
  });
</script>