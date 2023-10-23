<?php
$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$dbP = new SQLite3('database.sqlite');

// Get all of the images from the database using parameterized query
$stmtP = $dbP->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT 70");
$resultP = $stmtP->execute();
?>

    <div class="imagesC mb-2 mt-2">
      <?php while ($imageP = $resultP->fetchArray()): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imageA" href="image.php?artworkid=<?php echo $imageP['id']; ?>">
              <img class="imageI lazy-load <?php echo ($imageP['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
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
                  <li><button class="dropdown-item fw-bold" onclick="shareImageP(<?php echo $imageP['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageP['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>

                <!-- Modal -->
                <div class="modal fade" id="infoImage_<?php echo $imageP['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                <a class="w-100 h-100" href="image.php?artworkid=<?php echo $imageP['id']; ?>">
                                  <img class="rounded-4 object-fit-cover shadow lazy-load" height="400" width="100%" data-src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>">
                                </a>
                              </div>
                              <div class="btn-group mt-2 w-100">
                                <a class="btn btn-sm border-0" data-bs-toggle="collapse" href="#shareSection" role="button" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-share-fill icon-stroke-1"></i></a>
                                <button class="btn btn-sm fw-bold border-0"><i class="bi bi-bar-chart-line-fill"></i> <?php echo $imageP['view_count']?></button>
                                <button class="btn btn-sm border-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfoDesktop" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-info-circle-fill"></i></button>
                                <button class="btn btn-sm border-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDownload" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-download icon-stroke-1"></i></button>
                                <a href="image.php?artworkid=<?php echo $imageP['id']; ?>" class="btn btn-sm border-0"><i class="bi bi-eye-fill"></i></a>
                              </div>
                              <div class="container mt-2">
                                <?php
                                  if (!empty($imageP['tags'])) {
                                    $tags = explode(',', $imageP['tags']);
                                    foreach ($tags as $tag) {
                                      $tag = trim($tag);
                                        if (!empty($tag)) {
                                    ?>
                                      <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
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
                                  <h5 class="text-center fw-bold"><?php echo $imageP['title']?></h5>
                                  <p class="card-text fw-medium">
                                    <?php
                                      if (!empty($imageP['imgdesc'])) {
                                        $messageText = $imageP['imgdesc'];
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
                                  <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-twitter"></i>
                                  </a>
                                
                                  <!-- Line -->
                                  <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-line"></i>
                                  </a>
                                
                                  <!-- Email -->
                                  <a class="btn" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>">
                                    <i class="bi bi-envelope-fill"></i>
                                  </a>
                                
                                  <!-- Reddit -->
                                  <a class="btn" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-reddit"></i>
                                  </a>
                                
                                  <!-- Instagram -->
                                  <a class="btn" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-instagram"></i>
                                  </a>
                                
                                  <!-- Facebook -->
                                  <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-facebook"></i>
                                  </a>
                                </div>
                                <div class="btn-group w-100" role="group" aria-label="Share Buttons">
                                  <!-- WhatsApp -->
                                  <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-whatsapp"></i>
                                  </a>
    
                                  <!-- Pinterest -->
                                  <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-pinterest"></i>
                                  </a>
    
                                  <!-- LinkedIn -->
                                  <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-linkedin"></i>
                                  </a>
    
                                  <!-- Messenger -->
                                  <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-messenger"></i>
                                  </a>
    
                                  <!-- Telegram -->
                                  <a class="btn" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-telegram"></i>
                                  </a>
    
                                  <!-- Snapchat -->
                                  <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageP['id']; ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-snapchat"></i>
                                  </a>
                                </div>
                              </div>
                            </div>
                            <div class="collapse mt-2" id="collapseInfoDesktop">
                              <div class="card rounded-4 container">
                                <p class="text-center fw-semibold mt-2">Image Information</p>
                                <p class="text-start fw-semibold">Image ID: "<?php echo $imageP['id']?>"</p>
                                <?php
                                  $total_image_size = 0; // Initialize a variable to keep track of total image size
                                  
                                  // Calculate and display image size and dimensions for the main image
                                  $imageP_size = round(filesize('images/' . $imageP['filename']) / (1024 * 1024), 2);
                                  $total_image_size += $imageP_size; // Add the main image size to the total
                                  list($width, $height) = getimagesize('images/' . $imageP['filename']);
                                  echo "<p class='text-start fw-semibold'>Image data size: " . $imageP_size . " MB</p>";
                                  echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                                  echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $imageP['filename'] . "'>View original image</a></p>";
                                  
                                  // Assuming you have a separate query to fetch child images
                                  $child_images_result = $db->query("SELECT filename FROM image_child WHERE image_id = " . $imageP['id']);
                                  
                                  while ($child_image = $child_images_result->fetchArray()) {
                                    $child_image_size = round(filesize('images/' . $child_image['filename']) / (1024 * 1024), 2);
                                    $total_image_size += $child_image_size; // Add child image size to the total
                                    list($child_width, $child_height) = getimagesize('images/' . $child_image['filename']);
                                    echo "<p class='text-start fw-semibold'>Child Image data size: " . $child_image_size . " MB</p>";
                                    echo "<p class='text-start fw-semibold'>Child Image dimensions: " . $child_width . "x" . $child_height . "</p>";
                                    echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $child_image['filename'] . "'>View original child image</a></p>";
                                  }
                                
                                  // Display the total image size after processing all images
                                  echo "<p class='text-start fw-semibold'>Total Image data size: " . $total_image_size . " MB</p>";
                                ?>
                              </div>
                            </div>
                            <div class="collapse mt-2" id="collapseDownload">
                              <a class="btn btn-primary fw-bold rounded-4 w-100" href="#" onclick="downloadWithProgressBar(<?php echo $imageP['id']; ?>, '<?php echo $imageP['title']; ?>')">
                                <i class="bi bi-download text-stroke"></i> download all images (<?php echo $total_image_size; ?> MB)
                              </a>
                              <div class="progress fw-bold mt-2 rounded-4" id="progressBarContainer_<?php echo $imageP['id']; ?>" style="height: 30px; display: none;">
                                <div id="progressBar_<?php echo $imageP['id']; ?>" class="progress-bar progress-bar progress-bar-animated fw-bold" style="width: 0; height: 30px;">0%</div>
                              </div>
                              <script>
                                function downloadWithProgressBar(artworkId, title) {
                                  var progressBar = document.getElementById('progressBar_' + artworkId);
                                  var progressBarContainer = document.getElementById('progressBarContainer_' + artworkId);
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
                                    downloadLink.download = title + '_image_id_' + artworkId + '.zip';
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
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End of Modal -->

              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>    
    <style>
      .imagesC {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      .imageA  {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .imageI {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .imagesC {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }
    </style>
    <script>
      function shareImageP(userId) {
        // Compose the share URL
        var shareUrl = 'image.php?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>