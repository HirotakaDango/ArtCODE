<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['title']; ?></title>
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <meta property="og:url" content="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $_GET['title']; ?>">
    <meta property="og:image" content="<?php echo $web; ?>/icon/favicon.png">
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container my-3">
      <?php
      // Check if title and uid parameters are provided
      if (isset($_GET['title']) && isset($_GET['uid'])) {
        $episode_name = $_GET['title'];
        $user_id = $_GET['uid'];
        
        // Fetch JSON data from api_manga_title.php with title and uid parameters
        $json = file_get_contents($web . '/api_manga_title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id);
        $data = json_decode($json, true);

        // Check if the data is an array and not empty
        if (is_array($data) && !empty($data)) {
          $first_cover = $data['first_cover'];
          $latest_cover = $data['latest_cover'];
          $total_view_count = $data['total_view_count'];
          $total_count = $data['total_count'];
          $images = $data['images'];
          $artist_name = $latest_cover['artist'];
          $artistImageCount = $data['artist_image_count'];
          $tags = $data['tags'];
          $groupCounts = $data['group_counts'];
          $categoriesCounts = $data['categories_counts'];
          $languageCounts = $data['language_counts'];
          $tagString = implode(', ', array_keys($tags));

          $groupName = '';
          $groupCount = 0;
          if (!empty($groupCounts)) {
            $firstGroup = reset($groupCounts); // Get the first group from the $groupCounts array
            $groupName = $firstGroup['group']; // Get the group name
            $groupCount = $firstGroup['count']; // Get the group count
          }

          $categoriesName = '';
          $categoriesCount = 0;
          if (!empty($categoriesCounts)) {
            $firstCategory = reset($categoriesCounts); // Get the first category from the $categoriesCounts array
            $categoriesName = $firstCategory['categories']; // Get the category name
            $categoriesCount = $firstCategory['count']; // Get the category count
          }
        
          $languageName = '';
          $languageCount = 0;
          if (!empty($languageCounts)) {
            $firstLanguage = reset($languageCounts); // Get the first language from the $languageCounts array
            $languageName = $firstLanguage['language']; // Get the language name
            $languageCount = $firstLanguage['count']; // Get the language count
          }
          ?>
          <div class="row">
            <div class="col-md-4">
              <div class="cover-image">
                <a data-bs-toggle="modal" data-bs-target="#originalImage"><img class="rounded w-100 h-100" src="<?= $web . '/thumbnails/' . $latest_cover['filename']; ?>" alt="<?= $latest_cover['title']; ?>"></a>
              </div>
            </div>
            <div class="col-md-8">
              <h1 class="mb-4 fw-bold mt-4 mt-md-0"><?php echo $episode_name; ?></h1>
              <div class="mb-4">
                <p class="text-white shadowed-text small fw-medium" style="word-break: break-word;">
                  <?php
                    if (!empty($first_cover['imgdesc'])) {
                      $messageText = $first_cover['imgdesc'];
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $charLimit = 400; // Set your character limit

                      if (strlen($formattedText) > $charLimit) {
                        $limitedText = substr($formattedText, 0, $charLimit);
                        echo '<span id="limitedText">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                        echo '<span id="more" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                        echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0 text-white" onclick="myFunction()" id="myBtn"><small>read more</small></button>';
                      } else {
                        // If the text is within the character limit, just display it with line breaks.
                        echo nl2br($formattedText);
                      }
                    } else {
                      echo "User description is empty.";
                    }
                  ?>
                  <script>
                    function myFunction() {
                      var dots = document.getElementById("limitedText");
                      var moreText = document.getElementById("more");
                      var btnText = document.getElementById("myBtn");

                      if (moreText.style.display === "none") {
                        dots.style.display = "none";
                        moreText.style.display = "inline";
                        btnText.innerHTML = "read less";
                      } else {
                        dots.style.display = "inline";
                        moreText.style.display = "none";
                        btnText.innerHTML = "read more";
                      }
                    }
                  </script>
                </p>
              </div>
              <div class="mb-3">
                <div class="input-group">
                  <input type="text" id="urlInput2" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="form-control border-2 fw-bold" readonly style="display: none;">
                  <button class="btn btn-sm bg-transparent border-0 rounded fw-bold p-0 link-body-emphasis text-muted" onclick="copyUrlToClipboard()">
                    <small>?title=<?php echo $_GET['title']; ?>&uid=<?php echo $_GET['uid']; ?> <i class="bi bi-copy"></i></small>
                  </button>
                </div>
                <script>
                  function copyUrlToClipboard() {
                    var urlInput2 = document.getElementById('urlInput2');
                    var tempInput = document.createElement('input');
                    tempInput.value = urlInput2.value;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    tempInput.setSelectionRange(0, 99999); // For mobile devices
                
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                  }
                </script>
              </div>
              <div class="mb-2 row">
                <label for="artist" class="col-2 col-form-label text-nowrap fw-medium">Artist</label>
                <div class="col-10">
                  <div class="btn-group">
                    <a href="index.php?artist=<?php echo urlencode($artist_name); ?>&uid=<?php echo $user_id; ?>" class="btn bg-secondary-subtle fw-bold"><?php echo $artist_name; ?></a>
                    <a href="#" class="btn bg-body-tertiary fw-bold" disabled><?php echo $artistImageCount; ?></a>
                  </div>
                </div>
              </div>
              <?php if (isset($groupCounts) && !empty($groupCounts)): ?>
                <div class="mb-2 row">
                  <label for="artist" class="col-2 col-form-label text-nowrap fw-medium">Group</label>
                  <div class="col-10">
                    <div class="btn-group">
                      <a href="index.php?group=<?php echo urlencode($groupName); ?>" class="btn bg-secondary-subtle fw-bold"><?php echo $groupName; ?></a>
                      <a href="#" class="btn bg-body-tertiary fw-bold" disabled><?php echo $groupCount; ?></a>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="row">
                <label for="tags" class="col-2 col-form-label text-nowrap fw-medium">Tags</label>
                <div class="col-10">
                  <?php foreach($tags as $tag => $count): ?>
                    <div class="btn-group mb-2 me-1">
                      <a href="index.php?tag=<?php echo urlencode($tag); ?>" class="btn bg-secondary-subtle fw-bold">
                        <?php echo $tag; ?>
                      </a>
                      <a href="#" class="btn bg-body-tertiary fw-bold">
                        <?php echo $count; ?>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php if (isset($categoriesCount) && !empty($categoriesCount)): ?>
                <div class="mb-2 row">
                  <label for="artist" class="col-2 col-form-label text-nowrap fw-medium">Categories</label>
                  <div class="col-10">
                    <div class="btn-group">
                      <a href="index.php?categories=<?php echo urlencode($categoriesName); ?>" class="btn bg-secondary-subtle fw-bold"><?php echo $categoriesName; ?></a>
                      <a href="#" class="btn bg-body-tertiary fw-bold" disabled><?php echo $categoriesCount; ?></a>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <?php if (isset($languageCount) && !empty($languageCount)): ?>
                <div class="mb-2 row">
                  <label for="artist" class="col-2 col-form-label text-nowrap fw-medium">Language</label>
                  <div class="col-10">
                    <div class="btn-group">
                      <a href="index.php?language=<?php echo urlencode($languageName); ?>" class="btn bg-secondary-subtle fw-bold"><?php echo $languageName; ?></a>
                      <a href="#" class="btn bg-body-tertiary fw-bold" disabled><?php echo $languageCount; ?></a>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="mb-2 row">
                <label for="pages" class="col-2 col-form-label text-nowrap fw-medium">Pages</label>
                <div class="col-10">
                  <p class="form-control-plaintext fw-bold" id="pages"><?php echo $total_count; ?></p>
                </div>
              </div>
              <div class="mb-2 row">
                <label for="views" class="col-2 col-form-label text-nowrap fw-medium">Views</label>
                <div class="col-10">
                  <p class="form-control-plaintext fw-bold" id="views"><?php echo $total_view_count; ?></p>
                </div>
              </div>
              <div class="mb-2 row">
                <label for="date" class="col-2 col-form-label text-nowrap fw-medium">Date</label>
                <div class="col-10">
                  <p class="form-control-plaintext fw-bold" id="date"><?php echo date('Y/m/d', strtotime($latest_cover['date'])); ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container mb-5">
      <h5 class="my-3 fw-bold">All chapters in <?php echo $episode_name; ?> by <?php echo $artist_name; ?> (<?php echo count($images); ?>)</h5>
      <div class="btn-group mb-2 w-100 gap-2">
        <a class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50" href="view.php?title=<?php echo $episode_name; ?>&uid=<?php echo $user_id; ?>&id=<?php echo $first_cover['id']; ?>&page=1">read first</a>
        <button class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50 d-none d-md-block" href="#" data-bs-toggle="modal" data-bs-target="#shareLink">share</button>
        <a class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50" href="<?php echo $web; ?>/episode/?title=<?php echo $episode_name; ?>&uid=<?php echo $user_id; ?>" target="_blank">original</a>
      </div>
      <button class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-100 mb-2 d-md-none" href="#" data-bs-toggle="modal" data-bs-target="#shareLink">share</button>
      <div>
        <div>
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xxl-4 g-1">
            <?php foreach ($images as $image) : ?>
              <div class="col">
                <div class="card border-0 bg-body-tertiary shadow h-100 rounded-4">
                  <a class="text-decoration-none link-body-emphasis" href="view.php?title=<?php echo $image['episode_name']; ?>&uid=<?php echo $image['userid']; ?>&id=<?php echo $image['id']; ?>&page=1">
                    <div class="row g-0">
                      <div class="col-4">
                        <div class="ratio ratio-1x1 rounded-4">
                          <img class="object-fit-cover lazy-load h-100 w-100 rounded-start-4" data-src="<?= $web . '/thumbnails/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                        </div>
                      </div>
                      <div class="col-8">
                        <div class="card-body d-flex align-items-center justify-content-start h-100">
                          <div>
                            <h6 class="card-title fw-bold"><?php echo (!is_null($image['title']) && mb_strlen($image['title'], 'UTF-8') > 20) ? mb_substr($image['title'], 0, 20, 'UTF-8') . '...' : $image['title']; ?></h6>
                            <h6 class="card-title fw-bold small"><?php echo $image['view_count']; ?> views</h6>
                          </div>
                        </div>
                      </div>
                    </div>
                  </a>
                </div>
              </div>

              <div class="col d-none">
                <div class="card border-0 rounded-4">
                  <a href="view.php?title=<?php echo $image['episode_name']; ?>&uid=<?php echo $image['userid']; ?>&id=<?php echo $image['id']; ?>&page=1" class="text-decoration-none">
                    <div class="ratio ratio-cover">
                      <img class="rounded rounded-bottom-0 object-fit-cover lazy-load" data-src="<?= $web . '/thumbnails/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                    </div>
                    <h6 class="text-center fw-bold text-white text-decoration-none bg-dark-subtle p-2 rounded rounded-top-0"><?php echo $image['title']; ?></h6>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php } else { ?>
          <p>No data found.</p>
        <?php }
      } else { ?>
        <p>Missing title or uid parameter.</p>
      <?php } ?>
    </div>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
            <div class="input-group">
              <input type="text" id="urlInput1" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="form-control border-2 fw-bold" readonly>
              <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                <i class="bi bi-clipboard-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="<?= $web . '/images/' . $latest_cover['filename']; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="<?= $web . '/images/' . $latest_cover['filename']; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    <script>
      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }
    </script>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "<?php echo $web; ?>/icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder; // Apply default placeholder
          imageObserver.observe(image);
          image.style.filter = "blur(5px)"; // Apply initial blur to all images
          image.addEventListener("load", function() {
            image.style.filter = "none"; // Remove blur after image loads
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
              document.removeEventListener("scroll", lazyload);
              window.removeEventListener("resize", lazyload);
              window.removeEventListener("orientationChange", lazyload);
            }
          }, 20);
        }

        document.addEventListener("scroll", lazyload);
        window.addEventListener("resize", lazyload);
        window.addEventListener("orientationChange", lazyload);
      }
    </script>
  </body>
</html>