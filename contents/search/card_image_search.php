                <!-- Modal -->
                <div class="modal fade" id="infoImage_<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-fullscreen modal-dialog-centered" role="document">
                    <div class="modal-content bg-transparent border-0">
                      <div class="modal-body d-flex justify-content-center align-items-center">
                        <div class="card container rounded-5 p-3 position-relative" style="max-width: 750px;">
                          <style>
                            .icon-stroke-1 { -webkit-text-stroke: 1px; }
                            .icon-stroke-2 { -webkit-text-stroke: 2px; }
                            .icon-stroke-3 { -webkit-text-stroke: 3px; }
                          </style>
                          <div class="position-absolute top-0 start-100 translate-middle">
                            <button type="button" class="btn btn-sm rounded-circle btn-light shadow" data-bs-dismiss="modal"><i class="bi bi-x icon-stroke-1"></i></button>
                          </div>
                          <div class="row d-flex justify-content-center">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                              <div class="card border-0 rounded-4 overflow-auto scrollable-div" style="max-height: 250px;">
                                <a class="w-100 h-100" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/image.php?artworkid=<?php echo $row['id']; ?>">
                                  <img class="rounded-4 object-fit-cover shadow lazy-load" height="400" width="100%" data-src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/thumbnails/<?php echo $row['filename']; ?>" alt="<?php echo $row['title']; ?>">
                                </a>
                              </div>
                              <div class="btn-group mt-2 w-100">
                                <a class="btn btn-sm border-0" data-bs-toggle="collapse" href="#shareSection" role="button" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-share-fill icon-stroke-1"></i></a>
                                <button class="btn btn-sm fw-bold border-0"><i class="bi bi-bar-chart-line-fill"></i> <?php echo $row['view_count']?></button>
                                <button class="btn btn-sm border-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfoDesktop" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-info-circle-fill"></i></button>
                                <button class="btn btn-sm border-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDownload" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-download icon-stroke-1"></i></button>
                                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/image.php?artworkid=<?php echo $row['id']; ?>" class="btn btn-sm border-0"><i class="bi bi-eye-fill"></i></a>
                              </div>
                              <div class="container mt-2">
                                <?php
                                  if (!empty($row['tags'])) {
                                    $tags = explode(',', $row['tags']);
                                    foreach ($tags as $tag) {
                                      $tag = trim($tag);
                                        if (!empty($tag)) {
                                    ?>
                                      <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                                        class="btn btn-sm btn-outline-dark mb-1 rounded-pill fw-bold">
                                        <i class="bi bi-tags-fill"></i> <?php echo $tag; ?>
                                      </a>
                                    <?php
                                      }
                                    }
                                  } else {
                                    echo "No tags available.";
                                  }
                                ?>
                              </div>
                            </div>
                            <div class="col-sm-6">
                              <div class="h-100">
                                <div class="card-body">
                                  <h5 class="text-center fw-bold"><?php echo $row['title']?></h5>
                                  <p class="card-text fw-medium">
                                    <?php
                                      if (!empty($row['imgdesc'])) {
                                        $messageText = $row['imgdesc'];
                                        $messageTextWithoutTags = strip_tags($messageText);
                                        $pattern = '/\bhttps?:\/\/\S+/i';

                                        $formattedText = preg_replace_callback($pattern, function ($matches) {
                                          $url = htmlspecialchars($matches[0]);
                                          return '<a href="' . $url . '">' . $url . '</a>';
                                        }, $messageTextWithoutTags);

                                        $formattedTextWithLineBreaks = nl2br($formattedText);
                                        echo $formattedTextWithLineBreaks;
                                      } else {
                                        echo "Image description is empty.";
                                      }
                                    ?>
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="overflow-auto" style="max-height: 250px;">
                            <div class="collapse" id="shareSection">
                              <p class="text-start fw-bold mt-3">share to:</p>
                              <div class="card rounded-4 p-4">
                                <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                                  <!-- Twitter -->
                                  <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-twitter"></i>
                                  </a>
                                
                                  <!-- Line -->
                                  <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-line"></i>
                                  </a>
                                
                                  <!-- Email -->
                                  <a class="btn" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>">
                                    <i class="bi bi-envelope-fill"></i>
                                  </a>
                                
                                  <!-- Reddit -->
                                  <a class="btn" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-reddit"></i>
                                  </a>
                                
                                  <!-- Instagram -->
                                  <a class="btn" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-instagram"></i>
                                  </a>
                                
                                  <!-- Facebook -->
                                  <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-facebook"></i>
                                  </a>
                                </div>
                                <div class="btn-group w-100" role="group" aria-label="Share Buttons">
                                  <!-- WhatsApp -->
                                  <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-whatsapp"></i>
                                  </a>
    
                                  <!-- Pinterest -->
                                  <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-pinterest"></i>
                                  </a>
    
                                  <!-- LinkedIn -->
                                  <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-linkedin"></i>
                                  </a>
    
                                  <!-- Messenger -->
                                  <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-messenger"></i>
                                  </a>
    
                                  <!-- Telegram -->
                                  <a class="btn" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-telegram"></i>
                                  </a>
    
                                  <!-- Snapchat -->
                                  <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-snapchat"></i>
                                  </a>
                                </div>
                              </div>
                            </div>
                            <div class="collapse mt-2" id="collapseInfoDesktop">
                              <div class="card rounded-4 container">
                                <p class="text-center fw-semibold mt-2">Image Information</p>
                                <p class="text-start fw-semibold">Image ID: "<?php echo $row['id']?>"</p>
                                <?php
                                  $total_image_size = 0; // Initialize a variable to keep track of the total image size

                                  // Define the base URL for your images
                                  $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/images/';
                                
                                  // Define the local file system path to the image folder
                                  $image_folder_path = $_SERVER['DOCUMENT_ROOT'] . '/images/';

                                  // Calculate and display image size and dimensions for the main image
                                  $image_path = $image_folder_path . $row['filename'];
                                  $image_size = round(filesize($image_path) / (1024 * 1024), 2);
                                  $total_image_size += $image_size;
                                  list($width, $height) = getimagesize($image_path);
                                  echo "<p class='text-start fw-semibold'>Image data size: " . $image_size . " MB</p>";
                                  echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                                  echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='" . $base_url . $row['filename'] . "'>View original image</a></p>";

                                  // Assuming you have a separate query to fetch child images
                                  $child_images_result = $database->query("SELECT filename FROM image_child WHERE image_id = " . $row['id']);

                                  while ($child_image = $child_images_result->fetchArray()) {
                                    $child_image_path = $image_folder_path . $child_image['filename'];
                                    $child_image_size = round(filesize($child_image_path) / (1024 * 1024), 2);
                                    $total_image_size += $child_image_size;
                                    list($child_width, $child_height) = getimagesize($child_image_path);
                                    echo "<p class='text-start fw-semibold'>Child Image data size: " . $child_image_size . " MB</p>";
                                    echo "<p class='text-start fw-semibold'>Child Image dimensions: " . $child_width . "x" . $child_height . "</p>";
                                    echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='" . $base_url . $child_image['filename'] . "'>View original child image</a></p>";
                                  }

                                  // Display the total image size after processing all images
                                  echo "<p class='text-start fw-semibold'>Total Image data size: " . $total_image_size . " MB</p>";
                                ?>
                              </div>
                            </div>
                            <div class="collapse mt-2" id="collapseDownload">
                              <a class="btn btn-primary fw-bold rounded-4 w-100" href="login.php">
                                <i class="bi bi-download text-stroke"></i> you must login or signup first
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End of Modal -->