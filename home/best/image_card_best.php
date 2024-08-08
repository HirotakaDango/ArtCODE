    <div class="container-fluid px-5">
      <div id="imageCarouselUser" class="carousel slide" data-bs-ride="carousel">
        <h5 class="fw-bold text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">Popular Artworks</h5>
        <h6 class="text-white fw-bold small" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);">
          These images are displayed based on their view counts from <?php $time = isset($_GET['time']) ? $_GET['time'] : 'day'; echo $time === 'alltime' ? 'all time' : "this $time"; ?>. The more views an image has, the higher its ranking in this list.
        </h6>
        <div class="carousel-inner">
          <?php
          $totalImages = count($images);
          $slidesCount = ceil($totalImages / 5);
          
          for ($i = 0; $i < $slidesCount; $i++) :
            $startIndex = $i * 5;
            $endIndex = min($startIndex + 5, $totalImages);
          ?>
            <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>" data-bs-interval="false">
              <div class="position-relative">
                <div class="row row-cols-5 g-1">
                  <?php for ($j = $startIndex; $j < $endIndex; $j++) :
                    $imageU = $images[$j];
                    $image_id = $imageU['id'];
                    $image_url = $imageU['filename'];
                    $image_title = $imageU['title'];
                    $artist_name = htmlspecialchars($imageU['artist']);
                    $user_id = $imageU['user_id'];
                    $userPic = $imageU['pic'];
                    $views = $imageU['views']; // Get the views count
                    $current_image_id = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
                    
                    $artist_name = substr($artist_name, 0, 10);
                    $image_title = mb_substr($image_title, 0, 15, 'UTF-8');
                    
                    // Optionally, you can add an ellipsis if the string was truncated
                    if (mb_strlen($image_title, 'UTF-8') > 15) {
                      $image_title .= '...';
                    }
    
                    // Determine badge color
                    $badgeClass = '';
                    switch ($j) {
                      case 0:
                        $badgeClass = 'gold text-white shadow'; // Gold
                        break;
                      case 1:
                        $badgeClass = 'silver text-white shadow'; // Silver
                        break;
                      case 2:
                        $badgeClass = 'bronze text-white shadow'; // Bronze (you may need to define .bg-brown in your CSS)
                        break;
                      default:
                        $badgeClass = 'nothing text-white shadow'; // Default color for others
                    }
                  ?>
                    <div class="col position-relative">
                      <div class="position-relative">
                        <a href="/image.php?artworkid=<?php echo $image_id; ?>">
                          <div class="ratio ratio-1x1">
                            <img class="object-fit-cover rounded rounded-bottom-0" src="/thumbnails/<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($image_title); ?>" style="object-fit: cover;">
                          </div>
                        </a>
                        <!-- Badge for rank -->
                        <span class="position-absolute top-0 start-0 m-2 badge <?php echo $badgeClass; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);">No. <?php echo $j + 1; ?></span>
                        <!-- Badge for views count -->
                        <span class="position-absolute bottom-0 end-0 m-2 badge text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);"><?php echo $views; ?> views</span>
                      </div>
                      <div class="d-flex align-items-center p-2 bg-light rounded-bottom mx-0 text-dark">
                        <img class="rounded-circle object-fit-cover border border-1" width="40" height="40" src="/<?php echo !empty($userPic) ? $userPic : 'icon/profile.svg'; ?>" alt="Profile Picture" style="margin-top: -2px;">
                        <div class="ms-2">
                          <div class="fw-bold text-truncate" style="max-width: 150px;"><?php echo $image_title; ?></div>
                          <a class="fw-medium text-decoration-none text-dark link-body-emphasis small" href="#" type="button" data-bs-toggle="modal" data-bs-target="#userModalBest-<?php echo $user_id; ?>"><?php echo $artist_name; ?></a>
                        </div>
                      </div>
                    </div>
                    <div class="modal fade" id="userModalBest-<?php echo $user_id; ?>" tabindex="-1" aria-labelledby="userModalLabelBest" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-transparent border-0">
                          <div class="modal-body position-relative">
                            <a class="position-absolute top-0 end-0 m-4" href="/artist.php?id=<?php echo urlencode($user_id); ?>" target="_blank">
                              <i class="bi bi-box-arrow-up-right link-body-emphasis text-white" style="-webkit-text-stroke: 1px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                            </a>
                            <iframe src="/rows_columns/user_preview.php?id=<?php echo urlencode($user_id); ?>" class="rounded-4 p-0 shadow" width="100%" height="300" style="border: none;"></iframe>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endfor; ?>
                </div>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
    <style>
      .gold {
        background-color: gold;
      }
    
      .silver {
        background-color: silver;
      }
    
      .bronze {
        background-color: brown;
      }
      
      .nothing {
        background-color: lightgray;
      }
 
       .ratio-cover {
        position: relative;
        width: 100%;
        padding-bottom: 140%;
      }

      .ratio-cover img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    </style>