<?php
$stmt = $db->prepare("SELECT images.id, images.filename, images.tags, images.imgdesc, images.title, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id WHERE images.email = :email GROUP BY images.id ORDER BY favorite_count DESC");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
?>

    <div class="images">
      <?php while ($imageP = $result->fetchArray()): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imagesA" href="image.php?artworkid=<?php echo $imageP['id']; ?>">
              <img class="lazy-load imagesImg" data-src="thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li><button class="dropdown-item fw-bold" onclick="location.href='edit_image.php?id=<?php echo $imageP['id']; ?>'" ><i class="bi bi-pencil-fill"></i> edit image</button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo $imageP['id']; ?>"><i class="bi bi-trash-fill"></i> delete</button></li>
                  <?php
                    $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$imageP['id']}");
                    if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageP['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageP['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
              </div>
            </div>
          </div>
          <div>
            <form action="delete.php?by=<?php echo isset($_GET['by']) ? $_GET['by'] : ''; ?>" method="post">
              <!-- Modal -->
              <div class="modal fade" id="deleteImage_<?php echo $imageP['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                  <div class="modal-content shadow">
                    <div class="modal-body p-4 text-center">
                      <h5 class="mb-2 fw-bold">Are you sure want to delete the selected image?</h5>
                      <p class="fw-semibold">"<?php echo $imageP['title']?>" will be deleted permanently!</p>
                      <div class="row featurette">
                        <div class="col-md-5 order-md-1 mb-2">
                          <div class="position-relative">
                            <img class="rounded object-fit-cover shadow lazy-load" data-src="thumbnails/<?php echo $imageP['filename']; ?>" style="width: 100%; height: 100%;">
                            <button type="button" class="btn btn-dark rounded fw-bold opacity-75 position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
                          </div>
                        </div>
                        <div class="col-md-7 order-md-2">
                          <div class="card container">
                            <p class="text-center fw-semibold mt-2">Image Information</p>
                            <p class="text-start fw-semibold">Image ID: "<?php echo $imageP['id']?>"</p>
                            <?php
                              // Get image size in megabytes
                              $imageP_size = round(filesize('images/' . $imageP['filename']) / (1024 * 1024), 2);

                              // Get image dimensions
                              list($width, $height) = getimagesize('images/' . $imageP['filename']);

                              // Display image information
                              echo "<p class='text-start fw-semibold'>Image data size: " . $imageP_size . " MB</p>";
                              echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                              echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $imageP['filename'] . "'>View original image</a></p>";
                            ?>
                          </div>
                          <p class="mb-3 mt-2 fw-semibold">This action can't be undone! Make sure you download the image before you delete it.</p>
                          <a class="btn btn-primary fw-bold rounded-4 w-100" href="#" onclick="downloadWithProgressBar(<?php echo $imageP['id']; ?>, '<?php echo $imageP['title']; ?>')">
                            <i class="bi bi-download text-stroke"></i> download
                          </a>
                          <div class="progress fw-bold mt-2 rounded-4" id="progressBarContainer_<?php echo $imageP['id']; ?>" style="height: 30px; display: none;">
                            <div id="progressBar_<?php echo $imageP['id']; ?>" class="progress-bar progress-bar progress-bar-animated fw-bold" style="width: 0; height: 30px;">0%</div>
                          </div>
                          <script>
                            function downloadWithProgressBar(artworkId, title) {
                              var progressBar = document.getElementById('progressBar_' + artworkId);
                              var progressBarContainer = document.getElementById('progressBarContainer_' + artworkId);
                              var artist = '<?php echo $artist; ?>';
                              title = title.replace(/\s+/g, '_');

                              // Create a new XMLHttpRequest object
                              var xhr = new XMLHttpRequest();

                              // Function to update the progress bar
                              function updateProgress(event) {
                                if (event.lengthComputable) {
                                  var percentComplete = (event.loaded / event.total) * 100;
                                  progressBar.style.width = percentComplete + '%';
                                  progressBar.innerHTML = percentComplete.toFixed(2) + '%';
                                }
                              }

                              // Set up the XMLHttpRequest object
                              xhr.open('GET', 'download_images.php?artworkid=' + artworkId, true);

                              // Set the responseType to 'blob' to handle binary data
                              xhr.responseType = 'blob';

                              // Track progress with the updateProgress function
                              xhr.addEventListener('progress', updateProgress);

                              // On successful download completion
                              xhr.onload = function () {
                                progressBar.innerHTML = '100%';
                                // Delay hiding the progress bar to show 100% for a brief moment
                                setTimeout(function () {
                                  progressBarContainer.style.display = 'none';
                                }, 1000);

                                // Create a download link for the downloaded file
                                var downloadLink = document.createElement('a');
                                downloadLink.href = URL.createObjectURL(xhr.response);
                                downloadLink.download = title + '_image_id_' + artworkId + '_by_' + artist + '.zip';
                                downloadLink.style.display = 'none';
                                document.body.appendChild(downloadLink);
                                downloadLink.click(); // Trigger the click event to download the file
                                document.body.removeChild(downloadLink); // Remove the link from the document
                              };

                              // Show the progress bar container
                              progressBarContainer.style.display = 'block';

                              // Send the XMLHttpRequest to start the download
                              xhr.send();
                            }
                          </script>
                          <h5 class="fw-bold text-center mt-2">Please Note!</h5>
                          <p class="fw-bold text-center container">
                            <small>1. Download can take a really long time, wait until progress bar reach 100% or appear download pop up in the notification.</small>
                          </p>
                          <p class="fw-bold text-center container">
                            <small>2. If you found download error or failed, <a class="text-decoration-none" href="download_images.php?artworkid=<?php echo $imageP['id']; ?>">click this link</a> for third option if download all images error or failed.</small>
                          </p>
                          <p class="fw-bold text-center container">
                            <small>3. If you found problem where the zip contain empty file or 0b, download the images manually.</small>
                          </p>
                          <p class="fw-bold text-center container">
                            <small>4. Server sometimes have problem with file and folder path, download manually is the best option if this happening.</small>
                          </p>
                          <div class="btn-group mt-3 w-100">
                            <input type="hidden" name="id" value="<?php echo $imageP['id']; ?>">
                            <button class="btn btn-outline-danger rounded-start-4 fw-bold" type="submit" value="Delete">delete</button>
                            <button type="button" class="btn btn-outline-secondary rounded-end-4 fw-bold" data-bs-dismiss="modal">cancel</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <!-- Modal -->
          <div class="modal fade" id="infoImage_<?php echo $imageP['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen" role="document">
              <div class="modal-content shadow">
                <div class="modal-body p-4 text-center">
                  <h5 class="modal-title fw-bold text-start mb-2"><?php echo $imageP['title']?></h5>
                  <div class="row featurette">
                    <div class="col-md-5 order-md-1 mb-2">
                      <div class="position-relative">
                        <a href="image.php?artworkid=<?php echo $imageP['id']; ?>">
                          <img class="rounded object-fit-cover mb-3 shadow lazy-load" data-src="thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>" style="width: 100%; height: 100%;">
                        </a>
                        <button type="button" class="btn btn-dark rounded fw-bold opacity-75 position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
                      </div>
                    </div>
                    <div class="col-md-7 order-md-2">
                      <p class="text-start fw-semibold">share to:</p>
                      <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                        <!-- Twitter -->
                        <a class="btn btn-outline-dark" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-twitter"></i>
                        </a>
                              
                        <!-- Line -->
                        <a class="btn btn-outline-dark" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-line"></i>
                        </a>
                              
                        <!-- Email -->
                        <a class="btn btn-outline-dark" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>">
                          <i class="bi bi-envelope-fill"></i>
                        </a>
                              
                        <!-- Reddit -->
                        <a class="btn btn-outline-dark" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-reddit"></i>
                        </a>
                              
                        <!-- Instagram -->
                        <a class="btn btn-outline-dark" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-instagram"></i>
                        </a>
                              
                        <!-- Facebook -->
                        <a class="btn btn-outline-dark" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-facebook"></i>
                        </a>
                      </div>
                      <div class="btn-group w-100 mt-2 mb-3">
                        <a class="btn btn-outline-dark fw-bold" href="image.php?artworkid=<?php echo $imageP['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                        <a class="btn btn-outline-dark fw-bold" href="images/<?php echo $imageP['filename']; ?>" download><i class="bi bi-download text-stroke"></i> download</a>
                        <button class="btn btn-outline-dark fw-bold" onclick="shareImage(<?php echo $imageP['id']; ?>)"><i class="bi bi-share-fill text-stroke"></i> share</button>
                      </div>
                      <p class="text-start fw-semibold" style="word-wrap: break-word;">
                        <?php
                          $messageText = $imageP['imgdesc'];
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
                        <p class="text-start fw-semibold">Image ID: "<?php echo $imageP['id']?>"</p>
                        <?php
                          // Get image size in megabytes
                          $imageP_size = round(filesize('images/' . $imageP['filename']) / (1024 * 1024), 2);

                          // Get image dimensions
                          list($width, $height) = getimagesize('images/' . $imageP['filename']);
                            
                          // Display image information
                          echo "<p class='text-start fw-semibold'>Image data size: " . $imageP_size . " MB</p>";
                          echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                          echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $imageP['filename'] . "'>View original image</a></p>";
                        ?>
                      </div>
                      <div class="container mt-2">
                        <?php
                          $tags = explode(',', $imageP['tags']);
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
      <?php endwhile; ?>
    </div>