                <!-- Modal -->
                <div class="modal fade" id="infoImage_<?php echo $imageL['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen">
                    <div class="modal-content border-0">
                      <div class="modal-body p-0">
                        <style>
                          .icon-stroke-1 { -webkit-text-stroke: 1px; }
                          .icon-stroke-2 { -webkit-text-stroke: 2px; }
                          .icon-stroke-3 { -webkit-text-stroke: 3px; }
                        </style>
                        <div class="modal-body p-0">
                          <button type="button" class="btn border-0 link-body-emphasis z-3 position-fixed top-0 end-0 text-shadow" data-bs-dismiss="modal"><i class="bi bi-chevron-down fs-3" style="-webkit-text-stroke: 3px;"></i></button>
                          <div class="row g-0">
                            <div id="div1_<?php echo $imageL['id']; ?>" class="overflow-auto col-md-7 h-100 scrollable-div">
                              <div>
                                <a href="/image.php?artworkid=<?php echo $imageL['id']; ?>">
                                  <img class="object-fit-cover shadow lazy-load h-100 w-100" data-src="/thumbnails/<?php echo $imageL['filename']; ?>" alt="<?php echo $imageL['title']; ?>">
                                </a>
                              </div>
                            </div>
                            <div id="div2_<?php echo $imageL['id']; ?>" class="overflow-auto col-md-5 h-100 scrollable-div">
                              <div class="p-3">
                                <h5 class="text-center fw-bold my-4"><?= $imageL['title']; ?></h5>
                                <p class="text-start fw-medium" style="word-wrap: break-word;">
                                  <?php
                                    if (!empty($imageL['imgdesc'])) {
                                      $messageText = $imageL['imgdesc'];
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
                                <div class="btn-group mt-2 w-100">
                                  <a class="btn btn-sm border-0" data-bs-toggle="collapse" href="#shareSection" role="button" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-share-fill icon-stroke-1"></i></a>
                                  <button class="btn btn-sm fw-bold border-0"><i class="bi bi-bar-chart-line-fill"></i> <?php echo $imageL['view_count']?></button>
                                  <button class="btn btn-sm border-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfoDesktop" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-info-circle-fill"></i></button>
                                  <button class="btn btn-sm border-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDownload" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-download icon-stroke-1"></i></button>
                                  <a href="/image.php?artworkid=<?php echo $imageL['id']; ?>" class="btn btn-sm border-0"><i class="bi bi-eye-fill"></i></a>
                                </div>
                                <div class="collapse" id="shareSection">
                                  <p class="text-start fw-bold mt-3">share to:</p>
                                  <div class="card rounded-4 p-4">
                                    <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                                      <!-- Twitter -->
                                      <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-twitter"></i>
                                      </a>
                                    
                                      <!-- Line -->
                                      <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-line"></i>
                                      </a>
                                    
                                      <!-- Email -->
                                      <a class="btn" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>">
                                        <i class="bi bi-envelope-fill"></i>
                                      </a>
                                    
                                      <!-- Reddit -->
                                      <a class="btn" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-reddit"></i>
                                      </a>
                                    
                                      <!-- Instagram -->
                                      <a class="btn" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-instagram"></i>
                                      </a>
                                    
                                      <!-- Facebook -->
                                      <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-facebook"></i>
                                      </a>
                                    </div>
                                    <div class="btn-group w-100" role="group" aria-label="Share Buttons">
                                      <!-- WhatsApp -->
                                      <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-whatsapp"></i>
                                      </a>
        
                                      <!-- Pinterest -->
                                      <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-pinterest"></i>
                                      </a>
        
                                      <!-- LinkedIn -->
                                      <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-linkedin"></i>
                                      </a>
        
                                      <!-- Messenger -->
                                      <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-messenger"></i>
                                      </a>
        
                                      <!-- Telegram -->
                                      <a class="btn" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-telegram"></i>
                                      </a>
        
                                      <!-- Snapchat -->
                                      <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $imageL['id']; ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-snapchat"></i>
                                      </a>
                                    </div>
                                  </div>
                                </div>
                                <div class="collapse mt-2" id="collapseInfoDesktop">
                                  <div class="card rounded-4 container">
                                    <p class="text-center fw-medium mt-2">Metadata Information</p>
                                    <?php
                                      $total_image_size = 0; // Initialize a variable to keep track of the total image size
    
                                      // Define the base URL for your images
                                      $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/images/';
                                    
                                      // Define the local file system path to the image folder
                                      $imageL_folder_path = $_SERVER['DOCUMENT_ROOT'] . '/images/';
    
                                      // Calculate and display image size and dimensions for the main image
                                      $imageL_path = $imageL_folder_path . $imageL['filename'];
                                      $imageL_size = round(filesize($imageL_path) / (1024 * 1024), 2);
                                      $total_image_size += $imageL_size;
                                      list($width, $height) = getimagesize($imageL_path);
                                      echo "<div class='mb-3 row'>
                                              <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Image ID</label>
                                                <div class='col-sm-8'>
                                                  <input type='text' class='form-control-plaintext fw-bold' id='' value='" . $imageL['id'] . "' readonly>
                                                </div>
                                            </div>";
                  
                                      echo "<div class='mb-3 row'>
                                              <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Data Size</label>
                                              <div class='col-sm-8'>
                                                <input type='text' class='form-control-plaintext fw-bold' id='' value='{$imageL_size} MB' readonly>
                                              </div>
                                            </div>";
                  
                                      echo "<div class='mb-3 row'>
                                              <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Dimensions</label>
                                              <div class='col-sm-8'>
                                                <input type='text' class='form-control-plaintext fw-bold' id='' value='{$width}x{$height}' readonly>
                                              </div>
                                            </div>";
                                      echo "<p class='text-start fw-medium'><a class='text-decoration-none' href='" . $base_url . $imageL['filename'] . "'>View original image</a></p>";
                            
                                      // Assuming you have a separate query to fetch child images
                                      $child_images_result = $dbL->query("SELECT filename FROM image_child WHERE image_id = " . $imageL['id']);
    
                                      while ($child_image = $child_images_result->fetchArray()) {
                                        $child_imageP_path = $imageL_folder_path . $child_image['filename'];
                                        $child_image_size = round(filesize($child_imageP_path) / (1024 * 1024), 2);
                                        $total_image_size += $child_image_size;
                                        list($child_width, $child_height) = getimagesize($child_imageP_path);
                                        echo "<hr>";
                                        echo "<div class='mb-3 row'>
                                                <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Data Size</label>
                                                <div class='col-sm-8'>
                                                  <input type='text' class='form-control-plaintext fw-bold' id='' value='{$child_image_size} MB' readonly>
                                                </div>
                                               </div>";
                
                                        echo "<div class='mb-3 row'>
                                                <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Dimensions</label>
                                                <div class='col-sm-8'>
                                                  <input type='text' class='form-control-plaintext fw-bold' id='' value='{$child_width}x{$child_height}' readonly>
                                                </div>
                                              </div>";
                                        echo "<p class='text-start fw-medium'><a class='text-decoration-none' href='" . $base_url . $child_image['filename'] . "'>View original child image</a></p>";
                                      }
    
                                      // Display the total image size after processing all images
                                      echo "<hr>";
                                      echo "<div class='mb-3 row'>
                                              <label for='' class='col-sm-4 col-form-label text-nowrap fw-medium'>Total Size</label>
                                              <div class='col-sm-8'>
                                                <input type='text' class='form-control-plaintext fw-bold' id='' value='{$total_image_size} MB' readonly>
                                              </div>
                                             </div>";
                                    ?>
                                  </div>
                                </div>
                                <div class="collapse mt-2" id="collapseDownload">
                                  <a class="btn btn-primary fw-bold rounded-4 w-100" href="#" onclick="downloadWithProgressBar(<?php echo $imageL['id']; ?>, '<?php echo $imageL['title']; ?>')">
                                    <i class="bi bi-download text-stroke"></i> download all images (<?php echo $total_image_size; ?> MB)
                                  </a>
                                  <div class="progress fw-bold mt-2 rounded-4" id="progressBarContainer_<?php echo $imageL['id']; ?>" style="height: 30px; display: none;">
                                    <div id="progressBar_<?php echo $imageL['id']; ?>" class="progress-bar progress-bar progress-bar-animated fw-bold" style="width: 0; height: 30px;">0%</div>
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
                                      xhr.open('GET', '/download_images.php?artworkid=' + artworkId, true);
    
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
                                    <small>2. If you found download error or failed, <a class="text-decoration-none" href="/download_images.php?artworkid=<?php echo $imageL['id']; ?>">click this link</a> for third option if download all images error or failed.</small>
                                  </p>
                                  <p class="fw-bold text-center container">
                                    <small>3. If you found problem where the zip contain empty file or 0b, download the images manually.</small>
                                  </p>
                                  <p class="fw-bold text-center container">
                                    <small>4. Server sometimes have problem with file and folder path, download manually is the best option if this happening.</small>
                                  </p>
                                </div>
                                <div class="card shadow border-0 rounded-4 bg-body-tertiary mt-3">
                                  <div class="card-body">
                                    <!-- Tags -->
                                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-tags-fill"></i> Tags</h6>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                      <?php
                                      if (!empty($imageL['tags'])) {
                                        $tags = explode(',', $imageL['tags']);
                                        foreach ($tags as $tag) {
                                          $tag = trim($tag);
                                          if (!empty($tag)) {
                                            ?>
                                            <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                                              <i class="bi bi-tag-fill"></i> <?php echo htmlspecialchars($tag); ?>
                                            </a>
                                            <?php
                                          }
                                        }
                                      } else {
                                        echo "<p class='text-muted'>No tags available.</p>";
                                      }
                                      ?>
                                    </div>
                                
                                    <!-- Characters -->
                                    <?php if (!empty($imageL['characters'])): ?>
                                      <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-people-fill"></i> Characters</h6>
                                      <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php
                                        $characters = explode(',', $imageL['characters']);
                                        foreach ($characters as $character) {
                                          $character = trim($character);
                                          if (!empty($character)) {
                                            ?>
                                            <a href="character/?character=<?php echo urlencode($character); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                                              <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($character); ?>
                                            </a>
                                            <?php
                                          }
                                        }
                                        ?>
                                      </div>
                                    <?php endif; ?>
                                
                                    <!-- Parodies -->
                                    <?php if (!empty($imageL['parodies'])): ?>
                                      <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-journals"></i> Parodies</h6>
                                      <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php
                                        $parodies = explode(',', $imageL['parodies']);
                                        foreach ($parodies as $parody) {
                                          $parody = trim($parody);
                                          if (!empty($parody)) {
                                            ?>
                                            <a href="parody/?parody=<?php echo urlencode($parody); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                                              <i class="bi bi-journal"></i> <?php echo htmlspecialchars($parody); ?>
                                            </a>
                                            <?php
                                          }
                                        }
                                        ?>
                                      </div>
                                    <?php endif; ?>
                                
                                    <!-- Group -->
                                    <?php if (!empty($imageL['group'])): ?>
                                      <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-person-fill"></i> Group</h6>
                                      <div class="d-flex flex-wrap gap-2">
                                        <?php
                                        $groups = explode(',', $imageL['group']);
                                        foreach ($groups as $group) {
                                          $group = trim($group);
                                          if (!empty($group)) {
                                            ?>
                                            <a href="group/?group=<?php echo urlencode($group); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                                              <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($group); ?>
                                            </a>
                                            <?php
                                          }
                                        }
                                        ?>
                                      </div>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <script>
                          window.addEventListener('DOMContentLoaded', (event) => {
                            // Output PHP variable properly within JavaScript
                            const imageId = <?= $imageL['id']; ?>;
                        
                            // Select div elements with appropriate IDs
                            const div1 = document.getElementById('div1_' + imageId);
                            const div2 = document.getElementById('div2_' + imageId);
                    
                            function addClassBasedOnViewport() {
                              if (window.innerWidth >= 768) {
                                div1.classList.add('vh-100');
                                div2.classList.add('vh-100');
                              } else {
                                div1.classList.remove('vh-100');
                                div2.classList.remove('vh-100');
                              }
                            }
                    
                            // Call the function initially
                            addClassBasedOnViewport();
                    
                            // Call the function whenever the window is resized
                            window.addEventListener('resize', addClassBasedOnViewport);
                          });
                        </script>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End of Modal -->