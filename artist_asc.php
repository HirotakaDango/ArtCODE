<?php
// Get all images for the selected user from the images table
$query = $db->prepare('SELECT images.id, images.filename, images.title, images.imgdesc FROM images JOIN users ON images.email = users.email WHERE users.id = :id ORDER BY images.id ASC');
$query->bindParam(':id', $id);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="images">
      <?php foreach ($images as $imageA): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imagesA" href="image.php?artworkid=<?php echo $imageA['id']; ?>">
              <img class="lazy-load imagesImg" data-src="thumbnails/<?php echo $imageA['filename']; ?>" alt="<?php echo $imageA['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                    $is_favorited = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = {$imageA['id']}")->fetchColumn();
                    if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageA['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageA['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageA['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageA['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
                <!-- Modal -->
                <div class="modal fade" id="infoImage_<?php echo $imageA['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content shadow">
                      <div class="modal-body p-4 text-center">
                        <h5 class="modal-title fw-bold text-start mb-2"><?php echo $imageA['title']?></h5>
                        <div class="row featurette">
                          <div class="col-md-5 order-md-1 mb-2">
                            <div class="position-relative">
                              <a href="image.php?artworkid=<?php echo $imageA['id']; ?>">
                                <img class="rounded object-fit-cover mb-3 shadow lazy-load" data-src="thumbnails/<?php echo $imageA['filename']; ?>" alt="<?php echo $imageA['title']; ?>" style="width: 100%; height: 100%;">
                              </a>
                              <button type="button" class="btn btn-dark rounded fw-bold opacity-75 position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
                            </div>
                          </div>
                          <div class="col-md-7 order-md-2">
                            <p class="text-start fw-semibold">share to:</p>
                            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                              <!-- Twitter -->
                              <a class="btn btn-outline-dark" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageA['id']; ?>" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-twitter"></i>
                              </a>
                              
                              <!-- Line -->
                              <a class="btn btn-outline-dark" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageA['id']; ?>" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-line"></i>
                              </a>
                              
                              <!-- Email -->
                              <a class="btn btn-outline-dark" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageA['id']; ?>">
                                <i class="bi bi-envelope-fill"></i>
                              </a>
                              
                              <!-- Reddit -->
                              <a class="btn btn-outline-dark" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageA['id']; ?>" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-reddit"></i>
                              </a>
                              
                              <!-- Instagram -->
                              <a class="btn btn-outline-dark" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageA['id']; ?>" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-instagram"></i>
                              </a>
                              
                              <!-- Facebook -->
                              <a class="btn btn-outline-dark" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageA['id']; ?>" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-facebook"></i>
                              </a>
                            </div>
                            <div class="btn-group w-100 mt-2 mb-3">
                              <a class="btn btn-outline-dark fw-bold" href="image.php?artworkid=<?php echo $imageA['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                              <a class="btn btn-outline-dark fw-bold" href="images/<?php echo $imageA['filename']; ?>" download><i class="bi bi-download text-stroke"></i> download</a>
                              <button class="btn btn-outline-dark fw-bold" onclick="shareImage(<?php echo $imageA['id']; ?>)"><i class="bi bi-share-fill text-stroke"></i> share</button>
                            </div>
                            <p class="text-start fw-semibold" style="word-wrap: break-word;">
                              <?php
                                $messageText = $imageA['imgdesc'];
                                $messageTextWithoutTags = strip_tags($messageText);
                                $pattern = '/\bhttps?:\/\/\S+/i';

                                $formattedText = preg_replace_callback($pattern, function ($matches) {
                                  $url = htmlspecialchars($matches[0]);
                                  return '<a href="' . $url . '">' . $url . '</a>';
                                }, $messageTextWithoutTags);

                                $formattedTextWithLineBreaks = nl2br($formattedText);
                                echo $formattedTextWithLineBreaks;
                              ?>
                            </p>
                            <div class="card container">
                              <p class="text-center fw-semibold mt-2">Image Information</p>
                              <p class="text-start fw-semibold">Image ID: "<?php echo $imageA['id']?>"</p>
                              <?php
                                // Get image size in megabytes
                                $imageA_size = round(filesize('images/' . $imageA['filename']) / (1024 * 1024), 2);

                                // Get image dimensions
                                list($width, $height) = getimagesize('images/' . $imageA['filename']);
                            
                                // Display image information
                                echo "<p class='text-start fw-semibold'>Image data size: " . $imageA_size . " MB</p>";
                                echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                                echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $imageA['filename'] . "'>View original image</a></p>";
                              ?>
                            </div>
                            <div class="container mt-2">
                              <?php
                                $tags = explode(',', $imageA['tags']);
                                foreach ($tags as $tag) {
                                  $tag = trim($tag);
                                  if (!empty($tag)) {
                              ?>
                                <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                                  class="btn btn-sm btn-secondary mb-1 rounded-3 fw-bold opacity-50">
                                  <?php echo $tag; ?>
                                </a>
                              <?php }
                              } ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?> 
    </div>